<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Log;

class CompanyMissingInfoPlannerCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $companies, $output_file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($companies, $output_file)
    {
        $this->companies = $companies;
        $this->output_file = $output_file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $companies = $this->companies;
        $output_file = $this->output_file;

        $csv = "Name, Missing Info / Document, Expired / Last Updated, Next on Planner\r\n";
        foreach ($companies as $company) {
            //$company = Company::find($c->id);
            $planner_date = $company->nextDateOnPlanner();
            if ($company->missingInfo() && !preg_match('/cc-/', strtolower($company->name)))
                $csv .= "$company->name, " . $company->missingInfo() . ', ' . $company->updated_at->format('d/m/Y') . "\r\n";

            // Next on Planner
            $planner_date = $company->nextDateOnPlanner();
            $next_on_planner = ($planner_date) ? $planner_date->longAbsoluteDiffForHumans() : '';

            if ($company->missingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                foreach ($company->missingDocs() as $type => $name) {
                    $doc = $company->expiredCompanyDoc($type);
                    $csv .= "$company->name, $name, ";
                    $csv .= ($doc != 'N/A' && $company->expiredCompanyDoc($type)->expiry) ? $company->expiredCompanyDoc($type)->expiry->format('d/m/Y') : 'Never';
                    $csv .= ", $next_on_planner";
                    $csv .= "\r\n";
                }
        }

        //echo $csv;
        $bytes_written = File::put($output_file, $csv);
        //if ($bytes_written === false) die("Error writing to file");
    }
}
