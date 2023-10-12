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
use App\Models\Company\CompanyDocCategory;
use App\Models\Company\CompanyDocReview;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Misc\Action;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Misc\Supervisor\SuperChecklistSettings;
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
        $log = "Nightly Update - " . Carbon::now()->format('d/m/Y g:i a') . "\n-------------------------------------------------------------------------\n\n";
        $bytes_written = File::put(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");

        CronController::blessing();
        CronController::nonattendees();
        CronController::roster();
        CronController::qa();
        CronController::qaOnholdButCompleted();
        CronController::completeToDoCompanyDoc();
        CronController::completedToDoQA();
        CronController::expiredStandardDetailsDoc();
        CronController::expiredSWMS();
        CronController::archiveToolbox();
        CronController::brokenQaItem();
        CronController::emailPlannerKeyTasks();
        //CronController::actionPlannerKeyTasks();
        CronController::siteExtensions();
        CronController::superChecklists();
        CronController::uploadCompanyDocReminder();
        CronController::verifyZohoImport();

        // Monday
        if (Carbon::today()->isMonday()) {
            CronController::overdueToDo();
        }

        // Tuesday
        if (Carbon::today()->isTuesday()) {
            CronController::siteExtensionsSupervisorTask();
        }

        // Thursday
        if (Carbon::today()->isThursday()) {
            CronController::siteExtensionsSupervisorTaskReminder();
        }

        // Thursday
        if (Carbon::today()->isFriday()) {
            CronController::siteExtensionsSupervisorTaskFinalReminder();
        }


        // Email Nightly Reports
        CronReportController::nightly();

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
            Mail::to('fudge@jordan.net.au')->send(new \App\Mail\Misc\VerifyNightly("Failed"));
        }
    }

    /*
    * Blessing
    */
    static public function blessing()
    {
        $log = "+----------------------+\n";
        $log .= "|  Prayer of Blessing  |\n";
        $log .= "+----------------------+\n";
        $log .= " " . Carbon::now()->format('d/m/Y g:i a') . "\n\n";
        $log .= "May each of the following workers be blessed, may they be protected from injuries,\n";
        $log .= "may they experience a clarity of heart and mind while they work and their spirits be at peace.\n";
        $log .= "Today is a new day, and may they experience a freshness and freedom from past troubles and hurts,\n";
        $log .= "a restoration + healing of their minds, bodies and souls, plus a deeper understanding of Father God's love for them.\n\n";
        $log .= "";

        $users = User::all();
        foreach ($users->sortBy('firstname') as $user) {
            $log .= "$user->name (" . $user->company->name . ")\n";
        }
        $log .= "\n\nAmen.";
        $bytes_written = File::put(public_path('filebank/tmp/blessing.txt'), $log);
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
                            $newQA->createToDo($site->supervisor_id);
                        } else {
                            // Existing QA for site - make Active if currently On Hold
                            $qa = SiteQa::where('site_id', $site->id)->where('master_id', $qa_id)->first();
                            if ($qa->status == '2') {
                                // Task just ended on planner yesterday so create ToDoo + Reactive
                                if ($plan->to->format('Y-m-d') == Carbon::yesterday()->format('Y-m-d')) {
                                    $qa->status = 1;
                                    $qa->save();
                                    $qa->createToDo($site->supervisor_id);
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
                        $qa->createToDo($site->supervisor_id);
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
     * Check for QAs On Hold but Completed
     */
    static public function qaOnholdButCompleted()
    {
        $log = '';
        echo "<h2>Checking for On Holds QA but Completed</h2>";
        $log .= "Checking for On Holds QA but Completed\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $allowedSites = Company::find('3')->sites('1')->pluck('id')->toArray();
        if (Auth::check())
            $allowedSites = Auth::user()->company->sites('1')->pluck('id')->toArray();

        $on_hold = SiteQa::where('master', '0')->where('status', '2')->where('company_id', '3')->get();
        foreach ($on_hold as $qa) {
            if ($qa->items->count() == $qa->itemsCompleted()->count()) {
                $qa->status = 1;
                $qa->save();
                echo "Moved [$qa->id] $qa->name to Active<br>";
                $log .= "Moved [$qa->id] $qa->name to Active\n";
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

        echo "<b>Docs being marked as expired/renewal due</b></br>";
        $docs = CompanyDoc::where('status', 1)->whereDate('expiry', '<', $today->format('Y-m-d'))->get();
        if ($docs->count()) {
            foreach ($docs as $doc) {
                $company = Company::find($doc->for_company_id);
                $standard_details = ($doc->category_id == 22 || $doc->category->parent == 22) ? 'Renew' : '';

                // Expire document unless it's a Standard Details doc
                if (!$standard_details) {
                    echo "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]<br>";
                    $log .= "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]\n";
                    $doc->updated_by = 1;
                    $doc->updated_at = Carbon::now()->toDateTimeString();
                    $doc->status = 0;
                    $doc->save();
                }
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

                        $standard_details = ($doc->category_id == 22 || $doc->category->parent == 22) ? 'Renew' : '';
                        if (!$standard_details) {
                            // Send out reminder Email of expired doc
                            // - @ 2 weeks also send parent company an email
                            if ($date == Carbon::today()->addDays(28)->format('Y-m-d')) {
                                // Due in 4 weeks

                                // Email SeniorUsers + Parent Company
                                if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                                    $doc->emailExpired();
                                    echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "<br>";
                                    $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "\n";
                                }
                            } elseif ($date == Carbon::today()->addDays(14)->format('Y-m-d')) {
                                // Due in 2 weeks

                                // Email SeniorUsers + Parent Company
                                if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                                    $doc->emailExpired();
                                    echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "<br>";
                                    $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "\n";
                                }
                            } else {
                                $doc->closeToDo(User::find(1));
                                // Determine if doc hasn't been replaced with newer version
                                if (!$doc->company->activeCompanyDoc($doc->category_id)) {
                                    if (count($company->seniorUsers()) && $company->id != 3) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray());
                                    if ($date == Carbon::today()->subDays(14)->format('Y-m-d')) {
                                        // Email Parent Company
                                        if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                                            $doc->emailExpired();
                                            echo "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "<br>";
                                            $log .= "Emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('doc.' . $doc->category->type . '.approval')) . "\n";
                                        }
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
    * Check for Expired Company Docs
    */
    static public function expiredStandardDetailsDoc()
    {
        $log = '';
        echo "<h2>Checking for Expired Standard Details Documents</h2>";
        $log .= "Checking for Expired Standard Details Documents\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $company = Company::find(3);
        $today = Carbon::today();
        $standard_ids = array_merge([22], CompanyDocCategory::where('parent', 22)->pluck('id')->toArray());
        $docs = CompanyDoc::whereIn('category_id', $standard_ids)->where('status', 1)->whereDate('expiry', '<', $today->format('Y-m-d'))->get();

        $newRenewals = [];
        if ($docs->count()) {
            foreach ($docs as $doc) {
                $expire_date = $doc->expiry->format('d/m/Y');
                $review_doc = CompanyDocReview::where('doc_id', $doc->id)->first();

                if (!$review_doc) {
                    echo "$doc->name [$expire_date] added to renewal cycle<br>";
                    $log .= "$doc->name [$expire_date] added to renewal cycle\n";
                    $newRenewals[] = $doc->id;
                    $review_doc = CompanyDocReview::create(['doc_id' => $doc->id, 'name' => $doc->name, 'stage' => '1', 'original_doc' => $doc->attachment, 'status' => 1, 'created_by' => '1', 'updated_by' => 1]);
                    $review_doc->createAssignToDo(108); // Kirstie
                    $action = Action::create(['action' => 'Standard Details review initiated', 'table' => 'company_docs_review', 'table_id' => $review_doc->id, 'created_by' => '1', 'updated_by' => '1']);
                } else {
                    echo "$doc->name [$expire_date] already on renewal cycle<br>";
                    $log .= "$doc->name [$expire_date] already on renewal cycle\n";
                }
            }
            //dd($newRenewals);
            if ($newRenewals) {
                $docs = "The following documents expired $expire_date and are due for renewal:\r\n";
                foreach ($newRenewals as $doc_id) {
                    $doc = CompanyDoc::findOrFail($doc_id);
                    $docs .= "$doc->name\r\n";
                }
                //dd($docs);

                $email_to = $company->reportsTo()->notificationsUsersEmailType('doc.standard.renew');
                if (!\App::environment('prod')) $email_to = [env('EMAIL_DEV')];
                if ($email_to) Mail::to($email_to)->send(new \App\Mail\Company\CompanyDocRenewalMulti($docs));
                echo "Emailed " . implode("; ", $email_to) . "<br>";
                $log .= "Emailed " . implode("; ", $email_to) . "\n";
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
                                $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('swms.approval'));
                                echo "Created ToDo for company + emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "<br>";
                                $log .= "Created ToDo for company + emailed " . implode("; ", $company->reportsTo()->notificationsUsersEmailType('swms.approval')) . "\n";
                            } else {
                                $doc->closeToDo(User::find(1));
                                if (count($company->seniorUsers()) && $company->id != 3) $doc->createExpiredToDo($company->seniorUsers()->pluck('id')->toArray(), true);
                                echo "Created ToDo for company<br>";
                                $log .= "Created ToDo for company\n";
                                if ($date == Carbon::today()->subDays(28)->format('Y-m-d')) {
                                    $doc->emailExpired($company->reportsTo()->notificationsUsersEmailType('swms.approval'));
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

    static public function completedToDoQA()
    {
        $log = '';
        echo "<br><br>Todo QA doc completed/hold but still active<br><br>";
        $log .= "\nTodo QA doc completed/hold but still active\n";
        $log .= "------------------------------------------------------------------------\n\n";
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
        $log .= "------------------------------------------------------------------------\n\n";
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
    * Email Planner Key Tasks
    */
    static public function emailPlannerKeyTasks()
    {
        $log = '';
        $func_name = "Key Tasks on Planner";
        echo "<h2>Email $func_name</h2>";
        $log .= "Email $func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";
        $cc = Company::find(3);
        $email_list = [env('EMAIL_DEV')];
        if (\App::environment('prod'))
            $email_list = $cc->notificationsUsersEmailType('site.planner.key.tasks');
        $emails = implode("; ", $email_list);

        $date = Carbon::now()->format('Y-m-d');
        $keytasks = [
            4 => 'is now ready to inspect and review Packers and Floor Joist', // Lay Floor (LF)
            7 => 'is now ready to inspect and review the Frame and Roof']; // Frame & Roof FF (FR/FF)

        $email_sent = 0;
        foreach ($keytasks as $task_id => $subject) {
            $tasks = SitePlanner::whereDate('from', '=', $date)->where('task_id', $task_id)->orderBy('site_id')->get();

            // Log email being sent only once
            if ($tasks->count() && !$email_sent) {
                echo "Sending email to $emails<br>";
                $log .= "Sending email to $emails\n";
                $email_sent = 1;
            }

            foreach ($tasks as $task) {
                if ($task->site->status == 1) {
                    $mesg = 'Site ' . $task->site->name . ' ' . $subject;
                    echo "&nbsp; * $mesg<br>";
                    $log .= "&nbsp; * $mesg\n";
                    if ($email_list)
                        Mail::to($email_list)->send(new \App\Mail\Site\SitePlannerKeyTask($task, $mesg));
                }
            }
        }

        if (!$email_sent) {
            echo "No key tasks today<br>";
            $log .= "No key tasks today\n";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
   * Action Planner Key Tasks
   */
    static public function actionPlannerKeyTasks()
    {
        $log = '';
        $func_name = "Action Tasks on Planner";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";
        $cc = Company::find(3);

        $date = Carbon::now()->format('Y-m-d');
        $found_tasks = 0;

        //
        // Scafold Up - taskid: 297
        //
        $platform_up_ids = [220];
        $tasks = SitePlanner::whereDate('from', '=', $date)->whereIn('task_id', $platform_up_ids)->orderBy('site_id')->get();

        foreach ($tasks as $task) {
            if ($task->site->status == 1) {
                if ($task->entity_type == 'c' && $task->company->seniorUsers())
                    $mesg = 'Creating ToDo task Scaffold Handover Certificate for ' . $task->site->name . "\n";
                $mesg .= " - email sent to " . implode("; ", $task->company->seniorUsersEmail()) . "\n";
                echo "$mesg<br>";
                $log .= "$mesg\n";
                $todo_request = [
                    'type'       => 'scaffold handover',
                    'type_id'    => $task->site->id,
                    'name'       => 'Scaffold Handover Certificate for ' . $task->site->name,
                    'info'       => 'Please complete the Scaffold Handover Certificate for ' . $task->site->name,
                    'priority'   => '1',
                    'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
                    'company_id' => '3',
                    'created_by' => '1',
                    'updated_by' => '1'
                ];

                // Create ToDoo and assign to Site Supervisors
                $todo = Todo::create($todo_request);
                $todo->assignUsers($task->company->seniorUsers()->pluck('id')->toArray());
                $todo->emailToDo();
                $found_tasks ++;
            }
        }

        //
        // Project Information Sheets
        //

        $project_task_ids = [10, 378, 265];  // Polastic Eaves Windows (lock up), Pre-prac, Prac Completion
        $tasks = SitePlanner::whereDate('from', '=', $date)->whereIn('task_id', $project_task_ids)->orderBy('site_id')->get();

        foreach ($tasks as $task) {
            if ($task->site->status == 1) {

                // Create New Project Supply
                $project = SiteProjectSupply::where('site_id', $task->site->id)->first();
                if (!$project) {
                    $project = SiteProjectSupply::create(['site_id' => $task->site->id, 'version' => '1.0']);
                    $project->initialise();
                }
                $project->createReviewToDo($project->site->supervisor_id);
                $found_tasks ++;
            }
        }


        if (!$found_tasks) {
            echo "No key tasks today";
            $log .= "No key tasks today";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Site Contract Extension
    */
    static public function siteExtensions()
    {
        $log = '';
        $func_name = "Creating Site Extension for Active Sites";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $sites = Site::where('company_id', 3)->where('status', 1)->where('special', null)->get(); //whereNotIn('code', $hide_site_code);
        $mon = new Carbon('monday this week');
        $week2ago = new Carbon('monday this week');
        $week2ago->subWeeks(2);

        $data = [];
        $prac_yes = $prac_no = [];
        foreach ($sites as $site) {
            $start_job = SitePlanner::where('site_id', $site->id)->where('task_id', 11)->first();
            // Show only site which Job Start has before today
            if ($start_job && $start_job->from->lte($mon)) {
                $prac_completion = SitePlanner::where('site_id', $site->id)->where('task_id', 265)->first();
                if ($prac_completion && $prac_completion->from->lte($week2ago))
                    continue;
                $site_data = [
                    'id'              => $site->id,
                    'name'            => $site->name,
                    'completion_date' => $site->forecast_completion,
                    'completion_ymd'  => ($site->forecast_completion) ? $site->forecast_completion->format('ymd') : '',
                ];
                if ($site->forecast_completion)
                    $prac_yes[] = $site_data;
                else
                    $prac_no[] = $site_data;
            } else {
                //echo "No START[$site->id] $site->name<br>";
            }
        }

        usort($prac_yes, function ($a, $b) {
            return $a['completion_ymd'] <=> $b['completion_ymd'];
        });
        usort($prac_no, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        $data = array_merge($prac_yes, $prac_no);

        //dd($data);
        $mesg = "Existing";
        $ext = SiteExtension::whereDate('date', $mon->format('Y-m-d'))->first();
        if (!$ext) {
            $ext = SiteExtension::create(['date' => $mon->toDateTimeString(), 'status' => 1]);
            $mesg = "Creating new";
        }
        echo "$mesg week: " . $mon->format('d/m/Y') . "<br>";
        $log .= "$mesg week: " . $mon->format('d/m/Y') . "\n";

        // Create individual extension record for each site
        foreach ($data as $site) {
            $ext_site = SiteExtensionSite::where('extension_id', $ext->id)->where('site_id', $site['id'])->first();
            if (!$ext_site) {
                $ext_site = SiteExtensionSite::create(['extension_id' => $ext->id, 'site_id' => $site['id'], 'completion_date' => $site['completion_date']]);
                echo "Adding site [" . $site['id'] . "] " . $site['name'] . "<br>";
                $log .= "Adding site [" . $site['id'] . "] " . $site['name'] . "\n";
            } elseif ($ext_site->completion_date != $site['completion_date']) {
                $ext_site->completion_date = $site['completion_date'];
                $ext_site->save();
                echo "Updating site completion date[" . $site['id'] . "] " . $site['name'] . "<br>";
                $log .= "Updating site completion date[" . $site['id'] . "] " . $site['name'] . "\n";
            }
        }

        $ext->createPDF();

        // Close any Supervisor ToDoo tasks if all their sites completed
        foreach ($ext->sites as $site_ext) {
            $site = Site::findOrFail($site_ext->site_id);
            if ($site->supervisor_id && !$site_ext->extension->sitesNotCompletedBySupervisor($site->supervisor_id)->count()) {
                $todo = $site->supervisor->todoType('extension')->first();
                if ($todo)
                    $todo->close();
            }
        }

        // Archive old active extensions
        $old_extensions = SiteExtension::where('status', 1)->whereDate('date', '<', $mon->format('Y-m-d'))->get();
        if ($old_extensions) {
            foreach ($old_extensions as $ext) {
                $ext->status = 0;
                $ext->save();
                echo "Archiving week: " . $ext->date->format('d/m/Y') . "<br>";
                $log .= "Archiving week: " . $ext->date->format('d/m/Y') . "\n";
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Site Contract Extension Supervisor Task
     */
    static public function siteExtensionsSupervisorTask()
    {
        $log = '';
        $func_name = "Creating Site Extension Supervisor Task for Active Sites";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $extension = SiteExtension::where('status', 1)->first();

        $super_list = [];
        // Create List of Supervisors assigned to which Active Sites
        foreach ($extension->sites as $site_ext) {
            if (!$site_ext->reasons) {
                $super_id = $site_ext->site->supervisor_id;
                if (!isset($super_list[$super_id]))
                    $super_list[$super_id] = [$site_ext->site_id];
                else
                    $super_list[$super_id][] = $site_ext->site_id;
            }
        }

        foreach ($super_list as $super_id => $site_array) {
            $super = User::findOrFail($super_id);
            $site_list = '';
            $site_list2 = '';
            foreach ($site_array as $site_id) {
                $site = Site::findOrFail($site_id);
                $site_list .= "- $site->name\r\n";
                $site_list2 .= "$site->id, ";
            }

            // Create task for Supervisor
            $todo_request = [
                'type'       => 'extension',
                'type_id'    => $extension->id,
                'name'       => 'Contract Time Extensions',
                'info'       => "Please complete the Contract Time Extensions for the following sites:\r\n" . $site_list,
                'priority'   => '1',
                'due_at'     => Carbon::tomorrow()->format('Y-m-d') . ' 14:00:00',
                'company_id' => '3',
                'created_by' => '1',
                'updated_by' => '1'
            ];

            // Close any existing
            $todo = $site->supervisor->todoType('extension', 1)->first();
            if ($todo) $todo->close();

            // Create ToDoo and assign to Site Supervisors
            $todo = Todo::create($todo_request);
            $todo->assignUsers($super_id);
            $todo->emailToDo();

            echo "$super->name: $site_list2<br>";
            $log .= "$super->name: $site_list2\n";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Site Contract Extension Supervisor Task
     */
    static public function siteExtensionsSupervisorTaskReminder()
    {
        $log = '';
        $func_name = "Creating Site Extension Supervisor Task Reminder for Active Sites";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $extension = SiteExtension::where('status', 1)->first();

        $super_list = [];
        // Create List of Supervisors assigned to which Active Sites
        foreach ($extension->sites as $site_ext) {
            if (!$site_ext->reasons) {
                $super_id = $site_ext->site->supervisor_id;
                if (!isset($super_list[$super_id]))
                    $super_list[$super_id] = [$site_ext->site_id];
                else
                    $super_list[$super_id][] = $site_ext->site_id;
            }
        }

        foreach ($super_list as $super_id => $site_array) {
            $super = User::findOrFail($super_id);
            $site_list = '';
            foreach ($site_array as $site_id) {
                $site = Site::findOrFail($site_id);
                $site_list .= "- $site->name\n";
            }

            if ($site_ext->extension->sitesNotCompletedBySupervisor($super_id)->count()) {
                echo "- $super->fullname<br>";
                $log .= "- $super->fullname\n";

                // Send email to supervisor
                $email_list = (\App::environment('prod')) ? [$super->email] : [env('EMAIL_DEV')];
                $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
                if ($email_list && $email_cc) Mail::to($email_list)->cc($email_cc)->send(new \App\Mail\Site\SiteExtensionsReminder($extension, $site_list));
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Site Contract Extension Supervisor Task
     */
    static public function siteExtensionsSupervisorTaskFinalReminder()
    {
        $log = '';
        $func_name = "Sending Site Extension Con Manager Final Reminder";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $message = '';
        $site_list = '';
        $extension = SiteExtension::where('status', 1)->first();
        if (!$extension->approved_by) {
            foreach ($extension->sites as $site_ext) {
                if (!$site_ext->reasons)
                    $site_list .= "- " . $site_ext->site->name . " (" . $site_ext->site->supervisorName . ")\n";
                //$site_list .= "- " . $site_ext->site->name ."\n";
            }

            if ($site_list) {
                echo "<b>Outstanding sites</b><br>" . nl2br($site_list) . "<br>";
                $log .= "Outstanding sites\n$site_list\n";
                $message = "Please ensure all Contract Time Extensions are completed for week of " . $extension->date->format('d/m/Y') . " and Signed Off ASAP.<br><br>";
                $message .= "The following sites are yet to be completed:\n$site_list";
            } else {
                $message = "All Contract Time Extensions are completed for week of " . $extension->date->format('d/m/Y') . ", please Sign Off ASAP.<br>";
                echo "Con Manager Sign off still required<br>";
                $log .= "Con Manager Sign off still required\n";
            }

            // Send email
            $email_list = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
            $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
            if ($email_list && $email_cc) Mail::to($email_list)->cc($email_cc)->send(new \App\Mail\Site\SiteExtensionsFinalReminder($extension, $message));
        } else {
            echo "Already Signed off<br>";
            $log .= "Already Signed off\n";
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function superChecklists()
    {
        $log = '';
        $func_name = "Creating Supervisor Checklists for Active Supervisors";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $mon = new Carbon('monday this week');

        // Selected Supervisors
        $settings_supers = SuperChecklistSettings::where('field', 'supers')->where('status', 1)->first();
        $super_list = ($settings_supers) ? explode(',', $settings_supers->value) : [];
        $supers = User::find($super_list);

        foreach ($supers as $super) {
            if ($super->name == "TO BE ALLOCATED")
                continue;

            $mesg = "Existing";
            $checklist = SuperChecklist::where('super_id', $super->id)->whereDate('date', $mon->format('Y-m-d'))->first();
            if (!$checklist) {
                $checklist = SuperChecklist::create(['super_id' => $super->id, 'date' => $mon->toDateTimeString(), 'status' => 1]);
                $mesg = "Creating new";

                for ($day = 1; $day < 6; $day ++) {
                    foreach ($checklist->questions()->sortBy('id') as $question)
                        $response = SuperChecklistResponse::create(['checklist_id' => $checklist->id, 'day' => $day, 'question_id' => $question->id, 'status' => 1, 'created_by' => 1]);
                }
            }

            echo "$mesg week: " . $mon->format('d/m/Y') . " Super:$super->name<br>";
            $log .= "$mesg week: " . $mon->format('d/m/Y') . "Super:$super->name\n";

            // Create Todoo task for supervisor
            if (Carbon::today()->isWeekday()) {
                $checklist->closeToDo();
                //$checklist->createSupervisorToDo($super->id);
            }
        }


        // Archive old active checklists
        $old_checklists = SuperChecklist::where('status', 1)->whereDate('date', '<', $mon->format('Y-m-d'))->get();
        if ($old_checklists->count()) {
            foreach ($old_checklists as $checklist) {
                $checklist->status = 0;
                $checklist->save();
            }
            echo "Archiving week: " . $checklist->date->format('d/m/Y') . "<br>";
            $log .= "Archiving week: " . $checklist->date->format('d/m/Y') . "\n";
        }


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    static public function uploadCompanyDocReminder()
    {
        $log = '';
        $func_name = "Upload Company Docs Reminder";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $yesterday = Carbon::now()->subDays(1);
        $week_ago = Carbon::now()->subDays(7);

        $companies = Company::whereDate('created_at', '=', $yesterday)->get();
        foreach ($companies as $company) {
            if (!$company->isCompliant()) {
                echo "[$company->id] $company->name: ".$company->missingDocs('csv')."<br>";

                // Send email
                $primary_email = ($company->primary_user && validEmail($company->primary_contact()->email)) ? $company->primary_contact()->email : '';
                $email_to = (\App::environment('prod')) ? [$primary_email] : [env('EMAIL_DEV')];
                $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au', 'courtney@capecod.com.au'] : [env('EMAIL_DEV')];
                if ($email_to && $email_cc) {
                    Mail::to($email_to)->cc($email_cc)->send(new \App\Mail\Company\CompanyUploadDocsReminder($company));
                    $emails = implode("; ", array_merge($email_to, $email_cc));
                    echo "Sending email to $emails<br>";
                    $log .= "Sending email to $emails\n";
                }
            }
        }

        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

    /*
     * Verify Zoho Import
     */
    static public function verifyZohoImport()
    {
        $log = '';
        $func_name = "Verify Zoho Import";
        echo "<h2>$func_name</h2>";
        $log .= "$func_name\n";
        $log .= "------------------------------------------------------------------------\n\n";

        $yesterday = Carbon::now()->subDays(1)->format('Ymd');
        $successful = '';
        $logfile = public_path("filebank/log/zoho/$yesterday.txt");

        if (file_exists($logfile)) {
            $jobs_complete = strpos(file_get_contents($logfile), "ALL DONE - ZOHO IMPORT JOBS COMPLETE");
            $contacts_complete = strpos(file_get_contents($logfile), "ALL DONE - ZOHO IMPORT CONTACTS COMPLETE");

            if ($jobs_complete && $contacts_complete) {
                $log .= "Import successful\n";
            } else {
                $reason = '';
                if ($jobs_complete) {
                    $reason .= "Import of Contacts failed\n";
                } elseif ($contacts_complete) {
                    $reason .= "Import of Jobs failed\n";
                } else {
                    $reason .= "Import of Jobs + Contacts failed\n";
                }
                $log .= $reason;
                Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed($reason));
            }
        } else {
            $reason = "couldn't find logfile: $logfile";
            $log .= $reason;
            Mail::to(['support@openhands.com.au'])->send(new \App\Mail\Misc\ZohoImportFailed($reason));
        }


        echo "<h4>Completed</h4>";
        $log .= "\nCompleted\n\n\n";

        $bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        if ($bytes_written === false) die("Error writing to file");
    }

}