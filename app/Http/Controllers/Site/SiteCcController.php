<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Category;
use App\Models\Site\Site;
use App\Models\Site\SiteCc;
use App\Models\Site\SiteCcItem;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;

class SiteCcController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check() && Auth::user()->hasPermission2('view.site.cc'), 403);
            return $next($request);
        });
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation
        if (!Auth::user()->hasAnyPermissionType('site.cc'))
            abort(403);

        $stageOptions = $this->ccStageOptions();
        $selectedStage = session('cc.stage', 'Upcoming');

        return view('site/cc/list', compact('stageOptions', 'selectedStage'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cc = SiteCc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('view.site.cc', $cc))
            abort(403);

        return view('site/cc/show', compact('cc'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cc = SiteCc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.cc', $cc))
            abort(403);

        return view('site/cc/show', compact('cc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation
        if (!Auth::user()->allowed2('add.site.cc'))
            abort(403);

        $rules = ['site_id' => 'required|integer|exists:sites,id', 'item1' => 'required|string',
            'certifier' => 'nullable|string',
            'certifier_phone' => 'nullable|string',
            'pca_name' => 'nullable|string',
            'pca_email' => 'nullable|string',
            'inspections_email' => 'nullable|string',
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


        for ($i = 1; $i <= 24; $i++) {
            $rules["item$i"] = ($i === 1 ? 'required' : 'nullable') . '|string';
            $rules["cat$i"] = ['nullable', "required_with:item$i", \Illuminate\Validation\Rule::exists('categories', 'id')->where('type', 'cc_item')->where('status', 1)];
        }
        request()->validate($rules, $mesg); // Validate

        $site = Site::findOrFail(request('site_id'));
        abort_unless(Auth::user()->authSites('view.site.list')->contains('id', $site->id), 403);
        $cc_request = request()->only(['certifier', 'certifier_phone', 'pca_name', 'pca_email', 'inspections_email']);
        $cc_request['site_id'] = (int) request('site_id');
        $cc_request['stage'] = 'Upcoming';
        $cc_request['status'] = 2; // set new request to In Progress - Assign Supervisor

        $cc = DB::transaction(function () use ($cc_request) {
            // Create CC
            $cc = SiteCc::create($cc_request);
            $action = Action::create(['action' => "CC created", 'table' => 'site_cc', 'table_id' => $cc->id]);

            // Add Request Items
            for ($i = 1; $i < 25; $i++) {
                if (request("item$i")) {
                    SiteCcItem::create(['cc_id' => $cc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i, 'status' => 1]);
                }
            }

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_cc', 'table_id' => $cc->id, 'directory' => "site/$cc->site_id/cc"]);
                $attachment->saveAttachment($tmp_filename);
            }
            return $cc;
        });

        Toastr::success("Created CC");

        return redirect("/site/cc/$cc->id/edit");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation
        if (!Auth::user()->allowed2('add.site.cc'))
            abort(403);

        $cats = Category::where('type', 'cc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/cc/create', compact('cats'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $cc = SiteCc::findOrFail($id);
        $super_id_orig = $cc->super_id;


        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.cc', $cc))
            abort(403);

        $rules = [
            'cc_requested' => 'nullable|date_format:d/m/Y',
            'super_id' => 'nullable|integer|exists:users,id',
            'portal_fee_paid' => 'nullable|boolean',
            'wbo_waiting' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'final_photos_rcvd' => 'nullable|date_format:d/m/Y',
            'certifier' => 'nullable|string',
            'certifier_phone' => 'nullable|string',
            'pca_name' => 'nullable|string',
            'pca_email' => 'nullable|string',
            'inspections_email' => 'nullable|string',
        ];
        $mesg = [];

        request()->validate($rules, $mesg); // Validate

        $cc_request = request()->only(['certifier', 'certifier_phone', 'pca_name', 'pca_email', 'inspections_email', 'super_id', 'notes', 'portal_fee_paid', 'wbo_waiting']);
        $cc_request['cc_requested'] = request()->filled('cc_requested') ? Carbon::createFromFormat('d/m/Y', request('cc_requested'))->startOfDay()->toDateTimeString() : null;
        $cc_request['final_photos_rcvd'] = request()->filled('final_photos_rcvd') ? Carbon::createFromFormat('d/m/Y', request('final_photos_rcvd'))->toDateString() : null;

        if (array_key_exists('super_id', $cc_request) && (int) $cc_request['super_id'] !== (int) $super_id_orig) {
            abort_unless(Auth::user()->allowed2('sig.site.cc', $cc), 403);
        }
        $cc->update($cc_request);

        // Handle attachments
        $attachments = collect(request('filepond', []))->filter()->values();
        foreach ($attachments as $tmp_filename) {
            $attachment = Attachment::create(['table' => 'site_cc', 'table_id' => $cc->id, 'directory' => "site/$cc->site_id/cc"]);
            $attachment->saveAttachment($tmp_filename);
        }

        // Record supervisor changes without sending trial notifications.
        if (request('super_id') && request('super_id') != $super_id_orig) {
            $super = User::find($cc_request['super_id']);
            $action = Action::create(['action' => "Supervisor assigned $super->name", 'table' => 'site_cc', 'table_id' => $cc->id]);

            if ((int) $cc->status === 2) $cc->status = 1; // Set to Active if in progress
        }

        $cc->save();
        $cc->syncStage();
        Toastr::success("Updated Request");

        return redirect('site/cc/' . $cc->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport($id)
    {
        $cc = SiteCc::findOrFail($id);
        $validated = request()->validate(['status' => ['required', 'integer', \Illuminate\Validation\Rule::in([-1, 1])]]);
        $permission = (int) $validated['status'] === -1 ? 'del.site.cc' : 'edit.site.cc';
        abort_unless(Auth::user()->allowed2($permission, $cc), 403);
        $cc->update(['status' => (int) $validated['status']]);
        $cc->syncStage();
        Action::create(['action' => (int) $cc->status === -1 ? 'CC disabled' : 'CC restored', 'table' => 'site_cc', 'table_id' => $cc->id]);
        return response()->json(['success' => true]);
    }


    /**
     * Add Item the specified resource in storage.
     *
     */
    public function addItems($id)
    {
        $cc = SiteCc::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.cc', $cc)))
            abort(403);

        $cats = Category::where('type', 'cc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/cc/additems', compact('cc', 'cats'));
    }

    public function addItemsSave($id)
    {
        $cc = SiteCc::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.cc', $cc)))
            abort(403);

        $itemCount = $cc->items->count();
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


        for ($i = 1; $i <= 24; $i++) {
            $rules["item$i"] = ($i === 1 ? 'required' : 'nullable') . '|string';
            $rules["cat$i"] = ['nullable', "required_with:item$i", \Illuminate\Validation\Rule::exists('categories', 'id')->where('type', 'cc_item')->where('status', 1)];
        }
        request()->validate($rules, $mesg); // Validate


        DB::transaction(function () use ($cc, $itemCount) {
            // Create CC
            $action = Action::create(['action' => "Multiple items added", 'table' => 'site_cc', 'table_id' => $cc->id]);

            // Add Request Items
            for ($i = 1; $i < 25; $i++) {
                if (request("item$i")) {
                    SiteCcItem::create(['cc_id' => $cc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i + $itemCount, 'status' => 1]);
                }
            }

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_cc', 'table_id' => $cc->id, 'directory' => "site/$cc->site_id/cc"]);
                $attachment->saveAttachment($tmp_filename);
            }


        });

        Toastr::success("Created CC");

        return redirect("/site/cc/$cc->id/edit");
    }

    public function destroy($id)
    {
        $cc = SiteCc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('del.site.cc', $cc))
            abort(403);

        $cc->status = '-1';
        $cc->save();
        Action::create(['action' => 'CC disabled', 'table' => 'site_cc', 'table_id' => $cc->id]);

        return response()->json(['success' => true, 'message' => 'CC disabled',]);
    }

    public function settings()
    {
        // Check authorisation
        if (!Auth::user()->hasPermission2('edit.site.cc'))
            abort(403);

        $cats = Category::where('type', 'cc_item')->where('status', 1)->orderBy('order')->get();
        $usedCategoryIds = SiteCcItem::whereIn('category_id', $cats->pluck('id'))->distinct()->pluck('category_id')->map(fn ($id) => (int) $id)->all();

        $cats->each(function ($cat) use ($usedCategoryIds) {
            $cat->setAttribute('in_use', in_array((int) $cat->id, $usedCategoryIds, true));
        });

        return view('site/cc/settings-categories', compact('cats'));
    }

    public function storeCategory(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.cc'))
            abort(403);

        $validated = $request->validate(['create_name' => ['required', 'string', 'max:255']], ['create_name.required' => 'The name field is required.']);
        $order = (int) Category::where('type', 'cc_item')->where('status', 1)->max('order') + 1;

        Category::create([
            'type' => 'cc_item',
            'name' => $validated['create_name'],
            'order' => $order,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        Toastr::success('Added CC category');

        return redirect()->back();
    }

    public function updateCategory(Request $request, $id)
    {
        if (!Auth::user()->hasPermission2('edit.site.cc'))
            abort(403);

        $category = Category::where('type', 'cc_item')->where('status', 1)->findOrFail($id);
        $validated = $request->validate(['edit_name' => ['required', 'string', 'max:255']], ['edit_name.required' => 'The name field is required.']);
        $category->update(['name' => $validated['edit_name']]);

        Toastr::success('Updated CC category');

        return redirect()->back();
    }

    public function reorderCategories(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.cc'))
            return response()->json(['message' => 'You are not authorised to update CC categories.'], 403);

        $validated = $request->validate(['category_ids' => ['required', 'array'], 'category_ids.*' => ['required', 'integer', 'distinct']]);
        $categoryIds = array_map('intval', $validated['category_ids']);
        $activeIds = Category::where('type', 'cc_item')->where('status', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $submittedIds = $categoryIds;
        sort($submittedIds);
        sort($activeIds);

        if ($submittedIds !== $activeIds)
            return response()->json(['message' => 'The category list changed. Please refresh the page and try again.'], 422);

        DB::transaction(function () use ($categoryIds) {
            foreach ($categoryIds as $index => $categoryId)
                Category::where('type', 'cc_item')->whereKey($categoryId)->update(['order' => $index + 1]);
        });

        return response()->json(['message' => 'Updated category order']);
    }

    public function destroyCategory($id)
    {
        if (!Auth::user()->hasPermission2('edit.site.cc'))
            return response()->json(['message' => 'You are not authorised to remove CC categories.'], 403);

        $category = Category::where('type', 'cc_item')->where('status', 1)->findOrFail($id);
        $inUse = SiteCcItem::where('category_id', $category->id)->exists();

        DB::transaction(function () use ($category, $inUse) {
            if ($inUse) {
                $category->update(['status' => 0]);
            } else {
                $category->delete();
            }
            Category::where('type', 'cc_item')->where('status', 1)->orderBy('order')->get()->each(function ($activeCategory, $index) {
                $activeCategory->update(['order' => $index + 1]);
            });
        });

        return response()->json(['message' => $inUse ? 'Archived CC category' : 'Deleted CC category']);
    }

    private function ccStageOptions(): array
    {
        return SiteCc::stageOptions() + ['Disabled' => 'Disabled'];
    }

    /**
     * Get CCs current user is authorised to manage + Process datatables ajax request.
     */
    public function getCc()
    {
        $stage = request('stage', session('cc.stage', 'Upcoming'));

        if ($stage !== 'all' && !array_key_exists($stage, $this->ccStageOptions())) {
            return response()->json(['message' => 'Invalid CC stage'], 422);
        }

        // Remember the user's valid selection
        session(['cc.stage' => $stage]);

        $request_ids = SiteCc::with('items')->get()
            ->filter(fn ($cc) => Auth::user()->allowed2('view.site.cc', $cc))
            ->pluck('id')->all();
        $supervisor = request('supervisor', 'all');

        $records = DB::table('site_cc AS m')
            ->select([
                'm.id', 'm.site_id', 'm.super_id', 'm.status', 'm.stage', 'm.updated_at', 'm.created_at',
                'm.cc_requested',
                DB::raw('DATE_FORMAT(s.completion_signed, "%d/%m/%Y") AS prac_completed'),
                DB::raw('DATE_FORMAT(m.cc_requested, "%d/%m/%Y") AS cc_requested_date'),
                DB::raw('DATE_FORMAT(s.construction_rcvd, "%d/%m/%Y") AS cc_received'),
                's.code as sitecode', 's.name as sitename',
            ])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids);

        if ($supervisor && $supervisor !== 'all')
            $records->where('m.super_id', $supervisor);
        if ($stage === 'Disabled')
            $records->where(function ($query) {
                $query->where('m.status', -1)->orWhere('s.status', -2);
            });
        elseif ($stage !== 'all')
            $records->where('m.stage', $stage)->where('m.status', '!=', -1);


        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/cc/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('site_id', function ($rec) {
                return $rec->sitecode;
            })
            ->editColumn('stage', function ($rec) {
                $stageColours = [
                    'Upcoming' => '#95a5a6', // Grey
                    'Jobs in Const' => '#3598dc', // Blue
                    "Prac'd Jobs" => '#8e44ad', // Purple
                    'CC Booked' => '#17a2b8', // Teal
                    'WBO' => '#d68910', // Amber
                    'CC Received' => '#26a65b', // Green
                    'Disabled' => '#5c6670', // Dark grey
                ];

                $stage = (int) $rec->status === -1 ? 'Disabled' : ($rec->stage ?: 'Upcoming');
                $colour = $stageColours[$stage] ?? '#95a5a6';

                return '<span class="label label-sm" style="background-color: '
                    . $colour . '; color: #fff;">'
                    . e($stage)
                    . '</span>';
            })
            ->editColumn('super_id', function ($rec) {
                $d = SiteCc::find($rec->id);

                return ($d->super_id) ? $d->supervisor->initials : '-';
            })
            ->editColumn('prac_completed', function ($rec) {
                return $rec->prac_completed ?: '-';
            })
            ->editColumn('cc_requested_date', function ($rec) {
                return $rec->cc_requested_date ?: '-';
            })
            ->editColumn('cc_received', function ($rec) {
                return $rec->cc_received ?: '-';
            })
            ->addColumn('last_updated', function ($rec) {
                $cc = SiteCc::find($rec->id);

                return ($cc->lastAction()) ? $cc->lastAction()->updated_at->format('d/m/Y') : $cc->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($rec) {
                $cc = SiteCc::find($rec->id);
                $action = '';
                if (($rec->status > 0 && Auth::user()->allowed2('edit.site.cc', $cc)) || (!$rec->status && Auth::user()->allowed2('sig.site.cc', $cc)))
                    $action .= '<a href="/site/cc/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if ($rec->status != '-1' && $rec->stage == 'Upcoming' && Auth::user()->allowed2('del.site.cc', $cc))
                    $action .= '<button type="button" class="btn dark btn-xs btn-outline sbold uppercase margin-bottom delete-report" data-id="' . $rec->id . '" data-name="' . e($rec->sitename) . '"><i class="fa fa-eye-slash"></i></button>';
                if ($rec->status == '-1' && Auth::user()->allowed2('edit.site.cc', $cc))
                    $action .= '<button type="button" data-id="' . $rec->id . '" class="restore-report btn blue btn-xs btn-outline sbold uppercase margin-bottom">Restore</button>';

                return $action;
            })
            ->rawColumns(['id', 'name', 'stage', 'sitename', 'updated_at', 'completed', 'action', 'last_updated'])
            ->make(true);

        return $dt;
    }


}
