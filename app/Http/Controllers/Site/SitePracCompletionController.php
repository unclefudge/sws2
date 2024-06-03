<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SitePracCompletionItem;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteMaintenanceController
 * @package App\Http\Controllers\Site
 */
class SitePracCompletionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('prac.completion'))
            return view('errors/404');

        $requests = Auth::user()->maintenanceRequests(2);
        $request_ids = ($requests) ? $requests->pluck('id')->toArray() : [];

        $progress = SitePracCompletion::where('status', 2)->get();

        $pracs = SitePracCompletion::where('status', 1)->orderBy('created_at')->get();
        $assignedList = ['all' => 'All companies', '' => 'Not assigned'];
        foreach ($pracs as $prac) {
            foreach ($prac->items as $item) {
                if (!isset($assignedList[$item->assigned_to]))
                    $assignedList[$item->assigned_to] = $item->assigned->name;
            }
        }

        return view('site/prac-completion/list', compact('progress', 'assignedList'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prac = SitePracCompletion::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.prac.completion', $prac))
            return view('errors/404');

        return view('site/prac-completion/show', compact('prac'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $prac = SitePracCompletion::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.prac.completion', $prac))
            return view('errors/404');

        return view('site/prac-completion/show', compact('prac'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.prac.completion'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'item1' => 'required'];
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'item1.required' => 'The item field is required.'];
        request()->validate($rules, $mesg); // Validate

        $site_id = request('site_id');
        $prac_request = request()->all();
        $prac_request['status'] = 2; // set new request to In Progress - Assign Supervisor
        //dd($prac_request);

        // Create Prac Completion
        $prac = SitePracCompletion::create($prac_request);
        $action = Action::create(['action' => "Prac Completion created", 'table' => 'prac_completion', 'table_id' => $prac->id]);

        // Add Request Items
        for ($i = 1; $i < 25; $i++) {
            if (request("item$i")) {
                SitePracCompletionItem::create(['prac_id' => $prac->id, 'name' => request("item$i"), 'order' => $i, 'status' => 0]);
            }
        }

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_prac_completion', 'table_id' => $prac->id, 'directory' => "/filebank/site/$prac->site_id/prac"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        // Create ToDoo to assign Supervisor
        $prac->createAssignSupervisorToDo([108]); // Kirstie

        Toastr::success("Created Prac Completion");

        return redirect("/site/prac-completion/$prac->id/edit");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.prac.completion'))
            return view('errors/404');

        return view('site/prac-completion/create');
    }

    /**
     * Update the specified resource in storage.
     */
    public function review($id)
    {


        // Supervisor Assigned
        if (Auth::user()->allowed2('sig.prac.completion', $prac)) {
            $super = User::find(request('super_id'));
            $prac_request['step'] = 0;
            $prac_request['status'] = 1; // Set status to active
            $prac_request['assigned_super_at'] = Carbon::now()->toDateTimeString(); // Set Assigned Super date
            $action = Action::create(['action' => "$super->name assigned to supervise request ", 'table' => 'prac_completion', 'table_id' => $prac->id]);
            Toastr::success("Assigned Request");

            $prac->closeToDo();   // Delete Construction Mgr Todoo
            $prac->createSupervisorAssignedToDo([$super->id]); // Create ToDoo for supervisor

            // Update Site with new Maintenance Supervisor
            $prac->site->supervisor_id = request('super_id');
            $prac->site->save();

            // Add to Client Visit planner
            /*
            $newPlanner = SitePlanner::create(array(
                'site_id'     => $prac->site_id,
                'from'        => $visit_date->format('Y-m-d') . ' 00:00:00',
                'to'          => $visit_date->format('Y-m-d') . ' 00:00:00',
                'days'        => 1,
                'entity_type' => 'c',
                'entity_id'   => request('company_id'),
                'task_id'     => '524' // Client Visit
            ));*/

        }


        // Email Assigned Supervisor
        if (Auth::user()->allowed2('sig.prac.completion', $prac)) {
            $prac->emailAssigned($super);
        }

        return (request('status') == 2) ? redirect('site/prac-completion/' . $prac->id . '/edit') : redirect('site/prac-completion/' . $prac->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $prac = PracCompletion::findOrFail($id);
        $super_id_orig = $prac->super_id;
        $status_orig = $prac->status;

        //dd(request()->all());

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.prac.completion', $prac))
            return view('errors/404');

        $rules = ['supervisor' => 'required', 'completed' => 'required', 'onhold_reason' => 'required_if:status,4'];
        $mesg = ['supervisor.required' => 'The supervisor field is required.', 'completed.required' => 'The prac completed field is required.',
            'onhold_reason.required_if' => 'A reason is required to place request On Hold.'];
        request()->validate($rules, $mesg); // Validate

        $prac_request = request()->all();
        //dd($prac_request);

        $prac->update($prac_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename)
                $prac->saveAttachment($tmp_filename);
        }

        // Email if Super Assigned is updated
        if (request('super_id') && request('super_id') != $super_id_orig) {
            $super = User::find($prac_request['super_id']);
            $prac->emailAssigned($super);
            $action = Action::create(['action' => "Maintenance Supervisor updated to $super->name", 'table' => 'prac_completion', 'table_id' => $prac->id]);

            $prac->closeToDo();   // Delete Construction Mgr Todoo

            // Set Assigned to Super date field if not set
            if (!$prac->assigned_super_at)
                $prac->assigned_super_at = Carbon::now()->toDateTimeString();

            $prac->createSupervisorAssignedToDo([$super->id]); // Create ToDoo for new supervisor
            //$prac->site->supervisor_id = request('super_id'); // Update Site supervisor
            //$prac->site->save();
        }

        // Add note if change of Status
        if (request('status') && $status_orig != 4 && request('status') == 4) {
            $action = Action::create(['action' => "Request has been placed On Hold for the following reason: \n" . request('onhold_reason'), 'table' => 'prac_completion', 'table_id' => $prac->id]);
            $prac->closeToDo();
        }
        if (request('status') && $status_orig != 1 && request('status') == 1) {
            $action = Action::create(['action' => "Request has been Re-Activated", 'table' => 'prac_completion', 'table_id' => $prac->id]);
            $prac->supervisor_sign_by = null;
            $prac->supervisor_sign_at = null;
            $prac->manager_sign_by = null;
            $prac->manager_sign_at = null;
        }
        if (request('status') && $status_orig != '-1' && request('status') == '-1') {
            $action = Action::create(['action' => "Request has been Declined", 'table' => 'prac_completion', 'table_id' => $prac->id]);
            $prac->closeToDo();
        }

        // Add note if change of Category
        if (request('category_id') && request('category_id') != $prac->category_id) {
            $from = SiteMaintenanceCategory::find($prac->category_id)->name;
            $to = SiteMaintenanceCategory::find(request('category_id'))->name;
            $action = Action::create(['action' => "Request category updated from $from to $to", 'table' => 'prac_completion', 'table_id' => $prac->id]);
        }

        $prac->save();
        Toastr::success("Updated Request");

        return redirect('site/prac-completion/' . $prac->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport(Request $request, $id)
    {
        $prac = SitePracCompletion::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.prac.completion', $prac))
            return view('errors/404');

        // Only Allow Ajax requests
        if ($request->ajax()) {
            $prac_request = $request->all();

            // Determine if report being signed off
            $signoff = $request->get('signoff');
            if ($signoff == 'super') {
                $prac_request['supervisor_sign_by'] = Auth::user()->id;
                $prac_request['supervisor_sign_at'] = Carbon::now();

                // Close any outstanding ToDos for supervisors and Create one for Area Super / Con Mgr
                $prac->closeToDo();
                if (!$prac->manager_sign_by) {
                    $site = Site::findOrFail($prac->site_id);
                    $prac->createManagerSignOffToDo(array_merge(getUserIdsWithRoles('con-construction-manager'), [108]));
                }
                $action = Action::create(['action' => "Request has been signed off by Supervisor", 'table' => 'prac_completion', 'table_id' => $prac->id]);
            }
            if ($signoff == 'manager') {
                $prac_request['manager_sign_by'] = Auth::user()->id;
                $prac_request['manager_sign_at'] = Carbon::now();
                // Close any outstanding ToDos for Area Super / Con Mgr
                $prac->closeToDo();

                // Update Site Status back to completed
                $prac->site->status = 0;
                $prac->site->save();

                $action = Action::create(['action' => "Request has been signed off by Construction Manager", 'table' => 'prac_completion', 'table_id' => $prac->id]);

                $email_list = [env('EMAIL_DEV')];
                if (\App::environment('prod'))
                    $email_list = $prac->site->company->notificationsUsersEmailType('prac.completion.completed');

                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceCompleted($prac));
            }

            //dd($prac_request);

            $prac->update($prac_request);

            // Determine if Report Signed Off and if so mark completed
            if ($prac->supervisor_sign_by && $prac->manager_sign_by) {
                $prac->status = 0;
                if (!$prac->ac_form_required)
                    $prac->ac_form_sent = "0001-01-01 01:01:01";
                $prac->save();
            }


            Toastr::success("Updated Report");

            return $prac;
        }

        return view('errors/404');
    }


    /**
     * Add Item the specified resource in storage.
     *
     */
    public function addItem($id)
    {
        $prac = SitePracCompletion::findOrFail($id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.prac.completion', $prac) || Auth::user()->id == $prac->super_id))
            return view('errors/404');

        //dd(request()->all());
        $item = SitePracCompletionItem::create(['prac_id' => $prac->id, 'name' => request('name'), 'order' => request('order'), 'status' => 0]);

        // Assign ToDoo to Supervisor for item
        if ($prac->super_id)
            $item->createAssignSupervisorToDo($prac->super_id);

        // Update modified timestamp
        $prac->touch();

        Toastr::success("Added item");

        return $item;
    }

    public function delItem($id)
    {
        $item = SitePracCompletionItem::findOrFail($id);
        $prac = SitePracCompletion::findOrFail($item->prac_id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('del.prac.completion', $item->maintenance)))
            return view('errors/404');

        //dd(request()->all());

        // Delete planner task if present
        if ($item->planner_id)
            $delTask = SitePlanner::where('id', $item->planner_id)->delete();

        $item->closeToDo();
        $item->delete(); // Delete item

        // Reorder items
        $order = 1;
        foreach ($prac->items->sortBy('order') as $item) {
            $item->order = $order++;
            $item->save();
        }

        // Update modified timestamp
        $prac->touch();

        Toastr::success("Deleted item");

        return redirect('site/prac-completion/' . $prac->id);
    }

    /**
     * Update Item the specified resource in storage.
     *
     */
    public function updateItem(Request $request, $id)
    {
        $item = SitePracCompletionItem::findOrFail($id);
        $prac = SitePracCompletion::findOrFail($item->prac_id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.prac.completion', $prac) || Auth::user()->id == $prac->super_id))
            return view('errors/404');

        $item_request = $request->only(['assigned_to', 'status']);
        //dd($item_request);

        $assigned_to_orig = $item->assigned_to;
        $status_orig = $item->status;

        // Update Planer Task
        $planner_id = request('planner_id');
        $planner_id_orig = $item->planner_id;
        $planner_task_id = request('planner_task_id');
        $planner_date = (request('planner_date')) ? Carbon::createFromFormat('d/m/Y H:i', request('planner_date') . '00:00')->toDateTimeString() : null;
        if ($planner_task_id) {
            if ($planner_id_orig && $planner_id_orig != $planner_task_id)
                $delTask = SitePlanner::where('id', $planner_id_orig)->delete();  // Delete old planner task

            // Create new
            $planner = SitePlanner::create(['site_id' => $prac->site_id, 'from' => $planner_date, 'to' => $planner_date, 'days' => 1, 'entity_type' => 'c', 'entity_id' => request('assigned_to'), 'task_id' => $planner_task_id]);
            if ($planner) {
                $item->planner_id = $planner->id;
                $item->save();
            }
        } elseif ($planner_id_orig)
            $delTask = SitePlanner::where('id', $planner_id_orig)->delete();  // Delete old planner task

        // Update resolve date if just modified
        if (request('status') != $status_orig) {
            if (!request('status')) {
                $item->status = 0;
                $item->done_by = null;
                $item->done_at = null;
                $item->sign_by = null;
                $item->sign_at = null;
                $item->save();
                $action = Action::create(['action' => "Maintenance Item has been mark as NOT completed", 'table' => 'prac_completion', 'table_id' => $prac->id]);
            } else {
                // Item completed
                if ($item_request['status'] == 1 && $item->status != 1) {
                    $item_request['done_by'] = Auth::user()->id;
                    $item_request['done_at'] = Carbon::now()->toDateTimeString();
                    $item_request['sign_by'] = Auth::user()->id;
                    $item_request['sign_at'] = Carbon::now()->toDateTimeString();
                    $action = Action::create(['action' => "Maintenance Item has been completed", 'table' => 'prac_completion', 'table_id' => $prac->id]);
                }
                //dd($item_request);
            }
        }
        $item->update($item_request);

        // Email if Company Assigned is updated
        if (request('assigned_to') && request('assigned_to') != $assigned_to_orig) {
            $company = Company::find(request('assigned_to'));
            if ($company && $company->primary_contact())
                $item->emailAssigned($company->primary_contact());
            $action = Action::create(['action' => "Company assigned to request updated to $company->name", 'table' => 'prac_completion', 'table_id' => $prac->id]);

            // Set Assigned to date field if not set
            if (!$prac->assigned_at) {
                $prac->assigned_at = Carbon::now()->toDateTimeString();
                $prac->save();
            }

            $prac->closeToDo();
            $item->closeToDo();
        }

        // Update modified timestamp on QA Doc
        $prac = SitePracCompletion::findOrFail($item->prac_id);
        $prac->touch();

        Toastr::success("Updated record");

        return $item;
    }

    /**
     * Get Prac Completion date.
     */
    public function getPracCompletion()
    {
        $completed = SitePlanner::where('site_id', request('site_id'))->where('task_id', 265)->get()->last();
        if ($completed) {
            return $completed->to; //->format('d/m/Y');
        }

        return '';
    }

    /**
     * Get Site Supervisor.
     */
    public function getSiteSupervisor()
    {
        $site = Site::find(request('site_id'));
        $supers = [$site->supervisorName];

        return ($site) ? $supers : '';
    }


    /**
     * Get Pracs current user is authorised to manage + Process datatables ajax request.
     */
    public function getPrac()
    {
        if (request('supervisor_sel'))
            $request_ids = (request('supervisor') == 'all') ? SitePracCompletion::all()->pluck('id')->toArray() : SitePracCompletion::where('super_id', request('supervisor'))->pluck('id')->toArray();
        else {
            $requests = Auth::user()->pracCompletion(request('status'));
            $request_ids = ($requests) ? Auth::user()->pracCompletion(request('status'))->pluck('id')->toArray() : [];
        }

        if (request('assigned_to') != 'all')
            $request_ids = SitePracCompletionItem::whereIn('prac_id', $request_ids)->where('assigned_to', request('assigned_to'))->pluck('prac_id')->toArray();

        $records = DB::table('site_prac_completion AS m')
            ->select(['m.id', 'm.site_id', 'm.super_id', 'm.status', 'm.updated_at', 'm.created_at',
                DB::raw('DATE_FORMAT(m.updated_at, "%d/%m/%y") AS updated_date'),
                DB::raw('DATE_FORMAT(m.client_appointment, "%d/%m/%y") AS appointment_date'),
                DB::raw('DATE_FORMAT(m.client_contacted, "%d/%m/%y") AS contacted_date'),
                's.code as sitecode', 's.name as sitename'])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids)
            ->where('m.status', request('status'));

        //dd($records);
        $dt = Datatables::of($records)
            //->editColumn('id', '<div class="text-center"><a href="/site/prac-completion/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('id', function ($rec) {
                return "<div class='text-center'><a href='/site/prac-completion/$rec->id'>$rec->id</a></div>";
            })
            ->editColumn('site_id', function ($rec) {
                return $red->sitecode;
            })
            ->editColumn('super_id', function ($rec) {
                $d = SitePracCompletion::find($rec->id);

                return ($d->super_id) ? $d->supervisor->initials : '-';
            })
            ->editColumn('assigned_to', function ($rec) {
                $d = SitePracCompletion::find($rec->id);

                return $d->assignedToNames();
            })
            ->addColumn('last_updated', function ($rec) {
                $prac = SitePracCompletion::find($rec->id);
                $total = $prac->items()->count();
                $completed = $prac->itemsCompleted()->count();
                $pending = '';
                if ($total == $completed && $total != 0) {
                    if (!$prac->supervisor_sign_by)
                        $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                    elseif (!$prac->manager_sign_by)
                        $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                }

                return ($prac->lastAction()) ? $prac->lastAction()->updated_at->format('d/m/Y') . $pending : $prac->created_at->format('d/m/Y') . $pending;
            })
            ->addColumn('action', function ($rec) {
                $prac = SitePracCompletion::find($rec->id);
                if (($rec->status && Auth::user()->allowed2('edit.prac.completion', $prac)) || (!$rec->status && Auth::user()->allowed2('sig.prac.completion', $prac)))
                    return '<a href="/site/prac-completion/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/site/prac-completion/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action', 'last_updated'])
            ->make(true);

        return $dt;
    }

    /**
     * Display the specified resource.
     */
    public function exportQA()
    {
        return view('site/export/qa');
    }


    public function getItems(Request $request, $id)
    {
        //if ($request->ajax()) {

        $prac = SitePracCompletion::findOrFail($id);

        $items = [];
        $users = [];
        $companies = [];
        foreach ($prac->items as $item) {
            $taskid = $taskname = $taskdate = '';
            if ($item->planner_id) {
                $plan = SitePlanner::find($item->planner_id);
                if ($plan) {
                    $taskid = $plan->task_id;
                    $taskname = ($plan->task) ? $plan->task->name : '';
                    $taskdate = ($plan->from) ? $plan->from->format('d/m/Y') : '';
                }
            }
            $array = [];
            $array['id'] = $item->id;
            $array['assigned_to'] = (string)$item->assigned_to;
            $array['assigned_to_name'] = ($item->assigned_to) ? $item->assigned->name : 'Unassigned';
            $array['planner_id'] = (string)$item->planner_id;
            $array['planner_task'] = $taskname;
            $array['planner_task_id'] = $taskid;
            $array['planner_date'] = $taskdate;
            $array['order'] = $item->order;
            $array['name'] = $item->name;
            $array['super'] = $item->super;

            // Task Info
            //$array['task_id'] = $item->task_id;
            //$task = Task::find($item->task_id);
            //$array['task_name'] = $task->name;
            //$array['task_code'] = $task->code;


            // Done By
            $array['done_at'] = '';
            $array['done_by'] = '';
            $array['done_by_name'] = '';
            $array['done_by_company'] = '';
            $array['done_by_licence'] = '';
            if ($item->done_by) {
                // User Info - Array of unique users (store previous users to speed up)
                if (isset($users[$item->done_by])) {
                    $user_rec = $users[$item->done_by];
                } else {
                    $user = User::find($item->done_by);
                    $users[$item->done_by] = (object)['id' => $user->id, 'full_name' => $user->full_name, 'company_name' => $user->company->name_alias];
                    $user_rec = $users[$item->done_by];
                }

                $array['done_at'] = $item->done_at->format('Y-m-d');
                $array['done_by'] = $user_rec->id;
                $array['done_by_name'] = $user_rec->full_name;
                $array['done_by_company'] = $user_rec->company_name;
            }

            // Signed By
            $array['sign_at'] = '';
            $array['sign_by'] = '';
            $array['sign_by_name'] = '';
            if ($item->sign_by) {
                // User Info - Array of unique users (store previous users to speed up)
                if (isset($users[$item->sign_by])) {
                    $user = $users[$item->sign_by];
                } else {
                    $user = User::find($item->sign_by);
                    $users[$item->sign_by] = (object)['id' => $user->id, 'full_name' => $user->full_name];
                }

                $array['sign_at'] = $item->sign_at->format('Y-m-d');
                $array['sign_by'] = $user->id;
                $array['sign_by_name'] = $user->full_name;
            }


            $array['status'] = $item->status;
            $items[] = $array;
        };


        $actions = [];
        $actions[] = ['value' => '0', 'text' => 'Incomplete'];
        $actions[] = ['value' => '1', 'text' => 'Completed'];
        //$actions[] = ['value' => '-1', 'text' => 'Mark N/A'];
        $actions2[] = ['value' => '', 'text' => 'Select Action'];
        $actions2[] = ['value' => '0', 'text' => 'Incomplete'];
        $actions2[] = ['value' => '1', 'text' => 'Sign Off'];

        // Companies
        $company_list = Auth::user()->company->reportsTo()->companies('1')->sortBy('name')->pluck('name', 'id')->toArray();
        $company_list = ['' => 'Select company'] + $company_list;

        $sel_company = [];
        foreach ($company_list as $cid => $name) {
            $sel_company[] = ['value' => "$cid", 'text' => $name];
        }

        // Company tasks
        $sel_task = [];
        $sel_task[] = ['value' => '', 'text' => 'Select task'];

        /*
        if ($prac->assigned_to) {
            // Create array in specific Vuejs 'select' format.
            //echo "As:$prac->assigned_to<br>";
            //dd($prac->assignedTo->tradesSkilledIn);
            $trade_count = count($prac->assignedTo->tradesSkilledIn);
            foreach ($prac->assignedTo->tradesSkilledIn as $trade) {
                $tasks = Task::where('trade_id', '=', $trade->id)->orderBy('name')->get();
                foreach ($tasks as $task) {
                    if ($task->status) {
                        $text = $task->name;

                        if ($trade_count > 1)
                            $text = $trade->name . ':' . $task->name;

                        $sel_task[] = [
                            'value' => $task->id,
                            'text' => $text,
                            'name' => $task->name,
                            'code' => $task->code,
                            'trade_id' => $trade->id,
                            'trade_name' => $trade->name,
                        ];
                    }
                }
            }
        }*/


        $json = [];
        $json[] = $items;
        $json[] = $actions;
        $json[] = $actions2;
        $json[] = $sel_company;
        $json[] = $sel_task;

        return $json;
        //}
    }
}
