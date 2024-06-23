<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Site\Site;
use Carbon\Carbon;
use File;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mail;

class MailgunZohoController extends Controller
{

    public $debug = true;
    public $countSites = 0;
    public $siteDiffs = [];
    public $blankZohoFields = [];
    public $blankSWSFields = [];
    public $diffFields = [];
    public $convertHeaderFields = [];
    public $logfile = '';

    public function store(Request $request)
    {
        //if ($this->debug) app('log')->debug("========= Zoho Import ==========");
        //if ($this->debug) app('log')->debug(request()->all());

        // Ensure Email is sent from specified address
        $valid_senders = ['<fudge@jordan.net.au>', 'fudge@jordan.net.au', '<systemgenerated@zohocrm.com>', 'systemgenerated@zohocrm.com'];
        //$valid_senders = ['<fudge@jordan.net.au>', 'fudge@jordan.net.au', 'crap@crapme.com'];
        if (!(in_array(request('From'), $valid_senders) || in_array(request('X-Envelope-From'), $valid_senders))) {  // X-Envelope-From
            if ($this->debug) app('log')->debug("========= Zoho Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid Sender: [" . request('X-Envelope-From') . "]");
            if ($this->debug) app('log')->debug($valid_senders);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email'
            ], 406);
        }

        // Accept only CSV files
        $files = collect(json_decode(request()->input('attachments'), true))
            ->filter(function ($file) {
                return $file['content-type'] == 'text/csv';
            });

        // If no attachment return 406 (Not Acceptable) to Mailgun to prevent retries
        if ($files->count() === 0) {
            if ($this->debug) app('log')->debug("========= Zoho Import Failed ==========");
            if ($this->debug) app('log')->debug("Missing expected CSV attachment");
            if ($this->debug) app('log')->debug(request()->all());

            return response()->json([
                'status' => 'error',
                'message' => 'Missing expected CSV attachment'
            ], 406);
        } else {
            if ($this->debug) app('log')->debug("========= Zoho Import ==========");

            // Zoho Daily log
            $dir = '/filebank/log/zoho';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required
            $this->logfile = public_path('filebank/log/zoho/' . Carbon::now()->format('Ymd') . '.txt');

            // Delay Queued Job to Verify Import Success/Fail
            //ZohoImportVerify::dispatch($this->logfile)->delay(Carbon::now()->addMinutes(2));

            $log = "------------------------------------------\nZoho Import - " . Carbon::now()->format('d/m/Y g:i a') . "\n------------------------------------------\n\n";
            if (file_exists($this->logfile))
                $bytes_written = File::append($this->logfile, $log);
            else
                $bytes_written = File::put($this->logfile, $log);
            if ($bytes_written === false) die("Error writing to file");

            // Get the attachments
            $dir = '/filebank/tmp/zoho';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            foreach ($files as $file) {
                //$mailgun_file = $this->retrieveMailgunFile($file['url']);  // Get file from Mailgun storage

                // Save the file
                $saved_file = public_path($dir . '/' . substr($file['name'], 0, -4) . '.' . Carbon::now()->format('YmdHis') . '.csv');
                $guzzleClient = new Client();
                $response = $guzzleClient->get($file['url'], ['auth' => ['api', config('services.mailgun.secret')]]);
                file_put_contents($saved_file, $response->getBody());
                if ($this->debug) app('log')->debug("Saving file: $saved_file");

                $result = $this->parseFile($saved_file);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function verifyImport()
    {
        $logfile = public_path('filebank/log/zoho/' . Carbon::now()->format('Ymd') . '.txt');
        if (strpos(file_get_contents($logfile), "ALL DONE - ZOHO IMPORT COMPLETE") !== false)
            Mail::to([env('EMAIL_DEV')])->send(new \App\Mail\Misc\ZohoImportFailed('Zoho Import was SUCESSFUL'));
        else
            Mail::to([env('EMAIL_DEV')])->send(new \App\Mail\Misc\ZohoImportFailed(''));
    }

    /**
     * Parse file
     */
    public function parseFile($parsefile = null)
    {
        $file = $parsefile;
        //$file = public_path('filebank/tmp/zoho/Jobs_for_Fudge.csv');
        //$file = public_path('filebank/tmp/zoho/Jobs_modified_today 14.csv');
        //$file = public_path('filebank/tmp/zoho/Jobs_modified_today 24.csv');
        //$file = public_path('filebank/tmp/zoho/Contacts_for_Fudge.csv');
        //$file = public_path('filebank/tmp/zoho/zohocontacts.20220302215015.csv');
        //$file = public_path('filebank/tmp/zoho/zohojobs.20220303145635.csv');
        if ($this->debug) app('log')->debug("Parsing file: $file");


        $cc = Company::find(3);
        $save_enabled = true;
        $overwrite_with_blank = false;
        $report_type = '';
        $sites_imported = [];
        $sales_dropouts = 0;
        $on_holds = 0;
        $differences = '';
        $updatedSupers = '';
        $blankZohoFields = [];
        $newSites = [];
        $head = [];
        $row = 0;
        $row_report_type = 0;
        $row_header = 0;
        $row_data = 0;
        if (($handle = fopen($file, "r")) !== false) {
            $log = "Zoho File Import: $file\n";
            if (!$save_enabled) $log .= "Save: DISABLED\n";
            if ($overwrite_with_blank) $log .= "Save: Overwrite With Blank\n";
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;

                //
                // Report Type Row
                //
                if (!$row_report_type && (stripos($data[0], "Jobs ") === 0 || stripos($data[0], "Contacts ") === 0)) {
                    //"Contacts modified today"
                    $row_report_type = $row;

                    list($report_type, $crap) = explode(' ', $data[0]);
                    if (!in_array($report_type, ['Jobs', 'Contacts'])) {
                        $log .= "Invalid format line $row for Zoho import file $file\n";
                        $bytes_written = File::append($this->logfile, $log);
                        if ($bytes_written === false) die("Error writing to file");

                        Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed("Reason: Invalid format line $row for Zoho import file $file"));

                        return false;
                    }

                    $log .= "Report type: $report_type\n";
                    continue;
                }

                //
                // Headers Row
                //
                if (!$row_header && in_array('Modified Time', $data) && (in_array('Job Name', $data) || in_array('Job Name (Job Name)', $data)) && (in_array('CX Sent Date', $data) || in_array('First Name 1', $data))) {
                    $row_header = $row;
                    $head = $this->reportHeaders($data, $report_type);
                    continue;
                }


                //
                // Data Row
                //
                //if (stripos($data[0], "zcrm_") === 0) {
                if ($row_report_type && $row_header && stripos($data[0], "zcrm_") === 0) {  // || $data[$head['code']] && $data[$head['name']]
                    $row_data++;
                    $this->countSites++;
                    $site = ($report_type == 'Jobs') ? Site::where('code', $data[$head['code']])->first() : Site::where('name', $data[$head['name']])->first();
                    $job_stage = (isset($head['job_stage'])) ? $data[$head['job_stage']] : '';

                    $new_site = '';
                    // Create new site except for Stages '950 + 160'
                    if (!$site && $report_type == 'Jobs' && !in_array($job_stage, ['950 Sales Dropout', '160 On Hold'])) {
                        // Create Site + Equipment Location
                        if ($save_enabled) {
                            // Assigned TO BE ALLOCATED as Supervisor;
                            $site = Site::create(['name' => $data[$head['name']], 'code' => $data[$head['code']], 'state' => 'NSW', 'supervisor_id' => '136', 'status' => "-1", 'company_id' => 3, 'created_by' => 1, 'updated_by' => 1]);
                            $location = EquipmentLocation::where('site_id', $site->id)->first();
                            if (!$location)
                                $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => "1", 'company_id' => 3, 'created_by' => 1, 'updated_by' => 1]);
                        }
                        $newSites[$data[$head['name']]] = ($job_stage) ? $job_stage : $data[$head['address']];
                        //$log .= "New: " . $data[$head['name']] . " (" . $data[$head['address']] . ", " . $data[$head['suburb']] .")\n";
                        $new_site = ' ** New Site **';
                    }


                    if ($site) {
                        $sites_imported[] = $site->id;

                        $fields = [
                            'name', 'address', 'suburb', 'postcode', 'consultant_name', 'project_mgr', 'project_mgr_name', 'estimator_fc', 'osd', 'sw', 'holidays_added',
                            'client1_firstname', 'client1_lastname', 'client1_mobile', 'client1_email',
                            'client2_firstname', 'client2_lastname', 'client2_mobile', 'client2_email', 'client_intro'];
                        $datefields = [
                            'council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed',
                            'construction_rcvd', 'hbcf_start', 'forecast_completion'];
                        $yesno_fields = ['engineering'];
                        $exclude_update = ['completion_signed'];
                        $all_fields = array_merge($fields, $datefields, $yesno_fields, $exclude_update);


                        $diffs = $this->compareSiteData($site, $data, $head, $fields, $datefields, $yesno_fields, $exclude_update, $new_site);
                        if ($diffs)
                            $differences .= $diffs;

                        //
                        // Job Import
                        //
                        if ($report_type == 'Jobs') {
                            // If site was previously 'Cancelled' then set status to 'Upcoming'
                            if ($site->status == '-2') {
                                $site->status = '-1';
                                $site->save();
                            }

                            // For Stages '950 + 160' update Status to 'Cancelled'
                            if (in_array($job_stage, ['950 Sales Dropout', '160 Onld'] Ho) && $site->status != '-2') {
                                $site->status = '-2';
                                $site->save();
                                $site->cancelInspectionReports();

                                if ($job_stage == '950 Sales Dropout') $sales_dropouts++;
                                if ($job_stage == '160 On Hold') $on_holds++;
                            }

                            // If site 'Completed' then ensure Supervisor is same as Zoho
                            if ($site->status == '0') {
                                $supervisor_name = (isset($head['super_name'])) ? $data[$head['super_name']] : '';
                                if ($supervisor_name) {
                                    $user = $cc->supervisorMatch($supervisor_name);
                                    if ($user && $site->supervisor_id != $user->id) {
                                        $updatedSupers .= "[$site->id] $site->name : " . $site->supervisor->name . " => $user->name\n";
                                        $site->supervisor_id = $user->id;
                                        $site->save();
                                    }
                                }
                            }
                        }


                        //
                        // update Site record
                        //
                        foreach ($head as $field => $col) {
                            // ensure Site record has the given field as Zoho uses extra
                            //if ($site->hasAttribute($field)) {
                            if (in_array($field, $all_fields)) {
                                $zoho_data = ($data[$col] == '-') ? '' : $data[$col]; // Exclude '-' blank data
                                //$log .= "[$site->id] $site->name :$field: [" . $site->{$field} . "] -  [$zoho_data]\n";
                                if ($site->{$field} && empty($zoho_data)) {
                                    // Data present so don't override with blank Zoho data (unless overwrite set)
                                    //$log .= "*$field: [" . $site->{$field} . "] [$zoho_data]\n";
                                    if ($field == 'consultant_name') { // Convert consultant name to initials for blank checking
                                        if (empty($data[$head['consultant_initials']]))
                                            $blankZohoFields["$site->id:$field"] = $site->{$field};
                                    } else
                                        $blankZohoFields["$site->id:$field"] = $site->{$field};

                                    // Overwite SWS with blank data from Zoho
                                    if ($save_enabled && $overwrite_with_blank && !in_array($field, $exclude_update)) {
                                        $site->{$field} = null;
                                        $site->save();  // Save imported data
                                    }

                                } elseif (!empty($zoho_data)) {
                                    $newData = '';
                                    if (in_array($field, $datefields)) {
                                        // Date fields
                                        //if (preg_match('/^\d+\/d+\/d+$/', $zoho_data)) {
                                        list($d, $m, $y) = explode('/', $zoho_data);
                                        $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);  // produces "-=-=-Alien"sprintf('%02d', $y);
                                        //if ($site->{$field})
                                        //    echo " &nbsp; $field: [" . $site->{$field}->format('j/n/y') . "] [$date_with_leading_zeros]<br>";
                                        $newData = Carbon::createFromFormat('d/m/Y H:i', $date_with_leading_zeros . '00:00')->toDateTimeString();
                                        //}

                                    } elseif (in_array($field, $yesno_fields)) {
                                        // Yes / No fields
                                        //$newData = ($zoho_data == 'YES') ? 1 : 0;
                                        $newData = ($zoho_data == 'YES') ? 1 : $site->{$field};  // temp only import YES dat for Eng FJ Cert ?
                                    } elseif ($field == 'project_mgr' && $head['project_mgr_name']) {
                                        // Project Manager - convert Name into Userid
                                        $project_mgr_name = $data[$head['project_mgr_name']];
                                        if ($project_mgr_name) {
                                            $user = $cc->projectManagersMatch($project_mgr_name);
                                            $newData = ($user) ? $user->id : null;
                                        }
                                    } else
                                        $newData = $zoho_data;

                                    // Save imported data
                                    if ($save_enabled && !in_array($field, $exclude_update)) {
                                        if ($site->{$field} != $newData) {
                                            //$log .= "save [$site->id] $field:".$site->{$field}." <= [$newData]\n";
                                            $site->{$field} = $newData;
                                            $site->save();
                                        } //else
                                        //$log .= "---- [$site->id] $field:".$site->{$field}." <= [$newData]\n";
                                    }
                                }
                            } else {
                                //$log .= "**No Attribute [$field]\n";
                            }
                        }
                    }
                }
            }

            // Output Report
            if ($row_report_type && $row_header) {
                $log .= "\nRead $this->countSites jobs and found " . count($this->siteDiffs) . " with differences\n";
                $log .= "\nSummary\n------------\n";
                $log .= "SWS Blank fields: " . count($this->blankSWSFields) . "\n";
                $log .= "Zoho Blank fields: " . count($this->blankZohoFields) . "\n";
                $log .= "Different fields: " . count($this->diffFields) . "\n";
                $log .= "New Jobs: " . count($newSites) . "\n\n";
                $log .= "\nThe following differences were found:\n";
                $log .= $differences;
                if ($updatedSupers) {
                    $log .= "\n\nThe following supervisors were updated:\n------------------------------------------------------\n";
                    $log .= $updatedSupers;
                }

                // New Sites
                if (count($newSites)) {
                    $log .= "\n\nAdded " . count($newSites) . " new sites\n------------------------------------------------------\n";
                    foreach ($newSites as $key => $val)
                        $log .= "$key : $val\n";
                }

                // Blank Zoho
                //if (count($this->blankZohoFields)) {
                //    $log .= "\n\nBlank " . count($this->blankZohoFields) . " Zoho fields\n------------------------------------------------------\n";
                //    foreach ($this->blankZohoFields as $key => $val)
                //        $log .= "* $key : $val\n";
                //}

                //
                // Zoho Missing Data Fields
                //
                /*
                $last_site = '';
                if (count($this->blankZohoFields)) {
                    $emptyZohoLog = "The Zoho import into SafeWorksite found missing data in (" . count($this->blankZohoFields) . ") Zoho Jobs.\n\n---";
                    foreach ($this->blankZohoFields as $key => $val) {
                        list($site_id, $field) = explode(':', $key);
                        $site = Site::findOrFail($site_id);
                        $value = (in_array($field, $datefields)) ? Carbon::createFromFormat('Y-m-d H:i:s', $val)->format('d/m/Y') : $val;   // Convert to date if datefield
                        $zoho_field = array_search($field, $this->convertHeaderFields);
                        if ($site->id != $last_site) {
                            $emptyZohoLog .= "\n$site->name\n - $zoho_field:  $value\n";
                            $last_site = $site->id;
                        } else
                            $emptyZohoLog .= " - $zoho_field:  $value\n";
                    }
                    $log .= "\n\n\n======================================================\n$emptyZohoLog";

                    // Email report to Zoho data person
                    //Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoEmptyFields($emptyZohoLog));
                } */
            } else {
                $log .= "\nFailed to import any records:\n";
                $log .= ($row_report_type) ? " - Report type line: $row_report_type\n" : "Report type line: FAILED\n";
                $log .= ($row_header) ? " - Header line: $row_header\n" : "Header line: FAILED\n";
                $log .= ($row_data) ? " - Data lines: $row_data\n" : "Data lines: FAILED\n";
            }
        }


        $log .= "\n\n------------------------------------------------------\nALL DONE - ZOHO IMPORT " . strtoupper($report_type) . " COMPLETE\n\n\n\n";

        echo nl2br($log);

        if ($parsefile) {
            $bytes_written = File::append($this->logfile, $log);
            if ($bytes_written === false) die("Error writing to file");
        }
    }

    /**
     * Get Report Headers
     * - the headers array records the column in the CSV the field is found
     */
    public function reportHeaders($data, $report_type = null)
    {
        $this->convertHeaderFields = [
            // Jobs Module
            'Record id' => 'zoho_id',
            'Job Number' => 'code',
            'ASC:Job Number' => 'code',
            'Job Name' => 'name',
            // Address
            'Street' => 'address',
            'Suburb' => 'suburb',
            'Post Code' => 'postcode',
            // Supervisor
            'Super' => 'super_initials',
            'Super Name' => 'super_name',
            // Dates
            'Approval Date' => 'council_approval',
            'CX Sent Date' => 'contract_sent',
            'CX Sign Date' => 'contract_signed',
            'CX Rcvd Date' => 'contract_received',
            'CX Deposit Date' => 'deposit_paid',
            'Prac Signed' => 'completion_signed',
            //'Eng Certified'       => 'engineering_cert',
            'CC Rcvd Date' => 'construction_rcvd',
            'HBCF Start Date' => 'hbcf_start',
            'Fcst Comp Date' => 'forecast_completion',
            // Other info
            'Design Cons' => 'consultant_initials',
            'Design Cons (user)' => 'consultant_name',
            'Project Coordinator' => 'project_mgr',
            'Project Coordinator (user)' => 'project_mgr_name',
            'Eng FJ Certified?' => 'engineering',
            'Estimator FC' => 'estimator_fc',
            'OSD Y/N' => 'osd',
            'SW Y/N' => 'sw',
            'Holidays Added' => 'holidays_added',
            'Job Stage' => 'job_stage',

            // Contacts Module
            'Job Name (Job Name)' => 'name',
            'First Name 1' => 'client1_firstname',
            'Last Name 1' => 'client1_lastname',
            'Mobile' => 'client1_mobile',
            'Email' => 'client1_email',
            'First Name 2' => 'client2_firstname',
            'Last Name 2' => 'client2_lastname',
            'Mobile 2' => 'client2_mobile',
            'Email 2' => 'client2_email',
            'Letter Intro' => 'client_intro'
        ];

        $headers = [];
        $headers_jobs = [];
        $col = 0;

        // Loop through the data + match the appropiate Zoho Header field to SWS field
        foreach ($data as $name) {
            if (isset($this->convertHeaderFields[$name]))
                $headers[$this->convertHeaderFields[$name]] = $col;
            $col++;

            if ($report_type == 'Jobs')
                $headers_jobs[] = $name;
        }


        // Verify Correct Headers are all present for Jobs import
        $required_headers_jobs = [
            'Job Name', 'Street', 'Suburb', 'Post Code', 'Super', 'Super Name', 'Approval Date',
            'CX Sent Date', 'CX Sign Date', 'CX Rcvd Date', 'CX Deposit Date', 'Prac Signed', 'CC Rcvd Date', 'HBCF Start Date', 'Fcst Comp Date',
            'Design Cons', 'Design Cons (user)', 'Project Coordinator', 'Project Coordinator (user)', 'Eng FJ Certified?', 'Estimator FC', 'Holidays Added', 'OSD Y/N', 'SW Y/N', 'Job Stage'
        ];
        sort($required_headers_jobs);
        sort($headers_jobs);
        $diff = array_diff($required_headers_jobs, $headers_jobs); // checks if all elements of required are found in header_jobs
        if ($report_type == 'Jobs' && count($diff)) {
            $missing_csv = implode(', ', $diff);
            $missing_csv = "Missing fields: $missing_csv";
            if ($this->debug) app('log')->debug($missing_csv);
            Mail::to([env('EMAIL_DEV')])->send(new \App\Mail\Misc\ZohoImportMissingFields($missing_csv));
        }

        return $headers;
    }

    /**
     * Compare Site Data
     */
    public function compareSiteData($site, $data, $head, $fields, $datefields, $yesno_fields, $exclude_update, $new_site)
    {
        $diff = "[$site->id] $site->name $new_site\n";
        $cc = Company::find(3);


        foreach ($fields as $field) {
            $excluded = (in_array($field, $exclude_update)) ? ' **NOT IMPORTED**' : '';  // Adds Note for not Imported
            if (isset($head[$field])) {
                $zoho_data = ($data[$head[$field]] == '-') ? '' : $data[$head[$field]];

                // Project Manager - convert Name into Userid
                if ($field == 'project_mgr' && $zoho_data) {
                    $user = $cc->projectManagersMatch($data[$head['project_mgr_name']]);
                    $zoho_data = ($user) ? $user->id : null;
                }

                // both SWS + Zoho have data
                if ($site->{$field} && $zoho_data && strtoupper($site->{$field}) != strtoupper($zoho_data)) {
                    $diff .= "  $field: " . $site->{$field} . " <= $zoho_data $excluded\n";
                    $this->diffFields["$site->id:$field"] = $site->{$field} . " <= $zoho_data";
                } // only SWS has data
                else if ($site->{$field} && !$zoho_data) {
                    //$diff .= "  $field: " . $site->{$field} . " -- {empty}\n";
                    $this->blankZohoFields["$site->id:$field"] = $site->{$field};
                } // only Zoho has data
                else if (!$site->{$field} && $zoho_data && !in_array($field, $exclude_update)) {
                    $diff .= "  $field: {empty} <= $zoho_data $excluded\n";
                    $this->blankSWSFields["$site->id:$field"] = $zoho_data;
                }
            }
        }

        // Yes - No fields
        foreach ($yesno_fields as $field) {
            $excluded = (in_array($field, $exclude_update)) ? ' **NOT IMPORTED**' : '';  // Adds Note for not Imported
            if (isset($head[$field])) {
                $zoho_data = ($data[$head[$field]] == '-') ? '' : $data[$head[$field]];
                if ($zoho_data)
                    $zoho_data = ($zoho_data == 'YES') ? 1 : 0;

                // both SWS + Zoho have data
                if ($site->{$field} != null && $zoho_data && $site->{$field} != $zoho_data) {
                    $diff .= "  $field: " . $site->{$field} . " <= $zoho_data $excluded\n";
                    $this->diffFields["$site->id:$field"] = $site->{$field} . " <= $zoho_data";
                } // only SWS has data
                else if ($site->{$field} != null && !$zoho_data) {
                    //$diff .= "  $field: " . $site->{$field} . " -- {empty}\n";
                    $this->blankZohoFields["$site->id:$field"] = ($site->{$field}) ? 'YES' : 'NO';
                } // only Zoho has data
                else if ($site->{$field} == null && $zoho_data && !in_array($field, $exclude_update)) {
                    $diff .= "  $field: {empty} <= $zoho_data $excluded\n";
                    $this->blankSWSFields["$site->id:$field"] = ($zoho_data) ? 'YES' : 'NO';
                }
            }
        }

        // Date fields
        foreach ($datefields as $field) {
            $excluded = (in_array($field, $exclude_update)) ? ' ** Excluded Field - NOT IMPORTED **' : '';  // Adds Note for not Imported
            if (isset($head[$field])) {
                $zoho_data = ($data[$head[$field]] == '-') ? '' : $data[$head[$field]];

                if ($zoho_data) { //} && preg_match('/^\d+\/d+\/d+$/', $zoho_data)) {
                    list($d, $m, $y) = explode('/', $zoho_data);
                    $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);  // produces "-=-=-Alien"sprintf('%02d', $y);
                } else
                    $date_with_leading_zeros = '';

                // both SWS + Zoho have data   // j/n/y
                if ($site->{$field} && $zoho_data && $site->{$field}->format('d/m/Y') != $date_with_leading_zeros) {
                    $diff .= "* $field: " . $site->{$field}->format('d/m/Y') . " <= $date_with_leading_zeros $excluded\n";
                    $this->diffFields["$site->id:$field"] = "$site->{$field} <= $date_with_leading_zeros";
                } // only SWS has data
                else if ($site->{$field} && $site->{$field}->format('d/m/Y') != $date_with_leading_zeros) {
                    //$diff .= "  $field: " . $site->{$field}->format('d/m/Y') . " -- {empty}\n";
                    $this->blankZohoFields["$site->id:$field"] = $site->{$field};
                } // only Zoho has data
                else if (!$site->{$field} && $zoho_data && !in_array($field, $exclude_update)) {
                    $diff .= "  $field: {empty}  <= $date_with_leading_zeros $excluded\n";
                    $this->blankSWSFields["$site->id:$field"] = $zoho_data;
                }
            }
        }

        // Job Stage ie Site Status
        if (isset($head['job_stage'])) {
            $zoho_data = ($data[$head['job_stage']] == '-') ? '' : $data[$head['job_stage']];
            if (in_array($zoho_data, ['950 Sales Dropout', '160 On Hold'])) {
                if ($site && $site->status != '-2')
                    $diff .= "  status: -2  <= $site->status\n";
            } else {
                if ($site && $site->status == '-2')
                    $diff .= "  status: -1  <= $site->status\n";
            }

        }

        if ($diff != "[$site->id] $site->name $new_site\n") {
            $this->siteDiffs[$site->id] = "$site->name";

            return "------------------------------------------------------\n$diff";
        }

        return '';
    }

    /**
     * Retrieve file from Mailgun Storage
     */
    /*
    public function retrieveMailgunFile($url)
    {
        // Fetch file from Mailgun storage
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "api:" . config('services.mailgun.secret'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch);
        //app('log')->debug("-=-----=-");
        //app('log')->debug($output);
        curl_close($ch);

        return $output;
    }*/


}
