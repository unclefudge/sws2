<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MailgunZohoController extends Controller
{
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
            foreach ($files as $file) {
                app('log')->debug("-*-----*-");
                //app('log')->debug($file);
                app('log')->debug("N:".$file['name']." U:".$file['url']);

                // Fetch file from Mailgun storage
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $file['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, "api:".config('services.mailgun.secret'));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $output = curl_exec($ch);
                //app('log')->debug("-=-----=-");
                //app('log')->debug($output);
                curl_close($ch);

                // Save the file
                $dir = '/filebank/tmp/zoho';
                // Create directory if required
                if (!is_dir(public_path($dir))) mkdir(public_path($dir), 0777, true);

                $saved_file = public_path($dir.'/'.$file['name']);
                $fp = fopen($saved_file, "w") or die("Unable to open file!");
                fwrite($fp, $output);
                fclose($fp);
            }

            //dispatch(new ProcessWidgetFiles($files));
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
