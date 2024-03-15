<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanySupervisor;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CompanySupervisorController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('area.super'))
            return view('errors/404');

        return view('site/supervisor/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->hasPermission2('edit.area.super'))
        //    return view('errors/404');

        //return view('site/create');
    }

    /**
     * Store a newly created resource in storage via ajax.
     */
    public function store(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.area.super'))
            return view('errors/404');

        if ($request->ajax()) {
            CompanySupervisor::create($request->all());

            // Ensure each Supervisor has a CC company
            if (request('company_id') == 3) {
                $user = User::find(request('user_id'));
                list($first, $last) = explode(' ', $user->fullname, 2);
                $company_name = "Cc-" . strtolower($first) . " $last";
                $exists = Company::where('name', $company_name)->first();
                if ($exists) {
                    if ($exists->status == 0) {
                        $exists->status = 1;
                        $exists->save();
                    }
                } else {
                    $company = Company::create(
                        ['name' => $company_name, 'email' => $user->email, 'phone' => $user->phone, 'address' => $user->company->address, 'suburb' => $user->company->suburb,
                            'state' => $user->company->state, 'postcode' => $user->company->postcode, 'abn' => $user->company->abn, 'maxjobs' => 50,
                            'gst' => 0, 'payroll_tax' => 0, 'category' => 0, 'approved_by' => 1, 'approved_at' => Carbon::now()->toDateTimeString(),
                            'notes' => 'Working under Cape Cods Safe Work Method Statement', 'parent_company' => 3, 'status' => 1]);
                    $company->tradesSkilledIn()->sync([31]);
                }
            }
            return json_encode('success');
        }

        return view('errors/404');
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $record = CompanySupervisor::find($id);
        $user = User::find($record->user_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.area.super', $user))
            return view('errors/404');

        $deleted = CompanySupervisor::where('id', $id)->orWhere('parent_id', $id)->delete();

        // Deactivate CC company if required
        if ($user->company_id == 3) {
            $active = CompanySupervisor::where('user_id', $user->id)->first();
            if (!$active) {
                list($first, $last) = explode(' ', $user->fullname, 2);
                $company_name = "Cc-" . strtolower($first) . " $last";
                $exists = Company::where('name', $company_name)->first();
                if ($exists) {
                    if ($exists->status == 1) {
                        $exists->status = 0;
                        $exists->save();
                    }
                }
            }
        }
        return json_encode('success');
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(SiteRequest $request, $slug)
    {
        //
    }

    /**
     * Get Current Supervisors the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSupers(Request $request)
    {
        // Current Supervisors
        $supervisors = DB::table('company_supervisors AS s')->select('s.id', 's.user_id', 's.parent_id',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'))
            ->where('s.company_id', Auth::user()->company_id)
            ->join('users', 's.user_id', '=', 'users.id')->get();

        $supers = [];
        foreach ($supervisors as $super) {
            $supers[] = ['id' => $super->id, 'user_id' => $super->user_id, 'name' => $super->fullname, 'parent_id' => $super->parent_id, 'open' => false];
        }

        // Company Staff
        $staff = Auth::user()->company->staffStatus(1);
        $sel_staff = [];
        $sel_staff[] = ['value' => 0, 'text' => 'Select employee to add as Supervisor'];
        foreach ($staff as $user) {
            $sel_staff[] = ['value' => $user->id, 'text' => $user->firstname . ' ' . $user->lastname];
        }

        $json = [];
        $json[] = $supers;
        $json[] = $sel_staff;

        return $json;
    }


}
