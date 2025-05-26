<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteDoc;
use App\Models\Site\SiteScaffoldHandover;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteScaffoldHandoverController
 * @package App\Http\Controllers
 */
class SiteScaffoldHandoverController extends Controller
{

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

        //
        // Scaffold Handover for Ashbys
        //
        $ashby = [];
        $today = Carbon::now();
        $oneyear = Carbon::now()->subYear();
        $found_tasks = 0;
        // Manually exclude some older tasks
        $excludePlannerTasks = ['129578', '129601', '129993', '135665', '136666', '137626', '137903', '139403'];

        //
        // Erect Scaffold - taskid: 116
        //
        $plans = SitePlanner::whereDate('from', '>', $oneyear)->whereDate('from', '<', $today)->where('task_id', 116)->orderByDesc('site_id')->get();
        foreach ($plans as $plan) {
            if ($plan->site->status == 1) {
                // Check for Site Risk doc with word 'Scaffolding Handover Certificate'
                $certificate = SiteDoc::where('site_id', $plan->site_id)->where('name', 'like', '%Scaffolding Handover Certificate%')->first();
                if (!$certificate && !in_array($plan->site->name, $ashby) && !in_array($plan->id, $excludePlannerTasks)) {
                    // Add outstanding scaff to main list
                    $ashby[$plan->site->name] = ['name' => $plan->site->name, 'due_at' => $plan->from->format('d/m/Y'), 'status' => 'outstanding'];
                } elseif ($certificate && !in_array($plan->site->name, $ashby) && !in_array($plan->id, $excludePlannerTasks)) {
                    $ashby[$plan->site->name] = ['name' => $plan->site->name, 'due_at' => $plan->from->format('d/m/Y'), 'status' => 'completed'];
                }
            }
        }

        return view('site/scaffold/list', compact('ashby'));
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

        if ($report->status == 1)
            return view('/site/scaffold/edit', compact('report'));
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
            'site_id.required' => 'The site field is required.',
            'location.required' => 'The location field is required.',
            'use.required' => 'The intended use field is required.',
            'duty.required' => 'The duty classification field is required.',
            'decks.required' => 'The no. of decks field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        $report_request = request()->all();
        $report_request['status'] = 1;  // Active
        //dd($report_request);

        // Create Report
        $report = SiteScaffoldHandover::create($report_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename)
                $report->saveAttachment($tmp_filename);
        }
        Toastr::success("Created certificate");

        return redirect('/site/scaffold/handover/' . $report->id . '/edit');
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


        $rules = ['site_id' => 'required', 'location' => 'required', 'use' => 'required', 'duty' => 'required', 'decks' => 'required'];
        if (request('done_at')) {
            $rules = $rules + ['inspector_name' => 'required', 'handover_date' => 'required', 'singlefile' => 'required'];
        }
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'location.required' => 'The location field is required.',
            'use.required' => 'The intended use field is required.',
            'duty.required' => 'The duty classification field is required.',
            'decks.required' => 'The no. of decks field is required.',
            'client_name.required' => 'The name field is required.',
            'handover_date.required' => 'The date/time field is required.',
            'singlefile.required' => 'The licence field is required.'];

        request()->validate($rules, $mesg); // Validate

        $report_request = request()->all();
        //dd(request()->all());

        // Format date from datetime picker to mysql format
        if (request('handover_date')) {
            $handover_date = new Carbon (preg_replace('/-/', '', request('handover_date')));
            $report_request['handover_date'] = $handover_date->toDateTimeString();
        }
        if (request('done_at')) {
            $report_request['signed_by'] = Auth::user()->id;
            $report_request['signed_at'] = Carbon::now()->toDateTimeString();
            $report_request['status'] = 0;
        }

        //dd($report_request);
        $report->update($report_request);
        $report->closeToDo();

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename)
                $report->saveAttachment($tmp_filename);
        }

        // Handle attached file license
        if (request()->hasFile('singlefile')) {
            $file = request()->file('singlefile');

            $path = "filebank/site/$report->site_id/scaffold";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $report->inspector_licence = $name;
            $report->save();
        }
        Toastr::success("Submitted certificate");

        return redirect('site/scaffold/handover');
    }

    public function destroy($id)
    {
        $report = SiteScaffoldHandover::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.scaffold.handover', $report))
            return json_encode("failed");

        // Delete attached file
        //if (file_exists(public_path('/filebank/construction/doc/standards/' . $report->attachment)))
        //    unlink(public_path('/filebank/construction/doc/standards/' . $report->attachment));
        $report->delete();

        return json_encode('success');
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
        $status = request('status');
        if (Auth::user()->isCC())
            $scaff_ids = SiteScaffoldHandover::where('status', $status)->pluck('id')->toArray();
        else
            $scaff_ids = SiteScaffoldHandover::where('status', $status)->where('created_by', Auth::user()->id)->pluck('id')->toArray();
        //dd($scaff_ids);

        $scaff_records = SiteScaffoldHandover::select([
            'site_scaffold_handover.id', 'site_scaffold_handover.site_id', 'site_scaffold_handover.inspector_name',
            'site_scaffold_handover.handover_date', 'site_scaffold_handover.status', 'site_scaffold_handover.created_at',
            DB::raw('DATE_FORMAT(site_scaffold_handover.handover_date, "%d/%m/%y") AS handoverdate'),
            DB::raw('sites.name AS sitename'), 'sites.code'])
            ->join('sites', 'site_scaffold_handover.site_id', '=', 'sites.id')
            ->whereIn('site_scaffold_handover.id', $scaff_ids);

        $dt = Datatables::of($scaff_records)
            ->addColumn('view', function ($report) {
                $edit = ($report->status == 1 ? '/edit' : '');
                return ('<div class="text-center"><a href="/site/scaffold/handover/' . $report->id . $edit . '"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('sitename', function ($report) {
                $s = Site::find($report->site_id);
                return "$s->name ($s->address, $s->suburb)";
            })
            ->addColumn('due_at', function ($report) {
                $created_at = Carbon::createFromFormat('Y-m-d', $report->created_at->format('Y-m-d'));
                return nextWorkDate($created_at, '+', 1)->format('d/m/Y');
            })
            ->addColumn('action', function ($report) {
                $record = SiteScaffoldHandover::find($report->id);
                $actions = '';
                if (Auth::user()->allowed2('del.site.scaffold.handover', $record))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/scaffold/handover/' . $record->id . '" data-name="' . $record->site->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
