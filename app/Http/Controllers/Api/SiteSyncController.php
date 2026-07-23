<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\ZohoSiteLog;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestosRegister;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;

class SiteSyncController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Site::all();
        return SiteResource::collection(Site::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $site_request = request()->all();

        // Debug
        $save_enabled = true;
        // false: ignore explicit blank values from Zoho and preserve existing SWS data.
        // true: allow explicit blank values from Zoho to clear existing SWS data.
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

        if ($code && $cid) {
            $site = Site::where('code', request('code'))->where('company_id', request('company_id'))->first();
            $action = 'update';

            $job_stage = request('job_stage');
            $council_area = request('council_area');
            // Create new site if required (except for Stages '950 + 160')
            if (!$site && !in_array($job_stage, ['950 Sales Dropout', '160 On Hold'])) {
                $action = 'create';
                if ($save_enabled) {
                    $status = ($job_stage == '900 Dead Filed') ? 0 : -1;
                    // Assigned 'TO BE ALLOCATED' as Supervisor;
                    $site = Site::create(['name' => request('name'), 'code' => request('code'), 'state' => 'NSW', 'supervisor_id' => '136', 'status' => $status, 'company_id' => $cid, 'created_by' => 1, 'updated_by' => 1]);

                    // Create Equipment Location
                    $location = EquipmentLocation::where('site_id', $site->id)->first();
                    if (!$location)
                        $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => "1", 'company_id' => $cid, 'created_by' => 1, 'updated_by' => 1]);

                    $asb = SiteAsbestosRegister::where('site_id', $site->id)->first();
                    if (!$asb)
                        $asb = SiteAsbestosRegister::create(['site_id' => $site->id, 'version' => '1.0']);

                    $foc = SiteFoc::where('site_id', $site->id)->first();
                    if (!$foc) {
                        $foc = SiteFoc::create(['site_id' => $site->id, 'stage' => 'Upcoming', 'status' => '1', 'created_by' => 1, 'updated_by' => 1,]);
                        Action::create(['action' => "FOC created", 'table' => 'site_foc', 'table_id' => $foc->id]);
                    }

                }
            }


            if ($site) {
                //
                // Compare Zoho data with SWS
                //
                $diffDat = [];  // array used to hold field => data that needs updating
                $diffTxt = [];  // array used to hold the difference in text format for logging

                //
                // A few special cases to update for Site Status + Supervisor
                //
                if ($job_stage) {
                    //---------------------------------------------------------------------------------------
                    // Need to ensure we only do the below updates if JOB_STAGE is included in the Sync
                    // because sometimes the sync is only select fields when doing manual one-off syncs.
                    // Not providing a JOB_STAGE as a paramenter affects how the belows actions operate.
                    //---------------------------------------------------------------------------------------
                    if (in_array($job_stage, ['950 Sales Dropout', '160 On Hold'])) {
                        // For Stages '950 + 160' update Status to 'Cancelled'
                        if ($site->status != '-2') {
                            $log .= "Status: $site->status => CANCELLED\n";
                            if ($save_enabled) {
                                $diffDat['status'] = '-2';
                                $diffTxt['status'] = "$site->status => CANCELLED";
                                $site->cancelInspectionReports();
                            }
                        }
                    } else {
                        if ($site->status == '-2') {
                            // If site was previously 'Cancelled' so set status to 'Upcoming'
                            $diffDat['status'] = '-1';
                            $diffTxt['status'] = "CANCELLED => UPCOMING";
                        } elseif ($site->status == '0') {
                            // If site 'Completed' then ensure Supervisor is same as Zoho
                            $supervisor_name = request('super_name');
                            if ($supervisor_name) {
                                $user = $cc->supervisorMatch($supervisor_name);
                                if ($user && $site->supervisor_id != $user->id) {
                                    $diffDat['supervisor_id'] = $user->id;
                                    $diffTxt['supervisor_id'] = $site->supervisor->name . " => $user->name";
                                }
                            }
                        }
                    }
                }

                //
                // Create Plumbing / Electrical Reports - for Job Stage 150 Plans to Client
                //
                if ($job_stage && $job_stage == '150 Plans Sent to Client') {
                    // Electrical
                    $elec_report = SiteInspectionElectrical::where('site_id', $site->id)->first();
                    if (!$elec_report) {
                        $elec_report = SiteInspectionElectrical::create(['site_id' => $site->id, 'client_name' => $site->name, 'client_address' => $site->addressFormattedSingle, 'status' => 1]);
                        $elec_report->createAssignCompanyToDo([108]);  // Create Todoo to assign a company
                    }
                    // Plumbing
                    $plub_report = SiteInspectionPlumbing::where('site_id', $site->id)->first();
                    if (!$plub_report) {
                        $plub_report = SiteInspectionPlumbing::create(['site_id' => $site->id, 'client_name' => $site->name, 'client_address' => $site->addressFormattedSingle, 'status' => 1]);
                        $plub_report->createAssignCompanyToDo([108]);  // Create Todoo to assign a company
                    }
                }
                if ($job_stage && $job_stage == '110 Plan Order Accepted' && $council_area == 'Waverley') {
                    // Plumbing (Waverly Council)
                    $plub_report = SiteInspectionPlumbing::where('site_id', $site->id)->first();
                    if (!$plub_report) {
                        $plub_report = SiteInspectionPlumbing::create(['site_id' => $site->id, 'client_name' => $site->name, 'client_address' => $site->addressFormattedSingle, 'status' => 1]);
                        $plub_report->createAssignCompanyToDo([108]);  // Create Todoo to assign a company
                    }
                }

                //
                // Fields types
                //
                $textfields = Site::ZOHO_SYNC_TEXT_FIELDS;
                $datefields = Site::ZOHO_SYNC_DATE_FIELDS;
                $yesno_fields = Site::ZOHO_SYNC_BOOLEAN_FIELDS;
                $exclude_update = Site::ZOHO_SYNC_EXCLUDED_UPDATE_FIELDS;
                $all_fields = array_merge($textfields, $datefields, $yesno_fields);


                //
                // Loop through all fields and compare differences (after Zoho/SWS data converted to same format)
                //
                $old = [];
                $new = [];
                $debuglog = [];
                foreach ($all_fields as $field) {
                    if (request()->has($field)) {
                        // Fields in this list may be sent by Zoho for comparison/reference,
                        // but Zoho must never update them in SafeWorksite.
                        if (in_array($field, $exclude_update, true)) {
                            $debuglog["!$field"] = 'Ignored - excluded from Zoho updates';
                            continue;
                        }

                        $zRaw = request($field);              // Zoho original parameter
                        $zDat = $zRaw;                        // Zoho data converted to valid SWS format
                        // project_mgr is matched from project_mgr_name, so use the name
                        // when determining whether Zoho supplied a blank manager.
                        $blankCheckValue = ($field == 'project_mgr') ? request('project_mgr_name') : $zRaw;
                        $zBlank = $blankCheckValue === null || (is_string($blankCheckValue) && trim($blankCheckValue) === '');
                        $zTxt = $zBlank ? '{empty}' : $zRaw;  // Zoho text used for comparisons
                        $sRaw = $site->{$field};              // SWS original data

                        // Preserve the existing SafeWorksite value when Zoho explicitly
                        // supplies a blank, unless blank overwrites have been enabled above.
                        if ($zBlank && !$overwrite_with_blank) {
                            $debuglog["!$field"] = 'Ignored blank value from Zoho';
                            continue;
                        }

                        if ($field == 'project_mgr') {
                            // Project Manager - Convert name into userid
                            $user = $cc->projectManagersMatch(request('project_mgr_name'));
                            $zDat = ($user) ? $user->id : null;
                            $zTxt = ($zDat !== null) ? (string)$zDat : '{empty}';
                            $sTxt = ($sRaw !== null && $sRaw !== '') ? (string)$sRaw : '{empty}';

                            // An unmatched Zoho project manager must not accidentally clear
                            // an existing SafeWorksite project manager.
                            if ($zDat === null && !$overwrite_with_blank) {
                                $debuglog["!$field"] = 'Ignored unmatched Zoho project manager';
                                continue;
                            }
                        } elseif (in_array($field, $textfields)) {
                            // Text fields - Convert to {null} if empty
                            $zDat = $zBlank ? null : $zRaw;
                            $sTxt = ($sRaw !== null && $sRaw !== '') ? $sRaw : '{empty}';
                        } elseif (in_array($field, $datefields)) {
                            // Date fields - Convert to Y-m-d
                            $zDat = $zBlank ? null : Carbon::parse($zRaw);
                            $zTxt = ($zDat) ? $zDat->format('Y-m-d') : '{empty}';
                            $sTxt = ($sRaw) ? $sRaw->format('Y-m-d') : '{empty}';
                        } elseif (in_array($field, $yesno_fields)) {
                            // Yes/No fields - Convert to binary 1/0
                            $zDat = $zBlank ? null : $this->normaliseZohoBoolean($zRaw);
                            $zTxt = ($zDat === null) ? '{empty}' : (($zDat) ? 'Yes' : 'No');
                            $sTxt = ($sRaw === null) ? '{empty}' : (($sRaw) ? 'Yes' : 'No');
                        }
                        //ray("Field: $field - zTxt: $zTxt  - sTxt: $sTxt");

                        // Zoho and SWS data is different
                        if ($sTxt != $zTxt) {
                            $old[$field] = $sTxt;
                            $new[$field] = $zTxt;
                            // Assign directly so valid falsey values such as boolean 0
                            // are saved rather than being converted to null.
                            $diffDat[$field] = $zDat;
                            $diffTxt[$field] = "$sTxt => $zTxt";
                            $debuglog["*" . $field] = "$sTxt => $zTxt";
                        } else
                            $debuglog[$field] = "$sTxt";
                    }
                }

                // Loop through differences + create logfile
                $fields_csv = '';
                foreach ($diffTxt as $field => $difference) {
                    $log .= "$field: " . $diffTxt[$field] . "\n";
                    $fields_csv .= "$field,";
                }
                $fields_csv = rtrim($fields_csv, ',');

                //
                // Debug Email
                //

                $debug_email = false;
                if ($debug_email) {
                    Mail::to(['fudge@jordan.net.au'])->send(new \App\Mail\Site\SiteSync($site, $site_request, $debuglog));
                    //app('log')->debug("========= Zoho Import Debug ==========");
                    //app('log')->debug("Zoho Data");
                    //app('log')->debug($site_request);
                    //app('log')->debug("Difference");
                    //app('log')->debug($diffTxt);
                }


                //
                // Update Site Record
                //
                $diffCount = count($diffDat);
                if ($diffCount && $save_enabled) {
                    $site->update($diffDat);

                    // Try match Zoho user to SWS user
                    $zuser = User::where('email', request('useremail'))->first();
                    if (!$zuser) {
                        list($first, $last) = explode(' ', request('username'), 2);
                        $zuser = User::where('firstname', $first)->where('lastname', $last)->first();
                    }
                    $uid = ($zuser) ? $zuser->id : 1;


                    // Save log
                    $logged = ZohoSiteLog::create([
                        'site_id' => $site->id,
                        'site_code' => $site->code,
                        'user_id' => $uid,
                        'user_name' => request('username'),
                        'action' => $action,
                        'qty' => $diffCount,
                        'fields' => $fields_csv,
                        'old' => json_encode($old),
                        'new' => json_encode($new),
                        'log' => $log
                    ]);
                    return $this->success("updated job", json_encode($diffTxt));
                } else
                    return $this->success("nothing changed", []);
            }
        }
        return $this->error('invalid data', 406);
    }

    /**
     * Convert common Zoho boolean representations to a database boolean value.
     */
    private function normaliseZohoBoolean($value): int
    {
        return in_array(strtolower(trim((string)$value)), ['yes', 'true', '1'], true) ? 1 : 0;
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        return SiteResource::make($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    protected function success($message, $data, $status = 200)
    {
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    protected function error($message, $status)
    {
        return response()->json(['status' => $status, 'message' => $message], $status);
    }
}
