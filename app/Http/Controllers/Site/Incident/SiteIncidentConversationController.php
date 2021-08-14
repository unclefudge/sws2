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
use App\Models\Site\Incident\SiteIncidentConversation;
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
 * Class SiteIncidentConversationController
 * @package App\Http\Controllers
 */
class SiteIncidentConversationController extends Controller {

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
        $conversation = SiteIncidentConversation::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        return view('site/incident/conversation/show', compact('conversation', 'incident'));
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

        return view('site/incident/conversation/create', compact('incident'));
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

        $rules = ['name' => 'required', 'start' => 'required', 'end' => 'required', 'details' => 'required'];
        $mesg = [
            'name.required' => 'The conversation between field is required.',
            'start.required' => 'The started field is required.',
            'end.required' => 'The ended field is required.',
            'details.required' => 'The details field is required.',
        ];
        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        $conversation_request = request()->all();
        $conversation_request['incident_id'] = $incident->id;
        $conversation_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident)) ? 1 : 2;  // Set to '2' pending until approved
        $conversation_request['start'] = Carbon::createFromFormat('d/m/Y H:i', request('start'))->toDateTimeString();  // Format date from datetime picker to mysql format
        $conversation_request['end'] = Carbon::createFromFormat('d/m/Y H:i', request('end'))->toDateTimeString();  // Format date from datetime picker to mysql format
        //dd($conversation_request);

        // Create SiteIncidentConversation
        $conversation = SiteIncidentConversation::create($conversation_request);

        Toastr::success("Added conversation");

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
        $conversation = SiteIncidentConversation::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['name' => 'required', 'start' => 'required', 'end' => 'required', 'details' => 'required'];
        $mesg = [
            'name.required' => 'The conversation between field is required.',
            'start.required' => 'The started field is required.',
            'end.required' => 'The ended field is required.',
            'details.required' => 'The details field is required.',
        ];
        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());

        $conversation_request = request()->all();
        $conversation_request['incident_id'] = $incident->id;
        $conversation_request['status'] = (Auth::user()->allowed2('del.site.incident', $incident)) ? 1 : 2;  // Set to '2' pending until approved
        $conversation_request['start'] = Carbon::createFromFormat('d/m/Y H:i', request('start'))->toDateTimeString();  // Format date from datetime picker to mysql format
        $conversation_request['end'] = Carbon::createFromFormat('d/m/Y H:i', request('end'))->toDateTimeString();  // Format date from datetime picker to mysql format
        //dd($conversation_request);

        // Update SiteIncidentWitness
        $conversation->update($conversation_request);

        Toastr::success("Updated conversation");

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
        $conversation = SiteIncidentConversation::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.site.incident", $incident))
            return json_encode("failed");

        $conversation->delete();

        return json_encode('success');
    }
}
