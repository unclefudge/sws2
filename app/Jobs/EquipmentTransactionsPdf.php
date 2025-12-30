<?php

namespace App\Jobs;

use App\Models\Misc\Report;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;
use PDF;

class EquipmentTransactionsPdf implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(public int $reportId, public $transactions, public $from, public $to)
    {
    }

    public function handle()
    {
        $report = Report::findOrFail($this->reportId);
        $report->update(['status' => 'processing']);

        try {
            $pdf = PDF::loadView('pdf/equipment-transactions', ['transactions' => $this->transactions, 'from' => $this->from, 'to' => $this->to])->setPaper('a4', 'landscape')->output();
            Storage::disk('filebank_spaces')->put("$report->path/$report->name", $pdf);
            $report->update(['status' => 'completed', 'disk' => 'filebank_spaces']);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'error' => $e->getMessage(),]);
            throw $e;
        }
    }
}
