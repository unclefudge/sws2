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
use App\Models\Misc\FormQuestion;
use App\Models\Misc\FormResponse;
use App\Models\Misc\Action;
use App\Models\Comms\ToDo;
use App\Models\Comms\ToDoUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;

/**
 * Class SiteIncidentAnalysisController
 * @package App\Http\Controllers
 */
class SiteIncidentAnalysisController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // empty
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

        return view('site/incident/analysis', compact('incident'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function updateConditions($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['response_113' => 'required']; // Conditions
        $condition_options = [114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124];
        foreach ($condition_options as $opt) {
            if (request('response_113') && in_array($opt, request('response_113'))) $rules["response_$opt"] = 'required';
        }

        $mesg = [
            'response_113.required' => 'The conditions field is required.',
            'response_114.required' => 'The weather field is required.',
            'response_115.required' => 'The environment field is required.',
            'response_116.required' => 'The equipment field is required.',
            'response_117.required' => 'The testing / inspection field is required.',
            'response_118.required' => 'The risk assements field is required.',
            'response_119.required' => 'The physiology field is required.',
            'response_120.required' => 'The ability / training / experience field is required.',
            'response_121.required' => 'The supervision field is required.',
            'response_122.required' => 'The communication field is required.',
            'response_123.required' => 'The procedures / documents field is required.',
            'response_124.required' => 'The previous incidents field is required.',
        ];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'conditions');

            return back()->withErrors($validator)->withInput();
        }

        //
        // Form Responses
        //
        $options_all = [];
        $options_selected = [];
        $questions = [113];
        foreach ($questions as $qid) {
            $options_all = $options_all + FormQuestion::find($qid)->optionsArray();
            if (request("response_$qid")) {
                foreach (request("response_$qid") as $option_id) {
                    $options_selected[] = $option_id;
                    $info = request("response_$option_id");
                    $response = FormResponse::where('question_id', $qid)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
                    if ($response && $response->info != $info) {
                        $response->info = $info;
                        $response->save();
                    } elseif (!$response) {
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $info]);
                    }
                }
            }
        }
        //dd(request()->all());

        // Delete existing response if not current
        $delete_type = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        Toastr::success("Updated analysis");

        return redirect('site/incident/' . $incident->id . '/analysis');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateConfactors($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = [];
        if (request('response_125') && in_array('147', request('response_125'))) $rules['response_147'] = 'required'; // Other Defence
        if (request('response_148') && in_array('166', request('response_148'))) $rules['response_166'] = 'required'; // Other Team action
        if (request('response_167') && in_array('191', request('response_167'))) $rules['response_191'] = 'required'; // Other Workplace
        if (request('response_192') && in_array('218', request('response_192'))) $rules['response_218'] = 'required'; // Other DHuman factor

        $mesg = [
            'response_147.required' => 'The absent / failed defences other field is required.',
            'response_166.required' => 'The individual / team actions other field is required.',
            'response_192.required' => 'The workplace other field is required.',
            'response_218.required' => 'The human factors other field is required.',
        ];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'confactors');

            return back()->withErrors($validator)->withInput();
        }

        //
        // Form Responses
        //
        $options_all = [];
        $options_selected = [];
        $questions = [125, 148, 167, 192];
        foreach ($questions as $qid) {
            $options_all = $options_all + FormQuestion::find($qid)->optionsArray();
            if (request("response_$qid")) {
                foreach (request("response_$qid") as $option_id) {
                    $options_selected[] = $option_id;
                    $info = request("response_$option_id");
                    $response = FormResponse::where('question_id', $qid)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
                    if ($response && $response->info != $info) {
                        $response->info = $info;
                        $response->save();
                    } elseif (!$response) {
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $info]);
                    }
                }
            }
        }
        //dd(request()->all());

        // Delete existing response if not current
        $delete_type = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        Toastr::success("Updated analysis");

        return redirect('site/incident/' . $incident->id . '/analysis');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateRootcause($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['response_219' => 'required']; // Root cause
        $root_options = [220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235];
        foreach ($root_options as $opt) {
            if (request('response_219') && in_array($opt, request('response_219'))) $rules["response_$opt"] = 'required';
        }

        $mesg = [
            'response_219.required' => 'The root cause field is required.',
            'response_220.required' => 'The hardware field is required.',
            'response_221.required' => 'The training field is required.',
            'response_222.required' => 'The organisation field is required.',
            'response_223.required' => 'The communication field is required.',
            'response_224.required' => 'The incompatible goals field is required.',
            'response_225.required' => 'The procedures field is required.',
            'response_226.required' => 'The maintenance management field is required.',
            'response_227.required' => 'The design field is required.',
            'response_228.required' => 'The risk management field is required.',
            'response_229.required' => 'The management of change field is required.',
            'response_230.required' => 'The contractor management field is required.',
            'response_231.required' => 'The organisational culture field is required.',
            'response_232.required' => 'The regulatory influence field is required.',
            'response_233.required' => 'The organisational learning field is required.',
            'response_234.required' => 'The vehicle management field is required.',
            'response_235.required' => 'The management systems field is required.',
        ];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'rootcause');

            return back()->withErrors($validator)->withInput();
        }

        //
        // Form Responses
        //
        $options_all = [];
        $options_selected = [];
        $questions = [219];
        foreach ($questions as $qid) {
            $options_all = $options_all + FormQuestion::find($qid)->optionsArray();
            if (request("response_$qid")) {
                foreach (request("response_$qid") as $option_id) {
                    $options_selected[] = $option_id;
                    $info = request("response_$option_id");
                    $response = FormResponse::where('question_id', $qid)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
                    if ($response && $response->info != $info) {
                        $response->info = $info;
                        $response->save();
                    } elseif (!$response) {
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $info]);
                    }
                }
            }
        }
        //dd(request()->all());

        // Delete existing response if not current
        $delete_type = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        //
        // Actions to Prevent Reoccurance
        //
        $qRootCause = FormQuestion::find(219);
        $prevent_actions = [];
        foreach ($qRootCause->optionsArray() as $id => $cause) {
            if ($qRootCause->responseOther('site_incidents', $incident->id, $id)) {
                $name = "Site Incident Preventive Task ($id)";
                $action = ToDo::where('type', 'incident prevent')->where('type_id', $incident->id)->where('name', 'LIKE', "%$name%")->first();
                if (!$action)
                    ToDo::create(['name' => "$name : $cause", 'info' => '', 'type' => 'incident prevent', 'type_id' => $incident->id, 'company_id' => Auth::user()->company_id]);
            }
        }
        // Delete existing actions if not current + not completed
        $current_causes = $qRootCause->responsesArray('site_incidents', $incident->id);
        foreach ($incident->preventActions() as $action) {
            list($crap, $rest) = explode('(', $action->name);
            list($pid, $crap) = explode(')', $rest);
            if (!in_array($pid, $current_causes) && $action->status) $action->delete(); // delete only non-completed todoo tasks
        }
        Toastr::success("Updated analysis");

        return redirect('site/incident/' . $incident->id . '/analysis');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePrevent($id)
    {
        $incident = SiteIncident::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.incident', $incident))
            return view('errors/404');

        $rules = ['response_236' => 'required']; // Conditions
        $mesg = ['response_236.required' => 'The preventive stategies field is required.',];
        //dd(request()->all());

        // Validate
        $validator = Validator::make(request()->all(), $rules, $mesg);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'prevent');

            return back()->withErrors($validator)->withInput();
        }

        //
        // Form Responses
        //
        $options_all = [];
        $options_selected = [];
        $questions = [236];
        foreach ($questions as $qid) {
            $options_all = $options_all + FormQuestion::find($qid)->optionsArray();
            if (request("response_$qid")) {
                foreach (request("response_$qid") as $option_id) {
                    $options_selected[] = $option_id;
                    $info = request("response_$option_id");
                    $response = FormResponse::where('question_id', $qid)->where('option_id', $option_id)->where('table', 'site_incidents')->where('table_id', $incident->id)->first();
                    if ($response && $response->info != $info) {
                        $response->info = $info;
                        $response->save();
                    } elseif (!$response) {
                        FormResponse::create(['question_id' => $qid, 'option_id' => $option_id, 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $info]);
                    }
                }
            }
        }
        //dd(request()->all());

        // Delete existing response if not current
        $delete_type = FormResponse::whereIn('option_id', array_keys($options_all))->whereNotIn('option_id', $options_selected)->where('table', 'site_incidents')->where('table_id', $incident->id)->delete();

        Toastr::success("Updated analysis");

        return redirect('site/incident/' . $incident->id . '/analysis');
    }
}
