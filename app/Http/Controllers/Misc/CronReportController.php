<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;

use DB;
use PDF;
use Mail;
use File;
use Carbon\Carbon;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteQaAction;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Comms\SafetyTip;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CronReportController extends Controller {

    static public function nightly()
    {
        echo "<h1> Nightly Reports - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";
        $log = "\n\n------------------------------------------------------------------------\n\n";
        $log .= "Nightly Reports\n";
        $log .= "------------------------------------------------------------------------\n\n\n\n";
        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");


        if (Carbon::today()->isTuesday())
            CronReportController::emailOutstandingQA();

        // Monthly first Tuesday of the month
        $first_tues = new Carbon('first tuesday of this month');
        if (Carbon::today()->isSameDay($first_tues))
            CronReportController::emailMissingCompanyInfo();


        if (Carbon::today()->isThursday()) {
            CronReportController::emailJobstart();
            CronReportController::emailEquipmentTransfers();
            CronReportController::emailOnHoldQA();
        }

        // Fortnightly on Mondays starting 26 Oct 2020
        $start_monday = Carbon::createFromFormat('Y-m-d', '2020-10-26');
        if (Carbon::today()->isMonday() && $start_monday->diffInDays(Carbon::now()) % 2 == 0)
            CronReportController::emailFortnightlyReports();

        // Quarterly Reports 25th of month
        if (Carbon::today()->format('d') == '01' && in_array(Carbon::today()->format('m'), ['03', '06', '09', '12']))
            CronReportController::emailMaintenanceExecutive();

    }

    /*
    * Email Outstanding QA checklists
    */
    static public function emailOutstandingQA()
    {
        $log = '';
        echo "<h2>Email Outstanding QA Checklists</h2>";
        $log .= "Email Outstanding QA Checklists\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.site.qa.outstanding'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        // Create PDF
        $file = public_path('filebank/tmp/qa-outstanding-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'today'));
        //return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'today'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.site.qa.outstanding');
        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Missing Company Info
    */
    static public function emailMissingCompanyInfo()
    {
        $log = '';
        echo "<h2>Email Missing Company Info</h2>";
        $log .= "Email Missing Company Info\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.company.missing.info'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";


        $all_companies = $cc->companies(1);
        $cids = [];
        foreach ($all_companies as $company) {
            //if (!$company->activeCompanyDoc(12) && !preg_match('/cc-/', strtolower($company->name)))
            if (!preg_match('/cc-/', strtolower($company->name)))
                $cids[] = $company->id;
        }
        $companies = Company::find($cids)->sortBy('name');

        $comps = [];
        foreach ($companies as $company) {
            if ($company->missingInfo() && !preg_match('/cc-/', strtolower($company->name)))
                $comps[] = [$company->name, $company->missingInfo(), $company->updated_at->format('d/m/Y')];

            if ($company->missingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                foreach ($company->missingDocs() as $type => $name) {
                    $doc = $company->expiredCompanyDoc($type);
                    $comps[] = [$company->name, $name, ($doc != 'N/A') ? $company->expiredCompanyDoc($type)->expiry->format('d/m/Y') : 'Never'];
                }
        }

        $email_list = $cc->notificationsUsersEmailType('n.company.missing.info');
        Mail::to($email_list)->send(new \App\Mail\Company\CompanyMissingInfo($comps));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /*
    * Email Jobstart
    */
    static public function emailJobstart()
    {
        $log = '';
        echo "<h2>Email Jobstart</h2>";
        $log .= "Email Jobstart\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.site.jobstartexport'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $today = Carbon::now()->format('Y-m-d');
        $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.code', 'START')
            ->orderBy('p.from')->get();

        //dd($planner);
        $startdata = [];
        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            $entity_name = "Carpenter";
            if ($plan->entity_type == 'c')
                $entity_name = Company::find($plan->entity_id)->name;
            $startdata[] = [
                'date'            => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                'code'            => $site->code,
                'name'            => $site->name,
                'company'         => $entity_name,
                'supervisor'      => $site->supervisorsSBC(),
                'contract_sent'   => ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-',
                'contract_signed' => ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-',
                'deposit_paid'    => ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-',
                'eng'             => ($site->engineering) ? 'Y' : '-',
                'cc'              => ($site->construction) ? 'Y' : '-',
                'hbcf'            => ($site->hbcf) ? 'Y' : '-',
                'consultant'      => $site->consultant_name,
            ];
        }

        // Create PDF
        $file = public_path('filebank/tmp/jobstart-cron.pdf');
        if (file_exists($file))
            unlink($file);
        $pdf = PDF::loadView('pdf/plan-jobstart', compact('startdata'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.site.jobstartexport');
        $data = [
            'user_fullname'     => "Auto Generated",
            'user_company_name' => "Cape Cod",
            'startdata'         => $startdata
        ];
        Mail::send('emails/jobstart', $data, function ($m) use ($email_list, $data, $file) {
            $send_from = 'do-not-reply@safeworksite.com.au';
            $m->from($send_from, 'Safe Worksite');
            $m->to($email_list);
            $m->subject('Upcoming Job Start Dates');
            $m->attach($file);
        });


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Equipment Transfers
    */
    static public function emailEquipmentTransfers()
    {
        $log = '';
        echo "<h2>Email Equipment Transfers</h2>";
        $log .= "Email Equipment Transfers\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.equipment.transfers'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $to = Carbon::now();
        $from = Carbon::now()->subDays(7);
        $transactions = EquipmentLog::where('action', 'T')->whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('created_at', '<=', $to->format('Y-m-d'))->get();

        // Create PDF
        $file = public_path('filebank/tmp/equipment-transfers-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/equipment-transfers', compact('transactions', 'from', 'to'));
        //return PDF::loadView('pdf/equipment-transfers', compact('transactions', 'from', 'to'))->setPaper('a4', 'portrait')->stream();

        $pdf = PDF::loadView('pdf/equipment-transfers', compact('transactions', 'from', 'to'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.equipment.transfers');

        Mail::to($email_list)->send(new \App\Mail\Misc\EquipmentTransfers($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /*
     * Email OnHold QA checklists
    */
    static public function emailOnHoldQA()
    {
        $log = '';
        echo "<h2>Email On Hold QA Checklists</h2>";
        $log .= "Email On Hold QA Checklists\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.site.qa.onhold'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $today = Carbon::now();
        $qas = SiteQa::where('status', 2)->where('master', 0)->orderBy('updated_at')->get();

        // Create PDF
        $file = public_path('filebank/tmp/qa-onhold-cron.pdf');
        if (file_exists($file))
            unlink($file);

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        //return view('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'));
        //return PDF::loadView('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.site.qa.onhold');
        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOnhold($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Fortnightly Reports
    */
    static public function emailFortnightlyReports()
    {
        $log = '';
        echo "<h2>Email Fortnightly Reports</h2>";
        $log .= "Email Fortnightly Reports\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('n.site.maintenance.noaction');
        $emails = implode("; ", $email_list);
        echo "Sending No Actions email to $emails";
        $log .= "Sending No Actions email to $emails";

        //
        // Active Requests with No Action 14 Days
        //
        $active_requests = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        $mains = [];
        foreach ($active_requests as $main) {
            if ($main->lastUpdated()->lt(Carbon::now()->subDays(14)))
                $mains[] = $main;
        }

        $data = ['data' => $mains];

        if ($email_list) {
            Mail::send('emails/site/maintenance-noaction', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->cc('support@openhands.com.au');
                $m->subject('Maintenance Requests No Action');
            });
        }


        //
        // On Hold Requests
        //
        $email_list = $cc->notificationsUsersEmailType('n.site.maintenance.onhold');
        $emails = implode("; ", $email_list);
        echo "Sending On Hold email to $emails";
        $log .= "Sending On Hold email to $emails";
        $hold_requests = SiteMaintenance::where('status', 3)->orderBy('reported')->get();
        $data = ['data' => $hold_requests];

        if ($email_list) {
            Mail::send('emails/site/maintenance-onhold', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->cc('support@openhands.com.au');
                $m->subject('Maintenance Requests On Hold');
            });
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Site Maintenance Executive Report
    */
    static public function emailMaintenanceExecutive()
    {
        $log = '';
        echo "<h2>Email Site Maintenance Executive Report</h2>";
        $log .= "Email Site Maintenance Executive Report\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $emails = implode("; ", $cc->notificationsUsersEmailType('n.site.maintenance.executive'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $to = Carbon::now();
        $from = Carbon::now()->subDays(90);

        $mains = SiteMaintenance::whereDate('updated_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->get();
        $mains_old = SiteMaintenance::whereDate('updated_at', '<', $from->format('Y-m-d'))->whereIn('status', [1, 3])->get();
        $mains_created = SiteMaintenance::whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->get();

        $count = $count_allocated = 0;
        $total_allocated = $total_completed = 0;
        $cats = [];
        $supers = [];

        foreach ([$mains, $mains_old] as $mains_collect) {
            foreach ($mains_collect as $main) {
                $days = ($main->status == 1) ? $main->reported->diffInWeekDays($to) : $main->reported->diffInWeekDays($main->updated_at);
                $total_completed = $total_completed + $days;

                // Assigned Requests
                if ($main->assigned_at) {
                    // Need to set assigned_at time to 00:00 so we don't add and extra 'half' day if reported at 9am but assigned at 10am next day
                    $assigned_at = Carbon::createFromFormat('d/m/Y H:i', $main->assigned_at->format('d/m/Y') . '00:00');
                    $days = $assigned_at->diffInWeekDays($main->reported);
                    $total_allocated = $total_allocated + $days;
                    $count_allocated ++;
                }

                // Count Categories
                $name = ($main->category_id) ? SiteMaintenanceCategory::find($main->category_id)->name : 'N/A';
                if (!array_key_exists($name, $cats))
                    $cats[$name] = 1;
                else
                    $cats[$name] = $cats[$name] + 1;

                // Count Supers
                $name = ($main->super_id) ? User::find($main->super_id)->name : 'N/A';
                if (!array_key_exists($name, $supers)) {
                    $active = ($main->status == 1) ? 1 : 0;
                    $completed = ($main->status == 0) ? 1 : 0;
                    $onhold = ($main->status == 3) ? 1 : 0;
                    $supers[$name] = [$active, $completed, $onhold];
                } else {
                    $active = ($main->status == 1) ? $supers[$name][0] + 1 : $supers[$name][0];
                    $completed = ($main->status == 0) ? $supers[$name][1] + 1 : $supers[$name][1];
                    $onhold = ($main->status == 3) ? $supers[$name][2] + 1 : $supers[$name][2];
                    $supers[$name] = [$active, $completed, $onhold];
                }
                $count ++;
            }
        }

        ksort($cats);
        ksort($supers);
        $avg_completed = ($count) ? round($total_completed / $count) : 0;
        $avg_allocated = ($count_allocated) ? round($total_allocated / $count_allocated) : 0;

        // Create PDF
        $file = public_path('filebank/tmp/maintenace-executive-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'cats', 'supers'));
        //return PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'cats', 'supers'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'cats', 'supers'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.site.maintenance.executive');
        $email_list = ['fudge@jordan.net.au'];
        Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceExecutive($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }
}