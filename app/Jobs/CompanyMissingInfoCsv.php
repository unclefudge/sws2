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

class CompanyMissingInfoCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reportId, public $companies)
    {

    }

    public function handle()
    {
        $report = Report::findOrFail($this->reportId);
        $report->update(['status' => 'processing']);

        $companies = $this->companies;

        try {
            $csv = "Name, Missing Info / Document, Expiry / Last Updated\r\n";
            foreach ($companies as $company) {
                if ($company->missingInfo() && !preg_match('/cc-/', strtolower($company->name)))
                    $csv .= "$company->name, " . $company->missingInfo() . ', ' . $company->updated_at->format('d/m/Y') . "\r\n";

                if ($company->missingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                    foreach ($company->missingDocs() as $type => $name) {
                        $doc = $company->expiredCompanyDoc($type);
                        $csv .= "$company->name, $name, ";
                        $csv .= ($doc != 'N/A' && $company->expiredCompanyDoc($type)->expiry) ? $company->expiredCompanyDoc($type)->expiry->format('d/m/Y') : 'Never';
                        $csv .= "\r\n";
                    }
            }
            Storage::disk('filebank_spaces')->put("$report->path/$report->name", $csv);
            $report->update(['status' => 'completed', 'disk' => 'filebank_spaces']);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'error' => $e->getMessage(),]);
            throw $e;
        }
    }
}
