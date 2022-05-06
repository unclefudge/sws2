<?php

namespace App\Jobs;

use DB;
use PDF;
use Log;
use Mail;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Company\Company;
use App\Models\Site\Site;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ZohoImportVerify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logfile, $report_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($logfile)
    {
        $this->logfile = $logfile;
        //$this->report_type = $report_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //app('log')->debug("Log:$this->logfile");
        if (strpos(file_get_contents($this->logfile), "ALL DONE - ZOHO IMPORT COMPLETE") !== false) {
            //Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed('Zoho Import was SUCESSFUL'));
        } else {
            //Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed(''));
        }
    }
}
