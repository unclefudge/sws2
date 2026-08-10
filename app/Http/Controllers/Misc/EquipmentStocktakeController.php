<?php

namespace App\Http\Controllers\Misc;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Equipment\EquipmentLost;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use DB;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

class EquipmentStocktakeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('equipment'))
            return view('errors/404');

        return view('misc/equipment/list');
    }

    /**
     * Display the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (preg_match('/^newloc/', $id)) {
            // Create New Location for Site ID
            list($crap, $site_id) = explode('-', $id);
            $location = new EquipmentLocation(['site_id' => $site_id]);
            $location->save();
        } else
            $location = EquipmentLocation::find($id);

        $sites = $this->getSites();
        $others = $this->getOthers();

        if ($location)
            return redirect("/equipment/stocktake/$id/edit/general");
        else
            return view('misc/equipment/stocktake', compact('location', 'sites', 'others'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $tab = null)
    {
        if (preg_match('/^newloc/', $id)) {
            // Create New Location for Site ID
            list($crap, $site_id) = explode('-', $id);
            $location = new EquipmentLocation(['site_id' => $site_id]);
            $location->save();
        } else
            $location = EquipmentLocation::find($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.equipment.stocktake', $location) && in_array($tab, ['general', 'materials', 'scaffold', 'bulkhardware', 'history'])))
            return view('errors/404');

        $category = match ($tab) {
            'general' => 1,
            'scaffold' => 2,
            'materials' => 3,
            'bulkhardware' => 19,
            default => 0,
        };

        $sites = $this->getSites();
        $others = $this->getOthers();

        // Load equipment + categories once. The old version ran separate equipment queries
        // for each tab and the Blade then queried equipment again for the additional-item lists.
        $allEquipment = Equipment::with('category:id,name,parent')->get(['id', 'category_id', 'name', 'length', 'status'])->keyBy('id');

        $equipmentGroups = [
            1 => $allEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 1),
            2 => $allEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 2),
            3 => $allEquipment->filter(fn ($equipment) => $equipment->category && (int) $equipment->category->parent === 3),
            19 => $allEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 19),
        ];

        $equipmentIds = collect($equipmentGroups)->map(fn ($group) => $group->pluck('id')->all());

        $items = collect();
        $items_count = [1 => 0, 2 => 0, 3 => 0, 19 => 0];

        if ($location) {
            // One location-item query is enough for both the current tab and all tab counts.
            $locationItems = EquipmentLocationItem::where('location_id', $location->id)->get();

            // Reuse the equipment collection already loaded above so item_name and
            // item_category_name accessors don't lazy-load Equipment for every row.
            $locationItems->each(function ($item) use ($allEquipment) {
                if ($equipment = $allEquipment->get($item->equipment_id))
                    $item->setRelation('equipment', $equipment);
            });

            foreach ([1, 2, 3, 19] as $categoryId)
                $items_count[$categoryId] = $locationItems->whereIn('equipment_id', $equipmentIds->get($categoryId, []))->count();

            $items = $locationItems
                ->whereIn('equipment_id', $equipmentIds->get($category, []))
                ->filter(fn ($item) => $item->equipment && $item->equipment->status)
                ->sortBy('item_name');

            if ($category == 3)
                $items = $items->sortBy('item_category_name');
        }

        // Build the additional-item lists here instead of issuing four Equipment queries
        // from inside the Blade view. Preserve the old behaviour of only excluding items
        // already shown on the currently selected tab.
        $currentItemIds = $items->pluck('equipment_id')->all();
        $activeEquipment = $allEquipment->filter(fn ($equipment) => $equipment->status);

        $equipment_gen = $activeEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 1 && !in_array($equipment->id, $currentItemIds));
        $equipment_sca = $activeEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 2 && !in_array($equipment->id, $currentItemIds));
        $equipment_mat = $activeEquipment->filter(fn ($equipment) => $equipment->category && (int) $equipment->category->parent === 3 && !in_array($equipment->id, $currentItemIds));
        $equipment_bul = $activeEquipment->filter(fn ($equipment) => (int) $equipment->category_id === 19 && !in_array($equipment->id, $currentItemIds));

        return view('misc/equipment/stocktake-edit', compact(
            'location', 'sites', 'others', 'items', 'category', 'items_count',
            'equipment_gen', 'equipment_sca', 'equipment_mat', 'equipment_bul'
        ));
    }


    public function getSites()
    {
        $sites = [];
        foreach (EquipmentLocation::with('site')->where('status', 1)->where('notes', null)->where('site_id', '<>', '25')->get() as $loc)
            $sites[$loc->id] = $loc->name;

        // Active Site but current no equipment
        foreach (Auth::user()->authSites('view.site.list', 1) as $site) {
            $name = "$site->suburb ($site->name)";
            if (!in_array($name, $sites) && !in_array($site->id, [25, 92, 366])) // Store, Conference, OnLeave
                $sites["newloc-$site->id"] = "$name";
        }
        asort($sites);
        $sites = ['1' => 'CAPE COD STORE'] + $sites;

        return $sites;
    }

    public function getOthers()
    {
        $others = [];
        foreach (EquipmentLocation::where('status', 1)->where('notes', null)->where('site_id', null)->get() as $loc)
            $others[$loc->id] = $loc->name;
        asort($others);

        return $others;
    }


    /**
     * Display the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showStocktake($id)
    {
        $stock = EquipmentStocktake::find($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment.stocktake', $stock))
            return view('errors/404');

        return view('misc/equipment/stocktake-show', compact('stock'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $location = EquipmentLocation::with('site')->findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment.stocktake', $location))
            return view('errors/404');

        $extra_items = [];

        $stocktake = new EquipmentStocktake(['location_id' => $location->id]);
        $stocktake->save();
        $passed_all = 1;

        // Load the active equipment relationship in the same query set instead of
        // lazy-loading Equipment once for every location item.
        $items = EquipmentLocationItem::with([
                'equipment' => function ($query) {
                    $query->select('id', 'name', 'status', 'purchased', 'disposed');
                }
            ])
            ->where('location_id', $location->id)
            ->whereHas('equipment', function ($query) {
                $query->where('status', 1);
            })
            ->get();

        $equipmentIds = $items->pluck('equipment_id')->unique()->values();

        // total_excess used to execute two aggregate queries per item through the
        // Equipment accessors (total + total_lost). Calculate both sets once.
        $equipmentTotals = collect();
        $lostTotals = collect();

        if ($equipmentIds->isNotEmpty()) {
            $equipmentTotals = DB::table('equipment_location_items')
                ->join('equipment_location', 'equipment_location.id', '=', 'equipment_location_items.location_id')
                ->select('equipment_location_items.equipment_id', DB::raw('SUM(equipment_location_items.qty) AS total'))
                ->whereIn('equipment_location_items.equipment_id', $equipmentIds)
                ->where('equipment_location.status', 1)
                ->where(function ($query) {
                    $query->whereNull('equipment_location.other')
                        ->orWhere('equipment_location.other', 'NOT LIKE', '%Transfer in progress:%');
                })
                ->groupBy('equipment_location_items.equipment_id')
                ->pluck('total', 'equipment_id');

            $lostTotals = DB::table('equipment_lost')
                ->select('equipment_id', DB::raw('SUM(qty) AS total'))
                ->whereIn('equipment_id', $equipmentIds)
                ->groupBy('equipment_id')
                ->pluck('total', 'equipment_id');
        }

        $totalExcess = [];
        $equipmentById = collect();

        foreach ($items as $item) {
            $equipment = $item->equipment;
            $equipmentById->put($equipment->id, $equipment);

            $total = (int) ($equipmentTotals->get($equipment->id) ?? 0);
            $lost = (int) ($lostTotals->get($equipment->id) ?? 0);

            $totalExcess[$equipment->id] = $total - (int) $equipment->purchased + (int) $equipment->disposed + $lost;
        }

        $exclude = request('exclude', []);

        // Collect stocktake rows and insert them in one query at the end. This
        // preserves the same audit fields normally populated by the model boot method.
        $stocktakeRows = [];
        $now = now();
        $userId = Auth::user()->id;

        // Check if current qty matches DB
        foreach ($items as $item) {
            $qty_now = request($item->id . '-qty');
            $passed_item = 1;

            $qtyActual = $qty_now;

            if (($location->site_id == 25 && !in_array($item->id, $exclude)) || ($location->site_id != 25 && in_array($item->id, $exclude))) {
                // Ignore excluded items. For CapeCod Store 'excluded' items are actually 'included' - reverse
                $qtyActual = $passed_item = null;
            } else {
                if ($item->qty > $qty_now) {
                    // Missing items
                    $passed_all = $passed_item = 0;
                    $excess = $totalExcess[$item->equipment_id] ?? 0;

                    // There were less items found at location then expected so
                    // check if 'extra' items are elsewhere and any none 'extra' mark them as missing
                    if (($item->qty - $qty_now) > $excess)
                        $this->lostItem($item->location_id, $item->equipment_id, ($item->qty - $qty_now - $excess), $location);
                } elseif ($item->qty < $qty_now) {
                    // Extra items
                    $extra_items[$item->equipment_id] = ($qty_now - $item->qty);
                }

                // Update altered qty at location
                if ($item->qty != $qty_now) {
                    if ($qty_now) {
                        $item->qty = $qty_now;
                        $item->save();
                    } else {
                        $item->delete();
                    }
                }
            }

            $stocktakeRows[] = [
                'stocktake_id' => $stocktake->id,
                'equipment_id' => $item->equipment_id,
                'qty_expect' => $item->qty,
                'qty_actual' => $qtyActual,
                'passed' => $passed_item,
                'company_id' => 3,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $stocktake->passed = $passed_all;
        $stocktake->save();

        // Gather manually-added equipment first so it is fetched in one query rather
        // than up to ten individual Equipment::findOrFail() queries.
        $manualExtra = [];
        for ($i = 1; $i <= 10; $i++) {
            if (request("$i-extra_qty") && request("$i-extra_id")) {
                $manualExtra[$i] = [
                    'equipment_id' => (int) request("$i-extra_id"),
                    'qty' => request("$i-extra_qty"),
                ];
            }
        }

        $manualEquipment = collect();
        if ($manualExtra) {
            $manualIds = collect($manualExtra)->pluck('equipment_id')->unique()->values();
            $manualEquipment = Equipment::whereIn('id', $manualIds)
                ->get(['id', 'name', 'purchased', 'disposed'])
                ->keyBy('id');

            foreach ($manualIds as $equipmentId) {
                if (!$manualEquipment->has($equipmentId))
                    Equipment::findOrFail($equipmentId);
            }

            foreach ($manualEquipment as $equipment)
                $equipmentById->put($equipment->id, $equipment);
        }

        // Add extra items to location
        foreach ($manualExtra as $extra) {
            $equip = $manualEquipment->get($extra['equipment_id']);
            $extra_items[$equip->id] = $extra['qty'];

            // Add item to location
            $location->items()->save(new EquipmentLocationItem([
                'location_id' => $location->id,
                'equipment_id' => $equip->id,
                'qty' => $extra['qty'],
            ]));

            $stocktakeRows[] = [
                'stocktake_id' => $stocktake->id,
                'equipment_id' => $equip->id,
                'qty_expect' => 0,
                'qty_actual' => $extra['qty'],
                'passed' => 1,
                'company_id' => 3,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // One bulk insert replaces one INSERT per stocktake item.
        if ($stocktakeRows)
            EquipmentStocktakeItem::insert($stocktakeRows);

        // For extra items above the expected amount determine if they were missing
        // from another site. Load all matching lost rows in one query.
        if (count($extra_items)) {
            $extraEquipmentIds = array_map('intval', array_keys($extra_items));

            $lostItemsByEquipment = EquipmentLost::whereIn('equipment_id', $extraEquipmentIds)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->groupBy('equipment_id');

            foreach ($extra_items as $equip_id => $amount) {
                $equip_id = (int) $equip_id;
                $extra_amount = $amount;
                $lost_items = $lostItemsByEquipment->get($equip_id, collect());

                foreach ($lost_items as $lost) {
                    if (!$extra_amount)
                        break;

                    if ($lost->qty > $extra_amount) {
                        // More lost items then found so subtract only found amount
                        $lost->decrement('qty', $extra_amount);
                        $log = new EquipmentLog([
                            'equipment_id' => $lost->equipment_id,
                            'qty' => $extra_amount,
                            'action' => 'F',
                            'notes' => "Found $extra_amount items at $location->name",
                        ]);
                        $extra_amount = 0;
                    } else {
                        // Found more items then are actually lost so delete full amount from lost item.
                        $foundQty = $lost->qty;
                        $extra_amount = $extra_amount - $foundQty;
                        $log = new EquipmentLog([
                            'equipment_id' => $lost->equipment_id,
                            'qty' => $foundQty,
                            'action' => 'F',
                            'notes' => "Found $foundQty items at $location->name",
                        ]);
                        $lost->delete();
                    }

                    $log->save();
                }

                $extra_items[$equip_id] = $extra_amount;
            }

            // The old Equipment::total accessor ran another aggregate query for every
            // extra equipment type. Calculate the post-stocktake totals once instead.
            $currentTotals = DB::table('equipment_location_items')
                ->join('equipment_location', 'equipment_location.id', '=', 'equipment_location_items.location_id')
                ->select('equipment_location_items.equipment_id', DB::raw('SUM(equipment_location_items.qty) AS total'))
                ->whereIn('equipment_location_items.equipment_id', $extraEquipmentIds)
                ->where('equipment_location.status', 1)
                ->where(function ($query) {
                    $query->whereNull('equipment_location.other')
                        ->orWhere('equipment_location.other', 'NOT LIKE', '%Transfer in progress:%');
                })
                ->groupBy('equipment_location_items.equipment_id')
                ->pluck('total', 'equipment_id');

            // Load any equipment not already present in the original/manual collections.
            $missingEquipmentIds = collect($extraEquipmentIds)->reject(fn ($equipmentId) => $equipmentById->has($equipmentId));
            if ($missingEquipmentIds->isNotEmpty()) {
                Equipment::whereIn('id', $missingEquipmentIds)
                    ->get(['id', 'name', 'purchased', 'disposed'])
                    ->each(fn ($equipment) => $equipmentById->put($equipment->id, $equipment));
            }

            foreach ($extra_items as $equip_id => $extra_amount) {
                if (!$extra_amount)
                    continue;

                $equip = $equipmentById->get((int) $equip_id);
                if (!$equip)
                    continue;

                $currentTotal = (int) ($currentTotals->get((int) $equip_id) ?? 0);
                if (($currentTotal - ((int) $equip->purchased - (int) $equip->disposed)) > 0)
                    Toastr::warning("Item: $equip->name increased above actual number of purchased items.");
            }
        }

        if (!$passed_all)
            Toastr::error("Some items marked as missing");

        Toastr::success("Saved changes");

        return redirect("/equipment/stocktake/$location->id");
    }


    /**
     * Lost item
     */
    public function lostItem($location_id, $equipment_id, $qty, $location = null)
    {
        // update() already has the location loaded, so avoid looking it up again
        // for every item marked missing. Other callers can continue using 3 args.
        $location = $location ?: EquipmentLocation::with('site')->findOrFail($location_id);

        $existing = EquipmentLost::where('location_id', $location_id)->where('equipment_id', $equipment_id)->first();
        if ($existing) {
            // Update existing lost qty
            $existing->qty = $existing->qty + $qty;
            $existing->save();
        } else {
            // Create Lost item
            $newLost = new EquipmentLost(['location_id' => $location_id, 'equipment_id' => $equipment_id, 'qty' => $qty]);
            $newLost->save();
        }

        // Create New Transaction for log
        $log = new EquipmentLog(['equipment_id' => $equipment_id, 'qty' => $qty, 'action' => 'M', 'notes' => "Missing $qty items from $location->name"]);
        $log->save();
    }

    /**
     * Get Stocktake + Process datatables ajax request.
     */
    public function getStocktake()
    {
        //dd(request('location_id'));
        $stocktake = EquipmentStocktake::where('location_id', request('location_id'));

        $dt = Datatables::of($stocktake)
            ->editColumn('id', function ($stock) {
                return '<div class="text-center"><a href="/equipment/stocktake/view/' . $stock->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('created_at', function ($stock) {
                return $stock->created_at->format('d/m/Y');
            })
            ->editColumn('created_by', function ($stock) {
                return $stock->user->name;
            })
            ->addColumn('summary', function ($stock) {
                return ($stock->summary());
            })
            ->editColumn('passed', function ($stock) {
                return ($stock->passed) ? 'Yes' : 'No';
            })
            ->rawColumns(['id', 'created_by', 'summary'])
            ->make(true);

        return $dt;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }
}
