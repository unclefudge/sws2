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
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\Planner\SitePlanner;
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
 * Class SiteMaintenanceCategoryController
 * @package App\Http\Controllers\Site
 */
class SiteMaintenanceCategoryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        return view('site/maintenance/category/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        return view('site/maintenance/category/create');
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

        return view('site/maintenance/show', compact('main'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cat = SiteMaintenanceCategory::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        return view('site/maintenance/category/edit', compact('cat'));
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

        request()->validate(['name' => 'required']); // Validate

        // Create Site QA Category
        SiteMaintenanceCategory::create(request()->all());

        Toastr::success("Created new category");

        return redirect('/site/maintenance/category');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $cat = SiteMaintenanceCategory::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        request()->validate(['name' => 'required']); // Validate

        $cat->update(request()->all());

        Toastr::success("Updated Categoy");

        return redirect('site/maintenance/category');
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cat = SiteMaintenanceCategory::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.maintenance'))
            return view('errors/404');

        $cat->delete();

        return json_encode('success');
    }



    /**
     * Get QA templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getMainCategories()
    {
        $records = SiteMaintenanceCategory::where('status', 1)->orderBy('name');

        $dt = Datatables::of($records)
            ->addColumn('reports', function ($cat) {
                $reports = implode(', ',$cat->reports->where('master', 1)->where('status', 1)->pluck('name')->toArray());
                return $reports;
            })
            ->addColumn('action', function ($cat) {
                $actions = '<a href="/site/maintenance/category/' . $cat->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/maintenance/category/' . $cat->id . '" data-name="' . $cat->name . '"><i class="fa fa-trash"></i></button>';
                return $actions;
            })
            ->rawColumns(['id', 'name', 'reports', 'updated_at', 'action'])
            ->make(true);

        return $dt;
    }


}
