<?php

namespace App\Http\Controllers\Misc\Form;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormTemplate;
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

        return view('misc/form/list');
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
        $form = Form::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.scaffold.handover', $form))
        //    return view('errors/404');

        return view('/misc/form/show', compact('report'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
            return view('errors/404');

        $rules = ['inspector_name' => 'required', 'handover_date' => 'required', 'singlefile' => 'required'];
        $mesg = ['client_name.required'   => 'The name field is required.',
                 'handover_date.required' => 'The date/time field is required.',
                 'singlefile.required'    => 'The licence field is required.'];

        request()->validate($rules, $mesg); // Validate

        Toastr::success("Submitted certificate");

        return redirect('site/scaffold/handover');
    }


    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAttachment()
    {
        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.site.scaffold.handover') || Auth::user()->allowed2('edit.site.scaffold.handover', $report)))
        //    return json_encode("failed");

        //dd(request()->all());
        // Handle file upload
        $files = request()->file('multifile');
        foreach ($files as $file) {
            $path = "filebank/site/" . request('site_id') . '/scaffold';
            $name = request('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = request('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);

            $doc_request['scaffold_id'] = request('report_id');
            $doc_request['category'] = request('category');
            $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $doc_request['attachment'] = $name;
            $doc_request['type'] = (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'gif', 'png'])) ? 'photo' : 'doc';

            // Create SiteScaffoldHandoverDoc
            $doc = SiteScaffoldHandoverDoc::create($doc_request);
        }

        return json_encode("success");
    }


    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getTemplates()
    {
        $records = DB::table('form_templates')
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
