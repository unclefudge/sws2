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
        $save_enabled = true;
        $today = Carbon::now();
        $cc = Company::find(3);

        // Logging
        Log::channel('single')->debug('========= HIA Contract ==========');
        $log = "Zoho Sync: " . $today->format('Y-m-d h:ia') . "  (" . request('username') . ")\n";
        $log = '';
        if (!$save_enabled) $log .= "Save: DISABLED\n";

        // Min required fields
        $code = request('code');
        $cid = request('company_id');

        // Get Site
        $site = null;
        if ($code && $cid)
            $site = Site::where('code', request('code'))->where('company_id', request('company_id'))->first();

        if (!$site)
            return $this->error('Invalid data', 404);

        // Get or Create Contract
        $contract = SiteContract::where('site_id', $site->id)->first();
        $action = 'update';

        if (!$contract) {
            $action = 'create';

            if ($save_enabled)
                $contract = SiteContract::create(['site_id' => $site->id, 'status' => 1,]);
        }

        if (!$contract)
            return $this->error('Unable to create or locate SiteContract', 500);

        $fields = [
            'owner1_title', 'owner1_name', 'owner1_mobile', 'owner1_email', 'owner1_abn',
            'owner2_title', 'owner2_name', 'owner2_mobile', 'owner2_email', 'owner2_abn',
            'owner_address', 'owner_suburb', 'owner_state', 'owner_postcode',
            'contract_price', 'contract_net', 'contract_gst', 'deposit', 'building_period', 'initial_period',
            'land_lot', 'land_dp', 'land_title', 'land_address', 'land_suburb', 'land_state', 'land_postcode', //'hia_template_id',
        ];

        $data = ['action' => !empty($contract->hia_contract_id) ? 'created' : 'updated'];
        foreach ($fields as $field) {
            if (!request()->has($field)) continue;

            $value = request($field);
            $data[$field] = ($value === '') ? null : $value;
        }
        Log::channel('single')->debug($data);

        if ($save_enabled && count($data))
            $contract->update($data);

        // Refresh relationships/data before mapping to HIA
        $site->refresh();
        $contract->refresh();

        // -----------------------------
        // Sync to HIA
        // -----------------------------
        $hiaResult = null;
        $hiaPdfStored = null;
        $hadExistingHiaContract = !empty($contract->hia_contract_id);

        try {
            // Use site + related site_contract data in mapper
            $hiaData = $mapper->fromSite($site);
            ray($hiaData);

            if ($save_enabled) {
                if ($contract->hia_contract_id) {
                    // Update existing HIA contract
                    $hiaContract = $hia->updateContractFromData((int)$contract->hia_contract_id, $hiaData);
                } else {
                    // Create new HIA contract
                    $hiaContract = $hia->createContractFromTemplateAndData(9022, $hiaData);
                }
                ray($hiaContract);

                // Save HIA identifiers / XML
                $updateContractData = [
                    'hia_contract_id' => $hiaContract['ContractId'] ?? null,
                    'hia_template_id' => $hiaContract['TemplateId'] ?? null,
                    'hia_xml' => $hiaContract['Source'] ?? null,
                ];

                // Try fetch/store PDF
                if (!empty($hiaContract['ContractId'])) {
                    $pdfBinary = $hia->getContractPdf((int)$hiaContract['ContractId']);

                    $pdfPath = "site/{$site->id}/contracts/hia-contract-" . $hiaContract['ContractId'] . ".pdf";
                    Storage::disk('local')->put($pdfPath, $pdfBinary);

                    $updateContractData['hia_pdf'] = $pdfPath;
                    $hiaPdfStored = $pdfPath;
                }

                $contract->update($updateContractData);

                $hiaResult = [
                    'contract_id' => $hiaContract['ContractId'] ?? null,
                    'template_id' => $hiaContract['TemplateId'] ?? null,
                    'pdf_path' => $hiaPdfStored,
                    'sync_action' => $hadExistingHiaContract ? 'updated' : 'created',
                ];
            }
        } catch (\Throwable $e) {
            return $this->error('SiteContract saved, but HIA sync failed: ' . $e->getMessage(), 500);
        }

        return $this->success("{$action}d site contract", [
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
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data,], $status);
    }

    protected function error($message, $status = 400)
    {
        return response()->json(['status' => $status, 'message' => $message,], $status);
    }
}