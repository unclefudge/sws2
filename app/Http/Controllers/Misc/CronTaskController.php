<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Comms\Todo;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteUpcomingSettings;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Support\Facades\Auth;
use Mail;
use PDF;

class CronTaskController extends Controller
{

    static public function hourly()
    {
        echo "<h1> Hourly Update - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";
        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks ===\n";
        $bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

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
            $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks ===\n";
            $bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
            // 10am
            if ($hour == '10')
                CronTaskController::emailUpcomingJobs();
        }

        if (Carbon::today()->isWeekday()) {
            // 2pm
            if ($hour == '14') {
                $text = "=== Hourly Tasks @ 2pm " . Carbon::now()->format('d/m/Y G:i') . " ===\n";
                //app('log')->debug($text);
                CronTaskController::superChecklistsReminder();
            }
        }
    }


    /*
    * OutStanding Supervisor Checklist @ 2pm
    */
    static public function superChecklistsReminder()
    {
        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks - Super Checklit Reminder ===\n";
        $bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

        $todos = Todo::where('type', 'super checklist')->where('status', '1')->get();
        foreach ($todos as $todo) {
            $checklist = SuperChecklist::find($todo->type_id);
            if ($checklist) {
                //$checklist->emailSupervisorReminder();
                //echo "Email Supervisor Checklist Reminder - " . $checklist->supervisor->name . "<br>";
                //$log .= "Email Supervisor Checklist Reminder - " . $checklist->supervisor->name . "\n";
            }
        }

        $bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
    * Upcomimg Jobs
    */
    static public function emailUpcomingJobs()
    {
        $log = "=== " . Carbon::now()->format('d/m/Y G:i') . " Hourly Tasks - Upcoming Jobs ===\n";
        //$bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
        //if ($bytes_written === false) die("Error writing to file");
        echo "<h1>Upcoming Jobs</h1>";

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
        //dd($startdata);

        //return view('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf = PDF::loadView('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf->setPaper('A4', 'landscape');


        $file = public_path('filebank/tmp/upcoming-' . Auth::user()->id . '.pdf');
        if (file_exists($file))
            unlink($file);
        $pdf->save($file);

        $email_to = [env('EMAIL_DEV')];
        $email_cc = [env('EMAIL_DEV')];
        $email_subject = 'Jobs Board ';

        // to :"alethea@capecod.com.au, keith@capecod.com.au, kirstie@capecod.com.au, nadia@capecod.com.au, ross@capecod.com.au"
        //		cc:"clinton@capecod.com.au, jim@capecod.com.au, juliana@capecod.com.au, scott@capecod.com.au"
        //		subject :
        //		message :"Hi all,<br>Please see attached the Jobs Board for this week’s Planning Meeting.<br>"

        $today = Carbon::now();
        if (\App::environment('prod')) {
            if (Carbon::today()->isMonday()) {
                $email_to = ['alethea@capecod.com.au', 'keith@capecod.com.au', 'kirstie@capecod.com.au', 'nadia@capecod.com.au', 'ross@capecod.com.au', 'fudge@jordan.net.au'];
                $email_cc = ['clinton@capecod.com.au', 'jim@capecod.com.au', 'juliana@capecod.com.au', 'scott@capecod.com.au'];
                $email_subject = "Jobs Board - Pre Planning Meeting " . $today->format('d.m.y');
            }
            if (Carbon::today()->isThursday()) {
                $email_to = ['alethea@capecod.com.au', 'keith@capecod.com.au', 'kirstie@capecod.com.au', 'nadia@capecod.com.au', 'ross@capecod.com.au', 'fudge@jordan.net.au'];
                $email_cc = ['clinton@capecod.com.au', 'jim@capecod.com.au', 'juliana@capecod.com.au', 'scott@capecod.com.au', 'michelle@capecod.com.au', 'jayden@capecod.com.au'];
                $email_subject = "Jobs Board - Pre Planning Meeting " . $today->format('d.m.y');
            }
        }
        //$email_to = ['fudge@jordan.net.au'];
        //$email_cc = ['fudge@jordan.net.au'];
        //$email_subject = "Jobs Board - Pre Planning Meeting " . $today->format('d.m.y');

        //dd($email_to);

        if ($email_to) {
            Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Site\SiteUpcomingJobs($file, $email_subject));
            echo "emailed<br>";
        }

        //$bytes_written = File::append(public_path('filebank/log/hourly.txt'), $log);
        //if ($bytes_written === false) die("Error writing to file");
    }

}