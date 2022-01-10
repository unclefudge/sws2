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
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || Auth::user()->allowed2('add.site.incident', $incident)))
            return view('errors/404');

        $rules = ['type' => 'required', 'name' => 'required'];

        // Type required if type = 2 'injury'
        if (request('type') && request('type') == '13') $rules['type_other'] = 'required';

        $mesg = ['type.required' => 'The involvement type field is required.', 'type_other.required' => 'The other type field is required.', 'name.required' => 'The name field is required.'];
        request()->validate($rules, $mesg); // Validate

        $people_request = request()->all();
        $people_request['incident_id'] = $incident->id;
        $people_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident)) ? 1 : 2;  // Set to '2' pending until approved

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
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || (request('user_id') && request('user_id') == Auth::user()->id)))
            return view('errors/404');

        $rules = ['type' => 'required', 'name' => 'required'];

        // Type required if type = 2 'injury'
        if (request('type') && request('type') == '13') $rules['type_other'] = 'required';

        $mesg = ['type.required' => 'The involvement type field is required.', 'type_other.required' => 'The other type field is required.', 'name.required' => 'The name field is required.'];
        request()->validate($rules, $mesg); // Validate

        $people_request = request()->all();
        $people_request['incident_id'] = $incident->id;
        $people_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident)) ? 1 : 2;  // Set to '2' pending until approved

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
}
