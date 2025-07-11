<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestos;
use App\Models\Site\SiteDoc;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteUpcomingSettings;
use App\User;
use Carbon\Carbon;
use DB;
use File;
use Mail;
use PDF;

class CronReportController extends Controller
{

    static public function nightly()
    {
        echo "<h1> Nightly Reports - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";
        $log = "\n\n========================================================================\n\n";
        $log .= "Nightly Reports\n";
        $log .= "========================================================================\n\n\n\n";
        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

        // Weekly Reports
        if (Carbon::today()->isMonday()) {
            CronReportController::emailJobstart();
            CronReportController::emailMaintenanceAppointment();
            CronReportController::emailMaintenanceUnderReview();
            //CronReportController::emailMaintenanceOnHold();
            //CronReportController::emailMissingCompanyInfo();
            CronReportController::emailMissingCompanyInfoPlanner();
            CronReportController::emailCompanyDocsPending();
            CronReportController::emailActiveAsbestos();
            CronReportController::emailSupervisorAttendance();
            CronReportController::emailScaffoldOverdue();
            CronReportController::emailOutstandingOnHoldQA();
            CronReportController::emailEquipmentTransfers();
        }

        if (Carbon::today()->isTuesday()) {
            CronReportController::emailUpcomingJobCompilance();
            CronReportController::emailMaintenanceSupervisorNoAction();
            CronReportController::emailPracCompletionSupervisorNoAction();
        }
        if (Carbon::today()->isWednesday()) {
            //CronReportController::emailMaintenanceOnHold();
        }

        if (Carbon::today()->isThursday()) {
            CronReportController::emailOnHoldQA();
            CronReportController::emailActiveElectricalPlumbing();
        }

        if (Carbon::today()->isFriday()) {
            CronReportController::emailEquipmentRestock();
        }


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
        if (Carbon::today()->isSameDay($last_fri)) {
            CronReportController::emailOutstandingAftercare();
        }

        // Monthly Reports
        if (Carbon::today()->format('d') == '01') {
            CronReportController::emailTradesAttendance();
        }
        // Quarterly Reports 1th of month
        $quarterly_months = ['03', '06', '09', '12'];
        if (Carbon::today()->format('d') == '01' && in_array(Carbon::today()->format('m'), $quarterly_months)) {
            CronReportController::emailMaintenanceExecutive();
        }

    }

    static public function debugEmail($name1, $list1, $name2 = '', $list2 = '')
    {
        if (DEBUG_EMAIL) {
            $list = (is_array($list1)) ? implode(',', $list1) : $list1;
            app('log')->debug("DEBUG-EMAIL: $name1 [$list]");
            //app('log')->debug($list1);
            if ($name2) {
                $list = (is_array($list2)) ? implode(',', $list2) : $list2;
                app('log')->debug("DEBUG-EMAIL: $name2 [$list]");
            }
        }
    }

    /****************************************************
     *  Monday Reports
     *
     * CronReportController::emailJobstart();
     * CronReportController::emailMaintenanceAppointment();
     * CronReportController::emailMaintenanceUnderReview();
     * CronReportController::emailMissingCompanyInfo();
     * CronReportController::emailCompanyDocsPending();
     * CronReportController::emailOutstandingOnHoldQA();
     *
     * Fortnightly
     * CronReportController::emailFortnightlyReports();
     ***************************************************/

    /*
    * Email Jobstart
    */
    static public function emailJobstart()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Jobstart";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.jobstartexport') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

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
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                    'code' => $site->code,
                    'name' => $site->name,
                    'company' => $entity_name,
                    'supervisor' => $site->supervisorName,
                    'contract_sent' => ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-',
                    'contract_signed' => ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-',
                    'deposit_paid' => ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-',
                    'eng' => ($site->engineering) ? 'Y' : '-',
                    'cc' => ($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : '-',
                    'hbcf' => ($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : '-',
                    'consultant' => $site->consultant_name,
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
            'user_fullname' => "Auto Generated",
            'user_company_name' => "Cape Cod",
            'startdata' => $startdata
        ];
        CronController::debugEmail('EL', $email_list);
        if ($email_list) {
            Mail::send('emails/jobstart', $data, function ($m) use ($email_list, $data, $file) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Upcoming Job Start Dates');
                $m->attach($file);
            });
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
        }


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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Maintenance Without Appointment";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.appointment') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $mains = SiteMaintenance::where('status', 1)->where('client_appointment', null)->orderBy('reported')->get();
        $data = ['data' => $mains];

        echo "Requests Without Client Appointment: " . $mains->count() . "<br>";
        $log .= "Requests Without Client Appointment: " . $mains->count() . "\n";

        if ($mains->count()) {
            if ($email_list) {
                CronController::debugEmail('EL', $email_list);
                Mail::send('emails/site/maintenance-appointment', $data, function ($m) use ($email_list, $data) {
                    $send_from = 'do-not-reply@safeworksite.com.au';
                    $m->from($send_from, 'Safe Worksite');
                    $m->to($email_list);
                    $m->subject('Maintenance Requests Without Appointment');
                });

                echo "Sending email to: $emails<br>";
                $log .= "Sending email to: $emails\n";
            }
        }

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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Maintenance Under Review";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.underreview') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $mains = SiteMaintenance::where('status', 2)->orderBy('reported')->get();
        $today = Carbon::now();

        echo "Requests Under Review: " . $mains->count() . "<br>";
        $log .= "Requests Under Review: " . $mains->count() . "\n";

        if ($mains->count()) {
            // Create PDF
            $file = public_path('filebank/tmp/maintenance-under-review-cron.pdf');
            if (file_exists($file))
                unlink($file);

            //return view('pdf/site/maintenance-under-review', compact('mains', 'today'));
            //return PDF::loadView('pdf/site/maintenance-under-review', compact('mains', 'today'))->setPaper('a4', 'portrait')->stream();
            $pdf = PDF::loadView('pdf/site/maintenance-under-review', compact('mains', 'today'))->setPaper('a4', 'portrait');
            $pdf->save($file);

            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceUnderReviewReport($file, $mains));

            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Maintenance On Hold
    */
    static public function emailMaintenanceOnHold()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Maintenance On Hold";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";


        //
        // On Hold Requests
        //
        $cc = Company::find(3);
        $email_list = [env('EMAIL_DEV')];
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.onhold') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $mains = SiteMaintenance::where('status', 4)->orderBy('reported')->get();

        echo "Requests On Hold: " . $mains->count() . "<br>";
        $log .= "Requests On Hold: " . $mains->count() . "\n";

        if ($mains->count()) {
            if ($email_list) {
                $data = ['data' => $mains];
                CronController::debugEmail('EL', $email_list);
                Mail::send('emails/site/maintenance-onhold', $data, function ($m) use ($email_list, $data) {
                    $send_from = 'do-not-reply@safeworksite.com.au';
                    $m->from($send_from, 'Safe Worksite');
                    $m->to($email_list);
                    $m->subject('Maintenance Requests On Hold');
                });
                echo "Sending $func_name email to: $emails<br>";
                $log .= "Sending $func_name email to: $emails";
            }
        }

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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Missing Company Info";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('company.missing.info') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $missing_info = [];
        $expired_docs1 = [];
        $expired_docs2 = [];
        $expired_docs3 = [];
        $companies = $cc->companies(1)->sortBy('name');
        foreach ($companies as $company) {
            if (!preg_match('/cc-/', strtolower($company->name))) { // exclude fake cc- companies
                // Missing Info
                if ($company->missingInfo()) {
                    $missing_info[] = [
                        'id' => $company->id,
                        'company_name' => $company->name,
                        'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                        'data' => $company->missingInfo(),
                        'date' => $company->updated_at->format('d/m/Y'),
                        'link' => "/company/$company->id"
                    ];
                }

                // Expired Docs
                // Doc types
                // 1  PL - Public Liabilty
                // 2  WC - Workers Comp
                // 3  SA - Sickness & Accident
                // 4  Sub - Subcontractors Statement
                // 5  PTC - Period Trade Contract
                // 6  TT - Test & Tag
                // 7  CL - Contractors Licence
                // 12 PP - Privacy Policy
                if ($company->isMissingDocs()) {
                    foreach ($company->missingDocs() as $type => $name) {
                        $doc = $company->expiredCompanyDoc($type);
                        if (in_array($type, [1, 2, 3, 7, 12])) {
                            $expired_docs1[] = [
                                'id' => $company->id,
                                'company_name' => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data' => $name,
                                'date' => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link' => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        } elseif (in_array($type, [4, 5])) {
                            $expired_docs2[] = [
                                'id' => $company->id,
                                'company_name' => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data' => $name,
                                'date' => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link' => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        } elseif (in_array($type, [6])) {
                            $expired_docs3[] = [
                                'id' => $company->id,
                                'company_name' => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data' => $name,
                                'date' => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link' => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        }
                    }
                }
            }
        }

        CronController::debugEmail('EL', $email_list);
        Mail::to($email_list)->send(new \App\Mail\Company\CompanyMissingInfo($companies, $missing_info, $expired_docs1, $expired_docs2, $expired_docs3));
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function emailMissingCompanyInfoPlanner()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Missing Company Info";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('company.missing.info') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        // Planned Companies
        $companies = Company::where('parent_company', 3)->where('status', '1')->get();
        $cids = [];
        foreach ($companies as $company) {
            $planner_date = $company->nextDateOnPlanner();
            if ($planner_date)
                $cids[$company->id] = $planner_date->format('ymd');
        }

        asort($cids);
        $companies = [];
        foreach ($cids as $key => $value)
            $companies[] = Company::find($key);

        $today = \Carbon\Carbon::today();
        $dayago = \Carbon\Carbon::today()->subDays(1);

        $missing = [];
        foreach ($companies as $company) {
            if (!preg_match('/cc-/', strtolower($company->name)) && ($company->missingInfo() || $company->isMissingDocs())) { // exclude fake cc- companies
                $planner_date = $company->nextDateOnPlanner();
                $next_planner = ($planner_date) ? $planner_date->longAbsoluteDiffForHumans() : 'N/A';
                $nickname = ($company->nickname) ? "($company->nickname)" : '';
                $missing_info = ($company->missingInfo()) ? $company->missingInfo() . "<br>" : '';
                $missing_docs = [];
                foreach ($company->missingDocs() as $type => $name) {
                    $doc = $company->expiredCompanyDoc($type);
                    if ($doc && ($doc == 'N/A' || $doc->expiry->lt($dayago))) {
                        $expiry_human = ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->longAbsoluteDiffForHumans() : 'never';
                        $expiry_date = ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : '-';
                        if ($doc != 'N/A')
                            $link = "<a href='/company/$company->id/doc/$doc->id/edit'>$name</a>";
                        else
                            $link = "<a href='/company/$company->id/doc'>$name</a>";
                    }
                    $data = ['name' => $name, 'link' => $link, 'expiry_human' => $expiry_human, 'expiry_date' => $expiry_date];
                    $missing_docs[] = $data;
                }

                $missing[] = ['company_name' => $company->name, 'company_nickname' => $nickname, 'next_planner' => $next_planner, 'missing_info' => $missing_info, 'docs' => $missing_docs];
            }
        }

        //dd($missing);
        CronController::debugEmail('EL', $email_list);
        Mail::to($email_list)->send(new \App\Mail\Company\CompanyMissingInfoPlanner($missing));
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function emailCompanyDocsPending()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Company Docs Pending";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('company.doc.pending') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $pending = CompanyDoc::where('status', 3)->where('company_id', 3)->orderBy('for_company_id')->get();

        CronController::debugEmail('EL', $email_list);
        Mail::to($email_list)->send(new \App\Mail\Company\CompanyDocsPending($pending));
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Active Asbestos
    */
    static public function emailActiveAsbestos()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Active Asbestos";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.asbestos.active') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $abs = SiteAsbestos::where('status', 1)->orderBy('created_at')->get();
        $today = Carbon::now();

        echo "Active Notifications: " . $abs->count() . "<br>";
        $log .= "Active Notifications: " . $abs->count() . "\n";

        if ($abs->count()) {
            Mail::to($email_list)->send(new \App\Mail\Site\SiteAsbestosActiveReport($abs));

            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Email Supervisor Attendance
     */
    static public function emailSupervisorAttendance()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Supervisor Attendance";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        // $email_list = (\App::environment('prod')) ? ["kirstie@capecod.com.au", "ross@capecod.com.au", "damian@capecod.com.au"] : [env('EMAIL_DEV')];
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.attendance.super') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $date_to = Carbon::now()->subDays(1);
        $date_from = Carbon::now()->subDays(7);


        // Supervisors list
        foreach ($cc->supervisors() as $user) {
            if ($user->id != 136) {  // Ignore To Be Allocated super
                $attendance = SiteAttendance::where('user_id', $user->id)->whereDate('date', '>=', $date_from)->whereDate('date', '<=', $date_to)->get();
                //dd($attendance);
                $email_to = (\App::environment('prod') && validEmail($user->email)) ? [$user->email] : [env('EMAIL_DEV')];
                $emailing = $emails . implode("; ", $email_to);
                Mail::to($email_to)->cc($email_list)->send(new \App\Mail\Site\SiteSupervisorAttendanceReport($attendance, [$user->id => $user->name]));

                echo "Sending email to: $emailing<br>";
                $log .= "Sending email to: $emailing";
            }
        }


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    //
    // Email Scaffold Overdue
    //
    static public function emailScaffoldOverdue()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Scaffold Overdue";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);

        //
        // Scaffold Handover for Ian Ewin
        //
        $scaffold_overdue = [];
        $supers_overdue = [];
        $todos = Todo::where('status', '1')->where('type', 'scaffold handover')->whereDate('due_at', '<', Carbon::today()->format('Y-m-d'))->where('due_at', '<>', '0000-00-00 00:00:00')->orderBy('due_at')->get();
        foreach ($todos as $todo) {
            // Add outstading scaff to mnain list
            $scaffold_overdue[$todo->type_id] = ['name' => $todo->name, 'due_at' => $todo->due_at->format('d/m/Y')];
            // Add outstanding scaff to super list
            list($crap, $rest) = explode('Scaffold Handover Certificate for ', $todo->name);
            list($jobnum, $crap) = explode('-', $rest);
            $site = Site::where('code', $jobnum)->first();
            if ($site) {
                if (isset($site->supervisor_id, $supers_overdue))
                    $supers_overdue[$site->supervisor_id][] = $todo->type_id;
                else
                    $supers_overdue[$site->supervisor_id] = [$todo->type_id];
            }
        }

        // Email outstanding scaffold certificates
        if ($scaffold_overdue) {
            // Loop through each Supervisor for send out email to them
            foreach ($supers_overdue as $super_id => $overdue_ids) {
                $super = User::find($super_id);
                $super_email = ($super && $super->email && validEmail($super->email)) ? $super->email : '';
                $super_firstname = ($super) ? $super->firstname : 'No Allocated Supervisor';
                $scaffold_overdue_super = [];
                // Create list of overdue Scaffs for specfic Super
                foreach ($overdue_ids as $scaff_id) {
                    $scaffold_overdue_super[$scaff_id] = $scaffold_overdue[$scaff_id];
                }
                // send out email
                echo "<br><b>Sending Reminder Email to $super_email and cc:kirstie@capecod.com.au; ross@capecod.com.au; ianscottewin@gmail.com; damian@capecod.com.au for Outstanding Scaffold Handover Certificates:\n</b><br>";
                $log .= "\nSending Reminder Email to $super_email and cc:kirstie@capecod.com.au; ross@capecod.com.au; ianscottewin@gmail.com; damian@capecod.com.au for Outstanding Scaffold Handover Certificates:\n";
                foreach ($scaffold_overdue_super as $id => $array) {
                    echo "id[$id] " . $array['name'] . "<br>";
                    $log .= "id[$id] " . $array['name'] . "\n";
                }
                $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au', 'ross@capecod.com.au', 'ianscottewin@gmail.com', 'damian@capecod.com.au'] : [env('EMAIL_DEV')];
                $email_to = (\App::environment('prod') && $super_email) ? [$super_email] : [env('EMAIL_DEV')];
                Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SiteScaffoldHandoverOutstanding($scaffold_overdue_super, 'Ian Scott Ewin', $super_firstname));
            }
        }

        //
        // Scaffold Handover for Ashbys
        //
        $scaffold_overdue = [];
        $supers_overdue = [];
        $today = Carbon::now();
        $jan2024 = Carbon::createFromFormat('Y-m-d', '2024-01-01');
        $found_tasks = 0;
        // Manually exclude some older tasks
        $excludePlannerTasks = ['129578', '129601', '129993', '135665', '136666', '137626', '137903', '139403'];

        //
        // Erect Scaffold - taskid: 116
        //
        $plans = SitePlanner::whereDate('from', '>', $jan2024)->whereDate('from', '<', $today)->where('task_id', 116)->orderBy('from')->get();
        foreach ($plans as $plan) {
            if ($plan->site->status == 1) {
                // Check for Site Risk doc with word 'Scaffolding Handover Certificate'
                $certificate = SiteDoc::where('site_id', $plan->site_id)->where('name', 'like', '%Scaffolding Handover Certificate%')->first();
                if (!$certificate && !in_array($plan->id, $excludePlannerTasks)) {
                    // Add outstanding scaff to main list
                    $scaffold_overdue[$plan->id] = ['name' => $plan->site->name, 'due_at' => $plan->from->format('d/m/Y')];
                    // Add outstanding scaff to super list
                    if (isset($plan->site->supervisor_id, $supers_overdue))
                        $supers_overdue[$plan->site->supervisor_id][] = $plan->id;
                    else
                        $supers_overdue[$plan->site->supervisor_id] = [$plan->id];
                }
            }
        }

        // Email outstanding scaffold certificates
        if ($scaffold_overdue) {
            // Loop through each Supervisor for send out email to them
            foreach ($supers_overdue as $super_id => $overdue_ids) {
                $super = User::find($super_id);
                $super_email = ($super->email && validEmail($super->email)) ? $super->email : '';
                $scaffold_overdue_super = [];
                // Create list of overdue Scaffs for specfic Super
                foreach ($overdue_ids as $scaff_id) {
                    $scaffold_overdue_super[$scaff_id] = $scaffold_overdue[$scaff_id];
                }
                // send out email
                echo "<br><b>Sending Reminder Email to $super_email and cc:kirstie@capecod.com.au; ross@capecod.com.au; damian@capecod.com.au; construct@capecod.com.au; info@ashby.com.au for Outstanding Scaffold Handover Certificates:\n</b><br>";
                $log .= "\nSending Reminder Email to $super_email and cc:kirstie@capecod.com.au; ross@capecod.com.au; damian@capecod.com.au; construct@capecod.com.au; info@ashby.com.au for Outstanding Scaffold Handover Certificates:\n";
                foreach ($scaffold_overdue_super as $id => $array) {
                    echo "id[$id] " . $array['name'] . "<br>";
                    $log .= "id[$id] " . $array['name'] . "\n";
                }
                $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au', 'ross@capecod.com.au', 'damian@capecod.com.au', 'construct@capecod.com.au', 'info@ashby.com.au'] : [env('EMAIL_DEV')];
                $email_to = (\App::environment('prod') && $super_email) ? [$super_email] : [env('EMAIL_DEV')];
                Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SiteScaffoldHandoverOutstanding($scaffold_overdue_super, 'Ashbys', $super->firstname));
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Email Outstanding QA + Onhold checklists
    */
    static public function emailOutstandingOnHoldQA()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Outstanding QA & OnHold Checklists";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.qa.outstanding') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);


        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $file_list = [];
        //
        // Outstanding Qas
        //
        $outQas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();
        echo "Outstanding QAs: " . $outQas->count() . "<br>";
        $log .= "Outstanding QAs: " . $outQas->count() . "\n";

        if ($outQas->count()) {
            // Supervisors list
            $outSupers = [];
            foreach ($outQas as $qa) {
                if (isset($outSupers[$qa->site->supervisorName]))
                    $outSupers[$qa->site->supervisorName] = $outSupers[$qa->site->supervisorName] + 1;
                else
                    $outSupers[$qa->site->supervisorName] = 1;
                //$outSupers[$qa->site->supervisor_id] = $qa->site->supervisorName;
            }
            ksort($outSupers);

            $report_type = "Outstanding";
            // For each Super create their own pdf
            foreach ($outSupers as $supervisor => $count) {
                // Create PDF
                $super_name = strtolower(preg_replace('/\s+/', '-', $supervisor));
                $file = public_path("filebank/tmp/qa-outstanding-$super_name.pdf");
                $file_list[] = $file;
                if (file_exists($file))
                    unlink($file);
                //return view('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'supervisor', 'today'));
                //return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'supervisor', 'today'))->setPaper('a4', 'landscape')->stream();

                $qas = $outQas;
                $supers = $outSupers;
                $pdf = PDF::loadView('pdf/site/site-qa-outstanding', compact('report_type', 'qas', 'supers', 'supervisor', 'today'));
                $pdf->setPaper('A4', 'landscape');
                $pdf->save($file);
            }
        }


        //
        // On Hold Qas
        //
        $holdQas = SiteQa::where('status', 4)->where('master', 0)->orderBy('updated_at')->get();
        echo "On Hold QAs: " . $holdQas->count() . "<br>";
        $log .= "On Hold QAs: " . $holdQas->count() . "\n";

        if ($holdQas->count()) {
            // Supervisors list
            $holdSupers = [];
            foreach ($holdQas as $qa) {
                //$holdSupers[$qa->site->supervisor_id] = $qa->site->supervisorName;
                if (isset($holdSupers[$qa->site->supervisorName]))
                    $holdSupers[$qa->site->supervisorName] = $holdSupers[$qa->site->supervisorName] + 1;
                else
                    $holdSupers[$qa->site->supervisorName] = 1;
            }
            ksort($holdSupers);

            $report_type = "On Hold";
            // For each Super create their own pdf
            foreach ($holdSupers as $supervisor => $count) {
                // Create PDF
                $super_name = strtolower(preg_replace('/\s+/', '-', $supervisor));
                $file = public_path("filebank/tmp/qa-onhold-$super_name.pdf");
                $file_list[] = $file;
                if (file_exists($file))
                    unlink($file);

                $qas = $holdQas;
                $supers = $holdSupers;
                $pdf = PDF::loadView('pdf/site/site-qa-outstanding', compact('report_type', 'qas', 'supers', 'supervisor', 'today'));
                $pdf->setPaper('A4', 'landscape');
                $pdf->save($file);
            }
        }


        if ($outQas->count() || $holdQas->count()) {
            // Send email with multiple attachments
            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file_list, $outQas, $outSupers, $holdQas, $holdSupers));
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        echo "<h2>Email Fortnightly Reports</h2>";
        $log .= "Email Fortnightly Reports\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $func_name = "Maintenance No Actions";
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.noaction') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);


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

        echo "Requests Without Actions: " . count($mains) . "<br>";
        $log .= "Requests Without Actions: " . count($mains) . "\n";

        if (count($mains)) {
            $data = ['data' => $mains];
            if ($email_list) {
                CronController::debugEmail('EL', $email_list);
                Mail::send('emails/site/maintenance-noaction', $data, function ($m) use ($email_list, $data) {
                    $send_from = 'do-not-reply@safeworksite.com.au';
                    $m->from($send_from, 'Safe Worksite');
                    $m->to($email_list);
                    $m->subject('Maintenance Requests No Action');
                });
                echo "Sending $func_name email to: $emails<br>";
                $log .= "Sending $func_name email to: $emails\n";
            }
        }


        //
        // On Hold Requests
        //
        $func_name = "Maintenance On Hold";
        $email_list = [env('EMAIL_DEV')];
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.onhold') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $mains = SiteMaintenance::where('status', 4)->orderBy('reported')->get();

        echo "Requests On Hold: " . $mains->count() . "<br>";
        $log .= "Requests On Hold: " . $mains->count() . "\n";

        if ($mains->count()) {
            if ($email_list) {
                $data = ['data' => $mains];
                CronController::debugEmail('EL', $email_list);
                Mail::send('emails/site/maintenance-onhold', $data, function ($m) use ($email_list, $data) {
                    $send_from = 'do-not-reply@safeworksite.com.au';
                    $m->from($send_from, 'Safe Worksite');
                    $m->to($email_list);
                    $m->subject('Maintenance Requests On Hold');
                });
                echo "Sending $func_name email to: $emails<br>";
                $log .= "Sending $func_name email to: $emails";
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /****************************************************
     * Tuesday Reports
     *
     * CronReportController::emailUpcomingJobCompilance();
     * CronReportController::emailMaintenanceSupervisorNoAction();
     * CronReportController::emailPracCompletionSupervisorNoAction();
     *
     * First Tuesday of the Month
     * CronReportController::emailOldUsers();
     ***************************************************/

    /*
    * Email UpcomingJobCompliance
    */
    static public function emailUpcomingJobCompilance()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Upcoming Job Compliance";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.upcoming.compliance') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";


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
            CronController::debugEmail('EL', $email_list);
            Mail::send('emails/site/upcoming-compliance', $data, function ($m) use ($email_list, $data, $file) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('SafeWorksite - Upcoming Jobs Compliance Data');
                $m->attach($file);
            });

            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails";
        }

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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Maintenance Supervisor No Action";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.prac.completion.super.noaction') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $mains = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        $today = Carbon::now();

        // Supervisors list
        $supers = [];
        foreach ($mains as $main) {
            if ($main->super_id) {
                if (!isset($supers[$main->super_id]))
                    $supers[$main->super_id] = $main->taskOwner->fullname;
            } else
                $supers[0] = 'Unassigned';
        }
        asort($supers);

        foreach ($supers as $super_id => $super_name) {
            $body = '';
            $found_request = false;
            //if ($super_id) {
            $super = ($super_id) ? User::find($super_id) : null;

            $body .= "$super_name<br>";
            //
            // No Appointments
            //
            $body .= "<h3>No Appointment</h3><br>";
            $body .= "<table style='border: 1px solid; border-collapse: collapse'>";
            $body .= "<thead>";
            $body .= "<tr style='background-color: #F6F6F6; font-weight: bold; border: 1px solid; padding: 3px'>";
            $body .= "<th width='50' style='border: 1px solid'>#</th>";
            $body .= "<th width='80' style='border: 1px solid'>Reported</th>";
            $body .= "<th width='200' style='border: 1px solid'>Site</th>";
            $body .= "<th width='80' style='border: 1px solid'>Client Contacted</th>";
            $body .= "<th width='80' style='border: 1px solid'>Appointment</th>";
            $body .= "<th width='80' style='border: 1px solid'>Last Action</th>";
            $body .= "<th width='400' style='border: 1px solid'>Note</th>";
            $body .= "</tr>";
            $body .= "</thead>";
            $body .= "<tbody>";
            $super_count = 0;
            foreach ($mains as $main) {
                if ($main->super_id == $super_id || ($main->super_id == null && $super_id == '0')) {
                    if (!$main->client_appointment) {
                        $super_count++;
                        $found_request = true;

                        $client_contacted = ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '-';
                        $client_appointment = ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '-';
                        $last_action = ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y');

                        $body .= "<tr>";
                        $body .= "<td style='border: 1px solid'>M$main->code</td>";
                        $body .= "<td style='border: 1px solid'>" . $main->created_at->format('d/m/Y') . "</td>";
                        $body .= "<td style='border: 1px solid'>" . $main->site->name . "</td>";
                        $body .= "<td style='border: 1px solid'>$client_contacted</td>";
                        $body .= "<td style='border: 1px solid'>$client_appointment</td>";
                        $body .= "<td style='border: 1px solid'>$last_action</td>";
                        $body .= "<td style='border: 1px solid;'>" . $main->lastActionNote() . "</td>";
                        $body .= "</tr>";
                    }
                }
            }
            if ($super_count == 0)
                $body .= "<tr><td colspan = '7'> No Maintenance Requests found matching required criteria </td></tr>";

            $body .= "</tbody></table>";

            //
            // No Action 14 Days
            //
            $body .= "<br><br><h3>No Actions in last 14 days</h3><br>";
            $body .= "<table style='border: 1px solid; border-collapse: collapse'>";
            $body .= "<thead>";
            $body .= "<tr style='background-color: #F6F6F6; font-weight: bold; border: 1px solid; padding: 3px'>";
            $body .= "<th width='50' style='border: 1px solid'>#</th>";
            $body .= "<th width='80' style='border: 1px solid'>Reported</th>";
            $body .= "<th width='200' style='border: 1px solid'>Site</th>";
            $body .= "<th width='80' style='border: 1px solid'>Client Contacted</th>";
            $body .= "<th width='80' style='border: 1px solid'>Appointment</th>";
            $body .= "<th width='80' style='border: 1px solid'>Last Action</th>";
            $body .= "<th width='400' style='border: 1px solid'>Note</th>";
            $body .= "</tr>";
            $body .= "</thead>";
            $body .= "<tbody>";
            $super_count2 = 0;

            foreach ($mains as $main) {
                if ($main->super_id == $super_id || ($main->super_id == null && $super_id == '0')) {
                    // Exclude requests that have a task planned 1 week prior or after today
                    $recentTask = ($main->site->jobRecentTask && $main->site->jobRecentTask->gt(Carbon::now()->subDays(7))) ? $main->site->jobRecentTask->format('d/m/Y') : null;
                    $nextTask = ($main->site->jobNextTask && $main->site->jobNextTask->lt(Carbon::now()->addDays(7))) ? $main->site->jobNextTask->format('d/m/Y') : null;
                    $futureTasks = ($main->site->futureTasks()->count()) ? true : false;
                    if ($main->lastUpdated()->lt(Carbon::now()->subDays(14)) && !($recentTask || $nextTask || $futureTasks)) {
                        $super_count2++;
                        $found_request = true;

                        $client_contacted = ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '-';
                        $client_appointment = ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '-';
                        $last_action = ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y');

                        $body .= "<tr>";
                        $body .= "<td style='border: 1px solid'>M$main->code</td>";
                        $body .= "<td style='border: 1px solid'>" . $main->created_at->format('d/m/Y') . "</td>";
                        $body .= "<td style='border: 1px solid'>" . $main->site->name . "</td>";
                        $body .= "<td style='border: 1px solid'>$client_contacted</td>";
                        $body .= "<td style='border: 1px solid'>$client_appointment</td>";
                        $body .= "<td style='border: 1px solid'>$last_action</td>";
                        $body .= "<td style='border: 1px solid;'>" . $main->lastActionNote() . "</td>";
                        $body .= "</tr>";
                    }
                }
            }
            if ($super_count2 == 0)
                $body .= "<tr><td colspan = '7'> No Maintenance Requests found matching required criteria </td></tr>";

            $body .= "</tbody></table>";

            echo "No Appointments: $super_count<br>";
            $log .= "No Appointments: $super_count\n";
            echo "No Actions 14 days: $super_count2<br>";
            $log .= "No Actions 14 days: $super_count2\n";

            //
            // Send email to Supervisors
            //
            if ($found_request) {
                $email_to = [env('EMAIL_DEV')];
                $email_cc = [];
                if (\App::environment('prod')) {
                    if ($super && validEmail($super->email)) {
                        $email_to = [$super->email];
                        $email_cc = $email_list; //['kirstie@capecod.com.au'];
                    } else
                        $email_to = $email_list; //['kirstie@capecod.com.au'];
                }
                CronController::debugEmail('EL', $email_list, 'CC', $email_cc);
                if ($email_to && $email_cc)
                    Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SiteMaintenanceSupervisorNoActionSubReport($body));
                elseif ($email_to)
                    Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceSupervisorNoActionSubReport($body));

                $emails = implode("; ", array_merge($email_to, $email_cc));
                echo "Sending email to: $emails<br>";
                $log .= "Sending email to: $emails";
            }
            //}
        }

        //dd('here');

        // Create PDF
        //$file = public_path('filebank/tmp/maintenance-supervisor-cron.pdf');
        //if (file_exists($file))
        //    unlink($file);

        //return view('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'));
        //return PDF::loadView('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
        //$pdf = PDF::loadView('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'))->setPaper('a4', 'landscape');
        //$pdf->save($file);

        //Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceSupervisorNoActionReport($file, $mains));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * EmailPracCompletion Supervisor No Action
    */
    static public function emailPracCompletionSupervisorNoAction()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Pratical Completion No Action";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.super.noaction') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);
        $pracs = SitePracCompletion::where('status', 1)->orderBy('created_at')->get();
        $today = Carbon::now();

        // Supervisors list
        $supers = [];
        foreach ($pracs as $prac) {
            if ($prac->super_id) {
                if (!isset($supers[$prac->super_id]))
                    $supers[$prac->super_id] = $prac->supervisor->fullname;
            } else
                $supers[0] = 'Unassigned';
        }
        asort($supers);

        foreach ($supers as $super_id => $super_name) {
            $body = '';
            $found_request = false;
            $super = ($super_id) ? User::find($super_id) : null;

            $body .= "$super_name<br><br>";

            //
            // No Action 14 Days
            //
            $body .= "<table style='width:100%; border: 1px solid; border-collapse: collapse'>";
            $body .= "<thead>";
            $body .= "<tr style='background-color: #F6F6F6; font-weight: bold; border: 1px solid; padding: 3px'>";
            $body .= "<th style='width:80px; border: 1px solid'>Created</th>";
            $body .= "<th style='width:250px; border: 1px solid'>Site</th>";
            $body .= "<th style='width:500px; border: 1px solid'>Assigned Company</th>";
            $body .= "<th style='width:80px;border: 1px solid'>Updated</th>";
            $body .= "</tr>";
            $body .= "</thead>";
            $body .= "<tbody>";
            $super_count = 0;

            foreach ($pracs as $prac) {
                if ($prac->super_id == $super_id || ($prac->super_id == null && $super_id == '0')) {
                    // Only include Pracs not Updated or No new Notes within 14days
                    $days14 = Carbon::now()->subDays(14);
                    if ($prac->lastUpdated()->lt($days14)) {
                        $super_count++;
                        $found_request = true;

                        $body .= "<tr>";
                        $body .= "<td style='border: 1px solid'>" . $prac->created_at->format('d/m/Y') . "</td>";
                        $body .= "<td style='border: 1px solid'>" . $prac->site->name . "</td>";
                        $body .= "<td style='border: 1px solid'>" . $prac->assignedToNames() . "</td>";
                        $body .= "<td style='border: 1px solid;'>" . $prac->lastUpdated()->format('d/m/Y') . "</td>";
                        $body .= "</tr>";
                    }
                }
            }
            if ($super_count == 0)
                $body .= "<tr><td colspan = '7'> No Practical Completions found matching required criteria </td></tr>";

            $body .= "</tbody></table>";

            echo "No Actions 14 days: $super_count<br>";
            $log .= "No Actions 14 days: $super_count\n";

            //
            // Send email to Supervisors
            //
            if ($found_request) {
                $email_to = [env('EMAIL_DEV')];
                $email_cc = [];
                if (\App::environment('prod')) {
                    if ($super && validEmail($super->email)) {
                        $email_to = [$super->email];
                        $email_cc = ['kirstie@capecod.com.au', "ross@capecod.com.au", "damian@capecod.com.au"];
                    } else
                        $email_to = ['kirstie@capecod.com.au', "ross@capecod.com.au", "damian@capecod.com.au"];
                }

                if ($email_to && $email_cc)
                    Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SitePracCompletionSupervisorNoActionReport($body));
                elseif ($email_to)
                    Mail::to($email_to)->send(new \App\Mail\Site\SitePracCompletionSupervisorNoActionReport($body));

                $emails = implode("; ", array_merge($email_to, $email_cc));
                echo "Sending email to: $emails<br>";
                $log .= "Sending email to: $emails";
            }
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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Old Users";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('user.oldusers') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);


        $cc_users = $cc->users(1)->pluck('id')->toArray();
        $user_list = User::where('status', 1)->where('onsite', 1)->whereIn('id', $cc_users)->orderBy('company_id', 'ASC')->pluck('id')->toArray();

        $date_3_month = Carbon::today()->subMonths(3);
        $user_list2 = User::where('status', 1)->whereIn('id', $user_list)->wheredate('last_login', '<', $date_3_month->format('Y-m-d'))->orderBy('company_id', 'ASC')->get();

        $user_list3 = [];
        foreach ($user_list2 as $user) {
            if (in_array($user->company->category, [1, 2]) && $user->company->status == 1 && $user->hasAnyRole2('ext-leading-hand|tradie|labourers')) { // Onsite Trade + Active Company + appropriate role
                if (!$user->last_login)
                    $user_list3[] = $user->id;
                elseif ($user->company->lastDateOnPlanner()) {
                    if ($user->last_login->lt($date_3_month) && $user->last_login->lt($user->company->lastDateOnPlanner())) // User not logged in or not logged in last 3 months but company has been on planner
                        $user_list3[] = $user->id;
                }
            }
        }

        $users = User::whereIn('id', $user_list3)->orderBy('company_id', 'ASC')->get();
        echo "Old Users: " . $users->count() . "<br>";
        $log .= "Old Users: " . $users->count() . "\n";


        //dd($users);
        if ($users->count()) {
            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\User\OldUsers($users));
            echo "Sending email to: $emails";
            $log .= "Sending email to: $emails";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /****************************************************
     * Thursday Reports
     *
     * CronReportController::emailEquipmentTransfers();
     * CronReportController::emailOnHoldQA();
     * CronReportController::emailActiveElectricalPlumbing();
     ***************************************************/

    /*
    * Email Equipment Transfers
    */
    static public function emailEquipmentTransfers()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Equipment Transfers";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('equipment.transfers') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $to = Carbon::now();
        $from = Carbon::now()->subDays(7);
        $transactions = EquipmentLog::where('action', 'T')->whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('created_at', '<=', $to->format('Y-m-d'))->get();
        echo "Transactions: " . $transactions->count() . "<br>";
        $log .= "Transactions: " . $transactions->count() . "\n";

        if ($transactions->count()) {
            // Create PDF
            $file = public_path('filebank/tmp/equipment-transfers-cron.pdf');
            if (file_exists($file))
                unlink($file);

            //return view('pdf/equipment-transfers', compact('transactions', 'from', 'to'));
            //return PDF::loadView('pdf/equipment-transfers', compact('transactions', 'from', 'to'))->setPaper('a4', 'portrait')->stream();

            $pdf = PDF::loadView('pdf/equipment-transfers', compact('transactions', 'from', 'to'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->save($file);

            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Misc\EquipmentTransfers($file, $transactions));
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
        }

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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "On Hold QA Checklists";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.qa.onhold') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $today = Carbon::now();
        $qas = SiteQa::where('status', 4)->where('master', 0)->orderBy('updated_at')->get();
        echo "On Hold QAs: " . $qas->count() . "<br>";
        $log .= "On Hold QAs: " . $qas->count() . "\n";

        if ($qas->count()) {
            // Create PDF
            $file = public_path('filebank/tmp/qa-onhold-cron.pdf');
            if (file_exists($file))
                unlink($file);

            // Supervisors list
            $supers = [];
            foreach ($qas as $qa) {
                if (!in_array($qa->site->supervisorName, $supers))
                    $supers[] .= $qa->site->supervisorName;
            }
            sort($supers);

            //return view('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'));
            //return PDF::loadView('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();

            $pdf = PDF::loadView('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'));
            $pdf->setPaper('A4', 'landscape');
            $pdf->save($file);

            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOnhold($file, $qas));
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /*
     * Email Open Electrical & Plumbing
     */
    static public function emailActiveElectricalPlumbing()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Open Electrical Plumbing Inspection Reports";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.inspection.open') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $today = Carbon::now();
        $electrical = SiteInspectionElectrical::where('status', 1)->get();
        $plumbing = SiteInspectionPlumbing::where('status', 1)->get();


        // Electrical
        echo "Active Electrical: " . $electrical->count() . "<br>";
        $log .= "Active Electrical: " . $electrical->count() . "\n";
        if ($electrical->count()) {
            $assignedTo = [];
            foreach ($electrical as $report) {
                if (isset($assignedTo[$report->assigned_to]))
                    $assignedTo[$report->assigned_to][] = $report->id;
                else
                    $assignedTo[$report->assigned_to] = [$report->id];
            }
            foreach ($assignedTo as $cid => $ids) {
                $electrical = SiteInspectionElectrical::whereIn('id', $ids)->get();
                $company = Company::find($cid);

                if (\App::environment('prod') && $company && validEmail($company->email)) {
                    Mail::to($company->email)->cc($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Electrical'));
                    echo "Sending email to: $company->email<br>";
                    $log .= "Sending email to: $company->email\n";
                } else
                    Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Electrical'));

                echo "Sending email to: $emails<br>";
                $log .= "Sending email to: $emails\n";
            }
        }

        // Plumbing
        echo "Active Plumbing: " . $plumbing->count() . "<br>";
        $log .= "Active Plumbing: " . $plumbing->count() . "\n";
        if ($plumbing->count()) {
            $assignedTo = [];
            foreach ($plumbing as $report) {
                if (isset($assignedTo[$report->assigned_to]))
                    $assignedTo[$report->assigned_to][] = $report->id;
                else
                    $assignedTo[$report->assigned_to] = [$report->id];
            }
            foreach ($assignedTo as $cid => $ids) {
                $plumbing = SiteInspectionPlumbing::whereIn('id', $ids)->get();
                $company = Company::find($cid);

                if (\App::environment('prod') && $company && validEmail($company->email)) {
                    $email_to = [$company->email];
                    if ($company->id == '69' && $company->primary_contact() && validEmail($company->primary_contact()->email)) // Scott Bartley
                        $email_to[] = $company->primary_contact()->email;

                    $emailing = implode("; ", $email_to);
                    Mail::to($email_to)->cc($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Plumbing'));
                    echo "Sending email to: $emailing<br>";
                    $log .= "Sending email to: $emailing\n";
                } else
                    Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Plumbing'));

                echo "Sending email to: $emails<br>";
                $log .= "Sending email to: $emails\n";
            }
        }

        // Inspections 8 weeks over
        /*
        echo "<br>Inspections Over 8 weeks<br>";
        $log .= "\nInspections Over 8 weeks\n";
        $overdue_date = Carbon::now()->subWeek(8);
        $eids = [];
        foreach ($electrical as $report) {
            if ($report->assigned_at && $report->assigned_at->lte($overdue_date))
                $eids[] = $report->id;
        }
        $electrical = SiteInspectionElectrical::find($eids);
        $pids = [];
        foreach ($plumbing as $report) {
            if ($report->assigned_at && $report->assigned_at->lte($overdue_date))
                $pids[] = $report->id;
        }
        $plumbing = SiteInspectionPlumbing::find($pids);

        echo "Electrical: " . $electrical->count() . "<br>";
        $log .= "Electrical: " . $electrical->count() . "\n";
        echo "Active: " . $plumbing->count() . "<br>";
        $log .= "Active: " . $plumbing->count() . "\n";


        $email_list = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
        if ($electrical->count() || $plumbing->count()) {
            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Electrical/Plumbing', $overdue_date));
            $emails = implode("; ", $email_list);
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
        }
        */

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    /****************************************************
     * Friday Reports
     *
     * CronReportController::emailEquipmentRestock();
     *
     * Last Friday of the month
     * CronReportController::emailOutstandingAftercare();
     ***************************************************/

    /*
    * Email Equipment Restock
    */
    static public function emailEquipmentRestock()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Equipment Restock";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('equipment.restock') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $equipment = Equipment::where('min_stock', '!=', null)->where('status', 1)->orderBy('name')->get();
        $eids = [];
        foreach ($equipment as $equip) {
            if ($equip->total < $equip->min_stock)
                $eids[] = $equip->id;

        }
        $equipment = Equipment::find($eids);
        echo "Equipment: " . $equipment->count() . "<br>";
        $log .= "Equipment: " . $equipment->count() . "\n";


        if ($equipment->count() && $email_list) {
            $data = ['data' => $equipment];
            CronController::debugEmail('EL', $email_list);
            Mail::send('emails/misc/equipment-restock', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('SafeWorksite - Equipment Restock');
            });
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
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
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Outstanding After Care";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.aftercare') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $mains = SiteMaintenance::where('status', 0)->where('ac_form_sent', null)->orderBy('updated_at')->get();
        echo "Outstanding AfterCare: " . $mains->count() . "<br>";
        $log .= "Outstanding AfterCare: " . $mains->count() . "\n";
        $data = ['data' => $mains];

        if ($mains->count() && $email_list) {
            CronController::debugEmail('EL', $email_list);
            Mail::send('emails/site/maintenance-aftercare', $data, function ($m) use ($email_list, $data) {
                $send_from = 'do-not-reply@safeworksite.com.au';
                $m->from($send_from, 'Safe Worksite');
                $m->to($email_list);
                $m->subject('Maintenance Requests Without After Care');
            });
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    //
    // Monthly
    //
    // emailTradesAttendance
    //

    /*
    *  Email Trades Attebndance Report
    */
    static public function emailTradesAttendance()
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Trades Attendance Report";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.attendance.trades') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

        $from = new Carbon('first day of last month');
        $to = new Carbon('last day of last month');

        $dir = '/filebank/tmp/report';
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);

        $attendance_files = [];
        $non_attendance = [];
        // Active On-Site Companies
        $activeCompanies = Company::where('status', 1)->where('parent_company', 3)->whereNot('name', 'like', 'Cc-%')->whereIn('category', [1, 2])->orderBy('name')->get();
        foreach ($activeCompanies as $company) {
            echo "$company->name<br>";
            $user_ids = $company->staff->pluck('id')->toArray();
            $attendance = SiteAttendance::whereIn('user_id', $user_ids)->whereDate('date', '>=', $from->format('Y-m-d'))->whereDate('date', '<=', $to->format('Y-m-d'))->orderBy('date')->get();

            if ($attendance->count()) {
                // Create a separate report for each company with attendance
                $data = [];
                foreach ($attendance as $attend) {
                    $date = $attend->date->format('D M d, Y');
                    $user = $attend->user;
                    if (isset($data[$date]))
                        $data[$date][$attend->site->name][$user->id] = $user->full_name;
                    else
                        $data[$date][$attend->site->name][$user->id] = $user->full_name;

                }

                $output_file = public_path($dir . '/' . sanitizeFilename($company->name) . ' Monthly Attendance.pdf');
                touch($output_file);
                $attendance_files[] = $output_file;
                $pdf = PDF::loadView('pdf/company-attendance', compact('data', 'company', 'from', 'to'))->setPaper('a4', 'landscape')->save($output_file);
            } else {
                // List those with non-attenance
                $non_attendance[] = $company->name;
            }
        }

        Mail::to($email_list)->send(new \App\Mail\Site\SiteTradesAttendance($attendance_files, $non_attendance));
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }


    //
    //  Quarterly - First of the Month
    //
    //  emailMaintenanceExecutive
    //

    /*
    * Email Site Maintenance Executive Report
    */
    static public function emailMaintenanceExecutive($email_list = null)
    {
        $log = '';
        echo "<h1>++++++++ " . __FUNCTION__ . " ++++++++</h1>";
        $log .= "++++++++ " . __FUNCTION__ . " ++++++++\n";
        $func_name = "Site Maintenance Executive Report";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.executive') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);

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

                    $count++;
                } else {
                    $excluded++;
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

        CronController::debugEmail('EL', $email_list);
        Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceExecutive($file));
        echo "Sending email to: $emails<br>";
        $log .= "Sending email to: $emails\n";

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }
}