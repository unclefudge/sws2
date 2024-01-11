<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Misc\Attachment;
use App\Models\Site\Site;
use App\Models\Site\SiteNote;
use Carbon\Carbon;
use File;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mail;

class MailgunSiteNoteController extends Controller
{

    public $debug = true;
    public $logfile = '';

    public function store(Request $request)
    {
        if ($this->debug) app('log')->debug("========= SiteNote Import ==========");
        if ($this->debug) app('log')->debug(request()->all());
        ray(request()->all());

        // Ensure Email is sent from specified address
        $valid_senders = ['<fudge@jordan.net.au>', 'fudge@jordan.net.au', '<systemgenerated@zohocrm.com>', 'systemgenerated@zohocrm.com'];
        if (!(in_array(request('From'), $valid_senders) || in_array(request('X-Envelope-From'), $valid_senders))) {  // X-Envelope-From
            if ($this->debug) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid Sender: [" . request('X-Envelope-From') . "]");
            if ($this->debug) app('log')->debug($valid_senders);

            return response()->json(['status' => 'error', 'message' => 'Invalid email'], 406);  // Mailgun fail message
        }

        // Get email fields
        $emailFrom = request('X-Envelope-From');
        $emailSubject = request('subject');
        $emailBody = request('body-plain');

        if (str_contains($emailSubject, '#SiteNote:')) {
            if ($this->debug) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid Subject: [$emailSubject]");

            return response()->json(['status' => 'error', 'message' => 'Invalid email'], 406);  // Mailgun fail message
        }

        // Get Site Details from Subject  #SiteNote:1234
        list($crap, $rest) = explode('#SiteNote:', $emailSubject, 2);
        $filteredNumbers = array_filter(preg_split("/\D+/", $rest));
        $siteCode = reset($filteredNumbers);

        $site = Site::where('code', $siteCode)->first();
        if (!$site) {
            if ($this->debug) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid SiteCode: [$siteCode]");
            return response()->json(['status' => 'error', 'message' => 'Invalid site'], 406);  // Mailgun fail message
        }


        if ($this->debug) app('log')->debug("========= SiteNote Import ==========");
        if ($this->debug) app('log')->debug("Site:" . $site->name . "\nBody:\n$emailBody\n**** End Body ****\n");


        // SiteNote log
        $dir = '/filebank/log/sitenote';
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required
        $this->logfile = public_path('filebank/log/sitenote/sitenote.txt');

        $log = "------------------------------------------\nSiteNote Import - " . Carbon::now()->format('d/m/Y g:i a') . "\n------------------------------------------\n\n";
        $log .= "From: $emailFrom\n";
        $log .= "Site:" . $site->name . "\n";
        $log .= "Body:\n$emailBody\n**** end ****\n";


        // Create New Site Note
        $note = SiteNote::create([]);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_notes', 'table_id' => $note->id, 'directory' => "/filebank/site/$note->site_id/note"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }


        $bytes_written = File::append($this->logfile, $log);
        if ($bytes_written === false) die("Error writing to file");

        // Get the attachments
        $dir = '/filebank/tmp/sitenote';
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

        $files = collect(json_decode(request()->input('attachments'), true));
        if ($files->count()) {
            foreach ($files as $file) {
                //$mailgun_file = $this->retrieveMailgunFile($file['url']);  // Get file from Mailgun storage

                // Save the file
                $saved_file = public_path($dir . '/' . substr($file['name'], 0, -4) . '.' . Carbon::now()->format('YmdHis') . '.csv');
                $guzzleClient = new Client();
                $response = $guzzleClient->get($file['url'], ['auth' => ['api', config('services.mailgun.secret')]]);
                file_put_contents($saved_file, $response->getBody());
                if ($this->debug) app('log')->debug("Saving file: $saved_file");

                //$result = $this->parseFile($saved_file);
            }
        }


        return response()->json(['status' => 'ok'], 200);
    }
}
