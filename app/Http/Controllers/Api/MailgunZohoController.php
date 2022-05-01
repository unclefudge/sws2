<?php

namespace App\Http\Controllers\Api;

use Mail;
use File;
use Carbon\Carbon;
use App\User;
use App\Models\Site\Site;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Jobs\ZohoImportVerify;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MailgunZohoController extends Controller {

    public $countSites = 0;
    public $siteDiffs = [];
    public $blankZohoFields = [];
    public $blankSWSFields = [];
    public $diffFields = [];
    public $convertHeaderFields = [];
    public $logfile = '';

    public function store(Request $request)
    {
        app('log')->debug("========= Zoho Import ==========");
        app('log')->debug(request()->all());

        // Ensure Email is sent from specified address
        $valid_senders = ['<fudge@jordan.net.au>', 'fudge@jordan.net.au', '<systemgenerated@zohocrm.com>', 'systemgenerated@zohocrm.com'];
        $valid_senders = ['<fudge@jordan.net.au>', 'fudge@jordan.net.au'];
        if (!in_array(request('X-Envelope-From'), $valid_senders))
            app('log')->debug("========= Import Failed ==========");
            app('log')->debug("Invalid Sender: ".request('X-Envelope-From'));
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid email'
            ], 406);

        // Accept only CSV files
        $files = collect(json_decode(request()->input('attachments'), true))
            ->filter(function ($file) {
                return $file['content-type'] == 'text/csv';
            });

        // If no attachment return 406 (Not Acceptable) to Mailgun to prevent retries
        if ($files->count() === 0) {
            app('log')->debug("========= Import Failed ==========");
            app('log')->debug("Missing expected CSV attachment");
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing expected CSV attachment'
            ], 406);
        } else {
            app('log')->debug("========= Begin Import ==========");

            // Zoho Daily log
            $this->logfile = public_path('filebank/log/zoho/' . Carbon::now()->format('Ymd') . '.txt');

            // Delay Queued Job to Verify Import Success/Fail
            ZohoImportVerify::dispatch($this->logfile)->delay(Carbon::now()->addMinutes(1));

            $log = "Zoho Import - " . Carbon::now()->format('d/m/Y') . "\n------------------------------------------\n\n";
            $bytes_written = File::put($this->logfile, $log);
            if ($bytes_written === false) die("Error writing to file");

            // Get the attachments
            $dir = '/filebank/tmp/zoho';
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

            foreach ($files as $file) {
                //$mailgun_file = $this->retrieveMailgunFile($file['url']);  // Get file from Mailgun storage

                // Save the file
                $saved_file = public_path($dir . '/' . substr($file['name'], 0, - 4) . '.' . Carbon::now()->format('YmdHis') . '.csv');
                $guzzleClient = new Client();
                $response = $guzzleClient->get($file['url'], ['auth' => ['api', config('services.mailgun.secret')]]);
                file_put_contents($saved_file, $response->getBody());
                app('log')->debug("Saving file: $saved_file");

                $result = $this->parseFile($saved_file);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function verifyImport()
    {
        $logfile = public_path('filebank/log/zoho/' . Carbon::now()->format('Ymd') . '.txt');
        if (strpos(file_get_contents($logfile), "ALL DONE - ZOHO IMPORT COMPLETE") !== false)
            Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed('Zoho Import was SUCESSFUL'));
        else
            Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed(''));
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
        app('log')->debug("Parsing file: $file");


        $save_enabled = true;
        $overwrite_with_blank = false;
        $report_type = '';
        $sites_imported = [];
        $sales_dropouts = 0;
        $differences = '';
        $blankZohoFields = [];
        $newSites = [];
        $head = [];
        $row = 0;
        if (($handle = fopen($file, "r")) !== false) {
            $log = "Zoho File Import: $file\n";
            if (!$save_enabled) $log .= "Save: DISABLED\n";
            if ($overwrite_with_blank) $log .= "Save: Overwrite With Blank\n";
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;

                //
                // Headers
                //
                if ($row == 1) {
                    list($report_type, $crap) = explode(' ', $data[0]);
                    if (!$report_type) {
                        $log .= "Invalid format line 1 for Zoho import file $file\n";
                        $bytes_written = File::append($this->logfile, $log);
                        if ($bytes_written === false) die("Error writing to file");

                        Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed("Reason: Invalid format line 1 for Zoho import file $file"));

                        return false;
                    }
                    if ($report_type != 'Jobs') {
                        echo "Report type not Jobs<br>";
                        break;
                    }

                    $log .= "Report type: $report_type\n";
                    continue;
                }
                if ($row == 2) {
                    $head = $this->reportHeaders($data);
                    continue;
                }


                //
                // Data Row
                //
                //if (stripos($data[0], "zcrm_") === 0) {
                if (stripos($data[0], "zcrm_") === 0) {  // || $data[$head['code']] && $data[$head['name']]
                    $this->countSites ++;
                    $site = ($report_type == 'Jobs') ? Site::where('code', $data[$head['code']])->first() : Site::where('name', $data[$head['name']])->first();
                    $job_stage = (isset($head['job_stage'])) ? $data[$head['job_stage']] : '';

                    if ($job_stage == '950 Sales Dropout') { // Don't import Sales Dropouts
                        $sales_dropouts ++;
                        continue;
                    }
                    $job_precontruction = '';

                    $new_site = '';
                    if (!$site && $report_type == 'Jobs') {
                        // Create Site + Equipment Location
                        if ($save_enabled) {
                            $site = Site::create(['name' => $data[$head['name']], 'code' => $data[$head['code']]]);
                            $location = EquipmentLocation::where('site_id', $site->id)->first();
                            if (!$location)
                                $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => 1, 'company_id' => 3, 'created_by' => 1, 'updated_by' => 1]);
                        }
                        $newSites[$data[$head['name']]] = ($job_stage) ? $job_stage : $data[$head['address']];
                        //$log .= "New: " . $data[$head['name']] . " (" . $data[$head['address']] . ", " . $data[$head['suburb']] .")\n";
                        $new_site = ' ** New Site **';
                    }

                    if ($site && $report_type == 'Jobs') {
                        $sites_imported[] = $site->id;

                        $fields = [
                            'name', 'address', 'suburb', 'postcode', 'consultant_name',
                            'client_phone_desc', 'client_phone', 'client_email', 'client_phone2_desc', 'client_phone2', 'client_email2', 'client_intro'];
                        $datefields = [
                            'council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed',
                            'engineering_cert', 'construction_rcvd', 'hbcf_start'];
                        $exclude_update = ['completion_signed'];
                        $all_fields = array_merge($fields, $datefields, $exclude_update);


                        $diffs = $this->compareSiteData($site, $data, $head, $fields, $datefields, $exclude_update, $new_site);
                        if ($diffs)
                            $differences .= $diffs;


                        //
                        // update Site record
                        //
                        foreach ($head as $field => $col) {
                            //$log .= "f[$field] c[$col]\n";
                            // ensure Site record has the given field as Zoho uses extra
                            //if ($site->hasAttribute($field)) {
                            if (in_array($field, $all_fields)) {
                                //$log .= "[$site->id] $site->name :$field: [" . $site->{$field} . "] -  [" . $data[$col] . "]\n";
                                if ($site->{$field} && empty($data[$col])) {
                                    // Data present so don't override with blank Zoho data (unless overwrite set)
                                    //$log .= "*$field: [" . $site->{$field} . "] [" . $data[$col] . "]\n";
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

                                } elseif (!empty($data[$col])) {
                                    $newData = '';
                                    if (in_array($field, $datefields)) {
                                        list($d, $m, $y) = explode('/', $data[$col]);
                                        $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);  // produces "-=-=-Alien"sprintf('%02d', $y);
                                        //if ($site->{$field})
                                        //    echo " &nbsp; $field: [" . $site->{$field}->format('j/n/y') . "] [$date_with_leading_zeros]<br>";
                                        $newData = Carbon::createFromFormat('d/m/Y H:i', $date_with_leading_zeros . '00:00')->toDateTimeString();

                                    } else
                                        $newData = $data[$col];

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
            $log .= "\nRead $this->countSites jobs and found " . count($this->siteDiffs) . " with differences\n";
            $log .= "\nSummary\n------------\n";
            $log .= "SWS Blank fields: " . count($this->blankSWSFields) . "\n";
            $log .= "Zoho Blank fields: " . count($this->blankZohoFields) . "\n";
            $log .= "Different fields: " . count($this->diffFields) . "\n";
            $log .= "New Jobs: " . count($newSites) . "\n\n";
            $log .= "\nThe following differences were found:\n";
            $log .= $differences;

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
            }
        }

        $log .= "\n\n------------------------------------------------------\nALL DONE - ZOHO IMPORT COMPLETE";

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
    public function reportHeaders($data)
    {
        $this->convertHeaderFields = [
            // Jobs Module
            'ACCOUNTID'          => 'zoho_id',
            'Job Number'         => 'code',
            'ASC:Job Number'     => 'code',
            'Job Name'           => 'name',
            // Address
            'Street'             => 'address',
            'Suburb'             => 'suburb',
            'Post Code'          => 'postcode',
            // Supervisor
            'Super'              => 'super_initials',
            'Super Name'         => 'super_name',
            // Dates
            'Approval Date'      => 'council_approval',
            'CX Sent Date'       => 'contract_sent',
            'CX Rcvd Date'       => 'contract_received',
            'CX Sign Date'       => 'contract_signed',
            'CX Deposit Date'    => 'deposit_paid',
            'Prac Signed'        => 'completion_signed',
            'Eng Certified'      => 'engineering_cert',
            'CC Rcvd Date'       => 'construction_rcvd',
            'HBCF Start Date'    => 'hbcf_start',
            'Design Cons'        => 'consultant_initials',
            'Design Cons (user)' => 'consultant_name',
            'Job Stage'          => 'job_stage',

            // Contacts Module
            'CONTACTID'          => 'contact_id',
            'First Name 1'       => 'client_phone_desc',
            'Mobile'             => 'client_phone',
            'Email'              => 'client_email',
            'First Name 2'       => 'client_phone2_desc',
            'Mobile 2'           => 'client_phone2',
            'Email 2'            => 'client_email2'
        ];

        $headers = [];
        $col = 0;

        // Loop through the data + match the appropiate Zoho Header field to SWS field
        foreach ($data as $name) {
            if (isset($this->convertHeaderFields[$name]))
                $headers[$this->convertHeaderFields[$name]] = $col;
            $col ++;
        }

        return $headers;
    }

    /**
     * Compare Site Data
     */
    public function compareSiteData($site, $data, $head, $fields, $datefields, $exclude_update, $new_site)
    {
        $diff = "[$site->id] $site->name $new_site\n";

        //$fields = ['address', 'suburb', 'postcode', 'client_phone', 'client_phone_desc', 'client_email', 'client_phone2', 'client_phone2_desc', 'client_email2'];
        //$dates = ['council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed'];


        foreach ($fields as $field) {
            $excluded = (in_array($field, $exclude_update)) ? ' **NOT IMPORTED**' : '';  // Adds Note for not Imported
            if (isset($head[$field])) {
                // Convert Client Phones into AU phone format + remove A-Z chars
                if (in_array($field, ['client_phone', 'client_phone2']) && $data[$head[$field]]) {
                    $data[$head[$field]] = format_phone('au', $data[$head[$field]]);
                }

                // both SWS + Zoho have data
                if ($site->{$field} && $data[$head[$field]] && strtoupper($site->{$field}) != strtoupper($data[$head[$field]])) {
                    $diff .= "  $field: " . $site->{$field} . " <= " . $data[$head[$field]] . "$excluded\n";
                    $this->diffFields["$site->id:$field"] = $site->{$field}." <= " . $data[$head[$field]];
                } // only SWS has data
                else if ($site->{$field} && !$data[$head[$field]]) {
                    //$diff .= "  $field: " . $site->{$field} . " -- {empty}\n";
                    $this->blankZohoFields["$site->id:$field"] = $site->{$field};
                } // only Zoho has data
                else if (!$site->{$field} && $data[$head[$field]]) {
                    $diff .= "  $field: {empty} <= " . $data[$head[$field]] . "$excluded\n";
                    $this->blankSWSFields["$site->id:$field"] = $data[$head[$field]];
                }
            }
        }

        foreach ($datefields as $field) {
            $excluded = (in_array($field, $exclude_update)) ? ' ** Excluded Field - NOT IMPORTED **' : '';  // Adds Note for not Imported
            if (isset($head[$field])) {
                if ($data[$head[$field]]) {
                    list($d, $m, $y) = explode('/', $data[$head[$field]]);
                    $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);  // produces "-=-=-Alien"sprintf('%02d', $y);
                } else
                    $date_with_leading_zeros = '';

                // both SWS + Zoho have data   // j/n/y
                if ($site->{$field} && $data[$head[$field]] && $site->{$field}->format('d/m/Y') != $date_with_leading_zeros) {
                    $diff .= "* $field: " . $site->{$field}->format('d/m/Y') . " <= $date_with_leading_zeros $excluded\n";
                    $this->diffFields["$site->id:$field"] = "$site->{$field} <= $date_with_leading_zeros";
                } // only SWS has data
                else if ($site->{$field} && $site->{$field}->format('d/m/Y') != $date_with_leading_zeros) {
                    //$diff .= "  $field: " . $site->{$field}->format('d/m/Y') . " -- {empty}\n";
                    $this->blankZohoFields["$site->id:$field"] = $site->{$field};
                } // only Zoho has data
                else if (!$site->{$field} && $data[$head[$field]]) {
                    $diff .= "  $field: {empty}  <= $date_with_leading_zeros $excluded\n";
                    $this->blankSWSFields["$site->id:$field"] = $data[$head[$field]];
                }
            }
        }

        /*
        // Y / N
        $site->engineering = $data[$head['engineering']];
        */

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
