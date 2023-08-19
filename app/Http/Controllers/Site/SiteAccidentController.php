<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Site\Site;
use App\Models\Site\SiteAccident;
use App\Http\Requests;
use App\Http\Requests\Site\SiteAccidentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteHazardController
 * @package App\Http\Controllers
 */
class SiteAccidentController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.accident'))
            return view('errors/404');

        return view('site/accident/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.accident'))
            return view('errors/404');

        return view('site/accident/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SiteAccidentRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.accident'))
            return view('errors/404');

        $accident_request = $request->all();

        // Format date from datetime picker to mysql format
        $date = new Carbon (preg_replace('/-/', '', $request->get('date')));
        $accident_request['date'] = $date->toDateTimeString();
        $accident_request['supervisor'] = Site::find($request->get('site_id'))->supervisorName;

        // Create Site Accident
        $accident = SiteAccident::create($accident_request);
        $accident->emailAccident(); // Email accident

        Toastr::success("Lodged accident report");

        return redirect('dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $accident = SiteAccident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.accident', $accident))
            return view('errors/404');

        return view('site/accident/show', compact('accident'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $accident = SiteAccident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.accident', $accident))
            return view('errors/404');

        $this->validate(request(), ['notes' => 'required_without:status'], ['notes.required_without' => 'Please provide notes before you close the accident report']);

        //dd(request()->all());
        $accident_request = request()->all();

        // Format date from datetime picker to mysql format
        $date = new Carbon (preg_replace('/-/', '', request('date')));
        $accident_request['date'] = $date->toDateTimeString();

        // If Status closed 'field not present' set to 0
        if (!request()->has('status'))
            $accident_request['status'] = '0';

        // If status was modified then update resolved date
        if ($accident->status != $accident_request['status'])
            $accident_request['resolved_at'] = ($accident_request['status']) ? null : Carbon::now()->toDateTimeString();

        //dd($accident_request);
        $accident->update($accident_request);
        Toastr::success("Updated accident report");

        return redirect('site/accident/' . $accident->id);
    }

    /**
     * Get Accidents current user is authorised to manage + Process datatables ajax request.
     */
    public function getAccidents(Request $request)
    {
        $company_ids = (request('site_group')) ? [request('site_group')] : [Auth::user()->company_id, Auth::user()->company->reportsTo()->id];
        $accident_ids = Auth::user()->siteAccidents($request->get('status'))->pluck('id')->toArray();
        $accident_records = SiteAccident::select([
            'site_accidents.id', 'site_accidents.site_id', 'site_accidents.name',
            'site_accidents.location', 'site_accidents.nature', 'site_accidents.info',
            'site_accidents.status', 'sites.company_id',
            DB::raw('DATE_FORMAT(site_accidents.date, "%d/%m/%y") AS nicedate'),
            DB::raw('DATE_FORMAT(site_accidents.resolved_at, "%d/%m/%y") AS nicedate2'),
            DB::raw('sites.name AS sitename'), 'sites.code',
        ])
            ->join('sites', 'site_accidents.site_id', '=', 'sites.id')
            ->where('site_accidents.status', '=', $request->get('status'))
            ->whereIn('site_accidents.id', $accident_ids)
            ->whereIn('sites.company_id', $company_ids);

        $dt = Datatables::of($accident_records)
            ->addColumn('view', function ($accident) {
                return ('<div class="text-center"><a href="/site/accident/' . $accident->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('supervisor', function ($accident) {
                return ($accident->site->supervisorName);
            })
            ->editColumn('nicedate2', function ($accident) {
                return ($accident->nicedate2 == '00/00/00') ? '' : $accident->nicedate2;
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
