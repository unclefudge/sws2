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

class CronController extends Controller {

    static public function nightly()
    {
        echo "<h1> Nightly Update - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";
        $log = "Nightly Update\n--------------\n\n";
        $bytes_written = File::put(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

        CronController::nonattendees();
        CronController::roster();
        CronController::qa();
        CronController::completeToDoCompanyDoc();
        CronController::expiredCompanyDoc();
        CronController::expiredSWMS();
        CronController::archiveToolbox();
        CronController::completedQA();
        CronController::brokenQaItem();

        // Only run on week days otherwise get same email multiple times over weekend
        if (Carbon::today()->isMonday())
            CronController::overdueToDo();

        // Email Nightly Reports
        CronReportController::nightly();


        /*if (Carbon::today()->isTuesday())
            CronController::emailOutstandingQA();

        if (Carbon::today()->isThursday()) {
            CronController::emailJobstart();
            CronController::emailEquipmentTransfers();
            CronController::emailOnHoldQA();
        }

        // Fortnightly on Mondays starting 26 Oct 2020
        $start_monday = Carbon::createFromFormat('Y-m-d', '2020-10-26');
        if (Carbon::today()->isMonday() && $start_monday->diffInDays(Carbon::now()) % 2 == 0)
            CronController::emailFortnightlyReports();
        */


        echo "<h1>ALL DONE - NIGHTLY COMPLETE</h1>";
        $log = "\nALL DONE - NIGHTLY COMPLETE\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function verifyNightly()
    {
        $log = public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt');
        //echo "Log: $log<br>";
        if (strpos(file_get_contents($log), "ALL DONE - NIGHTLY COMPLETE") !== false) {
            //echo "successful";
            //Mail::to('support@openhands.com.au')->send(new \App\Mail\Misc\VerifyNightly("was Successful"));
        } else {
            //echo "failed";
            Mail::to('support@openhands.com.au')->send(new \App\Mail\Misc\VerifyNightly("Failed"));
        }
    }

    /*
     * Add non-attendees to the non-compliant list
     */
    static public function nonattendees()
    {
        $log = '';
        $yesterday = Carbon::now()->subDays(1);
        $lastweek = Carbon::now()->subDays(7);

        echo "<h2>Adding Non-Attendees to the Non-Logged in list (" . $lastweek->format('d/m/Y') . ' - ' . $yesterday->format('d/m/Y') . ")</h2>";
        $log .= "Adding Non-Attendees to the Non-Logged in list (" . $lastweek->format('d/m/Y') . ' - ' . $yesterday->format('d/m/Y') . ")\n";
        $log .= "-------------------------------------------------------------------------\n\n";

        $allowedSites = Site::all()->pluck('id')->toArray();
        if (Auth::check())
            $allowedSites = Auth::user()->company->sites('1')->pluck('id')->toArray();

        $roster = SiteRoster::where('date', '>=', $lastweek->format('Y-m-d'))->where('date', '<=', $yesterday->format('Y-m-d'))->whereIn('site_id', $allowedSites)->orderBy('site_id')->get();

        $found = false;
        foreach ($roster as $rost) {
            $site = Site::find($rost->site_id);
            $user = User::find($rost->user_id);
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $rost->date);

            // if date is weekday
            if ($date->isWeekday()) {
                if (!$site->isUserOnsite($rost->user_id, $rost->date) && !$site->isUserOnCompliance($rost->user_id, $rost->date)) {
                    echo $rost->date->format('d/m/Y') . " $site->name ($site->code) - <b>$user->fullname</b> (" . $user->company->name_alias . ") was absent<br>";
                    $log .= $rost->date->format('d/m/Y') . " $site->name ($site->code) - $user->fullname (" . $user->company->name_alias . ") was absent\n";
                    SiteCompliance::create(array(
                        'site_id'     => $site->id,
                        'user_id'     => $user->id,
                        'date'        => $rost->date,
                        'reason'      => null,
                        'status'      => 0,
                        'resolved_at' => '0000-00-00 00:00:00'
                    ));
                    $found = true;
                }
            }
        }
        if (!$found) {
            echo "There were no Non-Attendees to add or they were already on the list<br>";
            $log .= "There were no Non-Attendees to add or they were already on the list\n";
        }
        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Add new entities to Roster from Planner
     */
    static public function roster()
    {
        $log = '';
        echo "<h2>Adding Users to Roster</h2>";
        $log .= "Adding New Users to Roster\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $allowedSites = Site::all()->pluck('id')->toArray();
        if (Auth::check())
            $allowedSites = Auth::user()->company->sites('1')->pluck('id')->toArray();

        $date = Carbon::now()->format('Y-m-d');
        $planner = SitePlanner::where('from', '<=', $date)->where('to', '>=', $date)->whereIn('site_id', $allowedSites)->orderBy('site_id')->get();

        foreach ($planner as $plan) {
            if ($plan->entity_type == 'c') {
                $site = Site::find($plan->site_id);
                // Only add active sites to roster
                if ($site->status == 1 && $site->code != '0007') {
                    $company = Company::findOrFail($plan->entity_id);
                    $staff = $company->staffStatus(1)->pluck('id')->toArray();
                    $task = Task::find($plan->task_id);
                    echo "<br><b>Site:$site->name ($plan->site_id) &nbsp; Company: $company->name_alias &nbsp; Task: $task->name &nbsp; PID: $plan->id</b><br>";
                    $log .= "\nSite: $site->name ($plan->site_id) Company: $company->name_alias  Task: $task->name PID: $plan->id\n";
                    $found = false;
                    foreach ($staff as $staff_id) {
                        $user = User::findOrFail($staff_id);
                        if (!$site->isUserOnRoster($staff_id, $date)) {
                            echo 'adding ' . $user->fullname . ' (' . $user->username . ') to roster<br>';
                            $log .= 'adding ' . $user->fullname . ' (' . $user->username . ") to roster\n";
                            $newRoster = SiteRoster::create(array(
                                'site_id'    => $site->id,
                                'user_id'    => $staff_id,
                                'date'       => $date . ' 00:00:00',
                                'created_by' => '1',
                                'updated_by' => '1',
                            ));
                            $found = true;
                        }
                    }
                    if (!$found) {
                        echo "There were no users to add or they were already on the roster<br>";
                        $log .= "There were no users to add or they were already on the roster\n";
                    }
                }
            }
        }
        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Quality Assurance
     */
    static public function qa()
    {
        $log = '';
        echo "<h2>Checking for New QA to be triggered</h2>";
        $log .= "Checking for New QA to be triggered\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $allowedSites = Company::find('3')->sites('1')->pluck('id')->toArray();
        if (Auth::check())
            $allowedSites = Auth::user()->company->sites('1')->pluck('id')->toArray();

        $today = Carbon::today()->format('Y-m-d');
        $last7days = Carbon::today()->subDays(7)->format('Y-m-d');

        // Old Templates
        $trigger_ids_old = [];
        $active_templates_old = SiteQa::where('master', '1')->where('status', '1')->where('company_id', '3')->where('id', '<', 100)->get();
        foreach ($active_templates_old as $qa) {
            foreach ($qa->tasks() as $task) {
                if (isset($trigger_ids_old[$task->id])) {
                    if (!in_array($qa->id, $trigger_ids_old[$task->id]))
                        $trigger_ids_old[$task->id][] = $qa->id;
                } else
                    $trigger_ids_old[$task->id] = [$qa->id];
            }
        }
        ksort($trigger_ids_old);

        // New Templates
        $trigger_ids_new = [];
        $active_templates_new = SiteQa::where('master', '1')->where('status', '1')->where('company_id', '3')->where('id', '>', 100)->get();
        foreach ($active_templates_new as $qa) {
            foreach ($qa->tasks() as $task) {
                if (isset($trigger_ids_new[$task->id])) {
                    if (!in_array($qa->id, $trigger_ids_new[$task->id]))
                        $trigger_ids_new[$task->id][] = $qa->id;
                } else
                    $trigger_ids_new[$task->id] = [$qa->id];
            }
        }
        ksort($trigger_ids_new);


        echo "Task ID's for active templates Old(";
        $log .= "Task ID's for active templates Old(";
        foreach ($trigger_ids_old as $key => $value) {
            echo "$key,";
            $log .= "$key,";
        }
        echo ")<br><br>";
        $log .= ")\n\n";

        echo "Task ID's for active templates New(";
        $log .= "Task ID's for active templates New(";
        foreach ($trigger_ids_new as $key => $value) {
            echo "$key,";
            $log .= "$key,";
        }
        echo ")<br><br>";
        $log .= ")\n\n";

        $planner = SitePlanner::where('to', '<', $today)->where('to', '>', $last7days)->whereIn('site_id', $allowedSites)->orderBy('site_id')->get();
        $job_started_from = Carbon::createFromDate('2017', '07', '13');

        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            $trigger_ids = ($site->hasOldQa()) ? $trigger_ids_old : $trigger_ids_new;
            if (isset($trigger_ids[$plan->task_id])) {
                $start_date = SitePlanner::where('site_id', $plan->site_id)->where('task_id', '11')->first();
                if ($start_date->from->gt($job_started_from)) {
                    foreach ($trigger_ids[$plan->task_id] as $qa_id) {
                        if (!$site->hasTemplateQa($qa_id)) {
                            // Create new QA by copying required template
                            $qa_master = SiteQa::findOrFail($qa_id);

                            // Create new QA Report for Site
                            $newQA = SiteQa::create([
                                'name'       => $qa_master->name,
                                'site_id'    => $site->id,
                                'version'    => $qa_master->version,
                                'master'     => '0',
                                'master_id'  => $qa_master->id,
                                'company_id' => $qa_master->company_id,
                                'status'     => '1',
                                'created_by' => '1',
                                'updated_by' => '1',
                            ]);

                            // Copy items from template
                            foreach ($qa_master->items as $item) {
                                $newItem = SiteQaItem::create(
                                    ['doc_id'     => $newQA->id,
                                     'task_id'    => $item->task_id,
                                     'name'       => $item->name,
                                     'order'      => $item->order,
                                     'super'      => $item->super,
                                     'master'     => '0',
                                     'master_id'  => $item->id,
                                     'created_by' => '1',
                                     'updated_by' => '1',
                                    ]);
                            }
                            $newTemplate = ($qa_master->id > 100) ? ' *NEW*' : '';
                            echo "Created QA [$newQA->id] Task:$plan->task_code ($plan->task_id) - $newQA->name - Site:$site->name $newTemplate<br>";
                            $log .= "Created QA [$newQA->id] Task:$plan->task_code ($plan->task_id) - $newQA->name - Site:$site->name $newTemplate\n";
                            $newQA->createToDo($site->supervisors->pluck('id')->toArray());
                        } else {
                            // Existing QA for site - make Active if currently On Hold
                            $qa = SiteQa::where('site_id', $site->id)->where('master_id', $qa_id)->first();
                            if ($qa->status == '2') {
                                // Task just ended on planner yesterday so create ToDoo + Reactive
                                if ($plan->to->format('Y-m-d') == Carbon::yesterday()->format('Y-m-d')) {
                                    $qa->status = 1;
                                    $qa->save();
                                    $qa->createToDo($site->supervisors->pluck('id')->toArray());
                                    echo "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - reactived<br>";
                                    $log .= "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - reactived\n";
                                } else {
                                    echo "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - on hold<br>";
                                    $log .= "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - on hold\n";
                                }
                            } elseif ($qa->status == '-1') {
                                echo "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - not required<br>";
                                $log .= "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - not required\n";
                            } else {
                                echo "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - active<br>";
                                $log .= "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - active\n";
                            }
                        }
                    }
                }
            }

            // If Task = Prac Complete (id 265) make all non-completed reports active for given site
            if ($plan->task_id == '265') {
                $site_qa = SiteQa::where('site_id', $plan->site_id)->where('status', '<>', '0')->get();
                foreach ($site_qa as $qa) {
                    // Report On Hold so Reactive
                    if ($qa->status == '2') {
                        $qa->status = 1;
                        $qa->save();
                        $qa->createToDo($site->supervisors->pluck('id')->toArray());
                        echo "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - reactived due to PRAC Complete<br>";
                        $log .= "Existing QA[$qa->id] Task:$plan->task_code ($plan->task_id) - $qa->name  Site:$site->name - reactived due to PRAC Complete\n";
                    }
                }
            }
        }
        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";


        echo "<h2>Closing completed QA ToDos</h2>";
        $log .= "Closing completed QA ToDos\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $records = Todo::where('type', 'qa')->where('status', 1)->get();
        foreach ($records as $rec) {
            $qa = SiteQa::find($rec->type_id);
            if ($qa) {
                if ($qa->status == 0 || $qa->status == - 1) {
                    echo '[' . $rec->id . '] qaID:' . $rec->type_id . " - " . $qa->status . "<br>";
                    $rec->status = 0;
                    $rec->save();
                }
            }

        }
        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";


        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Check for Expired Company Docs
     */
    static public function expiredCompanyDoc()
    {
        $log = '';
        echo "<h2>Checking for Expired Company Documents</h2>";
        $log .= "Checking for Expired Company Documents\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $today = Carbon::today();
        $week2_coming = Carbon::today()->addDays(14);
        $week1_ago = Carbon::today()->subDays(7);
        $week2_ago = Carbon::today()->subDays(14);
        $week3_ago = Carbon::today()->subDays(21);
        $week4_ago = Carbon::today()->subDays(28);

        $dates = [
            $week2_coming->format('Y-m-d') => "Expiry in 2 weeks on " . $week2_coming->format('d/m/Y'),
            $today->format('Y-m-d')        => "Expired today on " . $today->format('d/m/Y'),
            $week1_ago->format('Y-m-d')    => "Expired 1 week ago on " . $week1_ago->format('d/m/Y'),
            $week2_ago->format('Y-m-d')    => "Expired 2 weeks ago on " . $week2_ago->format('d/m/Y'),
            $week3_ago->format('Y-m-d')    => "Expired 3 weeks ago on " . $week3_ago->format('d/m/Y'),
            $week4_ago->format('Y-m-d')    => "Expired 4 weeks ago on " . $week4_ago->format('d/m/Y'),
        ];

        echo "<b>Docs being marked as expired</b></br>";
        $docs = CompanyDoc::where('status', 1)->whereDate('expiry', '<', $today->format('Y-m-d'))->get();
        if ($docs->count()) {
            foreach ($docs as $doc) {
                $company = Company::find($doc->for_company_id);
                echo "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]<br>";
                $log .= "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]\n";
                $doc->updated_by = 1;
                $doc->updated_at = Carbon::now()->toDateTimeString();
                $doc->status = 0;
                $doc->save();
            }
        } else {
            echo "No expired docs<br><br>";
            $log .= "No expired docs<br><br>";
        }

        foreach ($dates as $date => $mesg) {
            echo "<br><b>$mesg</b><br>";
            $log .= "$mesg $date\n";

            $docs = CompanyDoc::whereDate('expiry', '=', $date)->get();
            if ($docs->count()) {
                foreach ($docs as $doc) {
                    $company = Company::find($doc->for_company_id);
                    if ($company->status) {
                        echo "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]<br>";
                        $log .= "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]\n";

                        if ($date == Carbon::today()->addDays(14)->format('Y-m-d')) {
                            // Due in 2 weeks
                            //if (count($company->seniorUsers())) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray(), false);

                            // Email SeniorUsers + Parent Company
                            if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                                $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval'), false);
                                echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "<br>";
                                $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "\n";
                            }
                        } else {
                            $doc->closeToDo(User::find(1));
                            // Determine if doc hasn't been replaced with newer version
                            if (!$doc->company->activeCompanyDoc($doc->category_id)) {
                                if (count($company->seniorUsers()) && $company->id != 3) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray(), true);
                                if ($date == Carbon::today()->subDays(14)->format('Y-m-d')) {
                                    // Email Parent Company
                                    if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                                        $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval'), true);
                                        echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "<br>";
                                        $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "\n";
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                echo "No expired documents<br>";
                $log .= "No expired documents\n";
            }
        }


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Check for Expired SWMS
     */
    static public function expiredSWMS()
    {
        $log = '';
        echo "<h2>Checking for Expired SWMS</h2>";
        $log .= "Checking for Expired SWMS\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $today = Carbon::today();
        $today2 = Carbon::today()->subYears(2);
        $week2_coming = Carbon::today()->addDays(14);
        $week2_coming2 = Carbon::today()->addDays(14)->subYears(3);
        $week4_ago = Carbon::today()->subDays(28);
        $week4_ago2 = Carbon::today()->subDays(28)->subYears(3);

        $dates = [
            $week2_coming2->format('Y-m-d') => "Expiry in 2 weeks on " . $week2_coming->format('d/m/Y'),
            $today2->format('Y-m-d')        => "Expired today on " . $today->format('d/m/Y'),
            $week4_ago2->format('Y-m-d')    => "Expired 4 weeks ago on " . $week4_ago->format('d/m/Y'),
        ];

        foreach ($dates as $date => $mesg) {
            echo "<br><b>$mesg</b> $date<br>";
            $log .= "$mesg $date\n";

            $docs = WmsDoc::where('master', '0')->whereDate('updated_at', '=', $date)->get();
            if ($docs->count()) {
                foreach ($docs as $doc) {
                    if ($doc->status == 1) {
                        $company = Company::find($doc->for_company_id);
                        if ($company->status) {
                            echo "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->updated_at->format('d/m/Y') . "]<br>";
                            $log .= "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->updated_at->format('d/m/Y') . "]\n";

                            if ($date == Carbon::today()->addDays(14)->subYear()->format('Y-m-d')) {
                                // Due in 2 weeks
                                if (count($company->seniorUsers()) && $company->id != 3) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray(), false);
                                $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('swms.approval'), false);
                                echo "Created ToDo for company + emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "<br>";
                                $log .= "Created ToDo for company + emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "\n";
                            } else {
                                $doc->closeToDo(User::find(1));
                                if (count($company->seniorUsers()) && $company->id != 3) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray(), true);
                                echo "Created ToDo for company<br>";
                                $log .= "Created ToDo for company\n";
                                if ($date == Carbon::today()->subDays(28)->format('Y-m-d')) {
                                    $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('swms.approval'), true);
                                    echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "<br>";
                                    $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "\n";
                                }
                            }
                        }
                    }
                }
            } else {
                echo "No expired SWMS<br>";
                $log .= "No expired SWMS\n";
            }
        }


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Archive completed Toolbox
     */
    static public function archiveToolbox()
    {
        $log = '';
        echo "<h2>Archive Completed Toolbox</h2>";
        $log .= "Archive Completed Toolbox\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $talks = ToolboxTalk::where('master', 0)->where('status', 1)->get();
        if ($talks->count()) {
            foreach ($talks as $talk) {
                if (!$talk->outstandingBy()->count()) {
                    // Archive completed Toolbox
                    echo "[$talk->id] All Completed - $talk->name<br>";
                    $log .= "[$talk->id] All Completed - $talk->name\n";
                    $talk->status = - 1;
                    $talk->save();
                } else {
                    $inactive = true;
                    foreach ($talk->outstandingBy() as $user) {
                        if ($user->status)
                            $inactive = false;
                    }
                    // Archive completed Toolbox because all outstanding users are inactive
                    if ($inactive) {
                        echo "**[$talk->id] Inactive Users - $talk->name<br>";
                        $log .= "[$talk->id] Inactive Users - $talk->name\n";
                        $talk->status = - 1;
                        $talk->save();
                    }

                }
            }
        } else {
            echo "No completed Toolbox<br>";
            $log .= "No completed Toolbox\n";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Archive completed Toolbox
     */
    static public function completeToDoCompanyDoc()
    {
        $log = '';
        echo "<h2>Todo company doc completed but still active</h2>";
        $log .= "Todo company doc completed but still active\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $todos = \App\Models\Comms\Todo::all();
        foreach ($todos as $todo) {
            if ($todo->status && $todo->type == 'company doc') {
                $doc = \App\Models\Company\CompanyDoc::find($todo->type_id);
                if ($doc) {
                    if ($doc->status == 1) {
                        //echo "ToDo [$todo->id] - $todo->name (".$doc->company->name.") ACTIVE DOC<br>";
                        //$todo->status = 0;
                        //$todo->done_at = Carbon::now();
                        //$todo->done_by = 1;
                        //$todo->save();
                    }
                    if ($doc->status == 0) {
                        if ($doc->company->activeCompanyDoc($doc->category_id)) {
                            echo "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") REPLACED DOC<br>";
                            $log .= "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") REPLACED DOC\n";
                            $todo->status = 0;
                            $todo->done_at = Carbon::now();
                            $todo->done_by = 1;
                            $todo->save();
                        } else {
                            //echo "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") INACTIVE DOC<br>";
                            //$log .= "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") INACTIVE DOC\n";
                        }

                    }

                } else {
                    echo "ToDo [$todo->id] - " . $todo->company->name . " (DELETED)<br>";
                }
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Check for overdue ToDoo
     */
    static public function overdueToDo()
    {
        $log = '';
        echo "<h2>Checking for Overdue ToDo's</h2>";
        $log .= "Checking for Overdue ToDo's\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $toolboxs_overdue = [];
        $todos = Todo::where('status', '1')->whereDate('due_at', '<', Carbon::today()->format('Y-m-d'))->where('due_at', '<>', '0000-00-00 00:00:00')->orderBy('due_at')->get();
        foreach ($todos as $todo) {
            // Quality Assurance
            if ($todo->type == 'qa') {
                echo "id[$todo->id] $todo->name [" . $todo->due_at->format('d/m/Y') . "]<br>";
                $log .= "id[$todo->id] $todo->name [" . $todo->due_at->format('d/m/Y') . "]\n";
                //$todo->emailToDo();
                $qa = SiteQa::find($todo->type_id);
                $email_to = [env('EMAIL_DEV')];
                if (\App::environment('prod') && $qa->site->areaSupervisorsEmails())
                    $email_to = $qa->site->areaSupervisorsEmails();
                //Mail::to($email_to)->send(new \App\Mail\Site\SiteQaOverdue($qa));
            }

            // Toolbox Talk
            if ($todo->type == 'toolbox') {
                $toolbox = ToolboxTalk::find($todo->type_id);
                if ($toolbox && $toolbox->status == 1) {
                    echo "id[$todo->id] $todo->name [" . $todo->due_at->format('d/m/Y') . "] - " . $todo->assignedToBySBC() . "<br>";
                    $log .= "id[$todo->id] $todo->name [" . $todo->due_at->format('d/m/Y') . "] - " . $todo->assignedToBySBC() . "\n";
                    $todo->emailToDo();
                    if (!in_array($todo->type_id, $toolboxs_overdue))
                        $toolboxs_overdue[] = $todo->type_id;
                } else {
                    // Toolbox is no longer active so close outstanding ToDos
                    $todo->status = 0;
                    $todo->done_at = Carbon::now();
                    $todo->done_by = 1;
                    $todo->save();
                }
            }
        }

        // Send single email to Parent company for each overdue Toolbox
        if ($toolboxs_overdue) {
            echo "<br><b>Sending email notification to parent company for following outstanding Toolbox Talks:</b><br>";
            $log .= "\nSending email notification to parent company for following outstanding Toolbox Talks:\n";
            foreach ($toolboxs_overdue as $toolbox_id) {
                $toolbox = ToolboxTalk::find($toolbox_id);
                echo "id[$toolbox->id] $toolbox->name<br>";
                $log .= "id[$toolbox->id] $toolbox->name\n";
                $toolbox->emailOverdue();
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function completedQA()
    {
        $log = '';
        echo "<br><br>Todo QA doc completed/hold but still active<br><br>";
        $log .= "\nTodo QA doc completed/hold but still active\n";
        $todos = Todo::all();
        foreach ($todos as $todo) {
            if ($todo->status && $todo->type == 'qa') {
                $qa = SiteQa::find($todo->type_id);
                if ($qa) {
                    if ($qa->status == 1) {
                        //echo "ToDo [$todo->id] - $todo->name ACTIVE QA<br>";
                    }
                    if ($qa->status == 0) {
                        echo "ToDo [$todo->id] - $todo->name COMPLETED QA<br>";
                        $log .= "ToDo [$todo->id] - $todo->name COMPLETED QA\n";
                        $todo->status = 0;
                        $todo->save();
                        // $todo->delete();
                    }
                    if ($qa->status == 2) {
                        echo "ToDo [$todo->id] - $todo->name HOLD QA<br>";
                        $log .= "ToDo [$todo->id] - $todo->name HOLD QA\n";
                        $todo->status = 0;
                        $todo->save();
                        // $todo->delete();
                    }

                } else {
                    echo "ToDo [$todo->id] (DELETED)<br>";
                    $log .= "ToDo [$todo->id] (DELETED)\n";
                    $todo->status = 0;
                    $todo->save();
                    // $todo->delete();
                }
            }
        }
        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function brokenQaItem()
    {
        $log = '';
        echo "<br><br>Fixing broken QA items<br><br>";
        $log .= "\nFixing broken QA items\n";
        $qas = SiteQa::where('status', '>', 0)->where('master', 0)->get();

        foreach ($qas as $qa) {
            foreach ($qa->items as $item) {
                if ($item->done_by === null && $item->status == 0 && $item->sign_by) {
                    echo "<br>[$qa->id] $qa->name (" . $qa->site->name . ")<br>- $item->name doneBy[$item->done_by] signBy[$item->sign_by] status[$item->status]<br>";
                    $log .= "\n[$qa->id] $qa->name (" . $qa->site->name . ")\n- $item->name doneBy[$item->done_by] signBy[$item->sign_by] status[$item->status]\n";
                    $item->status = 1;

                    // Check Planner which company did the task
                    $planned_task = SitePlanner::where('site_id', $qa->site_id)->where('task_id', $item->task_id)->first();
                    if ($planned_task && $planned_task->entity_type == 'c' && !$item->super)
                        $item->done_by = $planned_task->entity_id;

                    $item->save();
                }
            }
        }

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
        $emails = implode("; ", $cc->notificationsUsersEmailType('site.jobstartexport'));
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

        $email_list = $cc->notificationsUsersEmailType('site.jobstartexport');
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
        $emails = implode("; ", $cc->notificationsUsersEmailType('equipment.transfers'));
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

        $email_list = $cc->notificationsUsersEmailType('equipment.transfers');

        Mail::to($email_list)->send(new \App\Mail\Misc\EquipmentTransfers($file));

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
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
        $emails = implode("; ", $cc->notificationsUsersEmailType('site.qa.outstanding'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        // Create PDF
        $file = public_path('filebank/tmp/qa-outstanding-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/site-qa-outstanding', compact('qas', 'today'));
        //return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'today'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'today'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('site.qa.outstanding');

        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file));

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
        $emails = implode("; ", $cc->notificationsUsersEmailType('site.qa.outstanding'));
        echo "Sending email to $emails";
        $log .= "Sending email to $emails";

        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        // Create PDF
        $file = public_path('filebank/tmp/qa-outstanding-cron.pdf');
        if (file_exists($file))
            unlink($file);

        //return view('pdf/site/site-qa-outstanding', compact('qas', 'today'));
        //return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'today'))->setPaper('a4', 'landscape')->stream();

        $pdf = PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'today'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        $email_list = $cc->notificationsUsersEmailType('site.qa.outstanding');

        Mail::to($email_list)->send(new \App\Mail\Site\SiteQaOutstanding($file));

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
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.noaction');
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
        $email_list = $cc->notificationsUsersEmailType('site.maintenance.onhold');
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
}