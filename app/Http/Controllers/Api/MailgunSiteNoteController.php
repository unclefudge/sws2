<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Models\Site\SiteNote;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Mail;

class MailgunSiteNoteController extends Controller
{

    public $debug = true;
    public $logfile = '';

    public function store(Request $request)
    {
        //if ($this->debug) app('log')->debug("========= SiteNote Import ==========");
        //if ($this->debug) app('log')->debug(request()->all());

        // Ensure Email is sent from specified address
        $valid_senders_domains = ['jordan.net.au', 'capecod.com.au'];
        $sender = request('sender');
        list($send_name, $sender_domain) = explode('@', $sender);
        if (!in_array($sender_domain, $valid_senders_domains) || str_contains($sender, 'safeworksite.com.au')) {  // Sender
            if ($this->debug && !str_contains($sender, 'safeworksite.com.au')) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug && !str_contains($sender, 'safeworksite.com.au')) app('log')->debug("Invalid Sender: [$sender]");

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

        // Get Site Details from Subject  [SiteNote#1234-11]
        list($crap, $rest) = explode('SiteNote[#', $emailSubject, 2);
        list($siteCase, $crap) = explode(']', $rest, 2);
        list($siteCode, $siteNote) = explode('-', $siteCase);

        $site = Site::where('code', $siteCode)->first();
        if (!$site) {
            if ($this->debug) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid SiteCode: [$siteCode]");
            return response()->json(['status' => 'error', 'message' => 'Invalid site'], 406);  // Mailgun fail message
        }

        $note = SiteNote::where('id', $siteNote)->first();
        if (!$note) {
            if ($this->debug) app('log')->debug("========= SiteNote Import Failed ==========");
            if ($this->debug) app('log')->debug("Invalid SiteNote: [$siteNote]");
            return response()->json(['status' => 'error', 'message' => 'Invalid note'], 406);  // Mailgun fail message
        }

        // Valid Site Note - Process
        //if ($this->debug) app('log')->debug("========= SiteNote Import ==========");
        //if ($this->debug) app('log')->debug("Site:" . $site->name);
        //if ($this->debug) app('log')->debug("Note:" . $note->id);
        //if ($this->debug) app('log')->debug("Body:\n$emailBody\n**** End Body ****\n");


        // SiteNote log
        $dir = '/filebank/log';
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required
        $this->logfile = public_path('filebank/log/sitenote.txt');

        $log = "------------------------------------------\nSiteNote Import - " . Carbon::now()->format('d/m/Y g:i a') . "\n------------------------------------------\n\n";
        $log .= "From: $emailFrom\n";
        $log .= "Site:" . $site->name . "\n";
        $log .= "Body:\n$emailBody\n**** end ****\n";


        // Create New Site Note
        $note->touch();
        $newNote = SiteNote::create(['site_id' => $site->id, 'category_id' => $note->category_id, 'notes' => "[Note Reply From: $sender]\n$emailBody", 'parent' => $note->id, 'created_by' => 1, 'updated_by' => 1]);


        // Get the attachments
        $dir = '/filebank/tmp/sitenote';
        if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);  // Create directory if required

        $files = collect(json_decode(request()->input('attachments'), true));
        if ($files->count()) {
            foreach ($files as $file) {
                // Save the file
                $saved_file = public_path($dir . '/' . $file);
                $guzzleClient = new Client();
                $response = $guzzleClient->get($file['url'], ['auth' => ['api', config('services.mailgun.secret')]]);
                file_put_contents($saved_file, $response->getBody());
                if ($this->debug) app('log')->debug("Saving file: $saved_file");

                $attachment = Attachment::create(['table' => 'site_notes', 'table_id' => $note->id, 'directory' => "/filebank/site/$note->site_id/note"]);
                $attachment->saveAttachment($saved_file);

                //$result = $this->parseFile($saved_file);
            }
        }

        // Email New Note
        //$note->emailNote();

        $bytes_written = File::append($this->logfile, $log);
        if ($bytes_written === false) die("Error writing to file");

        return response()->json(['status' => 'ok'], 200);
    }
}
