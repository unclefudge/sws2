<?php

namespace App\Jobs;

use DB;
use PDF;
use Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Company\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CompanyMissingInfoPlannerCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected  $companies, $output_file;

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
                $csv .= "$company->name, " .$company->missingInfo(). ', ' . $company->updated_at->format('d/m/Y') . "\r\n";

            if ($company->missingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                foreach ($company->missingDocs() as $type => $name) {
                    $doc = $company->expiredCompanyDoc($type);
                    $csv .= "$company->name, $name, ";
                    $csv .= ($doc != 'N/A' && $company->expiredCompanyDoc($type)->expiry) ? $company->expiredCompanyDoc($type)->expiry->format('d/m/Y') : 'Never';
                    $csv .= ", ".$planner_date->longAbsoluteDiffForHumans();
                    $csv .= "\r\n";
                }
        }

        //echo $csv;
        $bytes_written = File::put($output_file, $csv);
        //if ($bytes_written === false) die("Error writing to file");
    }
}
