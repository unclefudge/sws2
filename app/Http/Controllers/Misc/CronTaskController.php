<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Comms\Todo;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteUpcomingSettings;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Mail;
use PDF;

class CronTaskController extends Controller
{
    static public function hourly()
    {
        echo "<h1> Hourly Update - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";

        // -------------------------------------------------
        // Log file
        // -------------------------------------------------
        $logDir = storage_path('app/log');
        $logFile = storage_path('app/log/hourly.txt');
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);

        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks ===\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        // Time helpers
        $hour = Carbon::now()->format('G'); // 24hr
        $minute = Carbon::now()->format('i');

        // Monday
        if (Carbon::today()->isMonday()) {
            // 1pm
            if ($hour == '13')
                CronTaskController::emailUpcomingJobs();
        }

        // Thursday
        if (Carbon::today()->isThursday()) {
            // 10am
            if ($hour == '10')
                CronTaskController::emailUpcomingJobs();
        }

        if (Carbon::today()->isWeekday()) {
            // 2pm
            if ($hour == '14') {
                $log = "=== Hourly Tasks @ 2pm " . Carbon::now()->format('d/m/Y G:i') . " ===\n";
                //file_put_contents($logFile, $log, FILE_APPEND);
                CronTaskController::superChecklistsReminder();
            }
        }
    }


    /*
    * OutStanding Supervisor Checklist @ 2pm
    */
    static public function superChecklistsReminder()
    {
        $logFile = storage_path('app/log/hourly.txt');
        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks - Super Checklit Reminder ===\n";
        file_put_contents($logFile, $log, FILE_APPEND);

        $todos = Todo::where('type', 'super checklist')->where('status', '1')->get();
        foreach ($todos as $todo) {
            $checklist = SuperChecklist::find($todo->type_id);
            if ($checklist) {
                //$checklist->emailSupervisorReminder();
                //echo "Email Supervisor Checklist Reminder - " . $checklist->supervisor->name . "<br>";
                //$log .= "Email Supervisor Checklist Reminder - " . $checklist->supervisor->name . "\n";
            }
        }

        if (!Auth::check()) file_put_contents($logFile, $log, FILE_APPEND);
    }

    /*
    * Upcomimg Jobs
    */
    static public function emailUpcomingJobs()
    {
        $logFile = storage_path('app/log/hourly.txt');
        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks - Upcoming Jobs ===\n";
        //file_put_contents($logFile, $log, FILE_APPEND);
        echo "<h2>Upcoming Jobs</h2>";

        $types = ['opt', 'cfest', 'cfadm'];
        foreach ($types as $type) {
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
        ray($startdata);

        // -------------------------------------------------
        // Generate PDF
        // -------------------------------------------------
        $file = storage_path('app/tmp/upcoming-jobs.pdf');
        //return view('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        //return PDF::loadView('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'))->setPaper('A4', 'landscape')->stream('upcoming-jobs.pdf');
        $pdf = PDF::loadView('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'))->setPaper('A4', 'landscape');
        $pdf->save($file);


        // -------------------------------------------------
        // Email
        // -------------------------------------------------
        $today = Carbon::now();
        if (app()->environment('prod')) {
            if ($today->isMonday()) {
                $email_to = ['alethea@capecod.com.au', 'keith@capecod.com.au', 'kirstie@capecod.com.au', 'nadia@capecod.com.au', 'ross@capecod.com.au', 'fudge@jordan.net.au'];
                $email_cc = ['clinton@capecod.com.au', 'jim@capecod.com.au', 'juliana@capecod.com.au', 'scott@capecod.com.au'];
                $email_subject = "Upcoming Jobs Compliance - Pre Planning Meeting " . $today->format('d.m.y');
            }
            if ($today->isThursday()) {
                $email_to = ['alethea@capecod.com.au', 'keith@capecod.com.au', 'kirstie@capecod.com.au', 'nadia@capecod.com.au', 'ross@capecod.com.au', 'fudge@jordan.net.au'];
                $email_cc = ['clinton@capecod.com.au', 'jim@capecod.com.au', 'juliana@capecod.com.au', 'scott@capecod.com.au', 'michelle@capecod.com.au', 'jayden@capecod.com.au'];
                $email_subject = "Upcoming Jobs Compliance - Post Planning Meeting " . $today->format('d.m.y');
            }

            if ($email_to)
                Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SiteUpcomingJobs($file, $email_subject));

        } else {
            $email_to = env('EMAIL_DEV');
            Mail::to($email_to)->send(new \App\Mail\Site\SiteUpcomingJobs($file, "Jobs Board - Planning Meeting"));
        }
    }

}