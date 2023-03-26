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
use App\Models\Site\SiteUpcomingSettings;
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
use App\Http\Controllers\Site\SiteUpcomingComplianceController;
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

        // Weekly Reports
        if (Carbon::today()->isMonday()) {
            CronReportController::emailJobstart();
            CronReportController::emailMaintenanceAppointment();
            CronReportController::emailMaintenanceSupervisorNoAction();
            CronReportController::emailMaintenanceUnderReview();
            CronReportController::emailMissingCompanyInfo();
        }

        if (Carbon::today()->isTuesday()) {
            CronReportController::emailOutstandingQA();
            CronReportController::emailUpcomingJobCompilance();
        }

        if (Carbon::today()->isThursday()) {
            CronReportController::emailEquipmentTransfers();
            CronReportController::emailOnHoldQA();
        }

        if (Carbon::today()->isFriday())
            CronReportController::emailEquipmentRestock();


        // Fortnightly on Mondays starting 26 Oct 2020
        $start_monday = Carbon::createFromFormat('Y-m-d', '2020-10-26');
        if (Carbon::today()->isMonday() && $start_monday->diffInDays(Carbon::now()) % 2 == 0)
            CronReportController::emailFortnightlyReports();

        // Monthly first Tuesday of the month
        $first_tues = new Carbon('first tuesday of this month');
        if (Carbon::today()->isSameDay($first_tues)) {
            CronReportController::emailOldUsers();
        }

        // Monthly last Friday of the month
        $last_fri = new Carbon('last friday of this month');
        if (Carbon::today()->isSameDay($last_fri))
            CronReportController::emailOutstandingAftercare();

        // Quarterly Reports 1th of month
        if (Carbon::today()->format('d') == '01' && in_array(Carbon::today()->format('m'), ['03', '06', '09', '12']))
            CronReportController::emailMaintenanceExecutive();

    }

    /*
    * Email Outstanding QA checklists
    */
    static public function emailOutstandingQA()
    {
        $log = '';
        $email_name = "Outstanding QA Checklists";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.qa.outstanding');
        $emails = implode("; ", $email_list);
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

        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file, $qas));

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
        $email_name = "Missing Company Info";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('company.missing.info');
        $emails = implode("; ", $email_list);
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
        $email_name = "Jobstart";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.jobstartexport');
        $emails = implode("; ", $email_list);
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
            if ($site->status == 1) {
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
                    'cc'              => ($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : '-',
                    'hbcf'            => ($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : '-',
                    'consultant'      => $site->consultant_name,
                ];
            }
        }

        // Create PDF
        $file = public_path('filebank/tmp/jobstart-cron.pdf');
        if (file_exists($file))
            unlink($file);
        $pdf = PDF::loadView('pdf/plan-jobstart', compact('startdata'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

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
   * Email UpcomingJobCompliance
   */
    static public function emailUpcomingJobCompilance()
    {
        $log = '';
        $email_name = "Upcoming Job Compliance";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.upcoming.compliance');
        $emails = implode("; ", $email_list);
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";


        // Colours
        $types = ['opt', 'cfest', 'cfadm'];
        foreach ($types as $type) {
            $settings_select[$type] = ['' => 'Select stage'] + SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('name', 'order')->toArray();
            $colours = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('colour', 'order')->toArray();
            $settings_colours[$type] = [];
            if ($colours) {
                foreach ($colours as $order => $colour) {
                    if ($colour) {
                        list($col1, $col2, $hex) = explode('-', $colour);
                        $settings_colours[$type][$order] = "#$hex";
                    } else
                        $settings_colours[$type][$order] = '';
                }
            }
            $settings_text[$type] = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('value', 'order')->toArray();
        }

        $startdata = SiteUpcomingComplianceController::getUpcomingData();
        //dd($startdata);

        // Create PDF
        $file = public_path('filebank/tmp/upcoming-jobs-compliance.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf = PDF::loadView('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        //Mail::to($email_list)->send(new \App\Mail\Site\SiteUpcomingCompliance($startdata, $file));
        if ($email_list) {
            $data = ['startdata' => $startdata, 'settings_colours' => $settings_colours];
            Mail::send('emails/site/upcoming-compliance', $data, function ($m) use ($email_list, $data, $file) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('SafeWorksite - Upcoming Jobs Compliance Data');
                $m->attach($file);
            });
        }

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
        $email_name = "Equipment Transfers";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('equipment.transfers');
        $emails = implode("; ", $email_list);
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

        Mail::to($email_list)->send(new \App\Mail\Misc\EquipmentTransfers($file, $transactions));

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
        $email_name = "On Hold QA Checklists";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.qa.onhold');
        $emails = implode("; ", $email_list);
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

        //dd($file);

        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOnhold($file, $qas));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /*
    * Email Maintenance Under Review
    */
    static public function emailMaintenanceUnderReview()
    {
        $log = '';
        $email_name = "Maintenance Under Review";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.underreview');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";
        $mains = SiteMaintenance::where('status', 2)->orderBy('reported')->get();
        $today = Carbon::now();

        // Create PDF
        $file = public_path('filebank/tmp/maintenance-under-review-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/maintenance-under-review', compact('mains', 'today'));
        //return PDF::loadView('pdf/site/maintenance-under-review', compact('mains', 'today'))->setPaper('a4', 'portrait')->stream();
        $pdf = PDF::loadView('pdf/site/maintenance-under-review', compact('mains', 'today'))->setPaper('a4', 'portrait');
        $pdf->save($file);

        Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceUnderReviewReport($file, $mains));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Maintenance Supervisor No Action
    */
    static public function emailMaintenanceSupervisorNoAction()
    {
        $log = '';
        $email_name = "Maintenance Supervisor No Action";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.super.noaction');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";
        $mains = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        //$app_requests = SiteMaintenance::where('status', 1)->where('client_appointment', null)->orderBy('reported')->get();
        $today = Carbon::now();

        // Supervisors list
        $supers = [];
        foreach ($mains as $main) {
            if (!isset($supers[$main->super_id]))
                $supers[$main->super_id] = $main->taskOwner->fullname;
        }
        asort($supers);

        // Create PDF
        $file = public_path('filebank/tmp/maintenance-supervisor-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'));
        //return PDF::loadView('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
        $pdf = PDF::loadView('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'))->setPaper('a4', 'landscape');
        $pdf->save($file);

        Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceSupervisorNoActionReport($file, $mains));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Maintenance Without Appointment
    */
    static public function emailMaintenanceAppointment()
    {
        $log = '';
        $email_name = "Maintenance Without Appointment";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.appointment');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";
        $app_requests = SiteMaintenance::where('status', 1)->where('client_appointment', null)->orderBy('reported')->get();
        $data = ['data' => $app_requests];

        if ($email_list) {
            Mail::send('emails/site/maintenance-appointment', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Maintenance Requests Without Appointment');
            });
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
   * Email Outstanding After Care
   */
    static public function emailOutstandingAftercare()
    {
        $log = '';
        $email_name = "Outstanding After Care";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.aftercare');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";
        $mains = SiteMaintenance::where('status', 0)->where('ac_form_sent', null)->orderBy('updated_at')->get();
        $data = ['data' => $mains];

        if ($email_list) {
            Mail::send('emails/site/maintenance-aftercare', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Maintenance Requests Without After Care');
            });
        }

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
        $email_name = "Maintenance No Actions";
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.noaction');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";

        //
        // Active Requests with No Action 14 Days
        //
        $active_requests = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        $mains = [];
        foreach ($active_requests as $main) {
            if ($main->lastUpdated()->lt(Carbon::now()->subDays(14)))
                $mains[$main->lastAction()->updated_at->format('Ymd')] = $main;
        }
        ksort($mains);

        $data = ['data' => $mains];
        if ($email_list) {
            Mail::send('emails/site/maintenance-noaction', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Maintenance Requests No Action');
            });
        }


        //
        // On Hold Requests
        //
        $email_name = "Maintenance On Hold";
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.onhold');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails<br>";
        $log .= "Sending $email_name email to $emails";
        $hold_requests = SiteMaintenance::where('status', 3)->orderBy('reported')->get();
        $data = ['data' => $hold_requests];

        if ($email_list) {
            Mail::send('emails/site/maintenance-onhold', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Maintenance Requests On Hold');
            });
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /*
    * Email Old Users
    */
    static public function emailOldUsers()
    {
        $log = '';
        $email_name = "Old Users";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('user.oldusers');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails";
        $log .= "Sending $email_name email to $emails";


        $cc_users = $cc->users(1)->pluck('id')->toArray();
        $user_list = User::where('status', 1)->whereIn('id', $cc_users)->orderBy('company_id', 'ASC')->pluck('id')->toArray();

        $date_3_month = Carbon::today()->subMonths(3);
        $user_list2 = User::where('status', 1)->whereIn('id', $user_list)->wheredate('last_login', '<', $date_3_month->format('Y-m-d'))->orderBy('company_id', 'ASC')->get();

        $user_list3 = [];
        foreach ($user_list2 as $user) {
            if (in_array($user->company->category, [1, 2]) && $user->company->status == 1 && $user->hasAnyRole2('ext-leading-hand|tradie|labourers')) { // Onsite Trade + Active Company + appropriate role
                if (!$user->last_login || ($user->last_login->lt($date_3_month) && $user->last_login->lt($user->company->lastDateOnPlanner()))) { // User not logged in or not logged in last 3 months but company has been on planner
                    $user_list3[] = $user->id;
                }
            }
        }

        $users = User::whereIn('id', $user_list3)->orderBy('company_id', 'ASC')->get();
        //dd($users);

        Mail::to($email_list)->send(new \App\Mail\User\OldUsers($users));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Equipment Restock
    */
    static public function emailEquipmentRestock()
    {
        $log = '';
        $email_name = "Equipment Restock";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('equipment.restock');
        $emails = implode("; ", $email_list);
        echo "Sending $email_name email to $emails";
        $log .= "Sending $email_name email to $emails";

        $equipment = Equipment::where('min_stock', '!=', null)->where('status', 1)->orderBy('name')->get();
        $data = ['data' => $equipment];

        if ($email_list) {
            Mail::send('emails/misc/equipment-restock', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('SafeWorksite - Equipment Restock');
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
    static public function emailMaintenanceExecutive($email_list = null)
    {
        $log = '';
        $email_name = "Site Maintenance Executive Report";
        echo "<h2>Email $email_name</h2>";
        $log .= "Email $email_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.executive');
        $emails = implode("; ", $email_list);
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $to = Carbon::now();
        $from = Carbon::now()->subDays(90);

        $mains = SiteMaintenance::whereDate('updated_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->where('status', '<>', 2)->get();
        $mains_old = SiteMaintenance::whereDate('updated_at', '<', $from->format('Y-m-d'))->whereIn('status', [1, 3])->get();
        $mains_created = SiteMaintenance::whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->get();

        $count = $count_allocated = $excluded = 0;
        $total_allocated = $total_completed = $total_contacted = $total_appoint = 0;
        $cats = [];
        $supers = [];

        foreach ([$mains, $mains_old] as $mains_collect) {
            foreach ($mains_collect as $main) {
                if ($main->created_at->gte(Carbon::createFromFormat('Y-m-d', '2021-05-01'))) {
                    $days = ($main->status == 1) ? $main->reported->diffInWeekDays($to) : $main->reported->diffInWeekDays($main->updated_at);
                    $total_completed = $total_completed + $days;

                    // Avg Assigned Days
                    if ($main->assigned_super_at) {
                        $assigned_at = Carbon::createFromFormat('d/m/Y H:i', $main->assigned_super_at->format('d/m/Y') . '00:00'); // Need to set assigned_at time to 00:00 so we don't add and extra 'half' day if reported at 9am but assigned at 10am next day
                        $assigned_days = $assigned_at->diffInWeekDays($main->reported);
                    } elseif ($main->status == 0 || $main->status == 3)
                        $assigned_days = $main->reported->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $assigned_days = $main->reported->diffInWeekDays($to);

                    $total_allocated = $total_allocated + $assigned_days;

                    // Avg Client Contacted Days
                    if ($main->client_contacted)
                        $total_contacted = $total_contacted + $main->client_contacted->diffInWeekDays($main->reported);
                    elseif ($main->status == 0 || $main->status == 3)
                        $total_contacted = $total_contacted + $main->reported->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $total_contacted = $total_contacted + $main->reported->diffInWeekDays($to);

                    // Avg Appointment to Completion Days
                    $appoint_from = ($main->client_appointment) ? $main->client_appointment : $main->reported;
                    if ($main->status == 0 || $main->status == 3)
                        $total_appoint = $total_appoint + $appoint_from->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $total_appoint = $total_appoint + $appoint_from->diffInWeekDays($to);

                    $count ++;
                } else {
                    $excluded ++;
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

            }
        }

        ksort($cats);
        ksort($supers);
        $avg_completed = ($count) ? round($total_completed / $count) : 0;
        $avg_allocated = ($count) ? round($total_allocated / $count) : 0;
        $avg_contacted = ($count) ? round($total_contacted / $count) : 0;
        $avg_appoint = ($count) ? round($total_appoint / $count) : 0;

        // Create PDF
        $file = public_path('filebank/tmp/maintenace-executive-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'avg_contacted', 'avg_appoint', 'cats', 'supers'));
        //return PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'avg_contacted', 'avg_appoint', 'cats', 'supers'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'avg_contacted', 'avg_appoint', 'cats', 'supers', 'excluded'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceExecutive($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }
}