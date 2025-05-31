<?php

namespace App\Http\Controllers\Site\Planner;


use App\Http\Controllers\Controller;
use App\Http\Requests\Misc\RoleRequest;
use App\Models\Site\Planner\PublicHoliday;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;

class PublicHolidayController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('settings')))
            return view('errors/404');

        return view('planner/publicholidays');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');

        return view('planner/publicholidays-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return view('errors/404');


        $rules = ['name' => 'required', 'date' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'date.required' => 'The date field is required.'];

        request()->validate($rules, $mesg); // Validate

        // Create PublicHoliday
        $holiday_request = request()->all();
        $holiday_request['date'] = (request('date')) ? Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString() : null;
        $holiday_request['type'] = 'public';


        //dd($holiday_request);
        PublicHoliday::create($holiday_request);
        Toastr::success("Added date");

        return redirect('/planner/publicholidays');
    }


    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $holiday = PublicHoliday::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('edit.settings'))
            return json_encode("failed");

        //dd($holiday->id);
        $holiday->delete();

        return json_encode('success');
    }


    public function show(Request $request)
    {
        //
        dd('show');
    }

    public function getDates()
    {
        $scaff_records = PublicHoliday::select([
            'public_holidays.id', 'public_holidays.name', 'public_holidays.date', 'public_holidays.status',
            DB::raw('DATE_FORMAT(public_holidays.date, "%d/%m/%y") AS nicedate')])
            ->where('public_holidays.date', '>', Carbon::today());

        $dt = Datatables::of($scaff_records)
            ->addColumn('view', function ($report) {
                return '<div class="text-center"><a href="/planner/publicholiday/' . $report->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->addColumn('day', function ($report) {
                $record = PublicHoliday::find($report->id);
                $day = $record->date->format('D');
                return $day;
            })
            ->addColumn('action', function ($report) {
                $record = PublicHoliday::find($report->id);
                $name = $record->name . " (" . $record->date->format('d/m/Y') . ")";
                $actions = '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/planner/publicholidays/' . $record->id . '" data-name="' . $name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }

}
