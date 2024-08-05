<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Misc\CategoryController;
use App\Models\Misc\Attachment;
use App\Models\Misc\Category;
use App\Models\Site\SiteNote;
use App\Models\Site\SiteNoteCategory;
use App\Models\Site\SiteNoteCost;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteNoteController
 * @package App\Http\Controllers\Site
 */
class SiteNoteController extends Controller
{

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

        $site_id = 'all';
        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['all' => 'All sites'] + Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();

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

        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['all' => 'All sites'] + Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();

        return view('site/note/list', compact('site_id', 'site_list', 'categories'));
    }


    /**
     * Show
     */
    public function show($id)
    {
        $note = SiteNote::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('view.site.note'))
            return view('errors/404');

        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['all' => 'All sites'] + Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();

        return view('site/note/show', compact('note', 'site_list', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function createNote($site_id)
    {
        //$site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('add.site.note'))
            return view('errors/404');

        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $cost_centres = Category::where('type', 'site_note_cost')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['' => 'Select site'] + Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();
        $site_list = ['' => 'Select site'] + Auth::user()->authSites('view.site.note', [1, 2])->pluck('name', 'id')->toArray();

        return view('site/note/create', compact('site_id', 'site_list', 'categories', 'cost_centres'));
    }

    public function createNoteFrom($id)
    {
        $existing = SiteNote::findOrFail($id);
        $site_id = $existing->site_id;

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('add.site.note'))
            return view('errors/404');

        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $cost_centres = Category::where('type', 'site_note_cost')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = ['' => 'Select site'] + Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();
        $site_list = ['' => 'Select site'] + Auth::user()->authSites('view.site.note', [1, 2])->pluck('name', 'id')->toArray();

        return view('site/note/createFrom', compact('site_id', 'site_list', 'categories', 'cost_centres', 'existing'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('add.site.note'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'category_id' => 'required'];
        $mesg = ['site_id.required' => 'The site field is required.',
            'category_id.required' => 'The category field is required.',
            'notes.required' => 'The notes field is required.',
            'costing_extra_credit.required' => 'The costing credit/extra field is required.',
            'costing_item.required' => 'The new item/in lieu field is required.',
            'costing_room.required' => 'The room field is required.',
            'costing_location.required' => 'The location days field is required.',
            'costing_priority.required' => 'The priority field is required.',
            'variation_name.required' => 'The name field is required.',
            'variation_info.required' => 'The description field is required.',
            'variation_net.required' => 'The net cost field is required.',
            'variation_cost.required' => 'The gross cost field is required.',
            'variation_days.required' => 'The days field is required.',
            'cc-1.required' => 'The cost centre item 1 required'
        ];

        // Costing Request
        if (request('category_id') == 15)
            $rules = $rules + ['costing_extra_credit' => 'required', 'costing_item' => 'required', 'costing_room' => 'required', 'costing_location' => 'required', 'costing_priority' => 'required', 'notes' => 'required'];

        // Variations
        elseif (in_array(request('category_id'), [16, 19])) { // Approved / For Issue to Client
            $rules = $rules + ['variation_name' => 'required', 'variation_info' => 'required', 'variation_net' => 'required', 'variation_cost' => 'required', 'variation_days' => 'required', 'cc-1' => 'required'];
            for ($i = 1; $i <= 20; $i++) {
                if (request("cc-$i")) {
                    $rules = $rules + ["cinfo-$i" => 'required'];
                    $mesg = $mesg + ["cc-$i.required" => "The cost centre item $i required", "cinfo-$i.required" => "The variation item $i details required"];
                }
            }
        } elseif (request('category_id') == 20) { // TBA Site Variations
            $rules = $rules + ['variation_name' => 'required', 'variation_info' => 'required', 'notes' => 'required'];
        } else {
            $rules = $rules + ['notes' => 'required'];
        }


        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        $note_request = request()->all();

        if (request('variation_extra_credit'))
            $note_request['costing_extra_credit'] = request('variation_extra_credit');

        // Create Site Note
        $note = SiteNote::create($note_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_notes', 'table_id' => $note->id, 'directory' => "/filebank/site/$note->site_id/note"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        // Create Variations Cost Items for Approved/For Issue
        if (in_array(request('category_id'), [16, 19])) {
            //$notes = "Cost Centres & Item Details\n-------------------------------\n";
            for ($i = 1; $i <= 20; $i++) {
                if (request("cc-$i") && request("cinfo-$i")) {
                    $item = SiteNoteCost::create(['note_id' => $note->id, 'cost_id' => request("cc-$i"), 'details' => request("cinfo-$i")]);
                    //$cost = Category::find(request("cc-$i"));
                    //$notes .= "$cost->name: " . request("cinfo-$i") . "\n";
                }
            }
            // Prepend CostCentres to notes
            //$note->notes = "$notes\n$note->notes";
            //$note->save();
        }

        //dd(request()->all());
        // Email note
        $note->emailNote();

        $previous_url = parse_url(request('previous_url'));
        if (preg_match("/notes/", $previous_url['path']) || preg_match("/site\/note/", $previous_url['path']))
            return redirect("site/$note->site_id/notes");
        else
            return redirect("site/$note->site_id");

        //if (preg_match("/\/site\/\d/", $previous_url['path']))
        //    return redirect("site/$note->site_id");
        //else
        //    return redirect("site/$note->site_id/notes");

    }

    /**
     * Edit the resource.
     */
    public function edit($id)
    {
        $note = SiteNote::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.note', $note))
            return view('errors/404');

        $categories = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $site_list = Auth::user()->authSites('view.site.note', [1, 2])->where('special', null)->pluck('name', 'id')->toArray();

        if ($note->status == 1)
            return view('/site/note/edit', compact('note', 'categories', 'site_list'));
        else
            return redirect("/site/note/$note->id");
    }

    /**
     * Updateresource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $note = SiteNote::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.note', $note))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'category_id' => 'required', 'notes' => 'required'];

        if (request('category_id') == 15) {
            $rules = $rules + ['costing_extra_credit' => 'required', 'costing_item' => 'required', 'costing_room' => 'required', 'costing_location' => 'required'];
        }
        if (request('category_id') == 16) {
            $rules = $rules + ['variation_name' => 'required', 'variation_info' => 'required', 'variation_cost' => 'required', 'variation_days' => 'required'];
        }
        $mesg = ['site_id.required' => 'The site field is required.',
            'category_id.required' => 'The category field is required.',
            'notes.required' => 'The notes field is required.',
            'costing_extra_credit.required' => 'The costing credit/extra field is required.',
            'costing_item.required' => 'The costing new item/in lieu field is required.',
            'costing_room.required' => 'The costing room field is required.',
            'costing_location.required' => 'The costing location days field is required.',
            'variation_name.required' => 'The variation name field is required.',
            'variation_info.required' => 'The variation description field is required.',
            'variation_cost.required' => 'The variation cost field is required.',
            'variation_days.required' => 'The variation days field is required.'
        ];
        request()->validate($rules, $mesg); // Validate

        // Update Site Note
        $note->update(request()->all());


        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_notes', 'table_id' => $note->id, 'directory' => "/filebank/site/$note->site_id/note"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        return redirect("site/$note->site_id/notes");

    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $note = SiteNote::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.site.note", $note))
            return json_encode("failed");

        $note->delete();

        return json_encode('success');
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

        $cats = Category::where('type', 'site_note')->where('status', 1)->orderBy('order')->get();

        return view('site/note/settings-categories', compact('cats'));
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());
        CategoryController::updateCategories('site_note', $request);

        Toastr::success("Updated categories");

        return redirect(url()->previous());
    }

    public function costCentres()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        $cats = Category::where('type', 'site_note_cost')->where('status', 1)->orderBy('order')->get();

        return view('site/note/settings-costcentres', compact('cats'));
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCostCentres(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());
        CategoryController::updateCategories('site_note_cost', $request);

        Toastr::success("Updated categories");

        return redirect(url()->previous());
    }


    /**
     * Get Site Notes + Process datatables ajax request.
     */
    public function getNotes()
    {
        $site_list = (request('site_id') == 'all') ? Auth::user()->authSites('view.site.note')->pluck('id')->toArray() : [request('site_id')];

        $records = SiteNote::select([
            'site_notes.id', 'site_notes.site_id', 'site_notes.category_id', 'site_notes.notes', 'site_notes.parent', 'site_notes.updated_at', 'site_notes.created_at', 'site_notes.created_by', // 'sites.name',
            'users.username', 'users.firstname', 'users.lastname', 'categories.name',
            DB::raw('DATE_FORMAT(site_notes.created_at, "%d/%m/%y") AS date_created'),
            DB::raw('DATE_FORMAT(site_notes.updated_at, "%d/%m/%y") AS date_updated'),
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name'),
            DB::raw('sites.name AS sitename')
        ])
            ->join('sites', 'sites.id', '=', 'site_notes.site_id')
            ->join('users', 'users.id', '=', 'site_notes.created_by')
            ->join('categories', 'categories.id', '=', 'site_notes.category_id')
            ->whereIn('site_notes.site_id', $site_list)
            ->where('site_notes.parent', null)
            ->where('site_notes.status', 1);

        $dt = Datatables::of($records)
            ->editColumn('id', function ($note) {
                return ('<div class="text-center"><a href="/site/note/' . $note->id . '">' . $note->id . '</a></div>');
            })
            ->editColumn('updated_at', function ($note) {
                return $note->updated_at->format('d/m/Y');
            })
            ->editColumn('category_id', function ($note) {
                return $note->category->name;
            })
            ->editColumn('notes', function ($note) {
                $string = truncate(nl2br($note->notes));

                if ($note->attachments()->count()) {
                    $string .= "<br><b>Attachments:</b><br>";
                    foreach ($note->attachments() as $attachment) {
                        $string .= "<a href='$attachment->url' target='_blank'>$attachment->name<br>";
                    }
                }
                return $string;
            })
            ->addColumn('action', function ($note) {
                $actions = '';
                //if (Auth::user()->allowed2('edit.site.note', $note))
                //    $actions .= '<a href="/site/note/' . $note->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if (Auth::user()->hasPermission2("del.site.note"))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/note/' . $note->id . '" data-name="' . $note->site->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'notes', 'action', 'id'])
            ->make(true);

        return $dt;
    }

}
