<?php

namespace App\Http\Controllers\Api;

use Mail;
use File;
use Carbon\Carbon;
use App\User;
use App\Models\Site\Site;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MailgunZohoController extends Controller {

    public $countBlankDates = 0;
    public $siteDiffs = [];
    public $logfile = '';

    public function store()
    {
        app('log')->debug("===================================================");
        app('log')->debug(request()->all());

        //$files = collect(json_decode(request()->input('attachments'), true));

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
            // Get the attachments
            app('log')->debug("---------");
            app('log')->debug($files);

            $dir = '/filebank/tmp/zoho';
            // Create directory if required
            if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);

            // Zoho Daily log
            $this->logfile = public_path('filebank/log/zoho/' . Carbon::now()->format('Ymd') . '.txt');
            $log = "Zoho Import - " . Carbon::now()->format('d/m/Y') . "\n--------------\n\n";
            $bytes_written = File::put($this->logfile, $log);
            if ($bytes_written === false) die("Error writing to file");

            foreach ($files as $file) {
                app('log')->debug("-*-----*-");
                //app('log')->debug($file);

                $mailgun_file = $this->retrieveMailgunFile($file['url']);

                // Save the file
                $saved_file = public_path($dir . '/' . substr($file['name'], 0, - 4) . '.' . Carbon::now()->format('YmdHis') . '.csv');
                $bytes_written = File::put($saved_file, $mailgun_file);
                if ($bytes_written === false) die("Error writing to file");

                app('log')->debug("-*-----*-");

                //$result = $this->parseFile($saved_file);
            }

            //dispatch(new ProcessWidgetFiles($files));
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Retrieve file from Mailgun Storage
     */
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
    }

    /**
     * Parse file
     */
    public function parseFile($file = null)
    {
        $file = public_path('filebank/tmp/zoho/zohojobs.20220302120650.csv');
        //$file = public_path('filebank/tmp/zoho/zohocontacts.20220302215015.csv');

        $report_type = '';
        $differences = '';
        $head = [];
        $row = 0;
        if (($handle = fopen($file, "r")) !== false) {
            echo "Zoho File Import : $file<br>";
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;

                //
                // Headers
                //
                if ($row == 1) {
                    $report_type = $this->reportType($data);
                    if (!$report_type) {
                        app('log')->error("Invalid format line 1 for Zoho import file $file");

                        return false;
                    }
                    echo "Report type: $report_type<br>";
                }
                if ($row == 2) {
                    $head = $this->reportHeaders($report_type, $data);
                }

                //
                // Data Row
                //
                if (stripos($data[0], "zcrm_") === 0) {
                    $site = Site::where('code', $data[$head['code']])->first();

                    if ($site) {
                        $diffs = $this->compareSiteData($site, $data, $head);

                        if ($diffs) {
                            $differences .= $diffs;
                        }

                        /*
                        // update Site record
                        echo "Site ID:$site->id  <br>";
                        $site->name = $data[$head['name']];
                        // Address
                        $site->address = $data[$head['address']];
                        $site->suburb = $data[$head['suburb']];
                        $site->postcode = $data[$head['postcode']];

                        // Dates
                        $site->council_approval = $data[$head['council_approval']];
                        $site->contract_sent = $data[$head['contract_sent']];
                        //$site->contract_received = $data[$head['contract_received']];
                        $site->contract_signed = $data[$head['contract_signed']];
                        $site->deposit_paid = $data[$head['deposit_paid']];
                        $site->completion_signed = $data[$head['completion_signed']];

                        // Y / N
                        //$site->construction = $data[$head['construction']];
                        //$site->hbcf = $data[$head['hbcf']];
                        //$site->engineering = $data[$head['engineering']];

                        $site->consultant_name = $data[$head['consultant_name']];

                        //if ($name == 'Super') $headers['super_initials'] = $col;
                        //if ($name == 'Super Name') $headers['super_name'] = $col;
                        */

                    } else {
                        // Create new site
                        $site_data = [
                            'name'              => $data[$head['name']],
                            'address'           => $data[$head['address']],
                            'suburb'            => $data[$head['suburb']],
                            'postcode'          => $data[$head['postcode']],
                            'council_approval'  => $data[$head['council_approval']],
                            'contract_sent'     => $data[$head['contract_sent']],
                            //'contract_received' => $data[$head['contract_received']],
                            'contract_signed'   => $data[$head['contract_signed']],
                            'deposit_paid'      => $data[$head['deposit_paid']],
                            'completion_signed' => $data[$head['completion_signed']],

                            // Y / N
                            //'construction' => $data[$head['construction']],
                            //'hbcf' => $data[$head['hbcf']],
                            //'engineering' => $data[$head['engineering']],

                            $site->consultant_name = $data[$head['consultant_name']],
                        ];
                        //$newSite = Site::create($site_data);
                    }
                }
            }

            // Output Report
            echo "<br><br>Found the following " . count($this->siteDiffs) . " Site Difference in the data<br>" . $differences;
        }

        //$bytes_written = File::append($this->logfile), $log);
        //if ($bytes_written === false) die("Error writing to file");
    }

    /**
     * Get Report Type
     */
    public function reportType($data)
    {
        if (stripos($data[0], "Jobs") === 0)
            return 'jobs';
        if (stripos($data[0], "Contacts") === 0)
            return 'contacts';

        return '';
    }

    /**
     * Get Report Headers
     */
    public function reportHeaders($report_type, $data)
    {
        $headers = [];
        $col = 0;

        if ($report_type == 'jobs') {
            foreach ($data as $name) {
                //echo "$col : $name<br>";
                if ($name == 'ACCOUNTID') $headers['zoho_id'] = $col;
                if ($name == 'Job Number') $headers['code'] = $col;
                if ($name == 'Job Name') $headers['name'] = $col;
                // Address
                if ($name == 'Street') $headers['address'] = $col;
                if ($name == 'Suburb') $headers['suburb'] = $col;
                if ($name == 'Post Code') $headers['postcode'] = $col;
                // Supervisor
                if ($name == 'Super') $headers['super_initials'] = $col;
                if ($name == 'Super Name') $headers['super_name'] = $col;
                // Dates
                if ($name == 'Approval Date') $headers['council_approval'] = $col;
                if ($name == 'CX Sent Date') $headers['contract_sent'] = $col;
                if ($name == 'CX Rcvd Date') $headers['contract_received'] = $col;
                if ($name == 'CX Sign Date') $headers['contract_signed'] = $col;
                if ($name == 'CX Deposit Date') $headers['deposit_paid'] = $col;
                if ($name == 'Prac Signed') $headers['completion_signed'] = $col;
                //if ($name == '????') $headers['engineering'] = $col;        // y/n
                if ($name == 'CC Rcvd Date') $headers['construction'] = $col; // y/n
                if ($name == 'HBCF Start Date') $headers['hbcf'] = $col;      // y/n
                if ($name == 'Design Cons') $headers['design_initials'] = $col;
                if ($name == 'Design Cons (user)') $headers['design_name'] = $col;
                $col ++;
            }
        }

        if ($report_type == 'contacts') {
            foreach ($data as $name) {
                //echo "$col : $name<br>";
                if ($name == 'CONTACTID') $headers['zoho_id'] = $col;
                if ($name == 'Job Number') $headers['name'] = $col;
                if ($name == 'Job Name') $headers['name'] = $col;
                // Client contact details
                if ($name == 'First Name 1') $headers['client_phone_desc'] = $col;
                if ($name == 'Mobile') $headers['client_phone'] = $col;
                if ($name == 'Email') $headers['client_email'] = $col;
                if ($name == 'First Name 2') $headers['client_phone2_desc'] = $col;
                if ($name == 'Mobile 2') $headers['client_phone2'] = $col;
                if ($name == 'Email 2') $headers['client_email2'] = $col;
                $col ++;
            }
        }

        return $headers;
    }

    /**
     * Compare Site Data
     */
    public function compareSiteData($site, $data, $head)
    {
        $diff = "[$site->id] $site->code-$site->name<br>";

        $fields = ['name', 'address', 'suburb', 'postcode', 'consultant_name'];
        $dates = ['council_approval', 'contract_sent', 'contract_signed', 'deposit_paid', 'completion_signed'];

        foreach ($fields as $field) {
            if ($field == 'name') {
                $name = "$site->code-$site->name-$site->suburb";
                if (isset($head[$field]) && strtoupper($name) != strtoupper($data[$head[$field]]))
                    $diff .= " &nbsp; $field:" . $site->code . "-" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
            } else if (isset($head[$field]) && strtoupper($site->{$field}) != strtoupper($data[$head[$field]]))
                $diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
        }

        foreach ($dates as $field) {
            if (isset($head[$field])) {
                if ($site->{$field} && $site->{$field}->format('j/n/y') != $data[$head[$field]])
                    $diff .= " &nbsp; $field:" . $site->{$field}->format('j/n/y') . " [" . $data[$head[$field]] . "]<br>";
                else if (!$site->{$field} && $data[$head[$field]]) {
                    //$diff .= " &nbsp; $field:{empty} [".$data[$head[$field]]."]<br>";
                    $this->countBlankDates ++;
                }

            }
        }

        /*
        // Y / N
        //$site->construction = $data[$head['construction']];
        //$site->hbcf = $data[$head['hbcf']];
        //$site->engineering = $data[$head['engineering']];

        if (isset($head['consultant_name']) && $site->consultant_name != $data[$head['consultant_name']]) $diff .= " &nbsp; Consultant:$site->consultant_name [".$data[$head['consultant_name']]."]<br>";
        */

        if ($diff != "[$site->id] $site->code-$site->name<br>") {
            $this->siteDiffs[$site->id] = "$site->code-$site->name";

            return "------------------------------------------------------<br>$diff";
        }

        return '';
    }

    /**
     * Compare Site Data
     */
    public function compareClientData($site, $data, $head)
    {
        $diff = "[$site->id] $site->code-$site->name<br>";

        $fields = ['client_phone', 'client_phone_desc', 'client_email', 'client_phone2', 'client_phone2_desc', 'client_email2'];

        foreach ($fields as $field) {
            if ($field == 'name') {
                $name = "$site->code-$site->name-$site->suburb";
                if (isset($head[$field]) && strtoupper($name) != strtoupper($data[$head[$field]]))
                    $diff .= " &nbsp; $field:" . $site->code . "-" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
            } else if (isset($head[$field]) && strtoupper($site->{$field}) != strtoupper($data[$head[$field]]))
                $diff .= " &nbsp; $field:" . $site->{$field} . " [" . $data[$head[$field]] . "]<br>";
        }

        if ($diff != "[$site->id] $site->code-$site->name<br>") {
            $this->siteDiffs[$site->id] = "$site->code-$site->name";

            return "------------------------------------------------------<br>$diff";
        }

        return '';
    }
}
