<?php

namespace App\Http\Controllers\Misc;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocationOther;
use DB;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

class EquipmentLocationOtherController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment'))
            return view('errors/404');

        return view('misc/equipment/other/list');
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

        return view('misc/equipment/other/create');
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
        $other = EquipmentLocationOther::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment') && Auth::user()->company_id == $other->company_id)
            return view('errors/404');

        return view('misc/equipment/other/edit', compact('other'));
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

        request()->validate(['name' => 'required']); // Validate

        // Create Location
        //dd(request()->all());
        $equip = EquipmentLocationOther::create(request()->all());
        Toastr::success("Created location");

        return redirect('/equipment/other-location');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $other = EquipmentLocationOther::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.equipment') && Auth::user()->company_id == $other->company_id)
            return view('errors/404');

        request()->validate(['name' => 'required']); // Validate

        $other->update(request()->all());

        Toastr::success("Saved changes");

        return redirect("/equipment/other-location");
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $other = EquipmentLocationOther::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.equipment", $other))
            return view('errors/404');

        $other->status = 0;
        $other->save();
        Toastr::error("Deleted location");

        return redirect("/equipment/other-location");
    }

    /**
     * Get Equipment Inventory + Process datatables ajax request.
     */
    public function getOther()
    {
        $other = EquipmentLocationOther::where('status', 1)->get();
        $dt = Datatables::of($other)
            ->editColumn('id', function ($other) {
                return '<div class="text-center"><a href="/equipment/' . $other->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->addColumn('action', function ($other) {
                return (Auth::user()->hasPermission2('add.equipment')) ? '<a href="/equipment/other-location/' . $other->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>' : '';
            })
            ->rawColumns(['id', 'total', 'action'])
            ->make(true);

        return $dt;
    }
}
