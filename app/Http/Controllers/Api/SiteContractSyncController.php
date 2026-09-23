<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteContract;
use App\Services\Hia\HiaContractMapper;
use App\Services\Hia\HiaContractService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteContractSyncController extends Controller
{
    public function index()
    {
        return response()->json(SiteContract::with('site')->get());
    }

    public function store(Request $request, HiaContractService $hia, HiaContractMapper $mapper)
    {
        // Debug
        $saveEnabled = true;
        $today = Carbon::now();
        $cc = Company::find(3);

        // Logging
        Log::channel('single')->debug('========= HIA Contract ==========');
        $log = "Zoho Sync: " . $today->format('Y-m-d h:ia') . "  (" . request('username') . ")\n";
        if (!$saveEnabled) $log .= "Save: DISABLED\n";

        // Min required fields
        $code = request('code');
        $companyId = request('company_id');
        $site = ($code && $companyId) ? Site::where('code', $code)->where('company_id', $companyId)->first() : null;

        // Get Site
        if (!$site) return $this->error('Invalid data', 404);

        // Get or Create Contract
        $contract = SiteContract::where('site_id', $site->id)->first();
        $hadExistingHiaContract = filled($contract?->hia_contract_id);
        $action = 'Update';

        if (!$contract) {
            $action = 'Create';
            if ($saveEnabled) $contract = SiteContract::create(['site_id' => $site->id, 'status' => 1]);
        }

        if (!$contract) return $this->error('Unable to create or locate SiteContract', 500);

        $fields = [
            'owner1_title', 'owner1_name', 'owner1_mobile', 'owner1_email', 'owner1_abn',
            'owner2_title', 'owner2_name', 'owner2_mobile', 'owner2_email', 'owner2_abn',
            'owner_address', 'owner_suburb', 'owner_state', 'owner_postcode',
            'contract_price', 'contract_net', 'contract_gst', 'deposit', 'building_period', 'initial_period',
            'land_lot', 'land_dp', 'land_title', 'land_address', 'land_suburb', 'land_state', 'land_postcode',
            'warranty_amount', 'special_conditions', 'special_conditions_full', 'stages',
        ];

        $data = [];
        foreach ($fields as $field) {
            if (!$request->has($field)) continue;
            $value = $request->input($field);
            $data[$field] = ($value === '') ? null : $value;
        }

        // HANDLE STAGES
        if ($request->has('stages') && is_array($request->stages)) {
            $data['stages'] = collect($request->stages)->sortBy('stage_no')->values()->toArray();
        }

        if ($saveEnabled && count($data)) $contract->update($data);

        // Refresh relationships/data before mapping to HIA
        $site->refresh();
        $contract->refresh();

        // -----------------------------
        // Sync to HIA
        // -----------------------------
        $hiaResult = null;
        $legacySkipped = false;

        try {
            // Use site + related site_contract data in mapper
            $hiaData = $mapper->fromSite($site);

            if ($saveEnabled) {
                if ($contract->hia_contract_id) {
                    // Update existing HIA contract
                    $existingHiaContract = $hia->getContractById((int) $contract->hia_contract_id);

                    if ((int) ($existingHiaContract['Status'] ?? 0) === 4) {
                        $legacySkipped = true;
                        $hiaResult = [
                            'contract_id' => $contract->hia_contract_id,
                            'status' => 4,
                            'sync_action' => 'skipped_legacy',
                            'message' => 'Legacy HIA contract is read-only and was not changed.',
                        ];
                    } else {
                        $hiaContract = $hia->updateFetchedContract($existingHiaContract, $hiaData);
                    }
                } else {
                    // Create new HIA contract
                    $hiaContract = $hia->createContractFromTemplateAndData(9022, $hiaData);
                }

                if (!$legacySkipped) {
                    $contractId = (int) ($hiaContract['ContractId'] ?? 0);
                    $pdfPath = "site/{$site->id}/contracts/hia-contract-{$contractId}.pdf";
                    Storage::disk('local')->put($pdfPath, $hia->getContractPdf($contractId));

                    $contract->update([
                        'hia_contract_id' => $contractId,
                        'hia_template_id' => $hiaContract['TemplateId'] ?? null,
                        'hia_xml' => $hiaContract['Source'] ?? null,
                        'hia_pdf' => $pdfPath,
                    ]);

                    $hiaResult = [
                        'contract_id' => $contractId,
                        'template_id' => $hiaContract['TemplateId'] ?? null,
                        'pdf_path' => $pdfPath,
                        'sync_action' => $hadExistingHiaContract ? 'updated' : 'created',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::channel('single')->debug('---------- SiteContract saved, but HIA sync failed ----------');
            return $this->error('SiteContract saved, but HIA sync failed: ' . $e->getMessage(), 500);
        }

        $message = $legacySkipped
            ? "{$action}d SafeWorksite contract. Legacy HIA contract was not changed."
            : "{$action}d HIA contract";

        return $this->success($message, [
            'site_id' => $site->id,
            'site_contract_id' => $contract->id,
            'updated_fields' => array_keys($data),
            'hia' => $hiaResult,
        ]);
    }

    public function show(SiteContract $siteContract)
    {
        return response()->json($siteContract->load('site'));
    }

    protected function success($message, $data = [], $status = 200)
    {
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    protected function error($message, $status = 400)
    {
        return response()->json(['status' => $status, 'message' => $message], $status);
    }
}
