<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Input;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteProjectSupplyItem;
use App\Models\Site\SiteProjectSupplyProduct;
use App\Models\Company\Company;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteProjectSupplyController
 * @package App\Http\Controllers\Site
 */
class SiteProjectSupplyController extends Controller {

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

        return view('site/project/supply/list');
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

        $sitelist = [];
        $sites_active = Auth::user()->authSites('view.site.project.supply', '1');
        $sites_maint = Auth::user()->authSites('view.site.project.supply', '2');

        foreach ($sites_active as $site) {
            $reg = SiteProjectSupply::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0005', '0006', '0007']))
                $sitelist[$site->id] = "$site->suburb - $site->address ($site->name)";
        }

        foreach ($sites_maint as $site) {
            $reg = SiteProjectSupply::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0005', '0006', '0007']))
                $sitelist[$site->id] = "$site->suburb - $site->address ($site->name)";
        }

        $lockup = [1, 2, 3, 4, 5, 6, 7, 8];
        $fixout = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31];

        $products = SiteProjectSupplyProduct::where('status', '1')->whereIn('id', $lockup)->get();
        //$products = SiteProjectSupplyProduct::where('status', '1')->orderBy('order')->get();

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

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.project.supply', $project))
        //    return view('errors/404');

        return view('site/project/supply/show', compact('project'));
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

        return view('site/project/supply/edit', compact('project'));

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

        // Create Site Project
        if ($project) {
            // Increment major version
            list($major, $minor) = explode('.', $project->version);
            $major ++;
            $project->version = $major . '.0';
        } else
            $project = SiteProjectSupply::create(['site_id' => request('site_id'), 'version' => '1.0']);

        // Create Item
        if ($project) {
            $maxID = SiteProjectSupplyProduct::all()->count();

            for ($i = 1; $i <= $maxID; $i ++) {
                //if (request("supplier-$i") || request("type-$i") || request("colour-$i") || request("notes-$i")) {
                echo "$i " . request("supplier-$i");
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
                                                                       'supplier'  => request("supplier-$i"), 'type' => request("type-$i"), 'colour' => request("colour-$i"),]));
                }
                //}
            }
        }
        //dd(request()->all());

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
        $major ++;
        $project->version = $major . '.0';

        //dd(request()->all());

        foreach ($project->items as $item) {
            if (request("product-$item->id") || request("supplier-$item->id") || request("type-$item->id") || request("colour-$item->id") || request("notes-$item->id")) {
                $item->product = request("product-$item->id");
                $item->supplier = request("supplier-$item->id");
                $item->type = request("type-$item->id");
                $item->colour = request("colour-$item->id");
                $item->save();
            }
        }
        //dd(request()->all());

        // Create PDF
        $project->attachment = $this->createPDF($project->id);
        $project->save();


        Toastr::success("Updated project");

        return redirect("/site/supply/$project->id");
    }

    /**
     * Delete Item
     */
    public function deleteItem($id)
    {
        $asbItem = SiteAsbestosRegisterItem::findOrFail($id);
        $asb = SiteAsbestosRegister::findOrFail($asbItem->register_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.project.supply', $asb)))
            return view('errors/404');

        $asbItem->delete();

        // Increment major version
        list($major, $minor) = explode('.', $asb->version);
        $major ++;
        $asb->version = $major . '.0';
        $asb->save();

        return redirect("/site/project/supply/$asb->id");
    }

    public function createPDF($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Set + create create directory if required
        $path = "filebank/site/$project->site_id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        $filename = "Project Supply Infomation-" . $project->site->code . ".pdf";

        //
        // Generate PDF
        //
        return view('pdf/site/supply-info', compact('project'));
        //return PDF::loadView('pdf/site/supply-info', compact('project'))->setPaper('a4', 'landscape')->stream();
        $pdf = PDF::loadView('pdf/site/supply-info', compact('project'));
        $pdf->setPaper('A4');
        $pdf->save(public_path("$path/$filename"));

        return $filename;
    }

    /**
     * Get Asbestos Reports current user is authorised to manage + Process datatables ajax request.
     */
    public function getReports()
    {
        $site_list = Auth::user()->authSites('view.site.project.supply')->pluck('id')->toArray();
        $status = (request('status') == 0) ? [0] : [1, 2];
        $records = DB::table('project_supply AS p')
            ->select(['p.id', 'p.site_id', 'p.attachment', 'p.status', 'p.updated_at',
                's.name as sitename', 's.code'])
            ->join('sites AS s', 'p.site_id', '=', 's.id')
            ->whereIn('p.site_id', $site_list)
            ->whereIn('s.status', $status);

        $dt = Datatables::of($records)
            ->editColumn('id', function ($project) {
                $pro = SiteProjectSupply::find($project->id);

                return ($pro->attachment_url) ? '<div class="text-center"><a href="' . $pro->attachment_url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>' : '';
                //return '<div class="text-center"><a href="' . $asb->attachment_url . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('updated_at', function ($project) {
                return (new Carbon($project->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($project) {
                return '<a href="/site/supply/' . $project->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
