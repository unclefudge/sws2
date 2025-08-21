<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Misc\CategoryController;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Category;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteFoc;
use App\Models\Site\SiteFocItem;
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

class SiteFocController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.foc'))
            return view('errors/404');

        $progress = SiteFoc::where('status', 2)->get();

        $focs = SiteFoc::where('status', 1)->orderBy('created_at')->get();
        $assignedList = ['all' => 'All companies', '' => 'Not assigned'];
        foreach ($focs as $foc) {
            foreach ($foc->items as $item) {
                if (!isset($assignedList[$item->assigned_to]))
                    $assignedList[$item->assigned_to] = $item->assigned->name;
            }
        }

        return view('site/foc/list', compact('progress', 'assignedList'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.foc', $foc))
            return view('errors/404');

        return view('site/foc/show', compact('foc'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.foc', $foc))
            return view('errors/404');

        return view('site/foc/show', compact('foc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.foc'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'item1' => 'required',
            'cat1' => 'required_with:item1',
            'cat2' => 'required_with:item2',
            'cat3' => 'required_with:item3',
            'cat4' => 'required_with:item4',
            'cat5' => 'required_with:item5',
            'cat6' => 'required_with:item6',
            'cat7' => 'required_with:item7',
            'cat8' => 'required_with:item8',
            'cat9' => 'required_with:item9',
            'cat10' => 'required_with:item10',
            'cat11' => 'required_with:item11',
            'cat12' => 'required_with:item12',
            'cat13' => 'required_with:item13',
            'cat14' => 'required_with:item14',
            'cat15' => 'required_with:item15',
            'cat16' => 'required_with:item16',
            'cat17' => 'required_with:item17',
            'cat18' => 'required_with:item18',
            'cat19' => 'required_with:item19',
            'cat20' => 'required_with:item20'];
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'item1.required' => 'The item field is required.'];


        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        $site_id = request('site_id');
        $foc_request = request()->all();
        $foc_request['status'] = 2; // set new request to In Progress - Assign Supervisor
        //dd($foc_request);

        // Create FOC
        $foc = SiteFoc::create($foc_request);
        $action = Action::create(['action' => "FOC created", 'table' => 'site_foc', 'table_id' => $foc->id]);

        // Add Request Items
        for ($i = 1; $i < 25; $i++) {
            if (request("item$i")) {
                SiteFocItem::create(['foc_id' => $foc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i, 'status' => 1]);
            }
        }

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_foc', 'table_id' => $foc->id, 'directory' => "/filebank/site/$foc->site_id/foc"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        // Create ToDoo to assign Supervisor
        $foc->createAssignSupervisorToDo([108]); // Kirstie

        Toastr::success("Created FOC");

        return redirect("/site/foc/$foc->id/edit");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.foc'))
            return view('errors/404');

        $cats = Category::where('type', 'foc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/foc/create', compact('cats'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $foc = SiteFoc::findOrFail($id);
        $super_id_orig = $foc->super_id;
        $status_orig = $foc->status;

        //dd(request()->all());

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.foc', $foc))
            return view('errors/404');

        $rules = ['onhold_reason' => 'required_if:status,4'];
        $mesg = ['onhold_reason.required_if' => 'A reason is required to place request On Hold.'];

        request()->validate($rules, $mesg); // Validate

        $foc_request = request()->all();
        //dd($foc_request);

        $foc->update($foc_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_foc', 'table_id' => $foc->id, 'directory' => "/filebank/site/$foc->site_id/prac"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        // Email if Super Assigned is updated
        if (request('super_id') && request('super_id') != $super_id_orig) {
            $super = User::find($foc_request['super_id']);
            $foc->emailAssigned($super);
            $action = Action::create(['action' => "Supervisor assigned $super->name", 'table' => 'site_foc', 'table_id' => $foc->id]);

            if ($foc->status) $foc->status = 1; // Set to Active if in progress
            $foc->closeToDo();   // Delete Assign Super Todoo
            $foc->createSupervisorAssignedToDo([$super->id]); // Create ToDoo for new supervisor
        }

        // Add note if change of Status
        if (request('status') && $status_orig != 4 && request('status') == 4) {
            $action = Action::create(['action' => "Report has been placed On Hold for the following reason: \n" . request('onhold_reason'), 'table' => 'site_foc', 'table_id' => $foc->id]);
            $foc->closeToDo();
        }
        if (request('status') && $status_orig != 1 && request('status') == 1) {
            $action = Action::create(['action' => "Report has been Re-Activated", 'table' => 'site_foc', 'table_id' => $foc->id]);
            $foc->supervisor_sign_by = null;
            $foc->supervisor_sign_at = null;
            $foc->manager_sign_by = null;
            $foc->manager_sign_at = null;
        }
        if (request('status') && $status_orig != '-1' && request('status') == '-1') {
            $action = Action::create(['action' => "Report has been Declined", 'table' => 'site_foc', 'table_id' => $foc->id]);
            $foc->closeToDo();
        }

        $foc->save();
        Toastr::success("Updated Request");

        return redirect('site/foc/' . $foc->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.foc', $foc))
            return view('errors/404');

        // Only Allow Ajax requests
        if (request()->ajax()) {
            $foc_request = request()->all();

            // Determine if report being signed off
            $signoff = request('signoff');
            if ($signoff == 'super') {
                $foc_request['supervisor_sign_by'] = Auth::user()->id;
                $foc_request['supervisor_sign_at'] = Carbon::now();

                // Close any outstanding ToDos for supervisors and Create one for Area Super / Con Mgr
                $foc->closeToDo();
                if (!$foc->manager_sign_by) {
                    $site = Site::findOrFail($foc->site_id);
                    $foc->createManagerSignOffToDo([108]); // Kirstie
                }
                $action = Action::create(['action' => "Report has been signed off by Supervisor", 'table' => 'site_foc', 'table_id' => $foc->id]);
            }
            if ($signoff == 'manager') {
                $foc_request['manager_sign_by'] = Auth::user()->id;
                $foc_request['manager_sign_at'] = Carbon::now();
                // Close any outstanding ToDos for Area Super / Con Mgr
                $foc->closeToDo();

                $action = Action::create(['action' => "Report has been signed off by Manager", 'table' => 'site_foc', 'table_id' => $foc->id]);

                $email_list = [env('EMAIL_DEV')];
                //if (\App::environment('prod'))
                //    $email_list = $foc->site->company->notificationsUsersEmailType('site.foc.completed');

                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteFocCompleted($foc));
            }

            //dd($foc_request);

            $foc->update($foc_request);

            // Determine if Report Signed Off and if so mark completed
            if ($foc->supervisor_sign_by && $foc->manager_sign_by) {
                $foc->status = 0;
                $foc->save();
            }


            Toastr::success("Updated Report");

            return $foc;
        }

        return view('errors/404');
    }


    /**
     * Add Item the specified resource in storage.
     *
     */
    public function addItem($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.foc', $foc) || Auth::user()->id == $foc->super_id))
            return view('errors/404');

        //dd(request()->all());
        $item = SiteFocItem::create(['foc_id' => $foc->id, 'name' => request('name'), 'category_id' => request('category_id'), 'order' => request('order'), 'status' => 1]);

        // Assign ToDoo to Supervisor for item
        if ($foc->super_id)
            $item->createAssignSupervisorToDo($foc->super_id);

        // Update modified timestamp
        $foc->touch();

        return $item;
    }

    public function addItems($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.foc', $foc) || Auth::user()->id == $foc->super_id))
            return view('errors/404');

        $cats = Category::where('type', 'foc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/foc/additems', compact('foc', 'cats'));
    }

    public function addItemsSave($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.foc', $foc) || Auth::user()->id == $foc->super_id))
            return view('errors/404');

        $itemCount = $foc->items->count();
        //dd(request()->all());
        $rules = ['item1' => 'required',
            'cat1' => 'required_with:item1',
            'cat2' => 'required_with:item2',
            'cat3' => 'required_with:item3',
            'cat4' => 'required_with:item4',
            'cat5' => 'required_with:item5',
            'cat6' => 'required_with:item6',
            'cat7' => 'required_with:item7',
            'cat8' => 'required_with:item8',
            'cat9' => 'required_with:item9',
            'cat10' => 'required_with:item10',
            'cat11' => 'required_with:item11',
            'cat12' => 'required_with:item12',
            'cat13' => 'required_with:item13',
            'cat14' => 'required_with:item14',
            'cat15' => 'required_with:item15',
            'cat16' => 'required_with:item16',
            'cat17' => 'required_with:item17',
            'cat18' => 'required_with:item18',
            'cat19' => 'required_with:item19',
            'cat20' => 'required_with:item20'];
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'item1.required' => 'The item field is required.',
            'cat1.required_with' => 'The category field is required',
            'cat2.required_with' => 'The category field is required',
            'cat3.required_with' => 'The category field is required',
            'cat4.required_with' => 'The category field is required',
            'cat5.required_with' => 'The category field is required',
            'cat6.required_with' => 'The category field is required',
            'cat7.required_with' => 'The category field is required',
            'cat8.required_with' => 'The category field is required',
            'cat9.required_with' => 'The category field is required',
            'cat10.required_with' => 'The category field is required',
            'cat11.required_with' => 'The category field is required',
            'cat12.required_with' => 'The category field is required',
            'cat13.required_with' => 'The category field is required',
            'cat14.required_with' => 'The category field is required',
            'cat15.required_with' => 'The category field is required',
            'cat16.required_with' => 'The category field is required',
            'cat17.required_with' => 'The category field is required',
            'cat18.required_with' => 'The category field is required',
            'cat19.required_with' => 'The category field is required',
            'cat20.required_with' => 'The category field is required',
        ];


        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());


        $foc_request = request()->all();

        //dd($foc_request);

        // Create FOC
        $action = Action::create(['action' => "Multiple items added", 'table' => 'site_foc', 'table_id' => $foc->id]);

        // Add Request Items
        for ($i = 1; $i < 25; $i++) {
            if (request("item$i")) {
                SiteFocItem::create(['foc_id' => $foc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i + $itemCount, 'status' => 1]);
            }
        }

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_foc', 'table_id' => $foc->id, 'directory' => "/filebank/site/$foc->site_id/foc"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        // Create ToDoo to assign Supervisor
        $foc->createAssignSupervisorToDo([108]); // Kirstie

        Toastr::success("Created FOC");

        return redirect("/site/foc/$foc->id/edit");
    }

    public function delItem($id)
    {
        $item = SiteFocItem::findOrFail($id);
        $foc = SiteFoc::findOrFail($item->foc_id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('del.site.foc', $item->foc)))
            return view('errors/404');

        //dd(request()->all());

        // Delete planner task if present
        //if ($item->planner_id)
        //    $delTask = SitePlanner::where('id', $item->planner_id)->delete();

        $item->closeToDo();
        $item->delete(); // Delete item

        // Reorder items
        $order = 1;
        foreach ($foc->items->sortBy('order') as $item) {
            $item->order = $order++;
            $item->save();
        }

        // Update modified timestamp
        $foc->touch();

        Toastr::success("Deleted item");

        return redirect('site/foc/' . $foc->id);
    }

    public function deleteAttachment($id, $doc_id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.foc', $foc))
            return view('errors/404');

        $doc = Attachment::where('id', $doc_id)->first();
        if ($doc) {
            if (file_exists(public_path($doc->url)))
                unlink(public_path($doc->url));
            $doc->delete();
        }

        return redirect('site/foc/' . $foc->id . '/edit');

    }

    /**
     * Update Item the specified resource in storage.
     *
     */
    public function updateItem(Request $request, $id)
    {
        $item = SiteFocItem::findOrFail($id);
        $foc = SiteFoc::findOrFail($item->foc_id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.foc', $foc) || Auth::user()->id == $foc->super_id))
            return view('errors/404');

        $item_request = $request->only(['name', 'category_id', 'assigned_to', 'status']);
        //dd($item_request);

        $assigned_to_orig = $item->assigned_to;
        $status_orig = $item->status;

        // Update Planer Task
        /* $planner_id = request('planner_id');
        $planner_id_orig = $item->planner_id;
        $planner_task_id = request('planner_task_id');
        $planner_date = (request('planner_date')) ? Carbon::createFromFormat('d/m/Y H:i', request('planner_date') . '00:00')->toDateTimeString() : null;
        if ($planner_task_id) {
            if ($planner_id_orig && $planner_id_orig != $planner_task_id)
                $delTask = SitePlanner::where('id', $planner_id_orig)->delete();  // Delete old planner task

            // Create new
            $planner = SitePlanner::create(['site_id' => $foc->site_id, 'from' => $planner_date, 'to' => $planner_date, 'days' => 1, 'entity_type' => 'c', 'entity_id' => request('assigned_to'), 'task_id' => $planner_task_id]);
            if ($planner) {
                $item->planner_id = $planner->id;
                $item->save();
            }
        } elseif ($planner_id_orig)
            $delTask = SitePlanner::where('id', $planner_id_orig)->delete();  // Delete old planner task
        */

        // Update resolve date if just modified
        if (request('status') != $status_orig) {
            if (request('status') == 1) {
                $item->status = 1;
                $item->sign_by = null;
                $item->sign_at = null;
                $item->save();
                //$action = Action::create(['action' => "FOC Item has been mark as NOT completed", 'table' => 'site_foc', 'table_id' => $foc->id]);
            } else {
                // Item completed
                if ($item_request['status'] == 0 && $item->status != 0) {
                    $item_request['sign_by'] = Auth::user()->id;
                    $item_request['sign_at'] = Carbon::now()->toDateTimeString();
                    //$action = Action::create(['action' => "FOC Item has been completed", 'table' => 'site_foc', 'table_id' => $foc->id]);
                }
                //dd($item_request);
            }
        }
        $item->update($item_request);

        // Email if Company Assigned is updated
        /*if (request('assigned_to') && request('assigned_to') != $assigned_to_orig) {
            $company = Company::find(request('assigned_to'));
            if ($company && $company->primary_contact())
                $item->emailAssigned($company->primary_contact());
            $action = Action::create(['action' => "Company assigned to request updated to $company->name", 'table' => 'site_foc', 'table_id' => $foc->id]);
            $item->closeToDo();
        }*/
        $foc->closeToDo();

        // Update modified timestamp on QA Doc
        $foc = SiteFoc::findOrFail($item->foc_id);
        $foc->touch();

        Toastr::success("Updated record");

        return $item;
    }

    public function destroy($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.foc', $foc))
            return view('errors/404');

        $foc->closeToDo();
        $foc->delete();
    }

    public function clearSignoff($id)
    {
        $foc = SiteFoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.foc', $foc))
            return view('errors/404');

        $foc->supervisor_sign_by = null;
        $foc->supervisor_sign_at = null;
        $foc->save();
        return redirect('site/foc/' . $foc->id . '/edit');
    }

    public function settings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $cats = Category::where('type', 'foc_item')->where('status', 1)->orderBy('order')->get();

        return view('site/foc/settings-categories', compact('cats'));
    }

    public function updateSettings(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());
        CategoryController::updateCategories('foc_item', $request);

        Toastr::success("Updated categories");

        return redirect(url()->previous());
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
     * Get FOCs current user is authorised to manage + Process datatables ajax request.
     */
    public function getFoc()
    {
        if (request('supervisor_sel')) {
            if (request('supervisor') == 'all')
                $request_ids = SiteFoc::all()->pluck('id')->toArray();
            elseif (request('supervisor') == 'signoff')
                $request_ids = SiteFoc::where('status', 1)->where('supervisor_sign_by', '<>', null)->pluck('id')->toArray();
            else
                $request_ids = SiteFoc::where('super_id', request('supervisor'))->pluck('id')->toArray();
        } else {
            $requests = Auth::user()->foc(request('status'));
            $request_ids = ($requests) ? Auth::user()->foc(request('status'))->pluck('id')->toArray() : [];
        }

        //if (request('assigned_to') != 'all')
        //    $request_ids = SiteFocItem::whereIn('foc_id', $request_ids)->where('assigned_to', request('assigned_to'))->pluck('foc_id')->toArray();

        $records = DB::table('site_foc AS m')
            ->select(['m.id', 'm.site_id', 'm.super_id', 'm.status', 'm.updated_at', 'm.created_at',
                DB::raw('DATE_FORMAT(m.updated_at, "%d/%m/%y") AS updated_date'),
                's.code as sitecode', 's.name as sitename'])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids)
            ->where('m.status', request('status'));

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/foc/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('site_id', function ($rec) {
                return $rec->sitecode;
            })
            ->editColumn('super_id', function ($rec) {
                $d = SiteFoc::find($rec->id);

                return ($d->super_id) ? $d->supervisor->initials : '-';
            })
            ->addColumn('last_updated', function ($rec) {
                $foc = SiteFoc::find($rec->id);
                $total = $foc->items()->count();
                $completed = $foc->itemsCompleted()->count();
                $pending = '';
                if ($total == $completed && $total != 0) {
                    if (!$foc->supervisor_sign_by)
                        $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                    elseif (!$foc->manager_sign_by)
                        $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                }

                return ($foc->lastAction()) ? $foc->lastAction()->updated_at->format('d/m/Y') . $pending : $foc->created_at->format('d/m/Y') . $pending;
            })
            ->addColumn('action', function ($rec) {
                $foc = SiteFoc::find($rec->id);
                $action = '';
                if (($rec->status && Auth::user()->allowed2('edit.site.foc', $foc)) || (!$rec->status && Auth::user()->allowed2('sig.site.foc', $foc)))
                    $action .= '<a href="/site/foc/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return $action;
            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action', 'last_updated'])
            ->make(true);

        return $dt;
    }


    public function getItems(Request $request, $id)
    {
        //if ($request->ajax()) {

        $foc = SiteFoc::findOrFail($id);

        $items = [];
        $users = [];
        $companies = [];
        foreach ($foc->items as $item) {
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
            $array['category_id'] = (string)$item->category_id;
            $array['assigned_to'] = (string)$item->assigned_to;
            $array['assigned_to_name'] = ($item->assigned_to) ? $item->assigned->name : 'Unassigned';
            $array['planner_id'] = (string)$item->planner_id;
            $array['planner_task'] = $taskname;
            $array['planner_task_id'] = $taskid;
            $array['planner_date'] = $taskdate;
            $array['order'] = $item->order;
            $array['name'] = $item->name;
            $array['super'] = $item->super;


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
        //$actions[] = ['value' => '1', 'text' => 'Incomplete'];
        //$actions[] = ['value' => '0', 'text' => 'Completed'];
        //$actions[] = ['value' => '-1', 'text' => 'Mark N/A'];
        $actions[] = ['value' => '', 'text' => 'Select category'];
        $cats = Category::where('type', 'foc_item')->where('status', 1)->orderBy('order')->get();
        foreach ($cats as $cat)
            $actions[] = ['value' => $cat->id, 'text' => $cat->name];
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
