<?php

namespace App\Jobs;

use App\Models\Misc\Report;
use DB;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;
use PDF;


class SitePlannerPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public int $reportId, public array $data, public string $view,)
    {
        if (empty($view)) {
            throw new \InvalidArgumentException('PDF view cannot be empty');
        }
    }


    public function handle()
    {
        $report = Report::findOrFail($this->reportId);
        $report->update(['status' => 'processing']);

        try {
            $pdf = PDF::loadView($this->view, ['data' => $this->data])->setPaper('a4', 'landscape')->output();
            Storage::disk('filebank_spaces')->put("$report->path/$report->name", $pdf);
            $report->update(['status' => 'completed', 'disk' => 'filebank_spaces']);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'error' => $e->getMessage(),]);
            throw $e;
        }
    }
}
