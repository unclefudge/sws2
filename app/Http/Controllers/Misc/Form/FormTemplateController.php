<?php

namespace App\Http\Controllers\Misc\Form;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class FormTemplateController
 * @package App\Http\Controllers
 */
class FormTemplateController extends Controller {

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

        return view('misc/form/template/list');
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

        return view('misc/form/template/create', compact('site'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $template = FormTemplate::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
        //    return view('errors/404');

        return view("/misc/form/template/edit", compact('template'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.scaffold.handover'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'location' => 'required', 'use' => 'required', 'duty' => 'required', 'decks' => 'required'];
        $mesg = [
            'site_id.required'  => 'The site field is required.',
            'location.required' => 'The location field is required.',
            'use.required'      => 'The intended use field is required.',
            'duty.required'     => 'The duty classification field is required.',
            'decks.required'    => 'The no. of decks field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $report_request = request()->all();
        $report_request['status'] = 2;
        //dd($report_request);

        // Create Report
        $report = SiteScaffoldHandover::create($report_request);
        Toastr::success("Created certificate");

        return redirect('/site/scaffold/handover/' . $report->id . '/edit');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = FormTemplate::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $report))
        //    return view('errors/404');

        return view('/form/template/show', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $template = FormTemplate::findOrFail($id);

        dd($template);
        Toastr::success("Template saved");

        return redirect('site/scaffold/handover');
    }


    /**
     * Save Custom Template
     */
    public function saveTemplate()
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
                for ($i=0; $i < count($pages); $i++) {
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
                    for ($i=0; $i < count($sections); $i++) {
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
     * Get Custom Template
     */
    public function getTemplate($id)
    {

        $template = FormTemplate::findOrFail($id);
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
        foreach($template->pages as $page) {
            // Create page Object
            $page_obj = new \stdClass();
            $page_obj->id = $page->id;
            $page_obj->name = $page->name;
            $page_obj->description = $page->description;
            $page_obj->order = $page->order;

            // Add Sections
            $page_obj->sections = [];
            foreach($page->sections as $section) {
                // Create Section Object
                $section_obj = new \stdClass();
                $section_obj->id = $section->id;
                $section_obj->name = $section->name;
                $section_obj->description = $section->description;
                $section_obj->order = $section->order;

                // Add Questions
                $section_obj->questions = [];
                foreach($section->questions as $question) {
                    // Create Question Object
                    $question_obj = new \stdClass();
                    $question_obj->id = $question->id;
                    $question_obj->name = $question->name;
                    $question_obj->type = $question->type;
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

        $json[] = $template_obj;

        //$json = [];
        //$json[] = $template;
        //$json[] = $pages;
        //$json[] = $template->sections;

        return $json;
    }


    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getTemplates()
    {
        $records = DB::table('forms_templates')
            ->select(['id', 'name', 'description', 'company_id', 'status', 'updated_at'])
            ->where('company_id', Auth::user()->company_id)
            ->where('status', 1);

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/form/template/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->addColumn('action', function ($rec) {
                return '<a href="/form/template/' . $rec->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
            })
            ->rawColumns(['id', 'name', 'updated_at', 'completed', 'action'])
            ->make(true);

        return $dt;
    }
}
