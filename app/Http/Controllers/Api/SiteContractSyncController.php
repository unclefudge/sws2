<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteContract;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SiteContractSyncController extends Controller
{
    public function index()
    {
        return response()->json(SiteContract::with('site')->get());
    }

    public function store(Request $request)
    {
        // Debug
        $save_enabled = true;
        $overwrite_with_blank = false;
        $today = Carbon::now();
        $cc = Company::find(3);

        // Logging
        $log = "Zoho Sync: " . $today->format('Y-m-d h:ia') . "  (" . request('username') . ")\n";
        $log = '';
        if (!$save_enabled) $log .= "Save: DISABLED\n";
        if ($overwrite_with_blank) $log .= "Save: Overwrite With Blank\n";

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

        $data = [];
        foreach ($fields as $field) {
            if (!request()->has($field)) continue;

            $value = request($field);
            $data[$field] = ($value === '') ? null : $value;
        }

        if ($save_enabled)
            $contract->update($data);

        return $this->success("{$action}d site contract", ['site_id' => $site->id, 'site_contract_id' => $contract->id, 'updated_fields' => array_keys($data),]);
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