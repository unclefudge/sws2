<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SiteQaRequest;
use App\Jobs\SiteQaClientPdf;
use App\Jobs\SiteQaPdf;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Report;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteQaItem;
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
 * Class SiteQaController
 * @package App\Http\Controllers\Site
 */
class SiteQaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.qa'))
            return view('errors/404');

        $signoff = false;

        return view('site/qa/list', compact('signoff'));
    }

    public function templates()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.qa.templates'))
            return view('errors/404');

        return view('site/qa/templates/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.qa.templates'))
            return view('errors/404');

        return view('site/qa/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qa = SiteQa::findOrFail($id);

        // Check authorisation and throw 404 if not
        if ($qa->master) {
            if (!Auth::user()->allowed2('view.site.qa.templates', $qa))
                return view('errors/404');
        } else {
            if (!Auth::user()->allowed2('view.site.qa', $qa))
                return view('errors/404');
        }

        return view('site/qa/show', compact('qa'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $qa = SiteQa::findOrFail($id);

        // Check authorisation and throw 404 if not
        if ($qa->master) {
            if (!Auth::user()->allowed2('edit.site.qa.templates', $qa))
                return view('errors/404');
        } else {
            if (!Auth::user()->allowed2('edit.site.qa', $qa))
                return view('errors/404');
        }

        return view('site/qa/edit', compact('qa'));
    }

    public function reportOrder()
    {
        if (!Auth::user()->allowed2('add.site.qa.templates'))
            return view('errors/404');

        $templates = SiteQa::where('status', 1)->where('master', 1)->orderBy('order')->get();
        $i = 1;
        foreach ($templates as $template) {
            if ($template->order == null) {
                $template->order = $i;
                $template->save();
            }
            $i++;
        }

        return view('site/qa/templates/order', compact('templates'));
    }

    public function reportOrderUpdate2()
    {
        if (!Auth::user()->allowed2('add.site.qa.templates'))
            return view('errors/404');

        //dd(request()->all());
        if (request('order')) {
            //dd(request('positions'));
            foreach (request('order') as $row) {
                $id = $row[0];
                $order = $row[1];
                $qa = SiteQa::find($id);
                if ($qa) {
                    $qa->order = $order;
                    $qa->save();
                }
            }
        }

        //Toastr::success("Updated order");
        return json_encode('success');
        //return response()->json($action);
    }

    public function reportOrderUpdate($direction, $id)
    {
        if (!Auth::user()->allowed2('add.site.qa.templates'))
            return view('errors/404');

        $template = SiteQa::findOrFail($id);

        if ($direction == 'up' && $template->order != 1) {
            $newPos = $template->order - 1;
            $template2 = SiteQa::where('status', 1)->where('order', $newPos)->first();
            if ($template2) {
                $template2->order = $template->order;
                $template2->save();
                $template->order = $newPos;
                $template->save();
            }
        }

        $last = SiteQa::where('status', 1)->orderByDesc('order')->first();
        if ($last && $direction == 'down' && $template->order != $last->order) {
            $newPos = $template->order + 1;
            $template2 = SiteQa::where('status', 1)->where('order', $newPos)->first();
            if ($template2) {
                $template2->order = $template->order;
                $template2->save();
                $template->order = $newPos;
                $template->save();
            }
        }
        Toastr::success("Updated order");

        return redirect(url()->previous());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SiteQaRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.qa.templates'))
            return view('errors/404');

        $qa_request = $request->all();

        // Create Site QA
        $newQA = SiteQa::create($qa_request);

        $order = 1;
        for ($i = 1; $i <= 25; $i++) {
            if ($request->get("item$i")) {
                $super = ($request->has("super$i")) ? '1' : '0';
                $cert = ($request->has("cert$i")) ? '1' : '0';
                $newItem = SiteQaItem::create(
                    ['doc_id' => $newQA->id,
                        'task_id' => $request->get("task$i"),
                        'name' => $request->get("item$i"),
                        'super' => $super,
                        'certification' => $cert,
                        'order' => $order++,
                        'master' => '1',
                    ]);
            }
        }
        Toastr::success("Created new template");

        return redirect('/site/qa/templates');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SiteQaRequest $request, $id)
    {
        $qa = SiteQa::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.qa.templates', $qa))
            return view('errors/404');

        $qa_request = $request->all();
        //dd($qa_request);

        // Increment minor version
        list($major, $minor) = explode('.', $qa->version);
        $minor++;
        $qa_request['version'] = $major . '.' . $minor;
        $qa_request['notes'] = "version $major.$minor released " . Carbon::now()->format('d/m/Y') . "\r\n" . $qa->notes;

        $qa->update($qa_request);

        // Delete existing Items
        foreach ($qa->items as $item)
            SiteQaItem::where('doc_id', $qa->id)->delete();

        // Re-create new ones
        $order = 1;
        for ($i = 1; $i <= 25; $i++) {
            if ($request->get("item$i") && $request->get("item$i") != 'DELETE-ITEM') {
                $super = ($request->has("super$i")) ? '1' : '0';
                $cert = ($request->has("cert$i")) ? '1' : '0';
                $newItem = SiteQaItem::create(
                    ['doc_id' => $qa->id,
                        'task_id' => $request->get("task$i"),
                        'name' => $request->get("item$i"),
                        'super' => $super,
                        'certification' => $cert,
                        'order' => $order++,
                        'master' => '1',
                    ]);
            }
        }

        Toastr::success("Updated Report");

        return redirect('site/qa/' . $qa->id . '/edit');
    }

    public function resetSignature($id)
    {
        $qa = SiteQa::findOrFail($id);
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.qa', $qa))
            return view('errors/404');

        $qa->status = 1;
        $qa->supervisor_sign_by = null;
        $qa->supervisor_sign_at = null;
        $qa->manager_sign_by = null;
        $qa->manager_sign_at = null;
        $qa->save();


        // Close any outstanding ToDos for Area Super / Con Mgr
        $qa->closeToDo(Auth::user());

        // Create ToDoo for Super
        $site = Site::findOrFail($qa->site_id);
        $qa->createToDo($site->supervisor_id);

        $action = Action::create(['action' => 'Reset sign-offs', 'table' => 'site_qa', 'table_id' => $qa->id]);

        return redirect("/site/qa/$qa->id");
    }

    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport(Request $request, $id)
    {
        $qa = SiteQa::findOrFail($id);
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.qa', $qa))
            return view('errors/404');

        // Only Allow Ajax requests
        if ($request->ajax()) {
            $qa_request = request()->all();

            // Determine if report being signed off
            $signoff = request('signoff');
            if ($signoff == 'super') {
                $qa_request['supervisor_sign_by'] = Auth::user()->id;
                $qa_request['supervisor_sign_at'] = Carbon::now();

                // Update QA to Active if not already
                if ($qa->status != 1) {
                    $action = Action::create(['action' => 'Moved report to Active', 'table' => 'site_qa', 'table_id' => $qa->id]);
                    $qa->status = 1;
                    $qa->save();
                }

                // Close any outstanding ToDos for supervisors and Create one for Area Super / Con Mgr
                $qa->closeToDo(Auth::user());
                if (!$qa->manager_sign_by) {
                    $site = Site::findOrFail($qa->site_id);
                    $qa->createManagerSignOffToDo([108]); // Kirstie
                }
            }
            if ($signoff == 'manager') {
                $qa_request['manager_sign_by'] = Auth::user()->id;
                $qa_request['manager_sign_at'] = Carbon::now();
                // Close any outstanding ToDos for Area Super / Con Mgr
                $qa->closeToDo(Auth::user());
            }

            // If report was placed On Hold then auto add an Action + close ToDoo
            if (request('status') == 4 && $qa->status != 4)
                $qa->moveToHold(Auth::user());

            // If report was placed Oewners Works then auto add an Action + close ToDoo
            if (request('status') == 5 && $qa->status != 5)
                $qa->moveToOwner(Auth::user());

            // If report was reactived then auto add an Action + create ToDoo
            if (request('status') == 1 && $qa->status != 1)
                $qa->moveToActive(Auth::user());

            // If report was marked Not Required then close ToDoo
            if (request('status') == '-1')
                $qa->closeToDo(Auth::user());

            $qa->update($qa_request);

            // Determine if Report Signed Off and if so mark completed
            if ($qa->supervisor_sign_by && $qa->manager_sign_by) {
                $qa->status = 0;
                $qa->save();
                if ($qa->master_id == '2581' && $qa->owned_by->notificationsUsersType('site.qa.handover')) // Only email if QA is Handover template  ie. final QA on site
                    Mail::to($qa->owned_by->notificationsUsersType('site.qa.handover'))->send(new \App\Mail\Site\SiteQaHandover($qa));
                if ($qa->master_id == '2752' && $qa->owned_by->notificationsUsersType('site.qa.super.photo')) // Only email if QA is Supervisor Photo Checklist template  ie. final QA on site
                    Mail::to($qa->owned_by->notificationsUsersType('site.super.photo'))->send(new \App\Mail\Site\SiteQaSuperPhoto($qa));
            }
            Toastr::success("Updated Report");

            return $qa;
        }

        return view('errors/404');
    }

    /**
     * Update Item the specified resource in storage.
     *
     */
    public function updateItem(Request $request, $id)
    {
        $item = SiteQaItem::findOrFail($id);
        $qa = SiteQa::findOrFail($item->doc_id);
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.qa', $qa))
        //    return view('errors/404');

        $item_request = $request->only(['status', 'done_by']);
        //dd(request()->all());

        // Update resolve date if just modified
        if (!request('status')) {
            $item->status = 0;
            $item->sign_by = null;
            $item->sign_at = null;
            //dd($item_request);
            $item->save();
        } else {
            if ($item_request['status'] == 1 && $item->status != 1) {
                // Item signed off
                $item_request['sign_by'] = Auth::user()->id;
                $item_request['sign_at'] = Carbon::now()->toDateTimeString();
                // Update QA to Active if not already
                if ($qa->status != 1)
                    $qa->moveToActive(Auth::user());

            }
            $item->update($item_request);
        }

        // Custom Assign Company for item done_by_company
        if (request('update_company')) {
            // Only assign selected item to specified custom company
            $item->done_by = request('done_by');
            $item->done_by_other = (request('done_by_other')) ? request('done_by_other') : null;
            $item->save();
        }

        // Below code now taken care of by vue function UpdateItemCompany
        /*if (request('done_by_all') && request('done_by_all') == 1) {
            // Assign all unassigned items to specified custom company also
            foreach ($qa->items as $qaItem) {
                if ($qaItem->status == 0 && !$qaItem->done_by) {
                    //echo "[$qaItem->id] $qaItem->name s:$qaItem->status dby:$qaItem->done_by <br>";
                    $qaItem->done_by = request('done_by');
                    $qaItem->done_by_other = (request('done_by_other')) ? request('done_by_other') : null;
                    $qaItem->save();
                }
            }
        }*/

        // Update modified timestamp on QA Doc
        $qa = SiteQa::findOrFail($item->doc_id);
        $qa->touch();

        Toastr::success("Updated record");

        return $item;
    }


    /**
     * Get QA Reports current user is authorised to manage + Process datatables ajax request.
     */
    public function getQaReports()
    {
        if (request('supervisor_sel')) {
            if (request('supervisor') == 'all')
                $site_list = Site::all()->pluck('id')->toArray();
            elseif (request('supervisor') == 'signoff') {
                $site_list = Auth::user()->authSites('view.site.qa')->pluck('id')->toArray();
                $qa_list = SiteQa::whereIn('status', [1, 4, 5])->whereNot('supervisor_sign_by', null)->whereIn('site_id', $site_list)->pluck('id')->toArray();
            } else
                $site_list = Site::where('supervisor_id', request('supervisor'))->pluck('id')->toArray();
        } else
            $site_list = Auth::user()->authSites('view.site.qa')->pluck('id')->toArray();

        //dd($qa_list);

        if (request('supervisor') != 'signoff') {
            $records = DB::table('site_qa AS q')
                ->select(['q.id', 'q.name', 'q.site_id', 'q.version', 'q.master_id', 'q.company_id', 'q.status', 'q.updated_at',
                    's.name as sitename'])
                ->join('sites AS s', 'q.site_id', '=', 's.id')
                ->where('q.company_id', Auth::user()->company_id)
                ->where('q.master', '0')
                ->whereIn('q.site_id', $site_list)
                ->where('q.status', request('status'));
        } else {
            $records = DB::table('site_qa AS q')
                ->select(['q.id', 'q.name', 'q.site_id', 'q.version', 'q.master_id', 'q.company_id', 'q.status', 'q.updated_at',
                    's.name as sitename'])
                ->join('sites AS s', 'q.site_id', '=', 's.id')
                ->where('q.company_id', Auth::user()->company_id)
                ->where('q.master', '0')
                ->whereIn('q.id', $qa_list)
                ->where('q.status', request('status'));
        }

        //dd($records);
        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/qa/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('sitename', function ($doc) {
                return $doc->sitename;
            })
            ->editColumn('name', function ($doc) {
                $name = $doc->name . ' &nbsp;<span class="font-grey-silver">v' . $doc->version . '</span>';
                //if (in_array($doc->status, ['1', '2']) && $doc->master_id > 100)
                //    $name .= " <span class='badge badge-warning badge-roundless'>New</span>";

                /*if ($doc->status == 1) {
                    $now = Carbon::now();
                    $weekago = $now->subWeek()->toDateTimeString();
                    if ($doc->updated_at <= $weekago)
                        $name = '<span class="font-red">'.$name.'</span>';
                }*/

                return $name;
            })
            ->addColumn('supervisor', function ($doc) {
                $site = Site::find($doc->site_id);

                return $site->supervisorName;
            })
            ->editColumn('updated_at', function ($doc) {
                if ($doc->status == 1) {
                    $now = Carbon::now();
                    $weekago = $now->subWeek()->toDateTimeString();
                    if ($doc->updated_at <= $weekago)
                        return '<span class="font-red">' . (new Carbon($doc->updated_at))->format('d/m/Y') . '</span>';
                }

                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('completed', function ($doc) {
                $qa = SiteQa::find($doc->id);
                $total = $qa->items()->count();
                $completed = $qa->itemsCompleted()->count();
                $pending = '';
                if ($qa->status != 0) {
                    if (Auth::user()->allowed2('edit.site.qa', $qa)) {
                        if ($total == $completed && $total != 0) {
                            $label_type = ($qa->supervisor_sign_by && $qa->manager_sign_by) ? 'label-success' : 'label-warning';
                            if (!$qa->supervisor_sign_by)
                                $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                            elseif (!$qa->manager_sign_by)
                                $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                        } else
                            $label_type = 'label-danger';

                        return '<span class="label pull-right ' . $label_type . '">' . $completed . ' / ' . $total . '</span>' . $pending;
                    }
                }

                return '<span class="label pull-right label-success">' . $completed . ' / ' . $total . '</span>';
            })
            ->addColumn('action', function ($qa) {
                if (($qa->status && Auth::user()->allowed2('edit.site.qa', $qa)) || (!$qa->status && Auth::user()->allowed2('sig.site.qa', $qa)))
                    return '<a href="/site/qa/' . $qa->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/site/qa/' . $qa->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get QA templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getQaTemplates()
    {
        $records = DB::table('site_qa')
            ->select(['id', 'name', 'version', 'company_id', 'status', 'updated_at'])
            ->where('company_id', Auth::user()->company_id)
            ->where('master', '1')
            ->where('status', request('status'));

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/qa/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('name', function ($qa) {
                $name = $qa->name . ' &nbsp;<span class="font-grey-silver">v' . $qa->version . '</span>';
                if ($qa->id > 100)
                    $name .= " <span class='badge badge-warning badge-roundless'>New</span>";

                return $name;
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($doc) {
                $qa = SiteQa::find($doc->id);
                if (Auth::user()->allowed2('edit.site.qa.templates', $qa))
                    return '<a href="/site/qa/' . $qa->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/site/qa/' . $qa->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action'])
            ->make(true);

        return $dt;
    }

    public function getQaUpcoming()
    {

        // Get list of sites
        if (request('supervisor') == 'all')
            $site_list = Site::where('status', 1)->pluck('id')->toArray();
        else
            $site_list = Site::where('status', 1)->where('supervisor_id', request('supervisor'))->pluck('id')->toArray();

        // Get All Active templates
        $templates = SiteQa::where('company_id', Auth::user()->company_id)->where('master', '1')->where('status', 1)->get();
        $template_ids = SiteQa::where('company_id', Auth::user()->company_id)->where('master', '1')->where('status', 1)->pluck('id')->toArray();

        // Get All templates for given site(s)
        $site_qas = SiteQa::where('company_id', Auth::user()->company_id)->where('master', '0')->whereIn('site_id', $site_list)->get();
        $site_qa_ids = SiteQa::where('company_id', Auth::user()->company_id)->where('master', '0')->whereIn('site_id', $site_list)->pluck('id')->toArray();

        $missing_qas = [];


        $records = DB::table('site_qa AS q')
            ->select(['q.id', 'q.name', 'q.site_id', 'q.version', 'q.master_id', 'q.company_id', 'q.status', 'q.updated_at',
                's.name as sitename'])
            ->join('sites AS s', 'q.site_id', '=', 's.id')
            ->where('q.company_id', Auth::user()->company_id)
            ->where('q.master', '0')
            ->whereIn('q.id', $qa_list)
            ->where('q.status', request('status'));

        //dd($records);
        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/qa/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('sitename', function ($doc) {
                return $doc->sitename;
            })
            ->editColumn('name', function ($doc) {
                $name = $doc->name . ' &nbsp;<span class="font-grey-silver">v' . $doc->version . '</span>';

                return $name;
            })
            ->addColumn('supervisor', function ($doc) {
                $site = Site::find($doc->site_id);

                return $site->supervisorName;
            })
            ->editColumn('updated_at', function ($doc) {
                if ($doc->status == 1) {
                    $now = Carbon::now();
                    $weekago = $now->subWeek()->toDateTimeString();
                    if ($doc->updated_at <= $weekago)
                        return '<span class="font-red">' . (new Carbon($doc->updated_at))->format('d/m/Y') . '</span>';
                }

                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('completed', function ($doc) {
                $qa = SiteQa::find($doc->id);
                $total = $qa->items()->count();
                $completed = $qa->itemsCompleted()->count();
                $pending = '';
                if ($qa->status != 0) {
                    if (Auth::user()->allowed2('edit.site.qa', $qa)) {
                        if ($total == $completed && $total != 0) {
                            $label_type = ($qa->supervisor_sign_by && $qa->manager_sign_by) ? 'label-success' : 'label-warning';
                            if (!$qa->supervisor_sign_by)
                                $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                            elseif (!$qa->manager_sign_by)
                                $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                        } else
                            $label_type = 'label-danger';

                        return '<span class="label pull-right ' . $label_type . '">' . $completed . ' / ' . $total . '</span>' . $pending;
                    }
                }

                return '<span class="label pull-right label-success">' . $completed . ' / ' . $total . '</span>';
            })
            ->addColumn('action', function ($qa) {
                if (($qa->status && Auth::user()->allowed2('edit.site.qa', $qa)) || (!$qa->status && Auth::user()->allowed2('sig.site.qa', $qa)))
                    return '<a href="/site/qa/' . $qa->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/site/qa/' . $qa->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action'])
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

    public function qaPDF($client_planner_data = null)
    {
        // Determine if function called by ExportQA or ClientPlannerEmail
        if ($client_planner_data) {
            $client_planner_id = $client_planner_data['email_id'];
            $date_from = $client_planner_data['date_from'];
        } else {
            $client_planner_id = null;
            $date_from = null;
        }

        $site = Site::find(request('site_id'));
        if (!$site)
            return redirect('/manage/report/recent');

        $completed = 1;
        $data = [];
        $users = [];
        $companies = [];

        if ($client_planner_id) {
            $site_qa = SiteQa::where('site_id', $site->id)->where('status', '0')->whereDate('updated_at', '>', $date_from)->get();
        } else {
            $site_qa = SiteQa::where('site_id', $site->id)->where('status', '<>', '-1')->get();
        }

        foreach ($site_qa->sortBy('reportOrder') as $qa) {

            $obj_qa = (object)[
                'id' => $qa->id,
                'name' => $qa->name,
                'status' => $qa->status,
                'super_sign_by' => '',
                'super_sign_at' => '',
                'manager_sign_by' => '',
                'manager_sign_at' => '',
                'items' => [],
                'actions' => [],
            ];

            // Supervisor sign-off
            if ($qa->supervisor_sign_by) {
                $users[$qa->supervisor_sign_by] ??= User::find($qa->supervisor_sign_by);
                $obj_qa->super_sign_by = $users[$qa->supervisor_sign_by]->fullname;
                $obj_qa->super_sign_at = $qa->supervisor_sign_at->format('d/m/Y');
            } else {
                $completed = 0;
            }

            // Manager sign-off
            if ($qa->manager_sign_by) {
                $users[$qa->manager_sign_by] ??= User::find($qa->manager_sign_by);
                $obj_qa->manager_sign_by = $users[$qa->manager_sign_by]->fullname;
                $obj_qa->manager_sign_at = $qa->manager_sign_at->format('d/m/Y');
            } else {
                $completed = 0;
            }

            // Items
            foreach ($qa->items as $item) {
                $obj_qa->items[$item->order] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'status' => $item->status,
                    'done_by' => '',
                    'sign_by' => '',
                    'sign_at' => '',
                ];

                if ($item->status == '1') {
                    $users[$item->sign_by] ??= User::find($item->sign_by);
                    $user_signed = $users[$item->sign_by];

                    if ($item->done_by) {
                        $companies[$item->done_by] ??= Company::find($item->done_by);
                        $company = $companies[$item->done_by];
                    } else {
                        $company = $user_signed->company;
                    }

                    $obj_qa->items[$item->order]['done_by'] =
                        ($company->id == 1)
                            ? $item->done_by_other . " (lic.)"
                            : "{$company->name_alias} (lic. {$company->licence_no})";

                    $obj_qa->items[$item->order]['sign_by'] = $user_signed->fullname;
                    $obj_qa->items[$item->order]['sign_at'] = $item->sign_at->format('d/m/Y');
                }
            }

            // Actions
            foreach ($qa->actions as $action) {
                if (!str_starts_with($action->action, 'Moved report to')) {
                    $users[$action->created_by] ??= User::find($action->created_by);
                    $obj_qa->actions[$action->id] = [
                        'action' => $action->action,
                        'created_by' => $users[$action->created_by]->fullname,
                        'created_at' => $action->created_at->format('d/m/Y'),
                    ];
                }
            }

            $data[] = $obj_qa;

            /**
             * ==========================
             * CLIENT PLANNER MODE
             * ==========================
             */
            if ($client_planner_id) {

                $strip = [
                    "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]", "}",
                    "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;",
                    "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ".", ">", "/", "?"
                ];

                $qa_name = trim(str_replace($strip, "", strip_tags($qa->name))) . " QA Checklist";
                $filename = "{$site->name} {$qa_name}.pdf";
                $path = "site/{$site->id}/emails/client";
                $attachment = Attachment::create(['table' => 'client_planner_emails', 'table_id' => $client_planner_id, 'name' => $filename, 'attachment' => $filename, 'directory' => "$path", 'status' => 2]);

                SiteQaClientPdf::dispatch($attachment->id, [$obj_qa], $site->id,);
            }
        }

        /**
         * ==========================
         * FULL EXPORT (NON-CLIENT)
         * ==========================
         */
        if (!$client_planner_id) {
            $name = 'QA ' . sanitizeFilename($site->name) . " ({$site->id}) " . Carbon::now()->format('YmdHis') . '.pdf';
            $path = "report/" . Auth::user()->company_id;
            $report = Report::create(['user_id' => Auth::id(), 'company_id' => Auth::user()->company_id, 'name' => $name, 'path' => $path, 'type' => 'site-qa', 'status' => 'pending',]);
            SiteQaPdf::dispatch($report->id, $data, $site->id);
        }

        return redirect('/manage/report/recent');
    }


    public function getItems(Request $request, $id)
    {
        //if ($request->ajax()) {

        $qa = SiteQa::findOrFail($id);

        $items = [];
        $users = [];
        $companies = [];
        foreach ($qa->items as $item) {
            $array = [];
            $array['id'] = $item->id;
            $array['order'] = $item->order;
            $array['name'] = $item->name;
            $array['super'] = $item->super;

            // Task Info
            $array['task_id'] = $item->task_id;
            $task = Task::find($item->task_id);
            //$array['task_name'] = $task->name;
            $array['task_code'] = $task->code;

            // Done By
            if ($item->done_by)
                $array['done_by'] = $item->done_by;
            else {
                // Check Planner which company did the task
                $planned_task = SitePlanner::where('site_id', $qa->site_id)->where('task_id', $item->task_id)->first();
                if ($planned_task && $planned_task->entity_type == 'c' && !$item->super)
                    $array['done_by'] = $planned_task->entity_id;
                else
                    $array['done_by'] = '';
            }

            $array['done_by_company'] = '';
            $array['done_by_licence'] = '';
            if ($array['done_by']) {
                if ($array['done_by'] == 1) {
                    // Done by 'Other Company'
                    $array['done_by_other'] = $item->done_by_other;
                    $array['done_by_company'] = $item->done_by_other;
                    $array['done_by_licence'] = '???????';
                } else {
                    // Company Info - Array of unique companies (store previous companies to speed up)
                    if (isset($companies[$item->done_by])) {
                        $company = $companies[$array['done_by']];
                    } else {
                        $company = Company::find($array['done_by']);
                        $companies[$array['done_by']] = (object)['id' => $company->id, 'name_alias' => $company->name_alias, 'licence_no' => $company->licence_no];
                    }
                    //$array['done_by'] = $item->done_by;
                    $array['done_by_company'] = $company->name_alias;
                    $array['done_by_licence'] = $company->licence_no;
                }
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
        $actions[] = ['value' => '', 'text' => 'Select Action'];
        $actions[] = ['value' => '1', 'text' => 'Sign Off'];
        $actions[] = ['value' => '-1', 'text' => 'Mark N/A'];
        $actions2[] = ['value' => '', 'text' => 'Select Action'];
        $actions2[] = ['value' => '-1', 'text' => 'Mark N/A'];

        $json = [];
        $json[] = $items;
        $json[] = $actions;
        $json[] = $actions2;

        return $json;
        //}
    }

    /**
     * Get Companies with that can do Specific Task
     */
    public function getCompaniesForTask(Request $request, $task_id)
    {
        $trade_id = Task::find($task_id)->trade_id;
        $company_list = Auth::user()->company->companies('1')->pluck('id')->toArray();
        $companies = Company::select(['companys.id', 'companys.name', 'companys.licence_no'])->join('company_trade', 'companys.id', '=', 'company_trade.company_id')
            ->where('companys.status', '1')->where('company_trade.trade_id', $trade_id)
            ->whereIn('companys.id', $company_list)->orderBy('name')->get();

        $array = [];
        $array[] = ['value' => '', 'text' => 'Select company'];
        // Create array in specific Vuejs 'select' format.
        foreach ($companies as $company) {
            $array[] = ['value' => $company->id, 'text' => $company->name_alias, 'licence' => $company->licence_no];
        }
        $array[] = ['value' => '1', 'text' => 'Other Company (specify)'];

        return $array;
    }

    public function upcoming($super_id = 'all')
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.qa'))
            return view('errors/404');

        // Get All Active templates
        $templates = SiteQa::where('company_id', Auth::user()->company_id)->where('master', '1')->where('status', 1)->orderBy('name')->get();

        // Get list of sites
        if ($super_id == 'all')
            $site_list = Site::where('status', 1)->where('company_id', Auth::user()->company_id)->whereNull('special')->orderBy('name')->get();
        else
            $site_list = Site::where('status', 1)->where('company_id', Auth::user()->company_id)->whereNull('special')->orderBy('name')->where('supervisor_id', $super_id)->get();

        $sites = [];
        foreach ($site_list as $site) {
            $obj = (object)[];
            $obj->id = $site->id;
            $obj->name = $site->name;
            $obj->supervisor = $site->supervisor->name;

            // QA's
            $qa_array = [];
            foreach ($templates as $template) {
                if (!$site->hasTemplateQa($template->id)) {
                    $next_plan = $site->nextPlannedQa($template->id);
                    if ($next_plan)
                        $qa_array[$next_plan->from->format('Ymd')] = [
                            'date' => $next_plan->from->format('M d'),
                            'name' => $template->name,
                            'task' => $next_plan->task->code,
                            'template' => $template->id,
                        ];
                    else {
                        $tasks = implode(', ', $template->tasks()->pluck('code')->toArray());
                        $qa_array["99999999$template->name"] = ['date' => '-', 'name' => $template->name, 'task' => $tasks, 'template' => $template->id];
                    }
                }
            }
            ksort($qa_array);
            $obj->qas = $qa_array;
            $sites[] = $obj;
        }

        //ray($sites);

        return view('site/qa/upcoming', compact('super_id', 'sites', 'site_list', 'templates'));
    }

    public function triggerQa($master_id, $site_id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.qa'))
            return view('errors/404');

        $site = Site::findOrFail($site_id);

        // Create new QA by copying required template
        $qa_master = SiteQa::findOrFail($master_id);

        // Create new QA Report for Site
        $newQA = SiteQa::create([
            'name' => $qa_master->name,
            'site_id' => $site_id,
            'version' => $qa_master->version,
            'master' => '0',
            'master_id' => $qa_master->id,
            'company_id' => $qa_master->company_id,
            'status' => '1',
            'created_by' => '1',
            'updated_by' => '1',
        ]);

        // Copy items from template
        foreach ($qa_master->items as $item) {
            $newItem = SiteQaItem::create(
                ['doc_id' => $newQA->id,
                    'task_id' => $item->task_id,
                    'name' => $item->name,
                    'order' => $item->order,
                    'super' => $item->super,
                    'master' => '0',
                    'master_id' => $item->id,
                    'created_by' => '1',
                    'updated_by' => '1',
                ]);
        }
        // Create Supervisor ToDoo
        $newQA->createToDo($site->supervisor_id);

        Toastr::success("Created QA");

        return redirect('site/qa/upcoming/all');
    }
}
