<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\ZohoSiteLog;
use App\Models\Site\Site;
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
            // Create new site if required (except for Stages '950 + 160')
            if (!$site && !in_array($job_stage, ['950 Sales Dropout', '160 On Hold'])) {
                $action = 'create';
                if ($save_enabled) {
                    // Assigned 'TO BE ALLOCATED' as Supervisor;
                    $site = Site::create(['name' => request('name'), 'code' => request('code'), 'state' => 'NSW', 'supervisor_id' => '136', 'status' => "-1", 'company_id' => $cid, 'created_by' => 1, 'updated_by' => 1]);
                    $location = EquipmentLocation::where('site_id', $site->id)->first();
                    if (!$location)
                        $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => "1", 'company_id' => $cid, 'created_by' => 1, 'updated_by' => 1]);
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
                    // Need to ensure we only do the below updates if JOB_STAGE is included in the Sync
                    // because sometimes the sync is only select fields when doing manual one-off syncs.
                    // Not providing a JOB_STAGE as a paramenter affects how the belows actions operate.
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
                // Fields types
                //
                $textfields = [
                    'name', 'address', 'suburb', 'postcode', 'consultant_name', 'project_mgr', 'project_mgr_name', 'estimator_fc', 'osd', 'sw', 'gal', 'holidays_added',
                    'client1_title', 'client1_firstname', 'client1_lastname', 'client1_mobile', 'client1_email',
                    'client2_title', 'client2_firstname', 'client2_lastname', 'client2_mobile', 'client2_email', 'client_intro'];
                $datefields = [
                    'council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed',
                    'construction_rcvd', 'hbcf_start', 'forecast_completion', 'client_occupation'];
                $yesno_fields = ['engineering'];
                $exclude_update = ['completion_signed'];
                $all_fields = array_merge($textfields, $datefields, $yesno_fields);


                //
                // Loop through all fields and compare differences (after Zoho/SWS data converted to same format)
                //
                $old = [];
                $new = [];
                $debuglog = [];
                foreach ($all_fields as $field) {
                    $zRaw = request($field);    // Zoho original paramater
                    $zDat = $zRaw;              // Zoho data in valid SWS format
                    $zTxt = ($zRaw) ? $zRaw : '{empty}';    // Zoho text used for comparisons
                    $sRaw = $site->{$field};                // SWS original data

                    if ($zRaw) {
                        if ($field == 'project_mgr') {
                            // Project Manager - Convert name into userid
                            $user = $cc->projectManagersMatch(request('project_mgr_name'));
                            $zDat = ($user) ? $user->id : null;
                            $sTxt = ($user) ? $user->fullname : "{empty}";
                        } elseif (in_array($field, $textfields)) {
                            // Text fields - Convert to {null} if empty
                            $sTxt = ($site->{$field}) ? $site->{$field} : '{empty}';
                        } elseif (in_array($field, $datefields)) {
                            // Date fields - Convert to Y-m-d
                            $zDat = Carbon::parse($zRaw);
                            $sTxt = ($site->{$field}) ? $site->{$field}->format('Y-m-d') : '{empty}';
                        } elseif (in_array($field, $yesno_fields)) {
                            // Yes/No fields - Convert to binary 1/0
                            $zTxt = ucfirst(strtolower($zRaw));
                            $zDat = ($zTxt == 'Yes') ? 1 : 0;
                            $sTxt = ($site->{$field}) ? 'Yes' : 'No';
                        }
                        //ray("Field: $field - zTxt: $zTxt  - sTxt: $sTxt");

                        // Zoho and SWS data is different
                        if ($sTxt != $zTxt) {
                            $old[$field] = $sTxt;
                            $new[$field] = $zTxt;
                            $diffDat[$field] = $zDat;
                            $diffTxt[$field] = "$sTxt => $zTxt";
                            $debuglog["*" . $field] = "$sTxt => $zTxt";
                        } else
                            $debuglog[$field] = "$sTxt";
                    }
                }

                // Loop through differences + create logfile
                $fields_csv = '';
                foreach ($diffDat as $field => $data) {
                    $log .= "$field: " . $diffTxt[$field] . "\n";
                    $fields_csv .= "$field,";
                }
                $fields_csv = rtrim($fields_csv, ',');

                //
                // Debug Email
                //

                $debug_email = true;
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
                if (count($diffDat) && $save_enabled) {
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
                        'qty' => count($diffDat),
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
