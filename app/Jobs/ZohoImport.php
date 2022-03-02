<?php

namespace App\Jobs;

use DB;
use PDF;
use Log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ZohoImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $site_id, $data, $output_file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($site_id, $data, $output_file)
    {
        $this->site_id = $site_id;
        $this->data = $data;
        $this->output_file = $output_file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $site = Site::findOrFail($this->site_id);
        $data = $this->data;

        $pdf = PDF::loadView('pdf/site-qa', compact('site', 'data'));
        $pdf->setPaper('a4');
        $pdf->save($this->output_file);
    }
}
