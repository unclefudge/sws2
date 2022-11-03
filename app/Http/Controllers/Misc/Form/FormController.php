<?php

namespace App\Http\Controllers\Misc\Form;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormResponse;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class FormController
 * @package App\Http\Controllers
 */
class FormController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
        //    return view('errors/404');

        return view('site/inspection/custom/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.scaffold.handover'))
        //    return view('errors/404');

        return view('misc/form/create', compact('site'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $form))
        //    return view('errors/404');

        return redirect("/form/$id/1");
        //return view('/site/inspection/custom/show', compact('form'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPage($id, $page)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $form))
        //    return view('errors/404');

        // Get Page data
        return view('/site/inspection/custom/show', compact('form', 'page'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
        //    return view('errors/404');

        return view("/misc/form/edit", compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.scaffold.handover'))
        //    return view('errors/404');

        //return redirect('/misc/form/' . $form->id . /edit);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
        //    return view('errors/404');

        $form_data = request()->all();
        //dd(request()->all());
        $nextpage = request('nextpage');

        // update Site Id if present
        if (request('site_id')) {
            $site_id = request('site_id');
            $form->site_id = $site_id;
            $site_question = FormQuestion::where('template_id', $form->template->id)->where('type_special', 'site')->first();
            if ($site_question) {
                $response = FormResponse::where('form_id', $form->id)->where('question_id', $site_question->id)->first();
                if ($response) {
                    $response->value = $site_id;
                    $response->save();
                } else
                    $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $site_question->id, 'value' => $site_id]);
            }
        }

        // Loop through ALL form questions
        foreach ($form->questions as $question) {
            $response = FormResponse::where('form_id', $form->id)->where('question_id', $question->id)->first();
            $qid = $question->id;
            $resp = request("q$qid");
            //echo "q$qid<br>";

            // Only update questions for current page
            if (request()->has("q$qid")) {
                // Page has a response to given question not blank/null
                if ($resp) {
                    //echo "*q$qid: $resp<br>";
                    $date = ($question->type == 'datetime') ? $resp : null;
                    $option_id = ($question->type == 'select') ? $resp : null;

                    if ($question->type == 'select') $option_id = $resp; // set option_id for selects
                    if ($question->type == 'datetime' && $resp) $date = Carbon::createFromFormat('d/m/Y H:i', $resp)->toDateTimeString(); // set date

                    //$item_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString();

                    if ($response) {
                        $response->value = $resp;
                        $response->option_id = $option_id;
                        $response->date = $date;
                        $response->save();
                    } else
                        $response = FormResponse::create(['form_id' => $form->id, 'question_id' => $qid, 'value' => $resp, 'option_id' => $option_id, 'date' => $date]);
                } elseif ($response)
                    $response->delete(); // Response is blank so delete

            }
        }

        $form->save();

        //dd($form_data);

        return redirect("form/$form->id/$nextpage");
    }


    /**
     * Save the Custom Form.
     */
    public function saveForm()
    {
        //dd(request()->all());
        $custom_form = request('custom_form');
        if ($custom_form) {
            $template = FormTemplate::findOrFail($custom_form['id']);

            if ($template) {
                // Update Template
                $template->name = $custom_form['name'];
                $template->description = $custom_form['description'];
                $template->save();

                // Save Pages
                $pages = $custom_form['pages'];
                for ($i = 0; $i < count($pages); $i ++) {
                    if ($pages[$i]['id'] != 'new') {
                        $page = FormPage::findOrFail($pages[$i]['id']);
                        if ($page) {
                            // Update existing Page
                            $page->name = $pages[$i]['name'];
                            $page->description = $pages[$i]['description'];
                            $page->order = $pages[$i]['order'];
                            $page->save();
                        }
                    } else {
                        // Create new Page
                    }

                    // Save Sections
                    $sections = $pages[$i]['sections'];
                    for ($i = 0; $i < count($sections); $i ++) {
                        if ($sections[$i]['id'] != 'new') {
                            $section = FormSection::findOrFail($sections[$i]['id']);
                            if ($section) {
                                // Update existing Section
                                $section->page_id = $page->id;
                                $section->name = $sections[$i]['name'];
                                $section->description = $sections[$i]['description'];
                                $section->order = $sections[$i]['order'];
                                $section->save();
                            }
                        } else {
                            // Create new Section
                        }
                    }
                }
            }
        }

        Toastr::success("Template saved");

        return response()->json(['status' => 'ok', 'success' => true,], 200);
        //return response()->json(['status'  => 'error', 'success' => false, 'message' => 'Invalid email'], 406);
        //return response()->json(['success' => true, 'message' => 'Your AJAX processed correctly']);
    }


    /**
     * Get Custom Form
     */
    public function getForm($id)
    {

        $form = Form::findOrFail($id);
        $template = FormTemplate::findOrFail($form->template_id);
        //$pages = $template->pages;
        //return ($pages);

        $pages = [];


        // Create Template Object
        $template_obj = new \stdClass();
        $template_obj->id = $template->id;
        $template_obj->name = $template->name;
        $template_obj->description = $template->description;

        // Add Pages
        $template_obj->pages = [];
        foreach ($template->pages as $page) {
            // Create page Object
            $page_obj = new \stdClass();
            $page_obj->id = $page->id;
            $page_obj->name = $page->name;
            $page_obj->description = $page->description;
            $page_obj->order = $page->order;

            // Add Sections
            $page_obj->sections = [];
            foreach ($page->sections as $section) {
                // Create Section Object
                $section_obj = new \stdClass();
                $section_obj->id = $section->id;
                $section_obj->name = $section->name;
                $section_obj->description = $section->description;
                $section_obj->order = $section->order;

                // Add Questions
                $section_obj->questions = [];
                foreach ($section->questions as $question) {
                    // Create Question Object
                    $question_obj = new \stdClass();
                    $question_obj->id = $question->id;
                    $question_obj->name = $question->name;
                    $question_obj->type = $question->type;

                    // Get Response if exists
                    $response = FormResponse::where('form_id', $form->id)->where('question_id', $question->id)->where('status', 1)->first();
                    if ($response) {
                        $question_obj->response_value = $response->value;
                        $question_obj->response_option = $response->option_id;
                    } else {
                        $question_obj->response_value = null;
                        $question_obj->response_option = null;
                    }

                    $question_obj->type_special = $question->type_special;
                    $question_obj->type_version = $question->type_version;
                    $question_obj->order = $question->order;
                    $question_obj->default = $question->default;
                    $question_obj->multiple = $question->multiple;
                    $question_obj->required = $question->required;
                    $question_obj->placeholder = $question->placeholder;
                    $question_obj->helper = $question->helper;
                    $question_obj->width = $question->width;


                    // Add Question Object to Section
                    $section_obj->questions[] = $question_obj;
                }
                // Add Section Object to Page
                $page_obj->sections[] = $section_obj;
            }
            // Add Page Object to Template
            $template_obj->pages[] = $page_obj;
        }

        // Get Form Responses
        $responses = $form->responses;


        $json[] = $template_obj;
        $json[] = $responses;


        //$json = [];
        //$json[] = $template;
        //$json[] = $pages;
        //$json[] = $template->sections;

        return $json;
    }


    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getSafetyDesignForms()
    {
        $records = Form::select([
            'forms.id', 'forms.template_id', 'forms.site_id', 'forms.name', 'forms.company_id', 'forms.status', 'forms.updated_at', 'forms.created_at',
            DB::raw('DATE_FORMAT(forms.created_at, "%d/%m/%y") AS createddate'),
            DB::raw('DATE_FORMAT(forms.updated_at, "%d/%m/%y") AS updateddate'),
            DB::raw('sites.name AS sitename')])
            ->join('sites', 'forms.site_id', '=', 'sites.id')
            ->where('forms.template_id', 1)
            ->where('forms.company_id', Auth::user()->company_id)
            ->where('forms.status', 1);

        $dt = Datatables::of($records)
            ->addColumn('view', function ($report) {
                return ('<div class="text-center"><a href="/site/inspection/custom/' . $report->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('action', function ($rec) {
                return '<a href="/site/inspection/custom/' . $rec->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
            })
            ->rawColumns(['view', 'name', 'updated_at', 'created_at', 'action'])
            ->make(true);

        return $dt;
    }
}
