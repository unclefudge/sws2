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
        $report = SiteScaffoldHandover::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.scaffold.handover', $report))
            return view('errors/404');

        return view('/site/scaffold/show', compact('report'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function documents($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.scaffold.handover'))
            return view('errors/404');

        $report->status = 3;
        $report->save();
        Toastr::success("Updated Certificate");

        return redirect('site/scaffold/handover/' . $report->id . '/edit');
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

        $report_request = request()->all();
        //dd(request()->all());

        // Format date from datetime picker to mysql format
        $handover_date = new Carbon (preg_replace('/-/', '', request('inspected_at')));
        $report_request['handover_date'] = $handover_date->toDateTimeString();
        $report_request['signed_by'] = Auth::user()->id;
        $report_request['signed_at'] = Carbon::now()->toDateTimeString();
        $report_request['status'] = 0;

        //dd($report_request);
        $report->update($report_request);
        $report->closeToDo();

        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $file = request()->file('singlefile');

            $path = "filebank/site/$report->site_id/scaffold";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $report->inspector_licence = $name;
            $report->save();
        }
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
     * Generate PDF report
     *
     * @return PDF
     */
    public function reportPDF($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        if ($report) {
            $site = Site::findOrFail($report->site_id);

            /*
            $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $output_file = public_path($dir . '/QA ' . sanitizeFilename($site->name) . ' (' . $site->id . ') ' . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);
            */

            //return view('pdf/site/scaffold-handover', compact('report', 'site'));
            return PDF::loadView('pdf/site/scaffold-handover', compact('report', 'site'))->setPaper('a4')->stream();
        }
    }

    /**
     * Generate + Email PDF report
     *
     * @return PDF
     */
    public function emailPDF($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        if ($report) {
            $site = Site::findOrFail($report->site_id);

            $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $output_file = public_path($dir . '/ScaffoldHandoverCertificate ' . $site->id . '-' . sanitizeFilename($site->name) . '-' . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);

            //return view('pdf/site/scaffold-handover', compact('report', 'site'));
            $pdf = PDF::loadView('pdf/site/scaffold-handover', compact('report', 'site'))->setPaper('a4');
            $pdf->save($output_file);

            // Email certificate
            $email_to = (validEmail(request('email'))) ? request('email') : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';

            if ($email_to && $email_user)
                Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($report, $output_file));
            elseif ($email_to)
                Mail::to($email_to)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($report, $output_file));
            elseif ($email_user)
                Mail::to($email_user)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($report, $output_file));

            Toastr::success("Emailed certificate");
        }

        return redirect("site/scaffold/handover/$report->id");
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
