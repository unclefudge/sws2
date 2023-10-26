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
use App\Models\Comms\Todo;
use App\Models\Site\Site;
use App\Models\Site\SiteNote;
use App\Models\Site\SiteNoteCategory;
use App\Models\Company\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteNoteController
 * @package App\Http\Controllers\Site
 */
class SiteNoteController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.note'))
            return view('errors/404');

        $site_id = '';
        $categories = SiteNoteCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['all' => 'All sites'] + Auth::user()->company->sites([1,2])->where('special', null)->pluck('name', 'id')->toArray();

        return view('site/note/list', compact('site_id', 'site_list', 'categories'));

    }

    /**
     * Display the specified resource.
     */
    public function showSiteNotes($site_id)
    {
        //$site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('view.site.note'))
            return view('errors/404');

        $categories = SiteNoteCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['all' => 'All sites'] + Auth::user()->company->sites([1,2])->where('special', null)->pluck('name', 'id')->toArray();

        return view('site/note/list', compact('site_id', 'site_list', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function createNote($site_id)
    {
        //$site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.site.note'))
            return view('errors/404');

        $categories = SiteNoteCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['' => 'Select site'] + Auth::user()->company->sites([1,2])->where('special', null)->pluck('name', 'id')->toArray();

        return view('site/note/create', compact('site_id', 'site_list', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.site.note'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'category_id' => 'required', 'notes' => 'required'];
        $mesg = ['site_id.required'    => 'The site field is required.',
            'category_id.required' => 'The category field is required.',
            'notes.required'  => 'The notes field is required.'];
        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        // Create Site Note
        $note = SiteNote::create(request()->all());

        return redirect("site/$note->site_id/notes");

    }

    /**
     * Edit the resource.
     */
    public function edit($id)
    {
        $note = SiteNote::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.note', $note))
        //    return view('errors/404');

        $categories = SiteNoteCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = Auth::user()->company->sites([1,2])->where('special', null)->pluck('name', 'id')->toArray();

        if ($note->status == 1)
            return view('/site/note/edit', compact('note', 'categories', 'site_list'));
        else
            return redirect("/site/note/$note->id");
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $cats = SiteNoteCategory::where('status', 1)->orderBy('order')->get();

        //dd($email_list);

        return view('site/note/settings', compact('cats'));
    }




    /**
     * Show
     */
    public function show()
    {
        //
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        if (request('add_field')) {
            $rules = ['add_field_name' => 'required'];
            $mesg = ['add_field_name.required' => 'The name field is required.'];
            request()->validate($rules, $mesg); // Validate
        }

        //dd(request()->all());
        $cats = SiteNoteCategory::where('status', 1)->get();
        // Get field values from request
        foreach ($cats as $cat) {
            if (request()->has("cat-$cat->id")) {
                if (request("cat-$cat->id")) {
                    $cat->name = request("cat-$cat->id");
                    $cat->save();
                } else
                    return back()->withErrors(["cat-$cat->id" => "The name field is required."]);
            }

        }

        // Add Extra Field
        if (request('add_field')) {
            $add_order = count($cats) + 1;
            SiteNoteCategory::create(['name' => request('add_field_name'), 'order' => $add_order, 'status' => 1]);
        }


        Toastr::success("Updated settings");

        return redirect("/site/note/settings");
    }

    /**
     *  Category Oder
     */
    public function categoryOrder($direction, $id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());
        $cat = SiteNoteCategory::findOrFail($id);

        if ($direction == 'up' && $cat->order != 1) {
            $newPos = $cat->order - 1;
            $cat2 = SiteNoteCategory::where('status', 1)->where('order', $newPos)->first();
            if ($cat2) {
                $cat2->order = $cat->order;
                $cat2->save();
                $cat->order = $newPos;
                $cat->save();
            }
        }

        $last =  SiteNoteCategory::where('status', 1)->orderByDesc('order')->first();
        if ($last && $direction == 'down' && $cat->order != $last->order) {
            $newPos = $cat->order + 1;
            $cat2 = SiteNoteCategory::where('status', 1)->where('order', $newPos)->first();
            if ($cat2) {
                $cat2->order = $cat->order;
                $cat2->save();
                $cat->order = $newPos;
                $cat->save();
            }
        }

        Toastr::success("Updated settings");

        return redirect("/site/note/settings");
    }

    /**
     *  Delete Setting
     */
    public function deleteSetting($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());

        // Delete setting
        $setting = SiteNoteCategory::findOrFail($id);
        $setting->status = 0;
        $setting->save();

        // Re-order settings
        $settings = SiteNoteCategory::where('status', 1)->orderBy('order')->get();
        $order = 1;
        foreach ($settings as $setting) {
            $setting->order = $order ++;
            $setting->save();
        }

        Toastr::success("Updated settings");

        return redirect("/site/note/settings");
    }

    /**
     * Get Site Notes + Process datatables ajax request.
     */
    public function getNotes()
    {
        $site_list = (request('site_id') == 'all') ? Auth::user()->authSites('view.site.note')->pluck('id')->toArray() : [request('site_id')];

        $records = SiteNote::select([
            'site_notes.id', 'site_notes.site_id', 'site_notes.category_id', 'site_notes.notes', 'site_notes.updated_at', 'sites.name',
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.company_id', 'site_notes_categories.id', 'site_notes_categories.name',
            DB::raw('DATE_FORMAT(site_notes.created_at, "%d/%m/%y") AS date_created'),
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name'),
            DB::raw('sites.name AS sitename')
        ])
            ->join('sites', 'sites.id', '=', 'site_notes.site_id')
            ->join('users', 'users.id', '=', 'site_notes.created_by')
            ->join('site_notes_categories', 'site_notes_categories.id', '=', 'site_notes.category_id')
            ->whereIn('site_notes.site_id', $site_list)
            ->where('site_notes.status', 1);

        $dt = Datatables::of($records)
            ->addColumn('view', function ($note) {
                return ('<div class="text-center"><a href="/site/note/' . $note->id . '/edit"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('updated_at', function ($note) {
                return $note->updated_at->format('d/m/Y');
            })
            ->editColumn('category_id', function ($note) {
                return  $note->category->name;
            })
            ->addColumn('action', function ($note) {
                if (Auth::user()->hasPermission2('edit.site.note'))
                    return '<a href="/site/note/' . $note->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                else
                    return '<a href="/site/notes/' . $note->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }

}
