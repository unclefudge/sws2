<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\User;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLost;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Equipment\EquipmentCategory;
use App\Models\Comms\Todo;
use App\Models\Site\Site;
use Intervention\Image\Facades\Image;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Alert;

class EquipmentController extends Controller {

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function inventory()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('equipment'))
            return view('errors/404');

        return view('misc/equipment/inventory');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function writeoff()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.equipment.stocktake'))
            return view('errors/404');

        $missing = EquipmentLost::all();

        return view('misc/equipment/writeoff', compact('missing'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment'))
            return view('errors/404');

        return view('misc/equipment/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $equip = Equipment::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.equipment', $equip))
            return view('errors/404');

        return view('misc/equipment/show', compact('equip'));
    }

    /**
     * Edit the form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Equipment::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment') && Auth::user()->company_id == $item->company_id)
            return view('errors/404');

        return view('misc/equipment/edit', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment') && Auth::user()->company_id == 3)
            return view('errors/404');

        request()->validate(['name' => 'required', 'subcategory_id' => 'required_if:category_id,3'], ['subcategory_id.required_if' => 'The sub-category field is required.']); // Validate

        // Create Item
        $equip_request = request()->all();
        if (request('category_id') == 3)
            $equip_request['category_id'] = request('subcategory_id');

        $equip = Equipment::create($equip_request);
        $qty = request('purchase_qty');

        // Handle attached Photo or Video
        if (request()->hasFile('media')) {
            $file = request()->file('media');
            $path = "filebank/equipment/";
            $name = 'e' . $equip->id . '.' . strtolower($file->getClientOriginalExtension());
            $path_name = $path . '/' . $name;
            $file->move($path, $name);

            // resize the image to a width of 1024 and constrain aspect ratio (auto height)
            if (exif_imagetype($path_name)) {
                Image::make(url($path_name))
                    ->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->save($path_name);
            } else
                Toastr::error("Bad image");

            $equip->attachment = $name;
            $equip->save();
        }

        // Purchase new items
        if ($qty) {
            $store = EquipmentLocation::where('site_id', 25)->first();
            // Create Store if not existing
            if (!$store) {
                $store = new EquipmentLocation(['site_id' => 25]);
                $store->save();
            }

            // Allocate New Item to Store
            $existing = EquipmentLocationItem::where('location_id', $store->id)->where('equipment_id', $equip->id)->first();
            if ($existing) {
                $existing->qty = $existing->qty + $qty;
                $existing->save();
            } else
                $store->items()->save(new EquipmentLocationItem(['location_id' => $store->id, 'equipment_id' => $equip->id, 'qty' => $qty]));

            // Update Purchased Qty
            $equip->purchased = $equip->purchased + $qty;
            $equip->purchased_last = Carbon::now()->toDateTimeString();
            $equip->save();

            // Update log
            $log = new EquipmentLog(['equipment_id' => $equip->id, 'qty' => $qty, 'action' => 'P']);
            $log->notes = 'Purchased ' . $qty . ' items';
            $equip->log()->save($log);
        }


        // Create New Transaction for log
        $trans = new EquipmentLog(['equipment_id' => $equip->id, 'action' => 'N', 'notes' => 'Created item']);
        $equip->log()->save($trans);

        Toastr::success("Created item");

        return redirect('/equipment/inventory');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $equip = Equipment::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment') && Auth::user()->company_id == $equip->company_id)
            return view('errors/404');

        request()->validate(['name' => 'required', 'subcategory_id' => 'required_if:category_id,3'], ['subcategory_id.required_if' => 'The sub-category field is required.']); // Validate

        // Update Equipment
        $equip_request = request()->all();
        if (request('category_id') == 3)
            $equip_request['category_id'] = request('subcategory_id');

        $equip->update($equip_request);

        $qty = request('purchase_qty');

        // Handle attached Photo or Video
        if (request()->hasFile('media')) {
            $file = request()->file('media');
            $path = "filebank/equipment/";
            $name = 'e' . $equip->id . '.' . strtolower($file->getClientOriginalExtension());
            $path_name = $path . '/' . $name;
            $file->move($path, $name);

            // resize the image to a width of 1024 and constrain aspect ratio (auto height)
            if (exif_imagetype($path_name)) {
                Image::make(url($path_name))
                    ->resize(1024, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->save($path_name);
            } else
                Toastr::error("Bad image");

            $equip->attachment = $name;
            $equip->save();
        }

        // Purchase new items
        if ($qty) {
            $store = EquipmentLocation::where('site_id', 25)->first();
            // Create Store if not existing
            if (!$store) {
                $store = new EquipmentLocation(['site_id' => 25]);
                $store->save();
            }

            // Allocate New Item to Store
            $existing = EquipmentLocationItem::where('location_id', $store->id)->where('equipment_id', $equip->id)->first();
            if ($existing) {
                $existing->qty = $existing->qty + $qty;
                $existing->save();
            } else
                $store->items()->save(new EquipmentLocationItem(['location_id' => $store->id, 'equipment_id' => $equip->id, 'qty' => $qty]));

            // Update Purchased Qty
            $equip->purchased = $equip->purchased + $qty;
            $equip->purchased_last = Carbon::now()->toDateTimeString();
            $equip->save();

            // Update log
            $log = new EquipmentLog(['equipment_id' => $equip->id, 'qty' => $qty, 'action' => 'P']);
            $log->notes = 'Purchased ' . $qty . ' items';
            $equip->log()->save($log);
        }

        Toastr::success("Saved changes");

        return redirect("/equipment/inventory");
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Equipment::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.equipment", $item))
            return view('errors/404');

        $item->status = 0;
        $item->save();
        Toastr::error("Deleted item");

        return redirect("/equipment/inventory");
    }


    /**
     * Write off the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function writeoffItems()
    {
        //dd(request()->all());

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2("del.equipment"))
            return view('errors/404');

        if (request('writeoff')) {
            foreach (request('writeoff') as $lost_id) {
                $lost = EquipmentLost::findOrFail($lost_id);
                $lost->equipment->disposed = $lost->equipment->disposed + $lost->qty;
                $lost->equipment->save();
                $log = new EquipmentLog(['equipment_id' => $lost->equipment_id, 'qty' => $lost->qty, 'action' => 'W', 'notes' => "Write off $lost->qty items from " . $lost->created_at->format('d/m/Y')]);
                $log->save();
                $lost->delete();
            }
        }
        Toastr::error("Items written off");

        return redirect("/equipment/inventory");
    }

    /**
     * Get Allocations + Process datatables ajax request.
     */
    public function getAllocation()
    {
        $items_list = (request('equipment_id')) ? [request('equipment_id')] : EquipmentLocationItem::all()->pluck('equipment_id')->toArray();

        if (request('site_id'))
            $items = EquipmentLocationItem::select([
                'equipment_location_items.id', 'equipment_location_items.location_id', 'equipment_location_items.equipment_id', 'equipment_location_items.qty', 'equipment_location_items.company_id',
                'equipment_location.site_id', 'equipment_location.other', 'equipment_location.status', 'equipment_categories.name AS catname',
                'equipment.name AS itemname', 'equipment.status', 'sites.name AS sitename', 'sites.code', 'sites.suburb'])
                ->join('equipment', 'equipment_location_items.equipment_id', '=', 'equipment.id')
                ->join('equipment_location', 'equipment_location_items.location_id', '=', 'equipment_location.id')
                ->join('equipment_categories', 'equipment_categories.id', '=', 'equipment.category_id')
                ->leftjoin('sites', 'equipment_location.site_id', '=', 'sites.id')
                ->whereIn('equipment_location_items.equipment_id', $items_list)
                ->where('equipment.status', 1)
                ->where('equipment_location.status', 1)
                ->where('equipment_location.site_id', request('site_id'));
        else {
            $items = EquipmentLocationItem::select([
                'equipment_location_items.id', 'equipment_location_items.location_id', 'equipment_location_items.equipment_id', 'equipment_location_items.qty', 'equipment_location_items.company_id',
                'equipment_location.site_id', 'equipment_location.other', 'equipment_location.status', 'equipment_categories.name AS catname',
                'equipment.name AS itemname', 'equipment.status', 'sites.name AS sitename', 'sites.code', 'sites.suburb'])
                ->join('equipment', 'equipment_location_items.equipment_id', '=', 'equipment.id')
                ->join('equipment_location', 'equipment_location_items.location_id', '=', 'equipment_location.id')
                ->join('equipment_categories', 'equipment_categories.id', '=', 'equipment.category_id')
                ->leftjoin('sites', 'equipment_location.site_id', '=', 'sites.id')
                ->where('equipment.status', 1)
                ->where('equipment_location.status', 1)
                ->whereIn('equipment_location_items.equipment_id', $items_list);
        }

        $dt = Datatables::of($items)
            ->addColumn('view', function ($item) {
                return '<div class="text-center"><a href="/equipment/' . $item->equipment_id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('qty', function ($item) {
                return ($item->equipment->total) ? "$item->qty / " . $item->equipment->total : 0;
            })
            ->editColumn('code', function ($item) {
                return ($item->location->site_id) ? $item->location->site->code : '-';
            })
            ->editColumn('suburb', function ($item) {
                return ($item->location->site_id) ? $item->location->site->suburb : '-';
            })
            ->editColumn('sitename', function ($item) {
                return ($item->location->site_id) ? $item->location->site->name : '-';
            })
            ->addColumn('action', function ($item) {
                $action = '';
                if (Auth::user()->allowed2('edit.equipment', $item)) {
                    if (!$item->inTransit())
                        $action .= "<a href='/equipment/$item->id/transfer' class='btn blue btn-xs btn-outline sbold uppercase margin-bottom'>Transfer</a>";
                }

                return $action;
            })
            ->rawColumns(['view', 'created_by', 'action'])
            ->make(true);

        return $dt;
    }


    /**
     * Get Missing + Process datatables ajax request.
     */
    public function getMissing()
    {

        $items = EquipmentLost::select([
            'equipment_lost.id', 'equipment_lost.location_id', 'equipment_lost.equipment_id', 'equipment_lost.qty', 'equipment_lost.company_id',
            'equipment_location.site_id', 'equipment_location.other', 'equipment_location.status',
            'equipment.name AS itemname', 'sites.name AS sitename', 'sites.code', 'sites.suburb'])
            ->join('equipment', 'equipment_lost.equipment_id', '=', 'equipment.id')
            ->join('equipment_location', 'equipment_lost.location_id', '=', 'equipment_location.id')
            ->leftjoin('sites', 'equipment_location.site_id', '=', 'sites.id')
            ->where('equipment_lost.equipment_id', request('equipment_id'));

        $dt = Datatables::of($items)
            ->editColumn('qty', function ($item) {
                return ($item->equipment->total) ? "$item->qty / " . $item->equipment->total : 0;
            })
            ->editColumn('code', function ($item) {
                return ($item->location->site_id) ? $item->location->site->code : '-';
            })
            ->editColumn('suburb', function ($item) {
                return ($item->location->site_id) ? $item->location->site->suburb : '-';
            })
            ->editColumn('sitename', function ($item) {
                return ($item->location->site_id) ? $item->location->site->name : '-';
            })
            ->addColumn('action', function ($item) {
                return (Auth::user()->allowed2('edit.equipment', $item)) ? '<a href="/equipment/' . $item->id . '/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>' : '';
            })
            ->rawColumns(['view', 'created_by', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Equipment Inventory + Process datatables ajax request.
     */
    public function getInventory()
    {
        $category_id = request('category_id');
        $cat_ids = array_merge([$category_id], EquipmentCategory::where('parent', $category_id)->where('status', 1)->pluck('id')->toArray());
        $equipment = Equipment::select([
            'equipment.id', 'equipment.category_id', 'equipment.name', 'equipment.length', 'equipment.purchased',  'equipment.min_stock', 'equipment.purchased_last', 'equipment.disposed', 'equipment.status', 'equipment.company_id',
            'equipment_categories.name AS catname'
        ])
            ->join('equipment_categories', 'equipment_categories.id', '=', 'equipment.category_id')
            ->whereIn('equipment.category_id', $cat_ids)
            ->where('equipment.status', 1);

        $dt = Datatables::of($equipment)
            ->editColumn('id', function ($equip) {
                return '<div class="text-center"><a href="/equipment/' . $equip->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('min_stock', function ($equip) {
                $str = $equip->min_stock;
                if ($equip->total < $equip->min_stock)
                    $str = "<span class='font-red'>$str</span>";
                return $str;
            })
            ->editColumn('purchased_last', function ($equip) {
                return ($equip->purchased_last) ? $equip->purchased_last->format('d/m/Y') : '-';
            })
            ->addColumn('total', function ($equip) {
                $str = $equip->total;
                if ($equip->total_excess > 0 && in_array($equip->category_id, [1, 2]))
                    $str = "<span class='label label-warning'>$equip->total</span>";
                if ($equip->total_excess < 0 && in_array($equip->category_id, [1, 2]))
                    $str = "<span class='label label-danger'>$equip->total</span>";

                return $str;
            })
            ->addColumn('lost', function ($equip) {
                return ($equip->total_lost) ? $equip->total_lost : '-';
            })
            ->addColumn('action', function ($equip) {
                return (Auth::user()->hasPermission2('add.equipment')) ? '<a href="/equipment/' . $equip->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>' : '';
            })
            ->rawColumns(['id', 'total', 'min_stock', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Transaction History + Process datatables ajax request.
     */
    public function getLog()
    {
        $transactions = EquipmentLog::where('equipment_id', request('equipment_id'));

        $dt = Datatables::of($transactions)
            ->editColumn('created_at', function ($trans) {
                return $trans->created_at->format('d/m/Y');
            })
            ->editColumn('created_by', function ($trans) {
                return $trans->user->name;
            })
            ->rawColumns(['id', 'created_by'])
            ->make(true);

        return $dt;
    }
}
