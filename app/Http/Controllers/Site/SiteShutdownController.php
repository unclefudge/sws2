<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteProjectSupplyProduct;
use App\Models\Site\SiteShutdown;
use App\Models\Site\SiteShutdownItem;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteProjectSupplyController
 * @package App\Http\Controllers\Site
 */
class SiteShutdownController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.project.supply'))
            return view('errors/404');

        $signoff = false;

        return view('site/shutdown/list', compact('signoff'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.project.supply'))
            return view('errors/404');

        $sitelist = ['' => 'Select site'];
        $sites_active = Auth::user()->authSites('view.site.project.supply', '1');
        $sites_maint = Auth::user()->authSites('view.site.project.supply', '2');

        foreach ($sites_active as $site) {
            $reg = SiteProjectSupply::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0003', '0005', '0006', '0007']))
                $sitelist[$site->id] = "$site->name";
        }

        foreach ($sites_maint as $site) {
            $reg = SiteProjectSupply::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0003', '0005', '0006', '0007']))
                $sitelist[$site->id] = "$site->name";
        }

        $lockup = [32, 33, 3, 4, 5, 6, 7, 8];
        $fixout = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31];

        $products = SiteProjectSupplyProduct::where('status', '1')->whereIn('id', $lockup)->get();
        $products = SiteProjectSupplyProduct::where('status', '1')->where('id', '>', 2)->orderBy('order')->get();

        return view('site/project/supply/create', compact('sitelist', 'products'));
    }

    public function show($id)
    {
        $shutdown = SiteShutdown::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.project.supply', $shutdown))
            return view('errors/404');

        return view('site/shutdown/show', compact('shutdown'));
    }

    public function edit($id)
    {
        $shutdown = SiteShutdown::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.project.supply', $shutdown)))
            return view('errors/404');

        $title = SiteProjectSupplyProduct::find(1);

        return view('site/shutdown/edit', compact('shutdown'));

    }


    public function update($id)
    {
        $shutdown = SiteShutdown::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.project.supply', $shutdown))
            return view('errors/404');

        //dd(request()->all());

        // Existing items
        foreach ($shutdown->items as $item) {
            $item->response = null;
            if (request("resp-$item->order"))
                $item->response = request("resp-$item->order");
            $item->save();
        }
        $shutdown->save();

        Toastr::success("Updated project");

        return redirect("/site/shutdown/$shutdown->id/edit");
    }

    /**
     * Sign Off Item
     */
    public function signoff($id)
    {
        $shutdown = SiteShutdown::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.project.supply', $shutdown))
            return view('errors/404');

        if (!$shutdown->supervisor_sign_by) {
            $shutdown->supervisor_sign_by = Auth::user()->id;
            $shutdown->supervisor_sign_at = Carbon::now();

            // Send Con Mgr ToDoo task to sign off
            $shutdown->closeToDo();
            $shutdown->createSignOffToDo([108]); // Kirstie
        } else {
            $shutdown->closeToDo();
            $shutdown->manager_sign_by = Auth::user()->id;
            $shutdown->manager_sign_at = Carbon::now();
            $shutdown->status = 0;
        }
        $shutdown->save();

        if ($shutdown->status)
            return redirect("/site/shutdown/$shutdown->id/edit");

        return redirect("/site/shutdown/$shutdown->id");

    }

    public function initialise()
    {
        if (Auth::user()->id == 3) {
            echo "<h1>Site Shutdown Initialsation</h1>";
            $activeSites = Site::whereIn('status', [1])->where('company_id', 3)->whereNull('special')->get();
            //$activeSites = Site::where('id', '537')->where('company_id', 3)->get();
            foreach ($activeSites as $site) {
                echo "Creating shutdown for: $site->name<br>";
                // Create Shutdown for each Active site
                $shutdown = SiteShutdown::create(['site_id' => $site->id, 'super_id' => $site->supervisor_id]);

                // Shutdown Questions
                $order = 1;
                $category = "General";
                $subcategory = "Site Information, Public Protection & Site Security";
                $q1 = SiteShutdownItem::create(['name' => 'Is the site or construction area(s) adequately secured against entry by unauthorised persons? (including the Client if residing/accessing the property during the shutdown period', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q2 = SiteShutdownItem::create(['name' => 'Means of securing site', 'type' => 'text', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q3 = SiteShutdownItem::create(['name' => 'Are Public areas unobstructed and/or adequately protected?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q4 = SiteShutdownItem::create(['name' => 'Principal Contractor signage and emergency contact details displayed and clearly visible from outside the workplace?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q5 = SiteShutdownItem::create(['name' => 'Does the Client (or others) reside on the property or part there of for the duration of or at any time during the shutdown?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $subcategory = "General";
                $q6 = SiteShutdownItem::create(['name' => 'Has waste removal been scheduled/taken place in preparation for the shutdown period?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q7 = SiteShutdownItem::create(['name' => 'Are roof tarps secured?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q8 = SiteShutdownItem::create(['name' => 'Is deck polyfabric secured?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q9 = SiteShutdownItem::create(['name' => 'Are materials stored appropriately? (i.e. in a manner that does not obstruct walkways/activities, create a fire hazard or risk the degradation of the materials?)', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $category = "Key Risk Areas";
                $subcategory = "Electrical";
                $q10 = SiteShutdownItem::create(['name' => 'Has electricity been appropriately terminated/isolated or reinstated in reference to the work taken place and site requirements over the shutdown period?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $subcategory = "Pools";
                $q11 = SiteShutdownItem::create(['name' => 'Are pools secured against access to requirements (i.e. all fencing reinstated, appropriate signage', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q12 = SiteShutdownItem::create(['name' => 'Have all excavations been suitably filled or barricaded?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $subcategory = "Scaffolds";
                $q13 = SiteShutdownItem::create(['name' => 'Have material/containment screens affixed to scaffold/roof rail/elevated work areas to arrest the free fall of objects to area below been removed so as to mitigate against wind/rain loads whilst construction activities are suspended?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q14 = SiteShutdownItem::create(['name' => 'Are scaffolds/work platforms secured from unauthorised access?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);
                $q15 = SiteShutdownItem::create(['name' => 'Are scaffolds/work platforms adequately tied in and complete to ensure stability?', 'type' => 'yn', 'shutdown_id' => $shutdown->id, 'category' => $category, 'sub_category' => $subcategory, 'order' => $order++]);

                // Create Supervisor ToDoo
                $shutdown->createSupervisorToDo($site->supervisor_id);
            }
        } else {
            echo "Not authorised";
        }
    }

    public function reminder()
    {
        $shutdowns = SiteShutdown::where('status', 1)->whereNull('supervisor_sign_by')->get();
        $super_list = [];
        // Create List of Supervisors assigned to which Active Sites
        foreach ($shutdowns as $shutdown) {
            $super_id = $shutdown->super_id;
            if (!isset($super_list[$super_id]))
                $super_list[$super_id] = [$shutdown->site_id];
            else
                $super_list[$super_id][] = $shutdown->site_id;
        }

        foreach ($super_list as $super_id => $site_array) {
            $super = User::findOrFail($super_id);
            $site_list = '';
            foreach ($site_array as $site_id) {
                $site = Site::findOrFail($site_id);
                $site_list .= "- $site->name\n";
            }

            $superActiveSites = SiteShutdown::where('status', 1)->where('super_id', $super_id)->whereNull('supervisor_sign_by')->get();
            if ($superActiveSites->count()) {
                echo "- $super->fullname<br>";

                // Send email to supervisor
                $email_list = (\App::environment('prod')) ? [$super->email] : [env('EMAIL_DEV')];
                $email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au', 'fudge@jordan.net.au'] : [env('EMAIL_DEV')];
                if ($email_list && $email_cc) Mail::to($email_list)->cc($email_cc)->send(new \App\Mail\Site\SiteShutdownReminder($site_list));
            }
        }
    }


    /**
     * Get Project Supply current user is authorised to manage + Process datatables ajax request.
     */
    public function getReports()
    {
        if (request('supervisor_sel')) {
            if (request('supervisor') == 'all')
                $site_list = SiteShutdown::all()->pluck('site_id')->toArray();
            elseif (request('supervisor') == 'signoff') {
                $site_list = Auth::user()->authSites('view.site.project.supply')->pluck('id')->toArray();
                $project_list = SiteShutdown::where('status', 1)->whereNot('supervisor_sign_by', null)->whereIn('site_id', $site_list)->pluck('id')->toArray();
            } else
                $site_list = Site::where('supervisor_id', request('supervisor'))->pluck('id')->toArray();
        } else
            $site_list = SiteShutdown::where('super_id', Auth::user()->id)->pluck('site_id')->toArray();


        //$site_list = Auth::user()->authSites('view.site.project.supply')->pluck('id')->toArray();
        $status = (request('status') == 0) ? [0] : [1];

        if (request('supervisor') != 'signoff') {
            $records = DB::table('site_shutdown AS p')
                ->select(['p.id', 'p.site_id', 'p.super_id', 'p.updated_at', 'p.status', 's.name as sitename', 's.code'])
                ->join('sites AS s', 'p.site_id', '=', 's.id')
                ->whereIn('p.site_id', $site_list)
                ->whereIn('p.status', $status);
        } else {
            $records = DB::table('site_shutdown AS p')
                ->select(['p.id', 'p.site_id', 'p.super_id', 'p.updated_at', 'p.status', 's.name as sitename', 's.code'])
                ->join('sites AS s', 'p.site_id', '=', 's.id')
                ->whereIn('p.id', $project_list)
                ->whereIn('p.status', $status);
        }

        $dt = Datatables::of($records)
            ->editColumn('id', function ($shutdown) {
                if ($shutdown->status)
                    return '<div class="text-center"><a href="/site/shutdown/' . $shutdown->id . '/edit"><i class="fa fa-search"></i></a></div>';
                else
                    return '<div class="text-center"><a href="/site/shutdown/' . $shutdown->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('super_id', function ($shutdown) {
                $s = SiteShutdown::find($shutdown->id);
                return $s->supervisor->name;
            })
            ->addColumn('completed', function ($shutdown) {
                $shut = SiteShutdown::find($shutdown->id);
                $total = $shut->items()->count();
                $completed = $shut->itemsCompleted()->count();
                $pending = '';
                if ($shut->status != 0) {
                    if (Auth::user()->allowed2('edit.site.project.supply', $shut)) {
                        if ($total == $completed && $total != 0) {
                            $label_type = ($shut->supervisor_sign_by && $shut->manager_sign_by) ? 'label-success' : 'label-warning';
                            $label_type = 'label-warning';
                            if (!$shut->supervisor_sign_by)
                                $pending = '<br><span class="badge badge-info badge-roundless pull-right">Pending Supervisor</span>';
                            elseif (!$shut->manager_sign_by)
                                $pending = '<br><span class="badge badge-primary badge-roundless pull-right">Pending Manager</span>';
                        } else
                            $label_type = 'label-danger';

                        return '<span class="label pull-right ' . $label_type . '">' . $completed . ' / ' . $total . '</span>' . $pending;
                    }
                }
                return '<span class="label pull-right label-success">' . $completed . ' / ' . $total . '</span>';
            })
            ->editColumn('updated_at', function ($shutdown) {
                return (new Carbon($shutdown->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($shutdown) {
                //$proj = SiteProjectSupply::findOrFail($project->id);
                if (Auth::user()->hasPermission2('edit.site.project.supply') && $shutdown->status)
                    return '<a href="/site/shutdown/' . $shutdown->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                else
                    return '<a href="/site/shutdown/' . $shutdown->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'action', 'completed'])
            ->make(true);

        return $dt;
    }
}
