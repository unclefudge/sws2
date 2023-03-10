<?php

namespace App\Jobs;

use DB;
use PDF;
use Log;
use App\Models\Site\SiteExtension;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SiteExtensionPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $view, $extension, $data, $output_file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($view, SiteExtension $extension, $data, $output_file)
    {
        $this->view = $view;
        $this->extension = $extension;
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
        $extension = $this->extension;
        $data = $this->data;

        $pdf = PDF::loadView($this->view, compact('extension', 'data'));
        $pdf->setPaper('a4', 'landscape');
        $pdf->save($this->output_file);
    }
}
