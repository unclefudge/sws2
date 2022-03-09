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

    public $countBlankZohoDates = 0;
    public $countBlankSwsDates = 0;
    public $countDiffDates = 0;
    public $countDiffCons = 0;
    public $countDiffAddr = 0;
    public $countDiffSname = 0;
    public $countSites = 0;
    public $siteDiffs = [];
    public $blankZohoFields = [];
    public $convertHeaderFields = [];
    public $logfile = '';

    public function store(Request $request)
    {
        //app('log')->debug("===================================================");
        //app('log')->debug(request()->all());

        // Ensure Email is sent from specified address
        $valid_senders = ['<fudge@jordan.net.au>', '<systemgenerated@zohocrm.com>'];
        if (!in_array(request('X-Envelope-From'), $valid_senders))
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
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing expected CSV attachment'
            ], 406);
        } else {
            //app('log')->debug($files);

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
                //app('log')->debug($file);
                //$mailgun_file = $this->retrieveMailgunFile($file['url']);  // Get file from Mailgun storage

                // Save the file
                $saved_file = public_path($dir . '/' . substr($file['name'], 0, - 4) . '.' . Carbon::now()->format('YmdHis') . '.csv');
                $guzzleClient = new Client();
                $response = $guzzleClient->get($file['url'], ['auth' => ['api', config('services.mailgun.secret')]]);
                file_put_contents($saved_file, $response->getBody());

                $result = $this->parseFile($saved_file);
            }

            //dispatch(new ProcessWidgetFiles($files));
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
        $file = public_path('filebank/tmp/zoho/Jobs_modified_today.csv');
        //$file = public_path('filebank/tmp/zoho/zohocontacts.20220302215015.csv');

        $overwrite_with_blank = true;
        $report_type = '';
        $sites_imported = [];
        $differences = '';
        $blankZohoFields = [];
        $newSites = [];
        $head = [];
        $row = 0;
        if (($handle = fopen($file, "r")) !== false) {
            $log = "Zoho File Import : $file\n";
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
                    $log .= "Report type: $report_type\n";
                }
                if ($row == 2)
                    $head = $this->reportHeaders($report_type, $data);


                //
                // Data Row
                //
                if (stripos($data[0], "zcrm_") === 0) {
                    $this->countSites ++;
                    $site = Site::where('code', $data[$head['code']])->first();

                    if (!$site && $report_type == 'Jobs') {
                        // Create Site + Equipment Location
                        $site = Site::create(['name' => $data[$head['name']], 'code' => $data[$head['code']]]);
                        $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => 1]);
                        $newSites[$data[$head['name']]] = (isset($data[$head['job_stage']])) ? $data[$head['job_stage']] : $data[$head['address']];
                    }

                    if ($site) {
                        $sites_imported[] = $site->id;

                        $diffs = $this->compareSiteData($site, $data, $head);
                        if ($diffs)
                            $differences .= $diffs;


                        // update Site record
                        $fields = [
                            'name', 'address', 'suburb', 'postcode', 'consultant_name',
                            'client_phone_desc', 'client_phone', 'client_email', 'client_phone2_desc', 'client_phone2', 'client_email2'];
                        $datefields = ['council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed'];

                        //echo "--------------------------------<br>[$site->id] $site->name  <br>";

                        foreach ($head as $field => $col) {
                            if ($field == 'Super Name') {
                                // Maybe sync Supers
                                // $site->supervisors()->sync(request('supervisors'));
                            } else {
                                // ensure Site record has the given field as Zoho uses extra
                                if ($site->hasAttribute($field)) {
                                    //echo "[$site->id] $site->name :$field: [" . $site->{$field} . "] -  [" . $data[$col] . "]<br>";
                                    if ($site->{$field} && empty($data[$col])) {
                                        // Data present so don't override with blank Zoho data (unless overwrite set)
                                        //echo "*$field: [" . $site->{$field} . "] [" . $data[$col] . "]<br>";
                                        if ($field == 'consultant_name') { // Convert consultant name to initials for blank checking
                                            if (empty($data[$head['consultant_initials']]))
                                                $blankZohoFields["$site->id:$field"] = $site->{$field};
                                        } else
                                            $blankZohoFields["$site->id:$field"] = $site->{$field};

                                        // Overwite SWS with blank data from Zoho
                                        if ($overwrite_with_blank) {
                                            $site->{$field} = null;
                                            //$site->save();  // Save imported data
                                        }

                                    } elseif (!empty($data[$col])) {
                                        if (in_array($field, $datefields)) {
                                            $site->{$field} = Carbon::createFromFormat('d/m/Y H:i', $data[$col] . '00:00')->toDateTimeString();
                                            //if ($site->{$field})
                                            //    echo " &nbsp; $field: [" . $site->{$field}->format('j/n/y') . "] [" . Carbon::createFromFormat('d/m/Y H:i', $data[$col] . '00:00')->format('j/n/y') . "]<br>";
                                        } else
                                            $site->{$field} = $data[$col];

                                        //$site->save();  // Save imported data
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Output Report
            $log .= "\nRead $this->countSites jobs and found " . count($this->siteDiffs) . " with differences\n";
            //echo "<br>------------------------------------------------------<br>";
            //echo "Conflicting Job Names: $this->countDiffSname</br>";
            //echo "Conflicting Addresses: $this->countDiffAddr</br>";
            //echo "Conflicting Dates: $this->countDiffDates</br>";
            //echo "Conflicting Consultants: $this->countDiffCons</br>";
            //echo "SWS Blank dates: $this->countBlankSwsDates</br>";
            //echo "Zoho Blank dates: $this->countBlankZohoDates</br>";
            //echo "New Jobs: " . count($newSites) . "<br>";
            //echo "<br>------------------------------------------------------<br>";

            //echo "<br>Site Differences<br>";
            $log .= $differences;

            // New Sites
            if (count($newSites)) {
                $log .= "\n\nAdded " . count($newSites) . " new sites\n------------------------------------------------------\n";
                foreach ($newSites as $key => $val)
                    $log .= "$key : $val\n";
            }

            //
            // Zoho Missing Data Fields
            //
            $last_site = '';
            if (count($blankZohoFields)) {
                $emptyZohoLog = "The Zoho import into SafeWorksite found missing data in (" . count($blankZohoFields) . ") Zoho Jobs.\n\n---";
                foreach ($blankZohoFields as $key => $val) {
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
                //$log .= "\n\n$emptyZohoLog";

                // Email report to Zoho data person
                Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoEmptyFields($emptyZohoLog));
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
    public function reportHeaders($report_type, $data)
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
            //'' => 'engineering',
            'CC Rcvd Date'       => 'construction',
            'HBCF Start Date'    => 'hbcf',
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
    public function compareSiteData($site, $data, $head)
    {
        $diff = "[$site->id] $site->code-$site->name\n";

        $fields = ['name', 'address', 'suburb', 'postcode', 'consultant_name', 'client_phone', 'client_phone_desc', 'client_email', 'client_phone2', 'client_phone2_desc', 'client_email2'];
        $dates = ['council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed'];

        foreach ($fields as $field) {
            if (isset($head[$field])) {
                // both SWS + Zoho have data
                if ($site->{$field} && $data[$head[$field]] && strtoupper($site->{$field}) != strtoupper($data[$head[$field]])) {
                    $diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]\n";
                } // only SWS has data
                else if ($site->{$field} && !$data[$head[$field]]) {
                    $diff .= "* $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]\n";
                    $this->blankZohoFields[$site->id] = "$field: $site->{$field}";
                } // only Zoho has data
                else if (!$site->{$field} && $data[$head[$field]]) {
                    $diff .= " &nbsp; $field:{empty} [" . $data[$head[$field]] . "]\n";
                }
            }
        }

        foreach ($dates as $field) {
            if (isset($head[$field])) {
                // both SWS + Zoho have data   // j/n/y
                if ($site->{$field} && $data[$head[$field]] && $site->{$field}->format('d/m/Y') != $data[$head[$field]]) {
                    $diff .= "* $field:" . $site->{$field}->format('d/m/Y') . " [" . $data[$head[$field]] . "]\n";
                } // only SWS has data
                else if ($site->{$field} && $site->{$field}->format('d/m/Y') != $data[$head[$field]]) {
                    $diff .= "* $field:" . $site->{$field}->format('d/m/Y') . " [" . $data[$head[$field]] . "]\n";
                    $this->blankZohoFields[$site->id] = "$field: $site->{$field}";
                } // only Zoho has data
                else if (!$site->{$field} && $data[$head[$field]]) {
                    $diff .= " &nbsp; $field:{empty} [" . $data[$head[$field]] . "]\n";
                }
            }
        }

        /*
        // Y / N
        $site->construction = $data[$head['construction']];
        $site->hbcf = $data[$head['hbcf']];
        $site->engineering = $data[$head['engineering']];
        */

        if ($diff != "[$site->id] $site->code-$site->name\n") {
            $this->siteDiffs[$site->id] = "$site->code-$site->name";

            return "------------------------------------------------------\n$diff";
        }

        return '';
    }

    /**
     * Compare Site Data
     */
    public function compareSiteDataOld($comparion, $site, $data, $head)
    {
        $diff = "[$site->id] $site->code-$site->name<br>";

        $fields = ['name', 'address', 'suburb', 'postcode', 'consultant_name', 'client_phone', 'client_phone_desc', 'client_email', 'client_phone2', 'client_phone2_desc', 'client_email2'];
        $dates = ['council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed'];

        foreach ($fields as $field) {
            if ($field == 'name') {
                $name = "$site->code-$site->name-$site->suburb";
                if (isset($head[$field]) && strtoupper($name) != strtoupper($data[$head[$field]])) {
                    $this->countDiffSname ++;
                    $diff .= " &nbsp; $field:" . $site->code . "-" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
                }
            } else if ($field == 'address' && strtoupper($site->{$field}) != strtoupper($data[$head[$field]])) {
                $this->countDiffAddr ++;
                $diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
            } else if ($field == 'consultant_name' && strtoupper($site->{$field}) != strtoupper($data[$head[$field]])) {
                $this->countDiffCons ++;
                //$diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
            } else if (isset($head[$field]) && strtoupper($site->{$field}) != strtoupper($data[$head[$field]]))
                $diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";

        }

        foreach ($dates as $field) {
            if (isset($head[$field])) {
                if ($site->{$field} && $data[$head[$field]] && $site->{$field}->format('j/n/y') != $data[$head[$field]]) {  // both SWS + Zoho have data
                    $diff .= "<b> &nbsp; $field:" . $site->{$field}->format('j/n/y') . " [" . $data[$head[$field]] . "]</b><br>";
                    $this->countDiffDates ++;
                } else if ($site->{$field} && $site->{$field}->format('j/n/y') != $data[$head[$field]]) {  // only SWS has data
                    $diff .= " &nbsp; $field:" . $site->{$field}->format('j/n/y') . " [" . $data[$head[$field]] . "]<br>";
                    $this->countBlankZohoDates ++;
                } else if (!$site->{$field} && $data[$head[$field]]) { // only Zoho has data
                    //$diff .= " &nbsp; $field:{empty} [".$data[$head[$field]]."]<br>";
                    $this->countBlankSwsDates ++;
                }
            }
        }

        if ($diff != "[$site->id] $site->code-$site->name<br>") {
            $this->siteDiffs[$site->id] = "$site->code-$site->name";

            return "------------------------------------------------------<br>$diff";
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
