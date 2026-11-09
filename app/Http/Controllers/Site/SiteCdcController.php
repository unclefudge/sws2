<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Category;
use App\Models\Site\Site;
use App\Models\Site\SiteCdc;
use App\Models\Site\SiteCdcItem;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;

class SiteCdcController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check() && Auth::user()->hasPermission2('view.site.cdc'), 403);
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
        if (!Auth::user()->hasAnyPermissionType('site.cdc'))
            abort(403);

        $stageOptions = $this->cdcStageOptions();
        $selectedStage = session('cdc.stage', 'Upcoming');
        if (!array_key_exists($selectedStage, $stageOptions)) $selectedStage = 'Upcoming';
        session(['cdc.stage' => $selectedStage]);
        $draftspersonOptions = ['all' => 'All sites', '__unassigned__' => 'Unassigned'];
        $names = DB::table('site_cdc AS m')->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $this->accessibleCdcIds())
            ->whereNotNull('s.draftsperson_cc')->whereRaw('TRIM(s.draftsperson_cc) <> ?', [''])
            ->distinct()->orderBy('s.draftsperson_cc')->pluck('s.draftsperson_cc');
        foreach ($names as $name) $draftspersonOptions[$name] = $name;

        return view('site/cdc/list', compact('stageOptions', 'selectedStage', 'draftspersonOptions'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cdc = SiteCdc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('view.site.cdc', $cdc))
            abort(403);

        return view('site/cdc/show', compact('cdc'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cdc = SiteCdc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.cdc', $cdc))
            abort(403);

        return view('site/cdc/show', compact('cdc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation
        if (!Auth::user()->allowed2('add.site.cdc'))
            abort(403);

        $rules = ['site_id' => 'required|integer|exists:sites,id', 'item1' => 'required|string',
            'certifier' => 'nullable|string',
            'certifier_phone' => 'nullable|string',
            'pca_name' => 'nullable|string',
            'pca_email' => 'nullable|string',
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
            $rules["cat$i"] = ['nullable', "required_with:item$i", \Illuminate\Validation\Rule::exists('categories', 'id')->where('type', 'cdc_item')->where('status', 1)];
        }
        request()->validate($rules, $mesg); // Validate

        $site = Site::findOrFail(request('site_id'));
        abort_unless(Auth::user()->authSites('view.site.list')->contains('id', $site->id), 403);
        if (!$this->isEligibleCdcSite($site)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'site_id' => 'Select a Cape Cod job with Lodgement Type CDC.',
            ]);
        }
        if (SiteCdc::where('site_id', $site->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'site_id' => 'This job already has CDC Requirements.',
            ]);
        }
        $cdc_request = request()->only(['certifier', 'certifier_phone', 'pca_name', 'pca_email']);
        $cdc_request['site_id'] = (int) request('site_id');
        $cdc_request['stage'] = 'Upcoming';
        $cdc_request['status'] = 2; // set new request to In Progress - Assign Supervisor

        $cdc = DB::transaction(function () use ($cdc_request) {
            // Create CDC
            $cdc = SiteCdc::create($cdc_request);
            $action = Action::create(['action' => "CDC created", 'table' => 'site_cdc', 'table_id' => $cdc->id]);

            // Add Request Items
            for ($i = 1; $i < 25; $i++) {
                if (request("item$i")) {
                    SiteCdcItem::create(['cdc_id' => $cdc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i, 'status' => 1]);
                }
            }

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_cdc', 'table_id' => $cdc->id, 'directory' => "site/$cdc->site_id/cdc"]);
                $attachment->saveAttachment($tmp_filename);
            }
            return $cdc;
        });

        Toastr::success("Created CDC");

        return redirect("/site/cdc/$cdc->id/edit");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation
        if (!Auth::user()->allowed2('add.site.cdc'))
            abort(403);

        $cats = Category::where('type', 'cdc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        $registeredSiteIds = SiteCdc::pluck('site_id')->flip();
        $siteOptions = Auth::user()->authSites('view.site.list')
            ->filter(fn (Site $site) => $this->isEligibleCdcSite($site) && !$registeredSiteIds->has($site->id))
            ->pluck('name', 'id');

        return view('site/cdc/create', compact('cats', 'siteOptions'));
    }


    private function isEligibleCdcSite(Site $site): bool
    {
        return (int) $site->company_id === 3
            && (int) $site->status !== -2
            && strtoupper(trim((string) $site->lodgement_type)) === 'CDC';
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $cdc = SiteCdc::findOrFail($id);
        $super_id_orig = $cdc->super_id;


        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.cdc', $cdc))
            abort(403);

        $rules = [
            'cdc_requested' => 'nullable|date_format:d/m/Y',
            'super_id' => 'nullable|integer|exists:users,id',
            'portal_fee_paid' => 'nullable|boolean',
            'wbo_waiting' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'final_photos_rcvd' => 'nullable|date_format:d/m/Y',
            'certifier' => 'nullable|string',
            'certifier_phone' => 'nullable|string',
            'pca_name' => 'nullable|string',
            'pca_email' => 'nullable|string',
        ];
        $mesg = [];

        request()->validate($rules, $mesg); // Validate

        abort_unless((int) $site->company_id === 3 && (int) $site->status !== -2
            && strtoupper(trim((string) $site->lodgement_type)) === 'CDC', 422, 'Select a Cape Cod job with Lodgement Type CDC.');
        abort_if(SiteCdc::where('site_id', $site->id)->exists(), 422, 'This job already has CDC Requirements.');
        $cdc_request = request()->only(['certifier', 'certifier_phone', 'pca_name', 'pca_email', 'super_id', 'notes', 'portal_fee_paid', 'wbo_waiting']);
        $cdc_request['cdc_requested'] = request()->filled('cdc_requested') ? Carbon::createFromFormat('d/m/Y', request('cdc_requested'))->startOfDay()->toDateTimeString() : null;
        if (request()->has('final_photos_rcvd')) {
            $cdc_request['final_photos_rcvd'] = request()->filled('final_photos_rcvd') ? Carbon::createFromFormat('d/m/Y', request('final_photos_rcvd'))->toDateString() : null;
        }

        if (array_key_exists('super_id', $cdc_request) && (int) $cdc_request['super_id'] !== (int) $super_id_orig) {
            abort_unless(Auth::user()->allowed2('sig.site.cdc', $cdc), 403);
        }
        $cdc->update($cdc_request);

        // Handle attachments
        $attachments = collect(request('filepond', []))->filter()->values();
        foreach ($attachments as $tmp_filename) {
            $attachment = Attachment::create(['table' => 'site_cdc', 'table_id' => $cdc->id, 'directory' => "site/$cdc->site_id/cdc"]);
            $attachment->saveAttachment($tmp_filename);
        }

        // Record supervisor changes without sending trial notifications.
        if (request('super_id') && request('super_id') != $super_id_orig) {
            $super = User::find($cdc_request['super_id']);
            $action = Action::create(['action' => "Supervisor assigned $super->name", 'table' => 'site_cdc', 'table_id' => $cdc->id]);

            if ((int) $cdc->status === 2) $cdc->status = 1; // Set to Active if in progress
        }

        $cdc->save();
        $cdc->syncStage();
        Toastr::success("Updated Request");

        return redirect('site/cdc/' . $cdc->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport($id)
    {
        $cdc = SiteCdc::findOrFail($id);
        $validated = request()->validate(['status' => ['required', 'integer', \Illuminate\Validation\Rule::in([-1, 1])]]);
        $permission = (int) $validated['status'] === -1 ? 'del.site.cdc' : 'edit.site.cdc';
        abort_unless(Auth::user()->allowed2($permission, $cdc), 403);
        $cdc->update(['status' => (int) $validated['status']]);
        $cdc->syncStage();
        Action::create(['action' => (int) $cdc->status === -1 ? 'CDC disabled' : 'CDC restored', 'table' => 'site_cdc', 'table_id' => $cdc->id]);
        return response()->json(['success' => true]);
    }


    /**
     * Add Item the specified resource in storage.
     *
     */
    public function addItems($id)
    {
        $cdc = SiteCdc::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.cdc', $cdc)))
            abort(403);

        $cats = Category::where('type', 'cdc_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/cdc/additems', compact('cdc', 'cats'));
    }

    public function addItemsSave($id)
    {
        $cdc = SiteCdc::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.cdc', $cdc)))
            abort(403);

        $itemCount = $cdc->items->count();
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
            $rules["cat$i"] = ['nullable', "required_with:item$i", \Illuminate\Validation\Rule::exists('categories', 'id')->where('type', 'cdc_item')->where('status', 1)];
        }
        request()->validate($rules, $mesg); // Validate


        DB::transaction(function () use ($cdc, $itemCount) {
            // Create CDC
            $action = Action::create(['action' => "Multiple items added", 'table' => 'site_cdc', 'table_id' => $cdc->id]);

            // Add Request Items
            for ($i = 1; $i < 25; $i++) {
                if (request("item$i")) {
                    SiteCdcItem::create(['cdc_id' => $cdc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i + $itemCount, 'status' => 1]);
                }
            }

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_cdc', 'table_id' => $cdc->id, 'directory' => "site/$cdc->site_id/cdc"]);
                $attachment->saveAttachment($tmp_filename);
            }


        });

        Toastr::success("Created CDC");

        return redirect("/site/cdc/$cdc->id/edit");
    }

    public function destroy($id)
    {
        $cdc = SiteCdc::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('del.site.cdc', $cdc))
            abort(403);

        $cdc->status = '-1';
        $cdc->save();
        Action::create(['action' => 'CDC disabled', 'table' => 'site_cdc', 'table_id' => $cdc->id]);

        return response()->json(['success' => true, 'message' => 'CDC disabled',]);
    }

    public function settings()
    {
        // Check authorisation
        if (!Auth::user()->hasPermission2('edit.site.cdc'))
            abort(403);

        $cats = Category::where('type', 'cdc_item')->where('status', 1)->orderBy('order')->get();
        $usedCategoryIds = SiteCdcItem::whereIn('category_id', $cats->pluck('id'))->distinct()->pluck('category_id')->map(fn ($id) => (int) $id)->all();

        $cats->each(function ($cat) use ($usedCategoryIds) {
            $cat->setAttribute('in_use', in_array((int) $cat->id, $usedCategoryIds, true));
        });

        return view('site/cdc/settings-categories', compact('cats'));
    }

    public function storeCategory(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.cdc'))
            abort(403);

        $validated = $request->validate(['create_name' => ['required', 'string', 'max:255']], ['create_name.required' => 'The name field is required.']);
        $order = (int) Category::where('type', 'cdc_item')->where('status', 1)->max('order') + 1;

        Category::create([
            'type' => 'cdc_item',
            'name' => $validated['create_name'],
            'order' => $order,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        Toastr::success('Added CDC category');

        return redirect()->back();
    }

    public function updateCategory(Request $request, $id)
    {
        if (!Auth::user()->hasPermission2('edit.site.cdc'))
            abort(403);

        $category = Category::where('type', 'cdc_item')->where('status', 1)->findOrFail($id);
        $validated = $request->validate(['edit_name' => ['required', 'string', 'max:255']], ['edit_name.required' => 'The name field is required.']);
        $category->update(['name' => $validated['edit_name']]);

        Toastr::success('Updated CDC category');

        return redirect()->back();
    }

    public function reorderCategories(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.cdc'))
            return response()->json(['message' => 'You are not authorised to update CDC categories.'], 403);

        $validated = $request->validate(['category_ids' => ['required', 'array'], 'category_ids.*' => ['required', 'integer', 'distinct']]);
        $categoryIds = array_map('intval', $validated['category_ids']);
        $activeIds = Category::where('type', 'cdc_item')->where('status', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $submittedIds = $categoryIds;
        sort($submittedIds);
        sort($activeIds);

        if ($submittedIds !== $activeIds)
            return response()->json(['message' => 'The category list changed. Please refresh the page and try again.'], 422);

        DB::transaction(function () use ($categoryIds) {
            foreach ($categoryIds as $index => $categoryId)
                Category::where('type', 'cdc_item')->whereKey($categoryId)->update(['order' => $index + 1]);
        });

        return response()->json(['message' => 'Updated category order']);
    }

    public function destroyCategory($id)
    {
        if (!Auth::user()->hasPermission2('edit.site.cdc'))
            return response()->json(['message' => 'You are not authorised to remove CDC categories.'], 403);

        $category = Category::where('type', 'cdc_item')->where('status', 1)->findOrFail($id);
        $inUse = SiteCdcItem::where('category_id', $category->id)->exists();

        DB::transaction(function () use ($category, $inUse) {
            if ($inUse) {
                $category->update(['status' => 0]);
            } else {
                $category->delete();
            }
            Category::where('type', 'cdc_item')->where('status', 1)->orderBy('order')->get()->each(function ($activeCategory, $index) {
                $activeCategory->update(['order' => $index + 1]);
            });
        });

        return response()->json(['message' => $inUse ? 'Archived CDC category' : 'Deleted CDC category']);
    }

    private function accessibleCdcIds(): array
    {
        return SiteCdc::with('items')->get()
            ->filter(fn ($cdc) => Auth::user()->allowed2('view.site.cdc', $cdc))
            ->pluck('id')->all();
    }

    private function cdcStageOptions(): array
    {
        return SiteCdc::stageOptions() + ['Disabled' => 'Disabled'];
    }

    /**
     * Get CDCs current user is authorised to manage + Process datatables ajax request.
     */
    public function getCdc()
    {
        $stage = request('stage', session('cdc.stage', 'Upcoming'));

        if ($stage !== 'all' && !array_key_exists($stage, $this->cdcStageOptions())) {
            return response()->json(['message' => 'Invalid CDC stage'], 422);
        }

        // Remember the user's valid selection
        session(['cdc.stage' => $stage]);

        $validated = request()->validate(['draftsperson' => 'nullable|string|max:255']);
        $request_ids = $this->accessibleCdcIds();
        $draftsperson = $validated['draftsperson'] ?? 'all';

        $records = DB::table('site_cdc AS m')
            ->select([
                'm.id', 'm.site_id', 'm.super_id', 'm.status', 'm.stage', 'm.updated_at', 'm.created_at',
                'm.cdc_requested',
                DB::raw('DATE_FORMAT(m.cdc_requested, "%d/%m/%Y") AS cdc_requested_date'),
                DB::raw('DATE_FORMAT(s.construction_rcvd, "%d/%m/%Y") AS cdc_received'),
                's.code as sitecode', 's.name as sitename', 's.draftsperson_cc', 's.construction_rcvd as received_date', 's.status as site_status',
            ])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $request_ids);

        SiteCdc::applyRegisterFilters($records, $stage, $draftsperson);

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/cdc/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('site_id', function ($rec) {
                return $rec->sitecode;
            })
            ->editColumn('stage', function ($rec) {
                $stageColours = ['Upcoming' => '#95a5a6', 'CDC Requested' => '#17a2b8', 'CDC Received' => '#26a65b', 'Disabled' => '#5c6670'];
                $stage = ((int) $rec->status === -1 || (int) $rec->site_status === -2) ? 'Disabled'
                    : ($rec->received_date !== null ? 'CDC Received' : ($rec->cdc_requested !== null ? 'CDC Requested' : 'Upcoming'));
                $colour = $stageColours[$stage] ?? '#95a5a6';

                return '<span class="label label-sm" style="background-color: '
                    . $colour . '; color: #fff;">'
                    . e($stage)
                    . '</span>';
            })
            ->editColumn('cdc_requested_date', function ($rec) {
                return $rec->cdc_requested_date ?: '-';
            })
            ->editColumn('cdc_received', function ($rec) {
                return $rec->cdc_received ?: '-';
            })
            ->addColumn('last_updated', function ($rec) {
                $cdc = SiteCdc::find($rec->id);

                return ($cdc->lastAction()) ? $cdc->lastAction()->updated_at->format('d/m/Y') : $cdc->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($rec) {
                $cdc = SiteCdc::find($rec->id);
                $action = '';
                if (($rec->status > 0 && Auth::user()->allowed2('edit.site.cdc', $cdc)) || (!$rec->status && Auth::user()->allowed2('sig.site.cdc', $cdc)))
                    $action .= '<a href="/site/cdc/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if ($rec->status != '-1' && (int) $rec->site_status !== -2 && Auth::user()->allowed2('del.site.cdc', $cdc))
                    $action .= '<button type="button" class="btn dark btn-xs btn-outline sbold uppercase margin-bottom delete-report" data-id="' . $rec->id . '" data-name="' . e($rec->sitename) . '"><i class="fa fa-eye-slash"></i></button>';
                if ($rec->status == '-1' && Auth::user()->allowed2('edit.site.cdc', $cdc))
                    $action .= '<button type="button" data-id="' . $rec->id . '" class="restore-report btn blue btn-xs btn-outline sbold uppercase margin-bottom">Restore</button>';

                return $action;
            })
            ->rawColumns(['id', 'name', 'stage', 'updated_at', 'completed', 'action', 'last_updated'])
            ->make(true);

        return $dt;
    }


}
