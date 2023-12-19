<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteProjectSupplyItem;
use App\Models\Site\SiteProjectSupplyProduct;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteProjectSupplyController
 * @package App\Http\Controllers\Site
 */
class SiteProjectSupplyController extends Controller
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

        return view('site/project/supply/list', compact('signoff'));
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createItem($id)
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.project.supply'))
            return view('errors/404');

        $asb = SiteAsbestosRegister::findOrFail($id);

        return view('site/project/supply/createItem', compact('asb'));
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = SiteProjectSupply::findOrFail($id);
        $title = SiteProjectSupplyProduct::findOrFail(1);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.project.supply', $project))
        //    return view('errors/404');

        return view('site/project/supply/show', compact('project', 'title'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.project.supply'))
            return view('errors/404');

        $title = SiteProjectSupplyProduct::findOrFail(1);
        $products = SiteProjectSupplyProduct::where('status', '1')->where('id', '>', 2)->orderBy('order')->get();

        return view('site/project/supply/settings', compact('title', 'products'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.project.supply', $project)))
            return view('errors/404');

        $title = SiteProjectSupplyProduct::find(1);

        return view('site/project/supply/edit', compact('project', 'title'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.project.supply'))
            return view('errors/404');

        $rules = ['site_id' => 'required'];
        $mesg = ['site_id.required' => 'The site field is required.'];
        request()->validate($rules, $mesg); // Validate

        $project = SiteProjectSupply::where('site_id', request('site_id'))->first();
        //dd(request()->all());

        // Create Site Project
        if ($project) {
            // Increment major version
            list($major, $minor) = explode('.', $project->version);
            $major++;
            $project->version = $major . '.0';
        } else
            $project = SiteProjectSupply::create(['site_id' => request('site_id'), 'version' => '1.0']);

        // Create Item
        if ($project) {
            $maxID = SiteProjectSupplyProduct::all()->count();

            for ($i = 3; $i <= $maxID; $i++) {
                $item = SiteProjectSupplyItem::where('supply_id', $project->id)->where('product_id', $i)->first();
                $product = SiteProjectSupplyProduct::findOrFail($i);

                if ($item) {
                    $item->product = $product->name;
                    $item->supplier = request("supplier-$i");
                    $item->type = request("type-$i");
                    $item->colour = request("colour-$i");
                    $item->save();
                } else {
                    $project->items()->save(new SiteProjectSupplyItem(['supply_id' => $project->id, 'product_id' => $i, 'product' => $product->name,
                        'supplier' => request("supplier-$i"), 'type' => request("type-$i"), 'colour' => request("colour-$i"),]));
                }
            }
        }

        // New Special items
        for ($i = 1; $i <= 5; $i++) {
            if (request("product-s$i") || request("supplier-s$i") || request("type-s$i") || request("colour-s$i") || request("notes-s$i")) {
                $project->items()->save(new SiteProjectSupplyItem(['supply_id' => $project->id, 'product_id' => 2, 'product' => request("product-s$i"),
                    'supplier' => request("supplier-s$i"), 'type' => request("type-s$i"), 'colour' => request("colour-s$i"),]));
            }
        }

        // Create PDF
        $project->attachment = $this->createPDF($project->id);
        $project->save();


        Toastr::success("Created project");

        return redirect("/site/supply/$project->id");
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.project.supply', $project))
            return view('errors/404');

        //$rules = ['site_id' => 'required'];
        //$mesg = ['site_id.required' => 'The site field is required.'];
        //request()->validate($rules, $mesg); // Validate


        // Increment major version
        list($major, $minor) = explode('.', $project->version);
        $major++;
        $project->version = $major . '.0';

        //dd(request()->all());

        // Existing items
        foreach ($project->items as $item) {
            if (request("product-$item->id") || request("supplier-$item->id") || request("type-$item->id") || request("colour-$item->id") || request("notes-$item->id")) {
                if (request("product-$item->id") == "DELETE-ITEM")
                    $item->delete();
                else {
                    $item->product = request("product-$item->id");
                    $item->supplier = request("supplier-$item->id");
                    $item->type = request("type-$item->id");
                    $item->colour = request("colour-$item->id");
                    $item->save();
                }
            }
        }

        // New Special items
        for ($i = 1; $i <= 5; $i++) {
            if (request("product-s$i") || request("supplier-s$i") || request("type-s$i") || request("colour-s$i") || request("notes-s$i")) {
                $project->items()->save(new SiteProjectSupplyItem(['supply_id' => $project->id, 'product_id' => 2, 'product' => request("product-s$i"),
                    'supplier' => request("supplier-s$i"), 'type' => request("type-s$i"), 'colour' => request("colour-s$i"),]));
            }
        }
        //dd(request()->all());

        // Create PDF
        $project->attachment = $this->createPDF($project->id);
        $project->save();

        // Close outstanding tasks
        $project->closeToDo(Auth::user());

        // If all items completed send Supervisor ToDoo task
        if ($project->items->count() == $project->itemsCompleted()->count())
            $project->createSignOffToDo($project->site->supervisor_id);

        Toastr::success("Updated project");

        return redirect("/site/supply/$project->id/edit");
    }

    /**
     * Sign Off Item
     */
    public function signoff($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.project.supply', $project))
            return view('errors/404');

        if (!$project->supervisor_sign_by) {
            $project->supervisor_sign_by = Auth::user()->id;
            $project->supervisor_sign_at = Carbon::now();

            // Send Con Mgr ToDoo task to sign off
            $project->closeToDo();
            $project->createSignOffToDo(array_merge(getUserIdsWithRoles('con-construction-manager'), [108]));
        } else {
            $project->closeToDo();
            $project->manager_sign_by = Auth::user()->id;
            $project->manager_sign_at = Carbon::now();
            $project->status = 0;

            // Email completion
            $email_list = (\App::environment('prod')) ? ['michelle@capecod.com.au', 'kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
            $report_file = ($project->attachment) ? public_path($project->attachmentUrl) : '';
            if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteProjectSupplyCompleted($project, $report_file));
        }
        $project->save();


        if ($project->status)
            return redirect("/site/supply/$project->id/edit");

        return redirect("/site/supply/$project->id");

    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.project.supply'))
            return view('errors/404');

        //dd(request()->all());
        $title = SiteProjectSupplyProduct::find(1);
        $title->name = request('title-product');
        $title->supplier = request('title-supplier');
        $title->type = request('title-type');
        $title->colour = request('title-colour');
        $title->save();

        $products = SiteProjectSupplyProduct::where('status', '1')->where('id', '>', 2)->orderBy('order')->get();
        foreach ($products as $product) {
            $product->supplier = request("supplier-$product->id");
            $product->type = request("type-$product->id");
            $product->colour = request("colour-$product->id");
            $product->save();
        }

        Toastr::success("Updated settings");

        return view('site/project/supply/settings', compact('title', 'products'));
    }

    public function createPDF($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Set + create create directory if required
        $path = "filebank/site/$project->site_id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        $filename = $project->site->name . "-Project Information Sheet.pdf";

        //
        // Generate PDF
        //
        //return view('pdf/site/supply-info', compact('project'));
        //return PDF::loadView('pdf/site/supply-info', compact('project'))->setPaper('a4')->stream();
        $pdf = PDF::loadView('pdf/site/supply-info', compact('project'));
        $pdf->setPaper('A4');
        $pdf->save(public_path("$path/$filename"));

        return $filename;
    }

    /**
     * Get Project Supply current user is authorised to manage + Process datatables ajax request.
     */
    public function getReports()
    {
        if (request('supervisor_sel')) {
            if (request('supervisor') == 'all')
                $site_list = Site::all()->pluck('id')->toArray();
            elseif (request('supervisor') == 'signoff') {
                $site_list = Auth::user()->authSites('view.site.project.supply')->pluck('id')->toArray();
                $project_list = SiteProjectSupply::where('status', 1)->whereNot('supervisor_sign_by', null)->whereIn('site_id', $site_list)->pluck('id')->toArray();
            } else
                $site_list = Site::where('supervisor_id', request('supervisor'))->pluck('id')->toArray();
        } else
            $site_list = Auth::user()->authSites('view.site.qa')->pluck('id')->toArray();


        //$site_list = Auth::user()->authSites('view.site.project.supply')->pluck('id')->toArray();
        $status = (request('status') == 0) ? [0] : [1];

        if (request('supervisor') != 'signoff') {
            $records = DB::table('project_supply AS p')
                ->select(['p.id', 'p.site_id', 'p.attachment', 'p.updated_at', 'p.status', 's.name as sitename', 's.code'])
                ->join('sites AS s', 'p.site_id', '=', 's.id')
                ->whereIn('p.site_id', $site_list)
                ->whereIn('p.status', $status);
        } else {
            $records = DB::table('project_supply AS p')
                ->select(['p.id', 'p.site_id', 'p.attachment', 'p.updated_at', 'p.status', 's.name as sitename', 's.code'])
                ->join('sites AS s', 'p.site_id', '=', 's.id')
                ->whereIn('p.id', $project_list)
                ->whereIn('p.status', $status);
        }

        $dt = Datatables::of($records)
            ->editColumn('id', function ($project) {
                $pro = SiteProjectSupply::find($project->id);

                return ($pro->attachment_url) ? '<div class="text-center"><a href="' . $pro->attachment_url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>' : '';
            })
            ->editColumn('updated_at', function ($project) {
                return (new Carbon($project->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($project) {
                //$proj = SiteProjectSupply::findOrFail($project->id);
                if (Auth::user()->hasPermission2('edit.site.project.supply'))
                    return '<a href="/site/supply/' . $project->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                else
                    return '<a href="/site/supply/' . $project->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
