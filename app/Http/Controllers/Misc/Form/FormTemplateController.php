<?php

namespace App\Http\Controllers\Misc\Form;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Misc\Form\FormTemplate;
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

        Toastr::success("Submitted certificate");

        return redirect('site/scaffold/handover');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTemplate($id)
    {

        $template = FormTemplate::findOrFail($id);

        $json = [];
        $json[] = $template;
        $json[] = $template->pages;
        $json[] = $template->sections;

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
