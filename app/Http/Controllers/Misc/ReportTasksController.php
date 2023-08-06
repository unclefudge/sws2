<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use File;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteExtension;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Safety\SafetyDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocReview;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;


class ReportTasksController extends Controller {

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
    * Active ToDoo Tasks
    */
    public function todoTasks($status = 1)
    {

        $cc = Company::find(3);
        $company_list = $cc->companies()->pluck('id')->toArray();
        $todo_tasks = Todo::where('status', $status)->whereIn('company_id', $company_list)->get();

        $tasks = [];
        $count = 0;
        foreach ($todo_tasks as $task) {
            $count ++;
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
                        $assigned_count ++;
                        $assigned_to[] = $user_name;
                        if ($assigned_count < 4) {
                            $assigned_names .= "$user_name, ";
                        } else
                            $assigned_names = "$assigned_count users";
                    }
                }
            }
            $assigned_names = rtrim($assigned_names, ', ');
            $assigned_names = ($assigned_names) ? $assigned_names : '-';

            // Group similar Todoo Types together
            $task_type = $task->type;
            if (strpos($task->type, 'incident') !== false) $task_type = 'incident';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';
            if (strpos($task->type, 'super checklist') !== false) $task_type = 'super checklist';
            if (strpos($task->type, 'inspection') !== false) $task_type = 'inspection';
            if (strpos($task->type, 'extension') !== false) $task_type = 'extension';

            //$site_tasks = ['incident', 'extension', 'hazard', 'maintenance', 'inspection', 'project supply', 'extension', 'equipment', 'scaffold handover', 'qa'];

            $info = '';
            $rec = $this->todoRecord($task, [0, 1]);
            if (!$rec) continue;

            $task_title = $task->name;
            $info .= $this->todoNotes($rec, $task);


            // Rename some Site Titles
            $titles2rename = ['hazard' => 'Site Hazard', 'incident' => 'Site Incident'];
            if (in_array($task->type, $titles2rename))
                $task_title = $titles2rename[$task->type] . " - " . $rec->site->name;

            $array = [
                'id'                => $task->id,
                'title'             => $task_title,
                'name'              => $task->name,
                'expand'            => 0,
                'info'              => $info,
                'type'              => $task_type,
                'due_at'            => ($task->due_at) ? $task->due_at->format('Y-m-d') : '',
                'lastupdated'       => $rec->updated_at->format('Y-m-d'),
                'lastupdated_human' => $rec->updated_at->diffForHumans(),
                //'done_at' => ($task_done_at) ? $task->done_at->format('d/m/Y') : '',
                //'done_by' => $task->done_by,
                'status'            => $task->status,
                'active'            => ($rec->status) ? 1 : 0,
                'company_id'        => $task->company_id,
                'created_by'        => $task->created_by,
                'created_at'        => $task->created_at->format('Y-m-d'),
                'assigned'          => count($assigned_to),
                'assigned_names'    => $assigned_names,
                'assigned_cc'       => $assigned_cc,
                'assigned_to'       => $assigned_to,
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
        $sel_assigned_cc[] = ['value' => 'all', 'text' => 'Both Cape Code + External Tasks'];

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
        //$sel_assigned_types[] = ['value' => 'supervisor', 'text' => 'Supervisor Checkin'];
        //$sel_assigned_types[] = ['value' => 'scaffold handover', 'text' => 'Scaffold Handover'];
        $sel_assigned_types[] = ['value' => 'project supply', 'text' => 'Project Supply Information'];
        $sel_assigned_types[] = ['value' => 'extension', 'text' => 'Contract Time Extensions'];
        $sel_assigned_types[] = ['value' => 'equipment', 'text' => 'Equipment Transfer'];
        $sel_assigned_types[] = ['value' => 'toolbox', 'text' => 'Toolbox Talks'];
        $sel_assigned_types[] = ['value' => 'swms', 'text' => 'Safe Work Method Statements'];
        $sel_assigned_types[] = ['value' => 'qa', 'text' => 'Quality Assurance'];
        $sel_assigned_types[] = ['value' => 'company doc', 'text' => 'Company Document'];
        $sel_assigned_types[] = ['value' => 'company doc review', 'text' => 'Standard Details Review'];
        $sel_assigned_types[] = ['value' => 'user doc', 'text' => 'User Documents'];


        $json = [];
        $json[] = $tasks;
        $json[] = $sel_assigned_tasks;
        $json[] = $sel_assigned_cc;
        $json[] = $sel_assigned_types;
        $json[] = $sel_active_record;

        return $json;
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
        if ($task_type == 'scaffold handover') null;
        if ($task_type == 'project supply') return SiteProjectSupply::where('id', $type_id)->whereIn('status', $status)->first();
        if (in_array($task_type, ['extension', 'extension signoff'])) return SiteExtension::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'equipment') return EquipmentLocation::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'qa') return SiteQa::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'toolbox') return ToolboxTalk::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'swms') return WmsDoc::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'company doc') return CompanyDoc::where('id', $type_id)->whereIn('status', $status)->first();
        if ($task_type == 'company doc review') return CompanyDocReview::where('id', $type_id)->whereIn('status', $status)->first();
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
                    $info .= $action->created_at->format('d/m/Y') . " - $action->action (".$action->user->name.")<br>";
            }
        }

        return $info;
    }
}
