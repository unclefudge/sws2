<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\Site\SiteScaffoldHandoverDoc;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteScaffoldHandoverController
 * @package App\Http\Controllers
 */
class SiteScaffoldHandoverController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
            return view('errors/404');

        return view('site/scaffold/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($site_id = null)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.scaffold.handover'))
            return view('errors/404');

        $site = ($site_id) ? Site::findOrFail($site_id) : null;

        return view('site/scaffold/create', compact('site'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.scaffold.handover', $report))
            return view('errors/404');

        if ($report->status == 1 || ($report->status == 0 && Auth::user()->allowed2('sig.site.scaffold.handover', $report)))
            return view('/site/scaffold/edit', compact('report'));
        elseif ($report->status == 2)
            return view('/site/scaffold/docs', compact('report'));
        elseif ($report->status == 3)
            return view('/site/scaffold/signoff', compact('report'));
        else
            return redirect('/site/scaffold/handover/' . $report->id);
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
                 'singlefile.required' => 'The licence field is required.'];

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
     * Get Certificates current user is authorised to manage + Process datatables ajax request.
     */
    public function getCertificates()
    {
        $status = (request('status') == 0) ? [0] : [1, 2, 3];
        $scaff_ids = SiteScaffoldHandover::whereIn('status', $status)->pluck('id')->toArray();
        //dd($scaff_ids);

        $scaff_records = SiteScaffoldHandover::select([
            'site_scaffold_handover.id', 'site_scaffold_handover.site_id', 'site_scaffold_handover.inspector_name',
            'site_scaffold_handover.handover_date', 'site_scaffold_handover.status',
            DB::raw('DATE_FORMAT(site_scaffold_handover.handover_date, "%d/%m/%y") AS handoverdate'),
            DB::raw('sites.name AS sitename'), 'sites.code'])
            ->join('sites', 'site_scaffold_handover.site_id', '=', 'sites.id')
            ->whereIn('site_scaffold_handover.id', $scaff_ids);

        $dt = Datatables::of($scaff_records)
            ->addColumn('view', function ($report) {
                return ('<div class="text-center"><a href="/site/scaffold/handover/' . $report->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('sitename', function ($doc) {
                $s = Site::find($doc->site_id);
                return "$s->name ($s->address, $s->suburb)";
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
