<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestos;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
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
        $log = "\n\n------------------------------------------------------------------------\n\n";
        $log .= "Nightly Reports\n";
        $log .= "------------------------------------------------------------------------\n\n\n\n";
        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

        // Weekly Reports
        if (Carbon::today()->isMonday()) {
            CronReportController::emailJobstart();
            CronReportController::emailMaintenanceAppointment();
            CronReportController::emailMaintenanceUnderReview();
            CronReportController::emailMissingCompanyInfo();
            CronReportController::emailActiveAsbestos();
        }

        if (Carbon::today()->isTuesday()) {
            CronReportController::emailOutstandingQA();
            CronReportController::emailUpcomingJobCompilance();
            CronReportController::emailMaintenanceSupervisorNoAction();
        }

        if (Carbon::today()->isThursday()) {
            CronReportController::emailEquipmentTransfers();
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
    * Email Missing Company Info
    */
    static public function emailMissingCompanyInfo()
    {
        $log = '';
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

    /*
    * Email Active Asbestos
    */
    static public function emailActiveAsbestos()
    {
        $log = '';
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
   * Email Fortnightly Reports
   */
    static public function emailFortnightlyReports()
    {
        $log = '';
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
        $mains = SiteMaintenance::where('status', 3)->orderBy('reported')->get();

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
     * CronReportController::emailOutstandingQA();
     * CronReportController::emailUpcomingJobCompilance();
     * CronReportController::emailMaintenanceSupervisorNoAction();
     *
     * First Tuesday of the Month
     * CronReportController::emailOldUsers();
     ***************************************************/


    /*
    * Email Outstanding QA checklists
    */
    static public function emailOutstandingQA()
    {
        $log = '';
        $func_name = "Outstanding QA Checklists";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.qa.outstanding') : [env('EMAIL_DEV')];
        $emails = implode("; ", $email_list);


        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        echo "Outstanding QAs: " . $qas->count() . "<br>";
        $log .= "Outstanding QAs: " . $qas->count() . "\n";

        if ($qas->count()) {
            // Supervisors list
            $supers = [];
            foreach ($qas as $qa) {
                if (!in_array($qa->site->supervisorName, $supers))
                    $supers[] .= $qa->site->supervisorName;
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

            CronController::debugEmail('EL', $email_list);
            Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file, $qas));
            echo "Sending email to: $emails<br>";
            $log .= "Sending email to: $emails\n";
        }

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
        $func_name = "Maintenance Supervisor No Action";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $cc = Company::find(3);
        $email_list = (\App::environment('prod')) ? $cc->notificationsUsersEmailType('site.maintenance.super.noaction') : [env('EMAIL_DEV')];
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
                $email_cc = '';
                if (\App::environment('prod')) {
                    if ($super && validEmail($super->email)) {
                        $email_to = [$super->email];
                        $email_cc = ['kirstie@capecod.com.au'];
                    } else
                        $email_to = ['kirstie@capecod.com.au'];
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
    * Email Old Users
    */
    static public function emailOldUsers()
    {
        $log = '';
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
                if (!$user->last_login || ($user->last_login->lt($date_3_month) && $user->last_login->lt($user->company->lastDateOnPlanner()))) { // User not logged in or not logged in last 3 months but company has been on planner
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
                    Mail::to($company->email)->cc($email_list)->send(new \App\Mail\Site\SiteInspectionActive($electrical, $plumbing, 'Plumbing'));
                    echo "Sending email to: $company->email<br>";
                    $log .= "Sending email to: $company->email\n";
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