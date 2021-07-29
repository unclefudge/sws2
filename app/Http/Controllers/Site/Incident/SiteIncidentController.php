<?php

namespace App\Http\Controllers\Site\Incident;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Site\Site;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Incident\SiteIncidentDoc;
use App\Models\Misc\FormQuestion;
use App\Models\Misc\FormResponse;
use App\Http\Requests;
//use App\Http\Requests\Site\SiteAccidentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteIncidentController
 * @package App\Http\Controllers
 */
class SiteIncidentController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.incident'))
            return view('errors/404');

        $progress = SiteIncident::where('status', 2)->get();

        return view('site/incident/list', compact('progress'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        if ($incident->step == 2)
            return view('site/incident/people', compact('incident'));
        elseif ($incident->step == 3)
            return view('site/incident/docs', compact('incident'));
        else
            return view('site/incident/show', compact('incident'));
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
            return view('site/incident/docs', compact('incident'));
        else
            return view('site/incident/show', compact('incident'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.incident'))
            return view('errors/404');

        return view('site/incident/create');
    }

    /**
     * Add docs/photos Form
     */
    public function docs($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $incident->step = 3;
        $incident->save();

        return view('site/incident/docs', compact('incident'));
    }

    /**
     * Lodge Incident Form
     */
    public function lodge($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $incident->step = 0;
        $incident->status = 1;
        $incident->save();

        return redirect('site/incident/');
    }

    /**
     * Show Incident - Involved
     */
    public function showInvolved($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        return view('site/incident/show-involved', compact('incident'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.incident'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'date' => 'required', 'type' => 'required', 'describe' => 'required', 'actions_taken' => 'required'];

        // Treatment required if type = 2 'injury'
        if (request('type') && in_array('2', request('type'))) {
            $rules['treatment'] = 'required';
            $rules['injured_part'] = 'required';
        }
        if (request('treatment') && in_array('20', request('treatment'))) $rules['treatment_other'] = 'required'; // type = 'other treatment'
        if (request('injured_part') && in_array('49', request('injured_part'))) $rules['injured_part_other'] = 'required'; // type = 'other treatment'
        if (request('type') && in_array('3', request('type'))) $rules['damage'] = 'required';  // Damage required if type = 3 'damage'


        //'treatment_other' => 'required_if:treatment,20', 'damage' => 'required_if:type,3'];
        $mesg = [
            'site_id.required'            => 'The site field is required.',
            'date.required'               => 'The date/time field is required.',
            'type.required'               => 'The type field is required.',
            'describe.required'           => 'The describe field is required.',
            'actions_taken.required'      => 'The actions field is required.',
            'injured_part.required'       => 'The part of body injured field is required',
            'injured_part_other.required' => 'The other body part field is required',
            'damage.required'             => 'The damage details field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $incident_request = request()->except('type', 'treatment', 'injured_part', 'injured_nature', 'injured_mechanism', 'injured_agency');

        // Format date from datetime picker to mysql format
        $incident_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date'))->toDateTimeString();
        $incident_request['supervisor'] = Site::find(request('site_id'))->supervisorsSBC();
        $incident_request['step'] = 2;
        $incident_request['status'] = 2;

        //dd($incident_request);

        // Create Site Incident
        $incident = SiteIncident::create($incident_request);

        //
        // Form Responses
        //
        $questions = ['type' => 1, 'treatment' => [14, 20], 'injured_part' => [21, 49], 'injured_nature' => 50, 'injured_mechanism' => 69, 'injured_agency' => 92];
        foreach ($questions as $field => $qid) {
            if (request($field)) {
                foreach (request($field) as $option_id) {
                    if (is_array($qid))
                        FormResponse::create(['question_id' => $qid[0], 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => ($option_id == $qid[1]) ? request($field.'_other') : null]);
                    else
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
                }
            }
        }
/*
        // Type
        foreach (request('type') as $option_id)
            FormResponse::create(['question_id' => 1, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);

        // Treatment
        if (request('treatment')) {

            foreach (request('treatment') as $option_id)
                FormResponse::create(['question_id' => 14, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => ($option_id == 20) ? request('treatment_other') : null]);
        }

        // Injured parts
        if (request('injured_part')) {
            foreach (request('injured_part') as $option_id)
                FormResponse::create(['question_id' => 21, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => ($option_id == 49) ? request('injured_part_other') : null]);
        }

        // Injured nature
        if (request('injured_nature')) {
            foreach (request('injured_nature') as $option_id)
                FormResponse::create(['question_id' => 50, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
        }

        // Injured mechanism
        if (request('injured_mechanism')) {
            foreach (request('injured_mechanism') as $option_id)
                FormResponse::create(['question_id' => 69, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
        }

        // Injured agency
        if (request('injured_agency')) {
            foreach (request('injured_agency') as $option_id)
                FormResponse::create(['question_id' => 92, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
        }
*/


        //$incident->emailIncident(); // Email incident

        Toastr::success("Lodged incident report");

        return redirect('site/incident/' . $incident->id . '/edit');
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function update($id)
    {
        $incident = SiteAccident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $this->validate(request(), ['notes' => 'required_without:status'], ['notes.required_without' => 'Please provide notes before you close the incident report']);

        //dd(request()->all());
        $incident_request = request()->all();

        // Format date from datetime picker to mysql format
        $date = new Carbon (preg_replace('/-/', '', request('date')));
        $incident_request['date'] = $date->toDateTimeString();

        // If Status closed 'field not present' set to 0
        if (!request()->has('status'))
            $incident_request['status'] = '0';

        // If status was modified then update resolved date
        if ($incident->status != $incident_request['status'])
            $incident_request['resolved_at'] = ($incident_request['status']) ? null : Carbon::now()->toDateTimeString();

        //dd($incident_request);
        $incident->update($incident_request);
        Toastr::success("Updated incident report");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function uploadAttachment(Request $request)
    {
        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main)))
        //    return json_encode("failed");

        //dd('here');
        //dd(request()->all());
        // Handle file upload
        $files = request()->file('multifile');
        foreach ($files as $file) {
            $path = "filebank/incident/" . request('incident_id');
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
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
            }

            $doc_request = request()->only('incident_id');
            $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $doc_request['attachment'] = $name;
            $doc_request['type'] = (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'gif', 'png'])) ? 'photo' : 'doc';

            // Create SiteIncidentDoc
            $doc = SiteIncidentDoc::create($doc_request);
            //$doc->incident_id = request('incident_id');
            //$doc->attachment = $name;
            //$doc->save();
        }

        return json_encode("success");
    }

    /**
     * Get Incidents current user is authorised to manage + Process datatables ajax request.
     */
    public
    function getIncidents()
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
