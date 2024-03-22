<?php

namespace App\Http\Controllers\Site\Incident;

use App\Http\Controllers\Controller;
use App\Models\Comms\Todo;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Incident\SiteIncidentWitness;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;

/**
 * Class SiteIncidentWitnessController
 * @package App\Http\Controllers
 */
class SiteIncidentWitnessController extends Controller
{

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
        $incident = SiteIncident::findorFail($incident_id);
        $witness = SiteIncidentWitness::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || $witness->user_id == Auth::user()->id))
            return view('errors/404');

        return view('site/incident/witness/show', compact('witness', 'incident'));
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

        return view('site/incident/witness/create', compact('incident'));
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

        $rules = ['name' => 'required', 'event_before' => 'required_if:assign_task,0', 'event' => 'required_if:assign_task,0', 'event_after' => 'required_if:assign_task,0'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'event_before_if.required' => 'The events leading up field is required.',
            'event.required_if' => 'The describe incident field is required.',
            'event_after_if.required' => 'The what happened after field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $witness_request = request()->all();
        $witness_request['incident_id'] = $incident->id;
        $witness_request['status'] = (request('assign_task') == 1) ? 2 : 1;  // Set to '2' pending until approved
        //dd($witness_request);

        // Create SiteIncidentWitness
        $witness = SiteIncidentWitness::create($witness_request);

        // Create ToDoo - if required
        if (request('assign_task')) {
            $todo = Todo::create(['name' => "Witness Statement for Incident @ $incident->site_name", 'info' => 'Please complete a Witness Statement for an incident that you witnessed', 'type' => 'incident witness', 'type_id' => $witness->id, 'company_id' => Auth::user()->company_id]);
            $todo->assignUsers(request('user_id'));
            $todo->emailToDo();
        }

        Toastr::success("Added witness statment");

        return redirect('site/incident/' . $incident->id . '/admin');
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($incident_id, $id)
    {
        $incident = SiteIncident::findorFail($incident_id);
        $witness = SiteIncidentWitness::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || $witness->user_id == Auth::user()->id))
            return view('errors/404');

        $rules = ['name' => 'required', 'event_before' => 'required', 'event' => 'required', 'event_after' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'event_before.required' => 'The events leading up field is required.',
            'event.required' => 'The describe incident field is required.',
            'event_after.required' => 'The what happened after field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $witness_request = request()->all();
        $witness_request['incident_id'] = $incident->id;
        $witness_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident)) ? 1 : 2;  // Set to '2' pending until approved
        //dd($witness_request);

        // Update SiteIncidentWitness
        $witness->update($witness_request);

        $todo = Todo::where('type', 'incident witness')->where('type_id', $witness->id)->first();
        if ($todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = Auth::user()->id;
            $todo->save();
        }

        Toastr::success("Updated witness statement");

        if ($witness->user_id == Auth::user()->id)
            return redirect('site/incident/' . $incident->id);

        return redirect('site/incident/' . $incident->id . '/admin');
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($incident_id, $id)
    {
        $incident = SiteIncident::findorFail($incident_id);
        $witness = SiteIncidentWitness::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.site.incident", $incident))
            return json_encode("failed");

        // Delete any Todoo
        $todo = Todo::where('type', 'incident witness')->where('type_id', $witness->id)->delete();

        $witness->delete();

        return json_encode('success');
    }
}
