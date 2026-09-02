<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;

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

        return redirect('/site/maintenance/category')->with('maintenance_category_modal', 'create');
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

        return redirect('/site/maintenance/category')
            ->with('maintenance_category_modal', 'edit')
            ->with('maintenance_category', ['id' => $cat->id, 'name' => $cat->name]);
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

        request()->validate(['create_name' => 'required']);

        SiteMaintenanceCategory::create([
            'company_id' => Auth::user()->company_id,
            'name' => request('create_name'),
        ]);

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

        request()->validate([
            '_category_id' => 'required|integer|in:' . $cat->id,
            'edit_name' => 'required',
        ]);

        $cat->update(['name' => request('edit_name')]);

        Toastr::success("Updated category");

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

        return response()->json(['message' => 'Deleted category']);
    }



    /**
     * Get maintenance categories the current user is authorised to manage and process the DataTables request.
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
                $actions = '<button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom btn-edit-category" data-category-id="' . $cat->id . '" data-category-name="' . e($cat->name) . '"><i class="fa fa-pencil"></i> Edit</button>';
                $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete" data-remote="/site/maintenance/category/' . $cat->id . '" data-name="' . e($cat->name) . '" title="Delete category" aria-label="Delete ' . e($cat->name) . '"><i class="fa fa-trash"></i></button>';
                return $actions;
            })
            ->rawColumns(['id', 'name', 'reports', 'updated_at', 'action'])
            ->make(true);

        return $dt;
    }


}
