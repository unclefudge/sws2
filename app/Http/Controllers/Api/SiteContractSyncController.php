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
        $unavailableContractId = null;

        try {
            // Use site + related site_contract data in mapper
            $hiaData = $mapper->fromSite($site);

            if ($saveEnabled) {
                if ($contract->hia_contract_id) {
                    // Update existing HIA contract
                    $existingHiaContract = $hia->getContractById((int) $contract->hia_contract_id);

                    if ((int) ($existingHiaContract['Status'] ?? 0) === 4) {
                        // A contract-specific Zoho sync means Cape Cod now wants a
                        // genuine contract. HIA status 4 means the existing contract
                        // is no longer available/editable in their online portal, so
                        // create a replacement rather than attempting to update it.
                        $unavailableContractId = $contract->hia_contract_id;
                        $hiaContract = $hia->createContractFromTemplateAndData(9022, $hiaData);
                    } else {
                        $hiaContract = $hia->updateFetchedContract($existingHiaContract, $hiaData);
                    }
                } else {
                    // Create new HIA contract
                    $hiaContract = $hia->createContractFromTemplateAndData(9022, $hiaData);
                }

                $contractId = (int) ($hiaContract['ContractId'] ?? 0);

                if ($contractId <= 0) {
                    throw new \RuntimeException('HIA returned no ContractId after synchronisation.');
                }

                $updateData = [
                    'hia_contract_id' => $contractId,
                    'hia_template_id' => $hiaContract['TemplateId'] ?? null,
                    'hia_xml' => $hiaContract['Source'] ?? null,
                ];

                if ($unavailableContractId) {
                    $auditNote = 'HIA contract ' . $unavailableContractId . ' (status 4 - not available in HIA portal) replaced by HIA contract ' . $contractId . ' following Zoho sync on ' . now()->format('d/m/Y H:i') . '.';
                    $updateData['notes'] = trim(implode("\n", array_filter([$contract->notes, $auditNote])));
                }

                // Save the confirmed HIA ID before fetching its PDF. If the PDF call
                // fails, a retry updates this contract instead of creating a duplicate.
                $contract->update($updateData);

                $pdfPath = null;
                $pdfError = null;

                try {
                    $pdfPath = "site/{$site->id}/contracts/hia-contract-{$contractId}.pdf";
                    Storage::disk('local')->put($pdfPath, $hia->getContractPdf($contractId));
                    $contract->update(['hia_pdf' => $pdfPath]);
                } catch (\Throwable $pdfException) {
                    $pdfError = $pdfException->getMessage();
                    Log::warning('HIA contract PDF refresh failed after Zoho sync', ['hia_contract_id' => $contractId, 'message' => $pdfError]);
                }

                $hiaResult = [
                    'contract_id' => $contractId,
                    'template_id' => $hiaContract['TemplateId'] ?? null,
                    'pdf_path' => $pdfPath,
                    'sync_action' => $unavailableContractId ? 'created_from_unavailable' : ($hadExistingHiaContract ? 'updated' : 'created'),
                    'replaced_contract_id' => $unavailableContractId,
                    'pdf_error' => $pdfError,
                ];
            }
        } catch (\Throwable $e) {
            Log::channel('single')->debug('---------- SiteContract saved, but HIA sync failed ----------');
            return $this->error('SiteContract saved, but HIA sync failed: ' . $e->getMessage(), 500);
        }

        $message = $unavailableContractId
            ? "Created HIA contract {$hiaResult['contract_id']} to replace portal-unavailable contract {$unavailableContractId}"
            : "{$action}d HIA contract";

        if (!empty($hiaResult['pdf_error'])) {
            $message .= '. Contract saved, but PDF refresh failed';
        }

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
