<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Category;
use App\Models\Site\Site;
use App\Models\Site\SitePreCon;
use App\Models\Site\SitePreConItem;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;

class SitePreConController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::check() && Auth::user()->hasPermission2('view.site.precon'), 403);
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
        if (!Auth::user()->hasAnyPermissionType('site.precon'))
            abort(403);

        $stageOptions = $this->reviewStageOptions();
        $selectedStage = session('precon.stage', 'active');

        return view('site/precon/list', compact('stageOptions', 'selectedStage'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cc = SitePreCon::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('view.site.precon', $cc))
            abort(403);

        return view('site/precon/show', compact('cc'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $cc = SitePreCon::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.precon', $cc))
            abort(403);

        return view('site/precon/show', compact('cc'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $cc = SitePreCon::findOrFail($id);


        // Check authorisation
        if (!Auth::user()->allowed2('edit.site.precon', $cc))
            abort(403);

        request()->validate(['notes' => 'nullable|string']);
        $cc->update(request()->only('notes'));

        // Handle attachments
        $attachments = collect(request('filepond', []))->filter()->values();
        foreach ($attachments as $tmp_filename) {
            $attachment = Attachment::create(['table' => 'site_pre_con', 'table_id' => $cc->id, 'directory' => "site/$cc->site_id/precon"]);
            $attachment->saveAttachment($tmp_filename);
        }

        Toastr::success("Updated Request");

        return redirect('site/precon/' . $cc->id);
    }


    /**
     * Update Status the specified resource in storage.
     */
    public function updateReport($id)
    {
        $cc = SitePreCon::findOrFail($id);
        $validated = request()->validate(['status' => ['required', 'integer', \Illuminate\Validation\Rule::in([-1, 1])]]);
        $permission = (int) $validated['status'] === -1 ? 'del.site.precon' : 'edit.site.precon';
        abort_unless(Auth::user()->allowed2($permission, $cc), 403);
        $cc->update(['status' => (int) $validated['status']]);
        Action::create(['action' => (int) $cc->status === -1 ? 'Pre Con Review disabled' : 'Pre Con Review restored', 'table' => 'site_pre_con', 'table_id' => $cc->id]);
        return response()->json(['success' => true]);
    }


    /**
     * Add Item the specified resource in storage.
     *
     */
    public function addItems($id)
    {
        $cc = SitePreCon::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.precon', $cc)))
            abort(403);

        $cats = Category::where('type', 'precon_item')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/precon/additems', compact('cc', 'cats'));
    }

    public function addItemsSave($id)
    {
        $cc = SitePreCon::findOrFail($id);

        // Check authorisation
        if (!(Auth::user()->allowed2('edit.site.precon', $cc)))
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
            $rules["cat$i"] = ['nullable', "required_with:item$i", \Illuminate\Validation\Rule::exists('categories', 'id')->where('type', 'precon_item')->where('status', 1)];
        }
        request()->validate($rules, $mesg); // Validate


        DB::transaction(function () use ($cc, $itemCount) {
            // Create Pre Con Review
            $action = Action::create(['action' => "Multiple items added", 'table' => 'site_pre_con', 'table_id' => $cc->id]);

            // Add Request Items
            for ($i = 1; $i < 25; $i++) {
                if (request("item$i")) {
                    SitePreConItem::create(['review_id' => $cc->id, 'name' => request("item$i"), 'category_id' => request("cat$i"), 'order' => $i + $itemCount, 'status' => 1]);
                }
            }

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'site_pre_con', 'table_id' => $cc->id, 'directory' => "site/$cc->site_id/precon"]);
                $attachment->saveAttachment($tmp_filename);
            }


        });

        Toastr::success("Created Pre Con Review");

        return redirect("/site/precon/$cc->id/edit");
    }

    public function destroy($id)
    {
        $cc = SitePreCon::findOrFail($id);

        // Check authorisation
        if (!Auth::user()->allowed2('del.site.precon', $cc))
            abort(403);

        $cc->status = '-1';
        $cc->save();
        Action::create(['action' => 'Pre Con Review disabled', 'table' => 'site_pre_con', 'table_id' => $cc->id]);

        return response()->json(['success' => true, 'message' => 'Pre Con Review disabled',]);
    }

    public function settings()
    {
        // Check authorisation
        if (!Auth::user()->hasPermission2('edit.site.precon'))
            abort(403);

        $cats = Category::where('type', 'precon_item')->where('status', 1)->orderBy('order')->get();
        $usedCategoryIds = SitePreConItem::whereIn('category_id', $cats->pluck('id'))->distinct()->pluck('category_id')->map(fn ($id) => (int) $id)->all();

        $cats->each(function ($cat) use ($usedCategoryIds) {
            $cat->setAttribute('in_use', in_array((int) $cat->id, $usedCategoryIds, true));
        });

        return view('site/precon/settings-categories', compact('cats'));
    }

    public function storeCategory(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.precon'))
            abort(403);

        $validated = $request->validate(['create_name' => ['required', 'string', 'max:255']], ['create_name.required' => 'The name field is required.']);
        $order = (int) Category::where('type', 'precon_item')->where('status', 1)->max('order') + 1;

        Category::create([
            'type' => 'precon_item',
            'name' => $validated['create_name'],
            'order' => $order,
            'company_id' => Auth::user()->company->reportsTo()->id,
            'status' => 1,
        ]);

        Toastr::success('Added Pre Con Review category');

        return redirect()->back();
    }

    public function updateCategory(Request $request, $id)
    {
        if (!Auth::user()->hasPermission2('edit.site.precon'))
            abort(403);

        $category = Category::where('type', 'precon_item')->where('status', 1)->findOrFail($id);
        $validated = $request->validate(['edit_name' => ['required', 'string', 'max:255']], ['edit_name.required' => 'The name field is required.']);
        $category->update(['name' => $validated['edit_name']]);

        Toastr::success('Updated Pre Con Review category');

        return redirect()->back();
    }

    public function reorderCategories(Request $request)
    {
        if (!Auth::user()->hasPermission2('edit.site.precon'))
            return response()->json(['message' => 'You are not authorised to update Pre Con Review categories.'], 403);

        $validated = $request->validate(['category_ids' => ['required', 'array'], 'category_ids.*' => ['required', 'integer', 'distinct']]);
        $categoryIds = array_map('intval', $validated['category_ids']);
        $activeIds = Category::where('type', 'precon_item')->where('status', 1)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $submittedIds = $categoryIds;
        sort($submittedIds);
        sort($activeIds);

        if ($submittedIds !== $activeIds)
            return response()->json(['message' => 'The category list changed. Please refresh the page and try again.'], 422);

        DB::transaction(function () use ($categoryIds) {
            foreach ($categoryIds as $index => $categoryId)
                Category::where('type', 'precon_item')->whereKey($categoryId)->update(['order' => $index + 1]);
        });

        return response()->json(['message' => 'Updated category order']);
    }

    public function destroyCategory($id)
    {
        if (!Auth::user()->hasPermission2('edit.site.precon'))
            return response()->json(['message' => 'You are not authorised to remove Pre Con Review categories.'], 403);

        $category = Category::where('type', 'precon_item')->where('status', 1)->findOrFail($id);
        $inUse = SitePreConItem::where('category_id', $category->id)->exists();

        DB::transaction(function () use ($category, $inUse) {
            if ($inUse) {
                $category->update(['status' => 0]);
            } else {
                $category->delete();
            }
            Category::where('type', 'precon_item')->where('status', 1)->orderBy('order')->get()->each(function ($activeCategory, $index) {
                $activeCategory->update(['order' => $index + 1]);
            });
        });

        return response()->json(['message' => $inUse ? 'Archived Pre Con Review category' : 'Deleted Pre Con Review category']);
    }

    private function accessiblePreConIds(): array
    {
        return SitePreCon::with('items')->get()
            ->filter(fn ($cc) => Auth::user()->allowed2('view.site.precon', $cc))
            ->pluck('id')->all();
    }

    private function reviewStageOptions(): array
    {
        return ['active' => 'All Reviews', 'Disabled' => 'Disabled'];
    }

    /**
     * Get CCs current user is authorised to manage + Process datatables ajax request.
     */
    public function getPreCon()
    {
        $stage = request('stage', session('precon.stage', 'active'));

        if (!array_key_exists($stage, $this->reviewStageOptions())) {
            return response()->json(['message' => 'Invalid Pre Con Review stage'], 422);
        }

        // Remember the user's valid selection
        session(['precon.stage' => $stage]);

        $records = DB::table('site_pre_con AS m')
            ->select(['m.id', 'm.site_id', 'm.status', 's.name as sitename', 's.code as sitecode', 's.status as site_status'])
            ->join('sites AS s', 'm.site_id', '=', 's.id')
            ->whereIn('m.id', $this->accessiblePreConIds());

        if ($stage === 'Disabled') {
            $records->where(fn ($query) => $query->where('m.status', -1)->orWhere('s.status', -2));
        } else {
            $records->where('m.status', '!=', -1)->where('s.status', '!=', -2);
        }

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/precon/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('site_id', function ($rec) {
                return $rec->sitecode;
            })
            ->addColumn('last_updated', function ($rec) {
                $cc = SitePreCon::find($rec->id);

                return ($cc->lastAction()) ? $cc->lastAction()->updated_at->format('d/m/Y') : $cc->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($rec) {
                $cc = SitePreCon::find($rec->id);
                $action = '';
                if (($rec->status > 0 && Auth::user()->allowed2('edit.site.precon', $cc)) || (!$rec->status && Auth::user()->allowed2('sig.site.precon', $cc)))
                    $action .= '<a href="/site/precon/' . $rec->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if ($rec->status != '-1' && (int) $rec->site_status !== -2 && Auth::user()->allowed2('del.site.precon', $cc))
                    $action .= '<button type="button" class="btn dark btn-xs btn-outline sbold uppercase margin-bottom delete-report" data-id="' . $rec->id . '" data-name="' . e($rec->sitename) . '"><i class="fa fa-eye-slash"></i></button>';
                if ($rec->status == '-1' && Auth::user()->allowed2('edit.site.precon', $cc))
                    $action .= '<button type="button" data-id="' . $rec->id . '" class="restore-report btn blue btn-xs btn-outline sbold uppercase margin-bottom">Restore</button>';

                return $action;
            })
            ->rawColumns(['id', 'name', 'stage', 'updated_at', 'completed', 'action', 'last_updated'])
            ->make(true);

        return $dt;
    }


}
