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
        if (Carbon::now()->format('d') == '25' && in_array(Carbon::now()->format('m'), ['02','05','08','11']))
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

        //return view('pdf/site/site-qa-onhold', compact('qas', 'today'));
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
        $mains = SiteMaintenance::whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('created_at', '<=', $to->format('Y-m-d'))->get();

        // Create PDF
        $file = public_path('filebank/tmp/maintenace-executive-cron.pdf');
        if (file_exists($file))
            unlink($file);

        return view('pdf/site/maintenance-executive', compact('mains', 'from', 'to'));
        //return PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'from', 'to'))->setPaper('a4', 'portrait')->stream();

        $pdf = PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'from', 'to'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('n.site.maintenance.executive');

        Mail::to($email_list)->send(new \App\Mail\Misc\EquipmentTransfers($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }
}