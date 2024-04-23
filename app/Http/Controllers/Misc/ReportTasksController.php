<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocReview;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\User\UserDoc;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Support\Facades\Auth;
use Session;


class ReportTasksController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /****************************************************
     * Tasks
     ***************************************************/

    /*
     * Active ToDoo Tasks Report
     */
    public function todo()
    {
        $today = Carbon::now()->format('Y-m-d');
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();
        $companies = Auth::user()->company->companies();

        return view('manage/report/todo', compact('companies'));
    }

    /*
    * ToDoo Tasks Assigned to Inactive Users Report
    */
    public function todoInactive()
    {
        $today = Carbon::now()->format('Y-m-d');
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();
        $companies = Auth::user()->company->companies();

        return view('manage/report/todo-inactive', compact('companies'));
    }

    /*
    * Active ToDoo Tasks
    */
    public function todoTasks($status = 1)
    {

        $cc = Company::find(3);
        $company_list = $cc->companies()->pluck('id')->toArray();
        $todo_tasks = Todo::where('status', $status)->whereIn('company_id', $company_list)->get();

        $tasks = [];
        $companies_with_tasks = [];
        $users_with_tasks = [];
        $count = 0;
        foreach ($todo_tasks as $task) {
            $count++;
            //if ($count > 30000) continue;


            $assigned_to = [];
            $assigned_cc = 0;
            $assigned_names = '';
            $assigned_count = 0;
            $company_list = [3, 425]; // CC, ProlificProjects(Jason Habib)
            foreach ($task->assignedTo() as $user) {
                if ($user->id != 3) {  // excluding Fudge
                    if (in_array($user->company_id, $company_list)) {
                        $user_name = $user->name;
                        $assigned_cc = 1;
                    } else
                        $user_name = $user->company->name_alias;

                    if (!in_array($user_name, $assigned_to)) {
                        $assigned_count++;
                        $assigned_to[] = $user_name;
                        if ($assigned_count < 4) {
                            $assigned_names .= "$user_name, ";
                        } else
                            $assigned_names = "$assigned_count users";
                    }

                    // List of Companies with ToDoo tasks
                    if (!in_array($user->company_id, $companies_with_tasks))
                        $companies_with_tasks[] = $user->company_id;

                    // List of Users with ToDoo tasks
                    if (!in_array($user->id, $users_with_tasks))
                        $users_with_tasks[] = $user->id;
                }
            }
            $assigned_names = rtrim($assigned_names, ', ');
            $assigned_names = ($assigned_names) ? $assigned_names : '-';

            // Group similar Todoo Types together
            $task_type = $task->type;
            if (strpos($task->type, 'incident') !== false) $task_type = 'incident';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';
            if (strpos($task->type, 'super checklist') !== false) $task_type = 'super checklist';
            if (strpos($task->type, 'inspection_electrical') !== false) $task_type = 'inspection';
            if (strpos($task->type, 'inspection_plumbing') !== false) $task_type = 'inspection';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';

            //$site_tasks = ['incident', 'extension', 'hazard', 'maintenance', 'inspection', 'project supply', 'extension', 'equipment', 'scaffold handover', 'qa'];

            //echo "Task[$task->id] [$task->type] [$task->type_id]<br>";
            $info = '';
            $status_options = (in_array($task->type, ['inspection_electrical', 'inspection_plumbing'])) ? [0, 1, 2, 3] : [0, 1];
            $rec = $this->todoRecord($task, $status_options);
            if (!$rec) continue;

            if (in_array($task->id, ['44426', '44607', '44913']))
                ray($task->name);

            $info .= $this->todoNotes($rec, $task);

            // Rename some Site Titles
            $task_title = $task->name;
            $titles2rename = ['hazard' => 'Site Hazard', 'incident' => 'Site Incident'];
            if (in_array($task->type, $titles2rename))
                $task_title = $titles2rename[$task->type] . " - " . $rec->site->name;

            // Last Updated
            $lastupdated = $rec->updated_at->format('Y-m-d');
            $lastupdated_human = $rec->updated_at->diffForHumans();
            if (!in_array($task_type, ['incident', 'company doc review'])) {
                $last_action = ($rec->actions) ? $rec->actions->sortByDesc('created_at')->first() : '';
                if ($last_action && $last_action->created_at->gt($rec->updated_at)) {
                    $lastupdated = $last_action->updated_at->format('Y-m-d');
                    $lastupdated_human = $last_action->updated_at->diffForHumans();
                }
            }


            $array = [
                'id' => $task->id,
                'title' => $task_title,
                'name' => $task->name,
                'expand' => 0,
                'info' => $info,
                'type' => $task_type,
                'due_at' => ($task->due_at) ? $task->due_at->format('Y-m-d') : '',
                'lastupdated' => $lastupdated,
                'lastupdated_human' => $lastupdated_human,
                //'done_at' => ($task_done_at) ? $task->done_at->format('d/m/Y') : '',
                //'done_by' => $task->done_by,
                'status' => $task->status,
                'active' => ($rec->status) ? 1 : 0,
                'company_id' => $task->company_id,
                'created_by' => $task->created_by,
                'created_at' => $task->created_at->format('Y-m-d'),
                'assigned' => count($assigned_to),
                'assigned_names' => $assigned_names,
                'assigned_cc' => $assigned_cc,
                'assigned_to' => $assigned_to,
            ];
            //if (count($assigned_to) > 0)
            $tasks[] = $array;
        }

        // Assigned task
        $sel_assigned_tasks[] = ['value' => '1', 'text' => 'Assigned Tasks'];
        $sel_assigned_tasks[] = ['value' => '0', 'text' => 'Non-assigned Tasks'];
        $sel_assigned_tasks[] = ['value' => 'all', 'text' => 'All Tasks'];

        // Assigned Cape Code
        $sel_assigned_cc[] = ['value' => '1', 'text' => 'Cape Cod Tasks Only'];
        $sel_assigned_cc[] = ['value' => '0', 'text' => 'External Tasks Only'];
        $sel_assigned_cc[] = ['value' => 'all', 'text' => 'Cape Cod + External Tasks'];

        // Active Record
        $sel_active_record[] = ['value' => '1', 'text' => 'Active/Open Record'];
        $sel_active_record[] = ['value' => '0', 'text' => 'Complete/Closed Record'];

        //Assigned types
        $sel_assigned_types[] = ['value' => 'all', 'text' => 'All Task Types'];
        $sel_assigned_types[] = ['value' => 'incident', 'text' => 'Site Incidents'];
        $sel_assigned_types[] = ['value' => 'hazard', 'text' => 'Site Hazards'];
        $sel_assigned_types[] = ['value' => 'maintenance', 'text' => 'Site Maintenance'];
        $sel_assigned_types[] = ['value' => 'inspection', 'text' => 'Site Inspection (Plumbing/Electrical)'];
        $sel_assigned_types[] = ['value' => 'super', 'text' => 'Supervisor Checklist'];
        $sel_assigned_types[] = ['value' => 'supervisor', 'text' => 'Supervisor Checkin'];
        $sel_assigned_types[] = ['value' => 'scaffold handover', 'text' => 'Scaffold Handover'];
        $sel_assigned_types[] = ['value' => 'project supply', 'text' => 'Project Supply Information'];
        $sel_assigned_types[] = ['value' => 'qa', 'text' => 'Quality Assurance'];
        $sel_assigned_types[] = ['value' => 'extension', 'text' => 'Contract Time Extensions'];
        $sel_assigned_types[] = ['value' => 'equipment', 'text' => 'Equipment Transfer'];
        $sel_assigned_types[] = ['value' => 'toolbox', 'text' => 'Toolbox Talks'];
        $sel_assigned_types[] = ['value' => 'swms', 'text' => 'Safe Work Method Statements'];
        $sel_assigned_types[] = ['value' => 'company doc review', 'text' => 'Standard Details Review'];
        $sel_assigned_types[] = ['value' => 'company doc', 'text' => 'Company Document'];
        $sel_assigned_types[] = ['value' => 'user doc', 'text' => 'User Documents'];

        // User Lists
        $sel_user_cc = [];
        $sel_user_ext = [];
        $sel_user_all = [];
        $sel_user_cc[] = ['value' => 'all', 'text' => 'All Cape Cod Users'];
        $sel_user_ext[] = ['value' => 'all', 'text' => 'All External Companies'];
        $sel_user_all[] = ['value' => 'all', 'text' => 'All Users/Companies'];

        $users = $cc->users()->where('status', 1)->sortBy('name');
        foreach ($users as $user) {
            if (in_array($user->id, $users_with_tasks)) {
                if (in_array($user->company_id, $company_list)) {
                    $sel_user_cc[] = ['value' => $user->name, 'text' => $user->name];
                    $sel_user_all[] = ['value' => $user->name, 'text' => "-" . $user->name];
                }
            }
        }
        $companies = $cc->companies()->where('status', 1)->sortBy('name');
        foreach ($companies as $company) {
            if (in_array($company->id, $companies_with_tasks)) {
                if (!in_array($company->id, $company_list)) {
                    $sel_user_ext[] = ['value' => $company->name_alias, 'text' => $company->name_alias];
                    $sel_user_all[] = ['value' => $company->name_alias, 'text' => $company->name_alias];
                }
            }
        }


        //dd($tasks);
        $json = [];
        $json[] = $tasks;
        $json[] = $sel_assigned_tasks;
        $json[] = $sel_assigned_cc;
        $json[] = $sel_assigned_types;
        $json[] = $sel_active_record;
        $json[] = $sel_user_cc;
        $json[] = $sel_user_ext;
        $json[] = $sel_user_all;

        //dd($tasks);
        return $json;
    }

    /*
    * ToDoo Tasks assigned to Inactive users
    */
    public function todoTasksInactive()
    {

        $cc = Company::find(3);
        $company_list = $cc->companies()->pluck('id')->toArray();
        $ignoreTasks = ['toolbox', 'company doc', 'swms', 'user doc'];
        $todo_tasks = Todo::where('status', 1)->whereNotIn('type', $ignoreTasks)->whereIn('company_id', $company_list)->get();

        $tasks = [];
        $companies_with_tasks = [];
        $users_with_tasks = [];
        $count = 0;
        foreach ($todo_tasks as $task) {
            $count++;

            if ($task->assignedTo()->count() == 1) {
                if ($task->assignedTo()->first()->status == 1)
                    continue;
            } else {
                $found_active = false;
                foreach ($task->assignedTo() as $user) {
                    if ($user->status == 1)
                        $found_active = true;
                }
                if ($found_active)
                    continue;
            }

            $assigned_to = [];
            $assigned_cc = 0;
            $assigned_names = '';
            $assigned_count = 0;
            $company_list = [3, 425]; // CC, ProlificProjects(Jason Habib)
            foreach ($task->assignedTo() as $user) {
                if ($user->id != 3) {  // excluding Fudge
                    if (in_array($user->company_id, $company_list)) {
                        $user_name = $user->name;
                        $assigned_cc = 1;
                    } else
                        $user_name = $user->company->name_alias;

                    if (!in_array($user_name, $assigned_to)) {
                        $assigned_count++;
                        $assigned_to[] = $user_name;
                        if ($assigned_count < 4) {
                            $assigned_names .= "$user_name, ";
                        } else
                            $assigned_names = "$assigned_count users";
                    }

                    // List of Companies with ToDoo tasks
                    if (!in_array($user->company_id, $companies_with_tasks))
                        $companies_with_tasks[] = $user->company_id;

                    // List of Users with ToDoo tasks
                    if (!in_array($user->id, $users_with_tasks))
                        $users_with_tasks[] = $user->id;
                }
            }
            $assigned_names = rtrim($assigned_names, ', ');
            $assigned_names = ($assigned_names) ? $assigned_names : '-';

            // Group similar Todoo Types together
            $task_type = $task->type;
            if (strpos($task->type, 'incident') !== false) $task_type = 'incident';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';
            if (strpos($task->type, 'super checklist') !== false) $task_type = 'super checklist';
            if (strpos($task->type, 'inspection_electrical') !== false) $task_type = 'inspection';
            if (strpos($task->type, 'inspection_plumbing') !== false) $task_type = 'inspection';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';

            //$site_tasks = ['incident', 'extension', 'hazard', 'maintenance', 'inspection', 'project supply', 'extension', 'equipment', 'scaffold handover', 'qa'];

            //echo "Task[$task->id] [$task->type] [$task->type_id]<br>";
            $info = '';
            $status_options = (in_array($task->type, ['inspection_electrical', 'inspection_plumbing'])) ? [0, 1, 2, 3] : [0, 1];
            $rec = $this->todoRecord($task, $status_options);
            if (!$rec) continue;

            if (in_array($task->id, ['44426', '44607', '44913']))
                ray($task->name);

            $info .= $this->todoNotes($rec, $task);

            // Rename some Site Titles
            $task_title = $task->name;
            $titles2rename = ['hazard' => 'Site Hazard', 'incident' => 'Site Incident'];
            if (in_array($task->type, $titles2rename))
                $task_title = $titles2rename[$task->type] . " - " . $rec->site->name;

            // Last Updated
            $lastupdated = $rec->updated_at->format('Y-m-d');
            $lastupdated_human = $rec->updated_at->diffForHumans();
            if (!in_array($task_type, ['incident', 'company doc review'])) {
                $last_action = ($rec->actions) ? $rec->actions->sortByDesc('created_at')->first() : '';
                if ($last_action && $last_action->created_at->gt($rec->updated_at)) {
                    $lastupdated = $last_action->updated_at->format('Y-m-d');
                    $lastupdated_human = $last_action->updated_at->diffForHumans();
                }
            }


            $array = [
                'id' => $task->id,
                'title' => $task_title,
                'name' => $task->name,
                'expand' => 0,
                'info' => $info,
                'type' => $task_type,
                'due_at' => ($task->due_at) ? $task->due_at->format('Y-m-d') : '',
                'lastupdated' => $lastupdated,
                'lastupdated_human' => $lastupdated_human,
                //'done_at' => ($task_done_at) ? $task->done_at->format('d/m/Y') : '',
                //'done_by' => $task->done_by,
                'status' => $task->status,
                'active' => ($rec->status) ? 1 : 0,
                'company_id' => $task->company_id,
                'created_by' => $task->created_by,
                'created_at' => $task->created_at->format('Y-m-d'),
                'assigned' => count($assigned_to),
                'assigned_names' => $assigned_names,
                'assigned_cc' => $assigned_cc,
                'assigned_to' => $assigned_to,
                'checked' => false,
            ];
            //if (count($assigned_to) > 0)
            $tasks[] = $array;
        }

        // Assigned task
        $sel_assigned_tasks[] = ['value' => '1', 'text' => 'Assigned Tasks'];
        $sel_assigned_tasks[] = ['value' => '0', 'text' => 'Non-assigned Tasks'];
        $sel_assigned_tasks[] = ['value' => 'all', 'text' => 'All Tasks'];

        // Assigned Cape Code
        $sel_assigned_cc[] = ['value' => '1', 'text' => 'Cape Cod Tasks Only'];
        $sel_assigned_cc[] = ['value' => '0', 'text' => 'External Tasks Only'];
        $sel_assigned_cc[] = ['value' => 'all', 'text' => 'Cape Cod + External Tasks'];

        // Action
        $sel_active_record[] = ['value' => '', 'text' => 'Select Action'];
        $sel_active_record[] = ['value' => 'reassign', 'text' => 'Reassign task(s)'];
        $sel_active_record[] = ['value' => 'delete', 'text' => 'Delete task(s)'];

        //Assigned types
        $sel_assigned_types[] = ['value' => 'all', 'text' => 'All Task Types'];
        $sel_assigned_types[] = ['value' => 'incident', 'text' => 'Site Incidents'];
        $sel_assigned_types[] = ['value' => 'hazard', 'text' => 'Site Hazards'];
        $sel_assigned_types[] = ['value' => 'maintenance', 'text' => 'Site Maintenance'];
        $sel_assigned_types[] = ['value' => 'inspection', 'text' => 'Site Inspection (Plumbing/Electrical)'];
        $sel_assigned_types[] = ['value' => 'super', 'text' => 'Supervisor Checklist'];
        $sel_assigned_types[] = ['value' => 'supervisor', 'text' => 'Supervisor Checkin'];
        $sel_assigned_types[] = ['value' => 'scaffold handover', 'text' => 'Scaffold Handover'];
        $sel_assigned_types[] = ['value' => 'project supply', 'text' => 'Project Supply Information'];
        $sel_assigned_types[] = ['value' => 'qa', 'text' => 'Quality Assurance'];
        $sel_assigned_types[] = ['value' => 'extension', 'text' => 'Contract Time Extensions'];
        $sel_assigned_types[] = ['value' => 'equipment', 'text' => 'Equipment Transfer'];
        $sel_assigned_types[] = ['value' => 'company doc review', 'text' => 'Standard Details Review'];

        // User Lists
        $sel_user_cc = [];
        $sel_user_ext = [];
        $sel_user_all = [];
        $sel_user_active = [];
        $sel_user_cc[] = ['value' => 'all', 'text' => 'All Cape Cod Users'];
        $sel_user_ext[] = ['value' => 'all', 'text' => 'All External Companies'];
        $sel_user_all[] = ['value' => 'all', 'text' => 'All Users/Companies'];

        $users = $cc->users()->where('status', 0)->sortBy('name');
        foreach ($users as $user) {
            if (in_array($user->id, $users_with_tasks)) {
                if (in_array($user->company_id, $company_list)) {
                    $sel_user_cc[] = ['value' => $user->name, 'text' => $user->name];
                    $sel_user_all[] = ['value' => $user->name, 'text' => "-" . $user->name];
                }
            }
        }
        $companies = $cc->companies()->sortBy('name');
        foreach ($companies as $company) {
            if (in_array($company->id, $companies_with_tasks)) {
                if (!in_array($company->id, $company_list)) {
                    $sel_user_ext[] = ['value' => $company->name_alias, 'text' => $company->name_alias];
                    $sel_user_all[] = ['value' => $company->name_alias, 'text' => $company->name_alias];
                }
            }
        }

        $users = $cc->users()->where('status', 1)->sortBy('name');
        foreach ($users as $user) {
            $sel_user_active[] = ['value' => $user->name, 'text' => $user->name . " (" . $user->company->name . ")"];
        }


        //dd($tasks);
        $json = [];
        $json[] = $tasks;
        $json[] = $sel_assigned_tasks;
        $json[] = $sel_assigned_cc;
        $json[] = $sel_assigned_types;
        $json[] = $sel_active_record;
        $json[] = $sel_user_cc;
        $json[] = $sel_user_ext;
        $json[] = $sel_user_all;
        $json[] = $sel_user_active;

        //dd($tasks);
        return $json;
    }

    public function todoTasksDelete()
    {
        if (request()->ajax()) {
            $todos = Todo::whereIn('id', request('deleteTasks'))->delete();
            return json_encode('success');
        }
    }


    public function todoRecord($task, $status = [1])
    {
        $task_type = $task->type;
        $type_id = $task->type_id;
        if ($task_type == 'hazard') return SiteHazard::where('id', $type_id)->whereIn('status', $status)->first();
        if (in_array($task_type, ['incident', 'incident prevent', 'incident review'])) return SiteIncident::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'accident') return null;
        if ($task_type == 'maintenance') return SiteMaintenance::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'inspection_electrical') return SiteInspectionElectrical::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'inspection_plumbing') return SiteInspectionPlumbing::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'super checklist') return SuperChecklist::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'supervisor') return null;
        if ($task_type == 'scaffold handover') return SiteScaffoldHandover::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'project supply') return SiteProjectSupply::where('id', $type_id)->whereIn('status', $status)->first();
        if (in_array($task_type, ['extension', 'extension signoff'])) return SiteExtension::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'equipment') return EquipmentLocation::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'qa') return SiteQa::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'toolbox') return ToolboxTalk::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'swms') return WmsDoc::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'company doc') return CompanyDoc::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'company doc review') return CompanyDocReview::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'user doc') return UserDoc::where('id', $type_id)->whereIn('status', $status)->first();
    }

    public function todoNotes($record, $task)
    {
        $type = $task->type;
        $info = '';

        // Record ID + link
        $recID = ($record->code) ? "M" . $record->code : $record->id;
        $recID = "<b>Record ID:</b> $recID";
        $recID = ($task->url()) ? "$recID &nbsp; &nbsp; <a href='" . $task->url() . "' target='_blank'>[ view record ]</a>" : "$recID";

        $recStatus = 'Status';
        // Resolved Records
        $rec_list = ['hazard', 'incident'];
        if (in_array($type, $rec_list)) {
            $resAt = ($record->resolved_at) ? $record->resolved_at->format('d/m/Y') : $record->updated_at->format('d/m/Y');
            $recStatus = ($record->status) ? "<span class='font-green'>Open</span>" : "<span class='font-red'>Resolved:  $resAt</span>";
        }
        // Active/Completed Records
        $rec_list = ['inspection_electrical', 'inspection_plumbing', 'project supply', 'equipment', 'toolbox', 'swms', 'qa', 'company doc review'];
        if (in_array($type, $rec_list)) {
            $recStatus = ($record->status) ? "<span class='font-green'>Active</span>" : "<span class='font-red'>Completed:  " . $record->updated_at->format('d/m/Y') . "</span>";
        }
        // Active/Onhold/Completed Records
        $rec_list = ['maintenance'];
        if (in_array($type, $rec_list)) {
            $recStat = 'Active';
            if ($record->status == 3) $recStat = 'On Hold';
            $recStatus = ($record->status) ? "<span class='font-green'>$recStat</span>" : "<span class='font-red'>Completed:  " . $record->updated_at->format('d/m/Y') . "</span>";
        }

        // Record link + Status
        $info .= "<span>$recID</span><span class='pull-right'>$recStatus</span><br>";

        // Task Message
        $info .= "<b>Message:</b> " . $task->info . "</b><br>";

        // Project Supply
        if ($type == 'project supply') {
            if ($record->items->count() != $record->itemsCompleted()->count())
                $info .= "<br><span class='font-red'>Waiting on " . $record->items->count() - $record->itemsCompleted()->count() . " items to be completed</span><br>";
            elseif (!($record->supervisor_sign_by && $record->manager_sign_by))
                $info .= "<br><span class='font-red'>Waiting on sign off</span><br>";
        }

        // Equipment
        if ($type == 'equipment' && $task->location && count($task->location->items)) {
            $info .= "<br><br>List of equipment to tranfer:<br><ul>";
            foreach ($task->location->items as $item)
                $info .= "<li>($item->qty) $item->item_name</li>";
            $info .= "</ul><br>";
        }

        // QA
        if ($type == 'qa') {
            $total = $record->items()->count();
            $completed = $record->itemsCompleted()->count();
            if ($record->status != 0) {
                if ($total == $completed && $total != 0) {
                    $label_type = ($record->supervisor_sign_by && $record->manager_sign_by) ? 'label-success' : 'label-warning';
                    if (!$record->supervisor_sign_by)
                        $info .= "<br>($completed/$total) All items completed - <span class='font-red'>Pending Supervisor signoff</span><br>";
                    elseif (!$record->manager_sign_by)
                        $info .= "<br>($completed/$total) All items completed - <span class='font-red'>Pending Manager signoff</span><br>";
                } else
                    $info .= "<br><span class='font-red'>($completed/$total) Outstanding items</span><br>";
            } else
                $info .= "($completed/$total) All items completed and Signed Off<br>";
        }

        // Notes
        if (!in_array($type, ['incident', 'company doc review'])) {
            $actions = ($record->actions) ? $record->actions->count() : 0;
            if ($actions) {
                $info .= "<br><b>Notes:</b><br>";
                foreach ($record->actions->sortByDesc('created_at') as $action)
                    $info .= $action->created_at->format('d/m/Y') . " - $action->action (" . $action->user->name . ")<br>";
            }
        }

        return $info;
    }
}
