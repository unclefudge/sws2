<?php

namespace App\Http\Controllers\Site\Incident;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Incident\SiteIncidentPeople;
use App\Models\Misc\FormQuestion;
use App\Models\Misc\FormResponse;
use App\Http\Requests;
//use App\Http\Requests\Site\SiteAccidentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteIncidentPeopleController
 * @package App\Http\Controllers
 */
class SiteIncidentPeopleController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($incident_id, $id)
    {
        $person = SiteIncidentPeople::findorFail($id);
        $incident = SiteIncident::findorFail($incident_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        return view('site/incident/people/show', compact('person', 'incident'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        if ($incident->step == 2)
            return view('site/incident/people', compact('incident'));
        elseif ($incident->step == 3)
            return view('site/incident/photos', compact('incident'));
        else
            return view('site/incident/show', compact('incident'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.incident'))
            return view('errors/404');

        return view('site/incident/people/create', compact('incident'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['type' => 'required', 'name' => 'required'];

        // Type required if type = 2 'injury'
        if (request('type') && request('type') == '13') $rules['type_other'] = 'required';

        $mesg = ['type.required' => 'The involvement type field is required.', 'type_other.required' => 'The other type field is required.', 'name.required' => 'The name field is required.'];
        request()->validate($rules, $mesg); // Validate

        $people_request = request()->all();
        $people_request['incident_id'] = $incident->id;
        $people_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident->id)) ? 1 : 2;  // Set to '2' pending until approved

        // Format date from datetime picker to mysql format
        $people_request['dob'] = (request('dob')) ? Carbon::createFromFormat('d/m/Y h:m', request('dob') . '00:00')->toDateTimeString() : null;

        if (request('user_id')) {
            $user = User::find(request('user_id'));
            if (!request('employer'))
                $people_request['employer'] = $user->company->name;
        }

        //dd($people_request);

        // Create SiteIncidentPeople
        $people = SiteIncidentPeople::create($people_request);

        Toastr::success("Added person involved");

        return redirect('site/incident/' . $incident->id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($incident_id, $id)
    {
        $incident = SiteIncident::findorFail($incident_id);
        $people = SiteIncidentPeople::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['type' => 'required', 'name' => 'required'];

        // Type required if type = 2 'injury'
        if (request('type') && request('type') == '13') $rules['type_other'] = 'required';

        $mesg = ['type.required' => 'The involvement type field is required.', 'type_other.required' => 'The other type field is required.', 'name.required' => 'The name field is required.'];
        request()->validate($rules, $mesg); // Validate

        $people_request = request()->all();
        $people_request['incident_id'] = $incident->id;
        $people_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident->id)) ? 1 : 2;  // Set to '2' pending until approved

        // Format date from datetime picker to mysql format
        $people_request['dob'] = (request('dob')) ? Carbon::createFromFormat('d/m/Y h:m', request('dob') . '00:00')->toDateTimeString() : null;

        if (request('user_id')) {
            $user = User::find(request('user_id'));
            if (!request('employer'))
                $people_request['employer'] = $user->company->name;
        }

        //dd($people_request);

        // Update SiteIncidentPeople
        $people->update($people_request);

        Toastr::success("Updated person involved");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Get Incidents current user is authorised to manage + Process datatables ajax request.
     */
    public function getIncidents()
    {
        $company_ids = (request('site_group')) ? [request('site_group')] : [Auth::user()->company_id, Auth::user()->company->reportsTo()->id];
        $incidents_ids = Auth::user()->siteIncidents(request('status'))->pluck('id')->toArray();
        $incident_records = SiteIncident::select([
            'site_incidents.id', 'site_incidents.site_id', 'site_incidents.describe',
            'site_incidents.status', 'sites.company_id',
            DB::raw('DATE_FORMAT(site_incidents.date, "%d/%m/%y") AS nicedate'),
            DB::raw('DATE_FORMAT(site_incidents.resolved_at, "%d/%m/%y") AS nicedate2'),
            DB::raw('sites.name AS sitename'), 'sites.code',
        ])
            ->join('sites', 'site_incidents.site_id', '=', 'sites.id')
            ->where('site_incidents.status', '=', request('status'))
            ->whereIn('site_incidents.id', $incidents_ids)
            ->whereIn('sites.company_id', $company_ids);

        $dt = Datatables::of($incident_records)
            ->addColumn('view', function ($incident) {
                return ('<div class="text-center"><a href="/site/incident/' . $incident->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('supervisor', function ($incident) {
                return ($incident->site->supervisorsSBC());
            })
            ->editColumn('nicedate2', function ($incident) {
                return ($incident->nicedate2 == '00/00/00') ? '' : $incident->nicedate2;
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
