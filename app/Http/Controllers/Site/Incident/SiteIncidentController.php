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
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;
use ZipArchive;

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
            return view('site/incident/create-people', compact('incident'));
        elseif ($incident->step == 3)
            return view('site/incident/create-docs', compact('incident'));
        else
            return view('site/incident/show', compact('incident'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAdmin($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');


        return view('site/incident/admin', compact('incident'));
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
    public function createDocs($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || Auth::user()->allowed2('add.site.incident', $incident)))
            return view('errors/404');

        $incident->step = 3;
        $incident->save();

        return view('site/incident/create-docs', compact('incident'));
    }

    /**
     * Lodge Incident Form
     */
    public function lodge($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.incident', $incident) || Auth::user()->allowed2('add.site.incident', $incident)))
            return view('errors/404');

        $incident->step = 0;
        $incident->status = 1;
        $incident->save();

        //$incident->emailIncident(); // Email incident

        return redirect('site/incident/');
    }

    /**
     * Show Incident - Involved
     */
    /*public function showInvolved($id)
    {
        $incident = SiteIncident::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        return view('site/incident/show-involved', compact('incident'));
    }*/

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

        $rules = ['site_cc'  => 'required', 'site_id' => 'required_if:site_cc,1', 'site_name' => 'required_if:site_cc,0', 'location' => 'required', 'date' => 'required', 'type' => 'required',
                  'describe' => 'required', 'actions_taken' => 'required'];

        // Treatment required if type = 2 'injury'
        if (request('type') && in_array('2', request('type'))) {
            $rules['treatment'] = 'required';
            $rules['injured_part'] = 'required';
        }
        if (request('treatment') && in_array('20', request('treatment'))) $rules['treatment_other'] = 'required'; // type = 'other treatment'
        if (request('injured_part') && in_array('49', request('injured_part'))) $rules['injured_part_other'] = 'required'; // type = 'other treatment'
        if (request('type') && in_array('3', request('type'))) $rules['damage'] = 'required';  // Damage required if type = 3 'damage'

        $mesg = [
            'site_cc.required'            => 'The incident occur field is required.',
            'site_id.required_if'         => 'The site field is required.',
            'site_name.required_if'       => 'The place of incident field is required.',
            'date.required'               => 'The date/time field is required.',
            'type.required'               => 'The type field is required.',
            'describe.required'           => 'The what occured field is required.',
            'actions_taken.required'      => 'The actions taken field is required.',
            'injured_part.required'       => 'The part(s) of body injured field is required',
            'injured_part_other.required' => 'The other body part field is required',
            'damage.required'             => 'The damage details field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $site = (request('site_id')) ? Site::findOrFail(request('site_id')) : null;

        $incident_request = request()->except('type', 'treatment', 'injured_part', 'injured_nature', 'injured_mechanism', 'injured_agency');

        $incident_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date'))->toDateTimeString();  // Format date from datetime picker to mysql format
        $incident_request['site_name'] = ($site) ? $site->name : request('site_name');
        $incident_request['site_supervisor'] = ($site) ? $site->supervisorsSBC() : 'N/A';
        $incident_request['company_id'] = ($site) ? $site->company_id : Auth::user()->company->reportsTo()->id;
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
                        FormResponse::create(['question_id' => $qid[0], 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => ($option_id == $qid[1]) ? request($field . '_other') : null]);
                    else
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
                }
            }
        }
        Toastr::success("Lodged incident report");

        return redirect('site/incident/' . $incident->id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['site_cc'  => 'required', 'site_id' => 'required_if:site_cc,1', 'site_name' => 'required_if:site_cc,0', 'location' => 'required', 'date' => 'required', 'type' => 'required',
                  'describe' => 'required', 'actions_taken' => 'required'];
        $mesg = [
            'site_cc.required'       => 'The incident occur field is required.',
            'site_id.required_if'    => 'The site field is required.',
            'site_name.required_if'  => 'The place of incident field is required.',
            'date.required'          => 'The date/time field is required.',
            'type.required'          => 'The type field is required.',
            'describe.required'      => 'The what occured field is required.',
            'actions_taken.required' => 'The actions taken field is required.',
        ];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'notification');

            return back()->withErrors($validator)->withInput();
        }

        $site = (request('site_id')) ? Site::findOrFail(request('site_id')) : null;
        $incident_request = request()->except('type');
        //dd($incident_request);


        $incident_request['site_id'] = (request('site_cc')) ? request('site_id') : null;
        $incident_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date'))->toDateTimeString();
        $incident_request['site_name'] = ($site) ? $site->name : request('site_name');
        $incident_request['site_supervisor'] = ($site) ? $site->supervisorsSBC() : 'N/A';

        // If status was modified then update resolved date
        //if ($incident_request['status'] && $incident->status != $incident_request['status'])
        //    $incident_request['resolved_at'] = ($incident_request['status']) ? null : Carbon::now()->toDateTimeString();

        //dd($incident_request);

        $incident->update($incident_request);

        //
        // Form Responses - Type only
        //
        $options_all = FormQuestion::find(1)->optionsArray();
        $options_selected = [];
        foreach (request('type') as $option_id) {
            $options_selected[] = $option_id;
            $response = FormResponse::where('question_id', 1)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
            if (!$response)
                FormResponse::create(['question_id' => '1', 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id]);
        }

        // Delete existing response if not current
        $delete_type = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        // Delete all existing 'Injury' responses if type != '2' (injury)
        if (!in_array('2', request('type')))
            $delete_non_injury = FormResponse::whereIn('question_id', [14, 21, 50, 69, 92])->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        Toastr::success("Updated incident report");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateInjury($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['treatment' => 'required', 'injured_part' => 'required'];

        if (request('treatment') && in_array('20', request('treatment'))) $rules['treatment_other'] = 'required'; // type = 'other treatment'
        if (request('injured_part') && in_array('49', request('injured_part'))) $rules['injured_part_other'] = 'required'; // type = 'other treatment'
        //if (request('type') && in_array('3', request('type'))) $rules['damage'] = 'required';  // Damage required if type = 3 'damage'

        $mesg = [
            'injured_part.required'       => 'The part(s) of body injured field is required',
            'injured_part_other.required' => 'The other body part field is required',
            //'damage.required'             => 'The damage details field is required.',
        ];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'injury');

            return back()->withErrors($validator)->withInput();
        }

        //
        // Form Responses
        //
        $options_all = FormQuestion::find(14)->optionsArray() + FormQuestion::find(21)->optionsArray() + FormQuestion::find(50)->optionsArray() + FormQuestion::find(69)->optionsArray() + FormQuestion::find(92)->optionsArray();
        $options_selected = [];
        $questions = ['treatment' => [14, 20], 'injured_part' => [21, 49], 'injured_nature' => 50, 'injured_mechanism' => 69, 'injured_agency' => 92];
        foreach ($questions as $field => $quest_id) {
            if (request($field)) {
                foreach (request($field) as $option_id) {
                    $options_selected[] = $option_id;
                    $qid = (is_array($quest_id)) ? $quest_id[0] : $quest_id;
                    $info = (is_array($quest_id) && $option_id == $quest_id[1]) ? request($field . '_other') : null;
                    $response = FormResponse::where('question_id', $qid)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
                    if ($response && $response->info != $info) {
                        $response->info = $info;
                        $response->save();
                    } elseif (!$response)
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $info]);
                }
            }
        }

        // Delete existing response if not current
        $deleted = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        Toastr::success("Updated incident report");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDamage($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['damage' => 'required'];
        $mesg = ['damage.required' => 'The damage details field is required.'];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'damage');

            return back()->withErrors($validator)->withInput();
        }

        $incident_request = request()->all();
        $incident->update($incident_request);

        Toastr::success("Updated incident report");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDetails($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $incident_request = request()->all();

        // If status was modified then update resolved date
        if ($incident->status != $incident['status'])
            $incident_request['resolved_at'] = ($incident_request['status']) ? null : Carbon::now()->toDateTimeString();

        $incident->update($incident_request);

        Toastr::success("Updated incident report");

        return redirect('site/incident/' . $incident->id . '/admin');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateRegulator($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['notifiable_reason' => 'required'];
        $mesg = ['notifiable_reason.required' => 'The context field is required.'];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'regulator');

            return back()->withErrors($validator)->withInput();
        }

        $incident_request = request()->all();
        $incident_request['regulator_date'] = (request('regulator_date')) ? Carbon::createFromFormat('d/m/Y H:i', request('regulator_date'))->toDateTimeString() : null;

        //dd($incident_request);
        $incident->update($incident_request);

        Toastr::success("Updated Regulator Actions");

        return redirect('site/incident/' . $incident->id . '/admin');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateReview($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $incident_request = request()->all();

        //dd($incident_request);
        $incident->update($incident_request);

        Toastr::success("Updated Review");

        return redirect('site/incident/' . $incident->id . '/admin');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function addDocs($id)
    {
        $incident = SiteIncident::findOrFail($id);
        $allowed = false;
        $reviewsBy = $incident->reviewsBy();
        if (Auth::user()->allowed2('edit.site.incident', $incident)) $allowed = true; // Edit incident
        if ($incident->people->where('user_id', Auth::user()->id)) $allowed = true;  // Involved Person
        if ($incident->hasAssignedTask(Auth::user()->id)) $allowed = true; // Assigned task
        if (isset($reviewsBy[Auth::user()->id])) $allowed = true; // Reviewed by

        // Check authorisation and throw 404 if not
        if (!$allowed)
            return view('errors/404');

        return view('site/incident/add-docs', compact('incident'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function addNote($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.incident', $incident))
            return view('errors/404');

        $rules = ['action' => 'required'];
        $mesg = ['action.required' => 'The description details field is required.'];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'notes');

            return back()->withErrors($validator)->withInput();
        }
        //dd(request()->all());

        $action_request = request()->all();
        $action_request['table'] = 'site_incidents';
        $action_request['table_id'] = $incident->id;

        //dd($action_request);
        $action = Action::create($action_request);
        //$incident->emailAction($action);

        Toastr::success("Added note");

        return redirect('site/incident/' . $incident->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function addReview($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['assign_review' => 'required', 'review_role' => 'required'];
        $mesg = ['assign_review.required' => 'The assigned to field is required.', 'review_role.required' => 'The role field is required.'];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'review');

            return back()->withErrors($validator)->withInput();
        }
        //dd(request()->all());

        // Create Todoo Review
        $name = "Site Incident Review : " . request('review_role');
        $todo = Todo::create(['name' => $name, 'info' => 'Please review and sign off your acceptance of this incident report', 'type' => 'incident review', 'type_id' => $incident->id, 'company_id' => Auth::user()->company_id]);
        $todo->assignUsers(request('assign_review'));
        $todo->emailToDo();

        Toastr::success("Assigned review");

        return redirect('site/incident/' . $incident->id . '/admin');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function signoff($id)
    {
        $incident = SiteIncident::findOrFail($id);
        $reviewsBy = $incident->reviewsBy();

        // Check authorisation and throw 404 if not
        if (!isset($reviewsBy[Auth::user()->id]))
            return view('errors/404');

        //dd(request()->all());

        // Get relevant Todoo Review task
        $todo = Auth::user()->todoType('incident review')->where('type_id', $incident->id)->first();
        $todo->comments = request('comments');

        if (request('done_at') == 1) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = Auth::user()->id;
            Toastr::success("Sign Off Acceptance");
        } else
            Toastr::success("Comments added");

        $todo->save();

        return redirect('site/incident/' . $incident->id);
    }


    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAttachment(Request $request)
    {
        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main)))
        //    return json_encode("failed");

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
        }

        return json_encode("success");
    }

    public function reportPDF($id)
    {
        $incident = SiteIncident::findOrFail($id);

        if ($incident) {
            //return view('pdf/site/incident', compact('incident'));
            return PDF::loadView('pdf/site/incident', compact('incident'))->setPaper('a4')->stream();

            $pdf = PDF::loadView('pdf/site/incident', compact('incident'))->setPaper('a4')->stream();
            $file = public_path('filebank/company/' . $doc->for_company_id . '/wms/' . $doc->name . ' v' . $doc->version . ' ref-' . $doc->id . ' ' . '.pdf');
            if (file_exists($file))
                unlink($file);
            $pdf->save($file);
            return $pdf->stream();
        }
    }

    public function reportZIP($id)
    {
        $incident = SiteIncident::findOrFail($id);

        if ($incident) {
            $dir = '/filebank/tmp/incident/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $report_file = public_path($dir . "/Incident Report $incident->id.pdf");

            //return view('pdf/site/incident', compact('incident'));
            $pdf = PDF::loadView('pdf/site/incident', compact('incident'))->setPaper('a4');
            if (file_exists($report_file))
                unlink($report_file);
            $pdf->save($report_file);


            // Generate ZIP
            $zip_file = public_path($dir . "/Incident Report $incident->id.zip");
            if (file_exists($zip_file))
                unlink($zip_file);

            $zip = new ZipArchive();
            if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE)
            {
                $zip->addFile($report_file, "Incident Report $incident->id.pdf"); // Incident Report

                // Photos / Docs
                foreach ($incident->docs as $doc) {
                    $file =  public_path('/filebank/incident/'.$doc->incident_id.'/'.$doc->attachment);
                    if (file_exists($file))
                        $zip->addFile($file, $doc->attachment);
                }

                // Assigned Tasks
                foreach ($incident->todos() as $todo) {
                    if ($todo->attachment && file_exists(public_path($todo->attachmentUrl)))
                        $zip->addFile(public_path($todo->attachmentUrl), $todo->attachment);

                }
                $zip->close();

                return redirect($dir . "/Incident Report $incident->id.zip");
            }
        }
    }


    /**
     * Get Incidents current user is authorised to manage + Process datatables ajax request.
     */
    public function getIncidents()
    {
        $incidents_ids = Auth::user()->siteIncidents(request('status'))->pluck('id')->toArray();
        $incident_records = SiteIncident::select([
            'site_incidents.id', 'site_incidents.site_name', 'site_incidents.site_supervisor', 'site_incidents.describe', 'site_incidents.exec_summary', 'site_incidents.status',
            DB::raw('DATE_FORMAT(site_incidents.date, "%d/%m/%y") AS nicedate'),
            DB::raw('DATE_FORMAT(site_incidents.resolved_at, "%d/%m/%y") AS nicedate2'),
        ])
            ->where('site_incidents.status', '=', request('status'))
            ->whereIn('site_incidents.id', $incidents_ids);

        $dt = Datatables::of($incident_records)
            ->addColumn('view', function ($incident) {
                return ('<div class="text-center"><a href="/site/incident/' . $incident->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('description', function ($incident) {
                return ($incident->exec_summary) ? $incident->exec_summary : $incident->describe;
            })
            ->editColumn('nicedate2', function ($incident) {
                return ($incident->nicedate2 == '00/00/00') ? '' : $incident->nicedate2;
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
