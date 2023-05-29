<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use App\Models\Site\SiteMaintenanceDoc;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Misc\Action;
use App\Models\Company\Company;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Misc\Role2;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteMaintenanceController
 * @package App\Http\Controllers\Site
 */
class SiteMaintenanceController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.maintenance'))
            return view('errors/404');

        $requests = Auth::user()->maintenanceRequests(2);
        $request_ids = ($requests) ? $requests->pluck('id')->toArray() : [];

        $under_review = DB::table('site_maintenance AS m')
            ->select(['m.id', 'm.site_id', 'm.code', 'm.supervisor', 'm.completed', 'm.reported', 'm.warranty', 'm.goodwill', 'm.category_id', 'm.status', 'm.updated_at', 'm.created_at',
                DB::raw('DATE_FORMAT(m.created_at, "%d/%m/%y") AS created_date'),
                DB::raw('DATE_FORMAT(m.completed, "%d/%m/%y") AS completed_date'),
                DB::raw('DATE_FORMAT(m.updated_at, "%d/%m/%y") AS updated_date'),
                's.code as sitecode', 's.name as sitename'])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids)
            ->where('m.status', 2)->get();

        return view('site/maintenance/list', compact('under_review'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        return view('site/maintenance/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $main = SiteMaintenance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.maintenance', $main))
            return view('errors/404');

        if ($main->step == 2)
            return view('site/maintenance/photos', compact('main'));
        elseif ($main->step == 3)
            return view('site/maintenance/review', compact('main'));
        else
            return view('site/maintenance/show', compact('main'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $main = SiteMaintenance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.maintenance', $main))
            return view('errors/404');

        if ($main->step == 2)
            return view('site/maintenance/photos', compact('main'));
        elseif ($main->step == 3)
            return view('site/maintenance/review', compact('main'));
        else
            return view('site/maintenance/show', compact('main'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'supervisor' => 'required', 'completed' => 'required', 'reported' => 'required', 'item1' => 'required'];
        $mesg = [
            'site_id.required'    => 'The site field is required.',
            'supervisor.required' => 'The supervisor field is required.',
            'completed.required'  => 'The prac completed field is required.',
            'reported.required'   => 'The reported field is required.',
            'item1.required'      => 'The item field is required.'];
        request()->validate($rules, $mesg); // Validate

        // Verify reported date
        if (request('reported')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('reported'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('reported'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['reported'] = Carbon::createFromFormat('d/m/Y H:i:s', request('reported') . ' 00:00:00');
                else
                    return back()->withErrors(['reported' => "Invalid reported date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['reported' => "Invalid reported date. Required format dd/mm/yyyy"]);
        }

        // Verify prac completed date
        if (request('completed')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('completed'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('completed'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['completed'] = Carbon::createFromFormat('d/m/Y H:i:s', request('completed') . ' 00:00:00');
                else
                    return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
        }

        // Verify AC Form send  date
        if (request('ac_form_sent')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('ac_form_sent'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('ac_form_sent'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['ac_form_sent'] = Carbon::createFromFormat('d/m/Y H:i:s', request('ac_form_sent') . ' 00:00:00');
                else
                    return back()->withErrors(['ac_form_sent' => "Invalid AC Form Sent date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['ac_form_sent' => "Invalid AC Form Sent date. Required format dd/mm/yyyy"]);
        }

        $site_id = request('site_id');
        $main_request = request()->except('multifile');
        $main_request['completed'] = (request('completed')) ? Carbon::createFromFormat('d/m/Y H:i', request('completed') . '00:00')->toDateTimeString() : null;
        $main_request['reported'] = (request('reported')) ? Carbon::createFromFormat('d/m/Y H:i', request('reported') . '00:00')->toDateTimeString() : null;
        $main_request['ac_form_sent'] = (request('ac_form_sent')) ? Carbon::createFromFormat('d/m/Y H:i', request('ac_form_sent') . '00:00')->toDateTimeString() : null;
        $main_request['status'] = 2; // set new request to 'Under Review'
        $main_request['step'] = 2; // set new request to step 2 'Add Photos'

        //dd($main_request);
        // Create Maintenance Request
        $newMain = SiteMaintenance::create($main_request);
        $newMain->code = $newMain->id + 1352; // Generate new incremental code with 1352 off set to maintain sequence
        $newMain->save();
        $action = Action::create(['action' => "Maintenance Request created", 'table' => 'site_maintenance', 'table_id' => $newMain->id]);

        // Add Request Items
        SiteMaintenanceItem::create(['main_id' => $newMain->id, 'name' => request("item1"), 'order' => 1, 'status' => 0]);
        /*$order = 1;
        for ($i = 1; $i <= 25; $i ++) {
            if (request("item$i")) {
                SiteMaintenanceItem::create(['main_id' => $newMain->id, 'name' => request("item$i"), 'order' => $order, 'status' => 0]);
                $order ++;
            }
        }*/
        //dd($main_request);

        // Update Site Status
        $site = Site::find($site_id);
        $site->status = 2;
        $site->save();

        Toastr::success("Created Maintenance Request");

        return redirect('/site/maintenance/' . $newMain->id . '/edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function photos($id)
    {
        $main = SiteMaintenance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        $main->step = 3;
        $main->save();

        // Create ToDoo for assignment to Supervisor
        $todo_request = [
            'type'       => 'maintenance',
            'type_id'    => $main->id,
            'name'       => 'Site Maintenance Client Request - ' . $main->site->name,
            'info'       => 'Please review request and assign to supervisor',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $main->site->owned_by->id,
        ];

        $user_list = DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray(); // Construction Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);

        Toastr::success("Updated Request");

        return redirect('site/maintenance/' . $main->id . '/edit');
    }


    /**
     * Update the specified resource in storage.
     */
    public function review($id)
    {
        $main = SiteMaintenance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main)))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'supervisor' => 'required', 'completed' => 'required', 'item1' => 'required'];
        $mesg = [
            'site_id.required'    => 'The site field is required.',
            'supervisor.required' => 'The supervisor field is required.',
            'completed.required'  => 'The prac completed field is required.',
            'item1.required'      => 'The item field is required.'];

        if (Auth::user()->allowed2('sig.site.maintenance', $main)) {
            $rules = $rules + ['super_id' => 'required'];
            $mesg = $mesg + ['super_id.required' => 'The assign to field is required'];
            //$visit_date = Carbon::createFromFormat('d/m/Y H:i:s', request('visit_date') . ' 00:00:00');
        }
        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        $main_request = request()->except('completed');

        // Verify reported date
        if (request('reported')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('reported'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('reported'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['reported'] = Carbon::createFromFormat('d/m/Y H:i:s', request('reported') . ' 00:00:00');
                else
                    return back()->withErrors(['reported' => "Invalid reported date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['reported' => "Invalid reported date. Required format dd/mm/yyyy"]);
        }

        // Verify prac completed date
        if (request('completed')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('completed'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('completed'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['completed'] = Carbon::createFromFormat('d/m/Y H:i:s', request('completed') . ' 00:00:00');
                else
                    return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
        }

        //dd($main_request);

        // Supervisor Assigned
        if (Auth::user()->allowed2('sig.site.maintenance', $main)) {
            $super = User::find(request('super_id'));
            $main_request['step'] = 4;
            $main_request['status'] = 1; // Set status to active
            $main_request['assigned_super_at'] = Carbon::now()->toDateTimeString(); // Set Assigned Super date
            $action = Action::create(['action' => "$super->name assigned to supervise request ", 'table' => 'site_maintenance', 'table_id' => $main->id]);
            Toastr::success("Assigned Request");

            $main->closeToDo();   // Delete Construction Mgr Todoo
            $main->createSupervisorAssignedToDo([$super->id]); // Create ToDoo for supervisor

            // Update Site with new Maintenance Supervisor
            $main->site->supervisors()->sync([request('super_id')]);

            // Add to Client Visit planner
            /*
            $newPlanner = SitePlanner::create(array(
                'site_id'     => $main->site_id,
                'from'        => $visit_date->format('Y-m-d') . ' 00:00:00',
                'to'          => $visit_date->format('Y-m-d') . ' 00:00:00',
                'days'        => 1,
                'entity_type' => 'c',
                'entity_id'   => request('company_id'),
                'task_id'     => '524' // Client Visit
            ));*/

        }

        // Update Items
        $item1 = $main->items->first();
        if ($item1->name != request("item1")) { // Items updated
            $action = Action::create(['action' => "Item details updated", 'table' => 'site_maintenance', 'table_id' => $main->id]);
            $item1->name = request("item1");
            $item1->save();
        }
        /*
        $order = 1;
        $current_items = $main->items->count();
        for ($i = 1; $i <= 25; $i ++) {
            $item = $main->item($i);
            if (request("item$i")) {
                if ($item) {
                    $item->name = request("item$i");
                    $item->order = $order;
                    $item->save();
                } else
                    SiteMaintenanceItem::create(['main_id' => $main->id, 'name' => request("item$i"), 'order' => $order, 'status' => 0]);
                $order ++;
            } elseif ($item)
                $item->delete();
        }

        if ($current_items != ($order - 1)) // Items updated
            $action = Action::create(['action' => "Items updated by " . Auth::user()->fullname, 'table' => 'site_maintenance', 'table_id' => $main->id]);
        */


        //dd($main_request);
        Toastr::success("Updated Request");
        $main->update($main_request);

        // Email Assigned Supervisor
        if (Auth::user()->allowed2('sig.site.maintenance', $main)) {
            $main->emailAssigned($super);
        }

        return (request('status') == 2) ? redirect('site/maintenance/' . $main->id . '/edit') : redirect('site/maintenance/' . $main->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $main = SiteMaintenance::findOrFail($id);
        $planner_id_orig = $main->planner_id;
        $super_id_orig = $main->super_id;
        $assigned_to_orig = $main->assigned_to;

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.maintenance', $main))
            return view('errors/404');

        $rules = ['supervisor' => 'required', 'completed' => 'required', 'onhold_reason' => 'required_if:status,3', 'planner_task_date' => 'required_with:planner_task_id'];
        $mesg = ['supervisor.required' => 'The supervisor field is required.', 'completed.required' => 'The prac completed field is required.',
                 'onhold_reason.required_if' => 'A reason is required to place request On Hold.', 'planner_task_date.required_with' => 'The task date field is required with the Planner task.'];
        request()->validate($rules, $mesg); // Validate

        $main_request = request()->all();
        //dd($main_request);

        // Verify prac completed date
        if (request('completed')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('completed'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('completed'));
                if (checkdate($mm, $dd, $yyyy))
                    $main_request['completed'] = Carbon::createFromFormat('d/m/Y H:i:s', request('completed') . ' 00:00:00');
                else
                    return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['completed' => "Invalid Prac Completed date. Required format dd/mm/yyyy"]);
        }
        // AC Form sent
        if (request('ac_form_sent') == 'N/A') {
            $main_request['ac_form_sent'] = "0001-01-01 01:01:01";
        } else
            $main_request['ac_form_sent'] = (request('ac_form_sent')) ? Carbon::createFromFormat('d/m/Y H:i', request('ac_form_sent') . '00:00')->toDateTimeString() : null;
        $main_request['client_contacted'] = (request('client_contacted')) ? Carbon::createFromFormat('d/m/Y H:i', request('client_contacted') . '00:00')->toDateTimeString() : null;
        $main_request['client_appointment'] = (request('client_appointment')) ? Carbon::createFromFormat('d/m/Y H:i', request('client_appointment') . '00:00')->toDateTimeString() : null;

        //dd($main_request);
        $main->update($main_request);

        // Update Planer Task
        $planner_id = request('planner_id');
        $planner_task_id = request('planner_task_id');
        $planner_task_date = (request('planner_task_date')) ? Carbon::createFromFormat('d/m/Y H:i', request('planner_task_date') . '00:00')->toDateTimeString() : null;
        if ($planner_task_id) {
           if ($planner_id_orig && $planner_id_orig != $planner_task_id)
                $delTask = SitePlanner::findOrFail($planner_id_orig)->delete();  // Delete old planner task

            // Create new
            $planner = SitePlanner::create(['site_id' => $main->site_id, 'from' => $planner_task_date, 'to' => $planner_task_date, 'days' => 1, 'entity_type' => 'c', 'entity_id' => $main->assigned_to, 'task_id' => $planner_task_id]);
            if ($planner) {
                $main->planner_id = $planner->id;
                $main->save();
            }
        }


        // Email if Super Assigned is updated
        if (request('super_id') && request('super_id') != $super_id_orig) {
            $super = User::find($main_request['super_id']);
            $main->emailAssigned($super);
            $action = Action::create(['action' => "Maintenance Supervisor updated to $super->name", 'table' => 'site_maintenance', 'table_id' => $main->id]);

            $main->closeToDo();   // Delete Construction Mgr Todoo

            // Set Assigned to Super date field if not set
            if (!$main->assigned_super_at)
                $main->assigned_super_at = Carbon::now()->toDateTimeString();

            //if (!$main->assigned_to) {
                $main->createSupervisorAssignedToDo([$super->id]); // Create ToDoo for new supervisor
            //}
            $main->site->supervisors()->sync([request('super_id')]); // Update Site supervisor
        }

        // Email if Company Assigned is updated
        if (request('assigned_to') && request('assigned_to') != $assigned_to_orig) {
            $company = Company::find($main_request['assigned_to']);
            if ($company && $company->primary_contact())
                $main->emailAssigned($company->primary_contact());
            $action = Action::create(['action' => "Company assigned to request updated to $company->name", 'table' => 'site_maintenance', 'table_id' => $main->id]);

            // Set Assigned to date field if not set
            if (!$main->assigned_at)
                $main->assigned_at = Carbon::now()->toDateTimeString();

            $main->closeToDo();
        }

        // Add note if change of Status
        if (request('status') && $main->status != 3 && request('status') == 3)
            $action = Action::create(['action' => "Request has been placed On Hold for the following reason: \n" . request('onhold_reason'), 'table' => 'site_maintenance', 'table_id' => $main->id]);
        if (request('status') && $main->status != 1 && request('status') == 1) {
            $action = Action::create(['action' => "Request has been Re-Activated", 'table' => 'site_maintenance', 'table_id' => $main->id]);
            $main->supervisor_sign_by = null;
            $main->supervisor_sign_at = null;
            $main->manager_sign_by = null;
            $main->manager_sign_at = null;
        }
        if (request('status') && $main->status != - 1 && request('status') == - 1)
            $action = Action::create(['action' => "Request has been Declined", 'table' => 'site_maintenance', 'table_id' => $main->id]);

        // Add note if change of Category
        if (request('category_id') && request('category_id') != $main->category_id) {
            $from = SiteMaintenanceCategory::find($main->category_id)->name;
            $to = SiteMaintenanceCategory::find(request('category_id'))->name;
            $action = Action::create(['action' => "Request category updated from $from to $to", 'table' => 'site_maintenance', 'table_id' => $main->id]);
        }

        $main->save();
        Toastr::success("Updated Request");

        return redirect('site/maintenance/' . $main->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport(Request $request, $id)
    {
        $main = SiteMaintenance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.maintenance', $main))
            return view('errors/404');

        // Only Allow Ajax requests
        if ($request->ajax()) {
            $main_request = $request->all();

            // Determine if report being signed off
            $signoff = $request->get('signoff');
            if ($signoff == 'super') {
                $main_request['supervisor_sign_by'] = Auth::user()->id;
                $main_request['supervisor_sign_at'] = Carbon::now();

                // Close any outstanding ToDos for supervisors and Create one for Area Super / Con Mgr
                $main->closeToDo();
                if (!$main->manager_sign_by) {
                    $site = Site::findOrFail($main->site_id);
                    $con_mgr = DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray(); // Construction Manager
                    $main->createManagerSignOffToDo($con_mgr);
                }
                $action = Action::create(['action' => "Request has been signed off by Supervisor", 'table' => 'site_maintenance', 'table_id' => $main->id]);
            }
            if ($signoff == 'manager') {
                $main_request['manager_sign_by'] = Auth::user()->id;
                $main_request['manager_sign_at'] = Carbon::now();
                // Close any outstanding ToDos for Area Super / Con Mgr
                $main->closeToDo();

                // Update Site Status back to completed
                $main->site->status = 0;
                $main->site->save();

                $action = Action::create(['action' => "Request has been signed off by Construction Manager", 'table' => 'site_maintenance', 'table_id' => $main->id]);

                $email_list = [env('EMAIL_DEV')];
                if (\App::environment('prod'))
                    $email_list = $main->site->company->notificationsUsersEmailType('site.maintenance.completed');

                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteMaintenanceCompleted($main));
            }

            //dd($main_request);

            $main->update($main_request);

            // Determine if Report Signed Off and if so mark completed
            if ($main->supervisor_sign_by && $main->manager_sign_by) {
                $main->status = 0;
                $main->save();
            }


            Toastr::success("Updated Report");

            return $main;
        }

        return view('errors/404');
    }

    /**
     * Update Item the specified resource in storage.
     *
     */
    public function updateItem(Request $request, $id)
    {
        $item = SiteMaintenanceItem::findOrFail($id);
        $main = SiteMaintenance::findOrFail($item->main_id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.maintenance', $main) || Auth::user()->id == $main->super_id))
            return view('errors/404');

        $item_request = $request->only(['status', 'done_by', 'sign_by']);
        //dd($item_request);

        // Update resolve date if just modified
        if (!request('status')) {
            $item->status = 0;
            $item->done_by = null;
            $item->done_at = null;
            $item->sign_by = null;
            $item->sign_at = null;
            $item->save();
            $action = Action::create(['action' => "Maintenance Item has been mark as NOT completed", 'table' => 'site_maintenance', 'table_id' => $main->id]);
        } else {
            // Item completed
            if ($item_request['status'] == 1 && $item->status != 1) {
                $item_request['done_by'] = Auth::user()->id;
                $item_request['done_at'] = Carbon::now()->toDateTimeString();
                $item_request['sign_by'] = Auth::user()->id;
                $item_request['sign_at'] = Carbon::now()->toDateTimeString();
                $action = Action::create(['action' => "Maintenance Item has been completed", 'table' => 'site_maintenance', 'table_id' => $main->id]);
            }
            /*
            // Item signed off
            if ($item_request['sign_by'] && !$item->sign_by) {
                $item_request['sign_by'] = Auth::user()->id;
                $item_request['sign_at'] = Carbon::now()->toDateTimeString();
            }
            // item marked incomplete
            if (!$item_request['sign_by'] && $item->sign_by) {
                $item_request['sign_by'] = null;
                $item_request['sign_at'] = null;
            }*/
            //dd($item_request);
            $item->update($item_request);
        }

        // Update modified timestamp on QA Doc
        $main = SiteMaintenance::findOrFail($item->main_id);
        $main->touch();

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
        $supers = [$site->supervisorsSBC()];

        return ($site) ? $supers : '';
    }

    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAttachment(Request $request)
    {
        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main)))
        //    return json_encode("failed");

        //dd('here');
        //dd(request()->all());
        // Handle file upload
        $files = $request->file('multifile');
        foreach ($files as $file) {
            $path = "filebank/site/" . $request->get('site_id') . '/maintenance';
            $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);

            $doc_request = $request->only('site_id');
            $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $doc_request['company_id'] = Auth::user()->company_id;
            $doc_request['type'] = (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'gif', 'png'])) ? 'photo' : 'doc';

            // Create SiteMaintenanceDoc
            $doc = SiteMaintenanceDoc::create($doc_request);
            $doc->main_id = $request->get('main_id');
            //
            $doc->attachment = $name;
            $doc->save();
        }


        return json_encode("success");
    }


    /**
     * Get QA Reports current user is authorised to manage + Process datatables ajax request.
     */
    public function getMaintenance()
    {
        if (request('supervisor_sel'))
            $request_ids = (request('supervisor') == 'all') ? SiteMaintenance::all()->pluck('id')->toArray() : SiteMaintenance::where('super_id', request('supervisor'))->pluck('id')->toArray();
        else {
            $requests = Auth::user()->maintenanceRequests(request('status'));
            $request_ids = ($requests) ? Auth::user()->maintenanceRequests(request('status'))->pluck('id')->toArray() : [];
        }


        $records = DB::table('site_maintenance AS m')
            ->select(['m.id', 'm.site_id', 'm.code', 'm.supervisor', 'm.assigned_to', 'm.super_id', 'm.completed', 'm.reported', 'm.warranty', 'm.client_appointment', 'm.client_contacted', 'm.category_id', 'm.status', 'm.updated_at', 'm.created_at',
                DB::raw('DATE_FORMAT(m.reported, "%d/%m/%y") AS reported_date'),
                DB::raw('DATE_FORMAT(m.completed, "%d/%m/%y") AS completed_date'),
                DB::raw('DATE_FORMAT(m.updated_at, "%d/%m/%y") AS updated_date'),
                DB::raw('DATE_FORMAT(m.client_appointment, "%d/%m/%y") AS appointment_date'),
                DB::raw('DATE_FORMAT(m.client_contacted, "%d/%m/%y") AS contacted_date'),
                's.code as sitecode', 's.name as sitename'])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids)
            ->where('m.status', request('status'));

        //dd($records);
        $dt = Datatables::of($records)
            //->editColumn('id', '<div class="text-center"><a href="/site/maintenance/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('id', function ($doc) {
                return "<div class='text-center'><a href='/site/maintenance/$doc->id'>M$doc->code</a></div>";
            })
            ->editColumn('site_id', function ($doc) {
                return $doc->sitecode;
            })
            //->editColumn('sitename', function ($doc) {
            //    $s = Site::find($doc->site_id);
            //    return $s->nameClient;
            //})
            ->editColumn('super_id', function ($doc) {
                $d = SiteMaintenance::find($doc->id);

                return ($d->super_id) ? $d->taskOwner->name : '-';
            })
            ->addColumn('last_updated', function ($doc) {
                $main = SiteMaintenance::find($doc->id);
                $total = $main->items()->count();
                $completed = $main->itemsCompleted()->count();
                $pending = '';
                if ($total == $completed && $total != 0) {
                    if (!$main->supervisor_sign_by)
                        $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                    elseif (!$main->manager_sign_by)
                        $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                }

                return ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') . $pending : $main->created_at->format('d/m/Y') . $pending;
            })/*
            ->addColumn('completed', function ($doc) {
                $main = SiteMaintenance::find($doc->id);
                $total = $main->items()->count();
                $completed = $main->itemsCompleted()->count();
                $pending = '';
                if ($main->status != 0) {
                    if (Auth::user()->allowed2('edit.site.maintenance', $main)) {
                        if ($total == $completed && $total != 0) {
                            $label_type = ($main->supervisor_sign_by && $main->manager_sign_by) ? 'label-success' : 'label-warning';
                            if (!$main->supervisor_sign_by)
                                $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                            elseif (!$main->manager_sign_by)
                                $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                        } else
                            $label_type = 'label-danger';

                        return '<span class="label pull-right ' . $label_type . '">' . $completed . ' / ' . $total . '</span>' . $pending;
                    }
                }

                return '<span class="label pull-right label-success">' . $completed . ' / ' . $total . '</span>';
            })*/
            ->addColumn('action', function ($doc) {
                $main = SiteMaintenance::find($doc->id);
                if (($doc->status && Auth::user()->allowed2('edit.site.maintenance', $main)) || (!$doc->status && Auth::user()->allowed2('sig.site.maintenance', $main)))
                    return '<a href="/site/maintenance/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/site/maintenance/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

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

        $main = SiteMaintenance::findOrFail($id);

        $items = [];
        $users = [];
        $companies = [];
        foreach ($main->items as $item) {
            $array = [];
            $array['id'] = $item->id;
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
                    $users[$item->done_by] = (object) ['id' => $user->id, 'full_name' => $user->full_name, 'company_name' => $user->company->name_alias];
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
                    $users[$item->sign_by] = (object) ['id' => $user->id, 'full_name' => $user->full_name];
                }

                $array['sign_at'] = $item->sign_at->format('Y-m-d');
                $array['sign_by'] = $user->id;
                $array['sign_by_name'] = $user->full_name;
            }


            $array['status'] = $item->status;
            $items[] = $array;
        };


        $actions = [];
        $actions[] = ['value' => '', 'text' => 'Select Action'];
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
            $sel_company[] = ['value' => $cid, 'text' => $name];
        }

        // Company tasks
        $sel_task = [];
        $sel_task[] = ['value' => '', 'text' => 'Select task'];

        if ($main->assigned_to) {
            // Create array in specific Vuejs 'select' format.
            //echo "As:$main->assigned_to<br>";
            //dd($main->assignedTo->tradesSkilledIn);
            $trade_count = count($main->assignedTo->tradesSkilledIn);
            foreach ($main->assignedTo->tradesSkilledIn as $trade) {
                $tasks = Task::where('trade_id', '=', $trade->id)->orderBy('name')->get();
                foreach ($tasks as $task) {
                    if ($task->status) {
                        $text = $task->name;

                        if ($trade_count > 1)
                            $text = $trade->name . ':' . $task->name;

                        $sel_task[] = [
                            'value'      => $task->id,
                            'text'       => $text,
                            'name'       => $task->name,
                            'code'       => $task->code,
                            'trade_id'   => $trade->id,
                            'trade_name' => $trade->name,
                        ];
                    }
                }
            }
        }


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
