<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Site\Site;
use App\Models\Site\SiteInspectionDoc;
use App\Models\Site\SiteInspectionElectrical;
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
 * Class SiteInspectionElectricalController
 * @package App\Http\Controllers
 */
class SiteInspectionElectricalController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.inspection'))
            return view('errors/404');

        $non_assigned = SiteInspectionElectrical::Where('assigned_to', null)->get();
        $pending = SiteInspectionElectrical::where('status', 3)->get();
        $assignedList = ['all' => 'All sites'];
        foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company) {
            if (in_array('4', $company->tradesSkilledIn->pluck('id')->toArray()))
                $assignedList[$company->id] = $company->name;
        }

        return view('site/inspection/electrical/list', compact('non_assigned', 'pending', 'assignedList'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.inspection', $report))
            return view('errors/404');

        if ($report->status == 1 || ($report->status == 0 && Auth::user()->allowed2('sig.site.inspection', $report)))
            return view('/site/inspection/electrical/edit', compact('report'));
        else
            return redirect('/site/inspection/electrical/' . $report->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.inspection'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'client_name' => 'required', 'client_address' => 'required'];
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'client_name.required' => 'The client name field is required.',
            'client_address.required' => 'The client address field is required.'
        ];
        request()->validate($rules, $mesg); // Validate

        $report_request = request()->all();
        $report_request['status'] = 1;
        //dd($report_request);

        // Create Report
        $report = SiteInspectionElectrical::create($report_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename)
                $report->saveAttachment($tmp_filename);
        }

        // Create Todoo to assign a company
        $report->createAssignCompanyToDo([108]);

        Toastr::success("Created inspection report");

        return redirect('/site/inspection/electrical/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.inspection'))
            return view('errors/404');

        return view('site/inspection/electrical/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.inspection', $report))
            return view('errors/404');

        if ($report->status == 1 && Auth::user()->allowed2('edit.site.inspection', $report))
            return redirect('/site/inspection/electrical/' . $report->id . '/edit');

        return view('/site/inspection/electrical/show', compact('report'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);
        $assigned_to_previous = $report->assigned_to;

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.inspection', $report))
            return view('errors/404');

        $rules = ['client_name' => 'required',
            'client_address' => 'required',
            'inspected_at' => 'required_if:status,0',
            'inspected_name' => 'required_if:status,0',
            'inspected_lic' => 'required_if:status,0'];
        $mesg = ['client_name.required' => 'The client name field is required.',
            'client_address.required' => 'The client address field is required.',
            'inspected_at.required_if' => 'The date/time of inspection field is required.',
            'inspected_name.required_if' => 'The inspection carried out by field is required.',
            'inspected_lic.required_if' => 'The licence no. field is required.'];

        if (in_array(Auth::user()->id, array_merge(getUserIdsWithRoles('gen-technical-manager'), [108]))) {
            $rules = $rules + ['assigned_to' => 'required'];
            $mesg = $mesg + ['assigned_to.required' => 'The assigned to company field is required.'];
        }

        request()->validate($rules, $mesg); // Validate

        //dd(request()->all());
        $report_request = request()->all();

        // Format date from datetime picker to mysql format
        if (request('inspected_at')) {
            $inspected_at = new Carbon (preg_replace('/-/', '', request('inspected_at')));
            $report_request['inspected_at'] = $inspected_at->toDateTimeString();
        }
        $report_request['client_contacted'] = (request('client_contacted')) ? Carbon::createFromFormat('d/m/Y H:i', request('client_contacted') . '00:00')->toDateTimeString() : null;

        if (request('status') == 0 && $report->status == 1) {
            // Reported completed by trade - close any outstanding ToDoos
            $report->closeToDo();
            $report_request['inspected_by'] = Auth::user()->id;
            $report_request['status'] = 3; // Pending signoff

            // Create ToDoo for Electrical Review
            $report->createSignOffToDo([464]); // Alethea

            // Set Eworks for Site
            $report->site->eworks = $report->assigned_to;
            $report->site->save();
        } elseif (request('status') == '4' && $report->status != '4') {
            // Report placed OnHold so send out CancelledReport Notification
            $report->site->cancelInspectionReports();
        } elseif (request('status') == '1') {
            $report_request['inspected_name'] = null;
            $report_request['inspected_lic'] = null;
        }

        // Create ToDoo for change of assigned company
        if (request('assigned_to') && request('assigned_to') != $report->assigned_to) {
            $report->closeToDo();
            $report_request['assigned_at'] = Carbon::now()->toDateTimeString();
            $company = Company::find(request('assigned_to'));
            if ($company && $company->primary_user) {
                $report->createAssignedToDo([$company->primary_user]);

                // Email assigned notification
                $email_list = (\App::environment('prod')) ? ['alethea@capecod.com.au'] : [env('EMAIL_DEV')];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalAssigned($report));

                // Email assigned notification to Next Point Admin
                if ($company->id == 108) {  // Next Point
                    $email_list = (\App::environment('prod')) ? ['admin@nextpointelectrical.com.au'] : [env('EMAIL_DEV')];
                    if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalAssignedTrade($report));
                }
            }
        }

        //dd($report_request);
        $report->update($report_request);

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename)
                $report->saveAttachment($tmp_filename);
        }

        Toastr::success("Updated inspection report");

        if (request('assigned_to') && $assigned_to_previous == null)
            return redirect('site/inspection/electrical');
        elseif (in_array($report->status, [1]))
            return redirect('site/inspection/electrical/' . $report->id . '/edit');
        else
            return redirect('site/inspection/electrical/' . $report->id);
    }

    /**
     * Sign Off on the Report
     *
     * @return \Illuminate\Http\Response
     */
    public function signoff($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.inspection', $report))
            return view('errors/404');

        //dd(request()->all());

        $current_user = Auth::User()->full_name;

        // Electrical Admin Signoff
        if (request('supervisor_sign_by')) {
            if (request('supervisor_sign_by') == 'y') {
                $report->supervisor_sign_by = Auth::User()->id;
                $report->supervisor_sign_at = Carbon::now();
                $report->status = 3; // Pending signoff
                $action = Action::create(['action' => "Report signed off by Admin Officer ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);

                // Create ToDoo for Tech Mgr
                $report->closeToDo();
                $report->createSignOffToDo(array_merge(getUserIdsWithRoles('gen-technical-manager'), [108]));
            } else {
                $action = Action::create(['action' => "Report rejected by Admin Officer ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);
                $report->inspected_name = null;
                $report->inspected_lic = null;
                $report->status = 1;

                // Create ToDoo for trade to Re-complete report
                $report->closeToDo();
                $company = Company::find($report->assigned_to);
                if ($company && $company->primary_user)
                    $report->createAssignedToDo([$company->primary_user]);

                Toastr::error("Report Rejected");

            }
            $report->save();
        }

        // Tech Mgr Signoff
        if (request('manager_sign_by')) {
            if (request('manager_sign_by') == 'y') {
                $report->manager_sign_by = Auth::User()->id;
                $report->manager_sign_at = Carbon::now();
                $report->status = 0;

                $report->closeToDo();
                $action = Action::create(['action' => "Report signed off by Technical Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);

                // Email completed notification
                $email_list = (\App::environment('prod')) ? $report->site->company->notificationsUsersEmailType('site.inspection.completed') : [env('EMAIL_DEV')];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalCompleted($report));


                // Email completed PDF to Trade
                $site = Site::findOrFail($report->site_id);
                $pdf = PDF::loadView('pdf/site/inspection-electrical', compact('report', 'site'))->setPaper('a4');
                $file = public_path("filebank/tmp/$site->name - Electrical Inspection Report.pdf");
                if (file_exists($file))
                    unlink($file);
                $pdf->save($file);

                // Trade who completed report
                $email_list = [env('EMAIL_DEV')];
                $company = Company::find($report->assigned_to);
                if (\App::environment('prod') && $company && $company->primary_user && validEmail($company->primary_contact()->email))
                    $email_list = [$company->primary_contact()->email];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalReportTrade($report, $file));

                Toastr::success("Report Signed Off");

                //dd($email_list);
            } else {
                $action = Action::create(['action' => "Report rejected by Construction Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);
                $report->supervisor_sign_by = null;
                $report->supervisor_sign_at = null;
                $report->status = 3;  // Pending signoff

                // Create ToDoo for Electrical Review
                $report->closeToDo();
                $report->createElectricalReviewToDo([464]); // Alethea
                Toastr::error("Report Rejected");

            }
            $report->save();
        }

        return redirect("site/inspection/electrical/$report->id");
    }

    /**
     * Update Status the specified resource in storage.
     */
    public function updateStatus($id, $status)
    {
        $report = SiteInspectionElectrical::findOrFail($id);
        $old_status = $report->status;

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.inspection', $report))
            return view('errors/404');

        // Update Status
        if ($status != $old_status) {
            $report->status = $status;
            $report->save();

            if ($status == 1) {
                // Email re-opened notification
                $email_list = (\App::environment('prod')) ? $report->site->company->notificationsUsersEmailType('site.inspection.completed') : [env('EMAIL_DEV')];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalReopened($report));
            }
        }

        return redirect('site/inspection/electrical/' . $report->id . '/edit');
    }

    /**
     * Delete the specified resource in storage.
     */
    public function destroy($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.inspection', $report))
            return view('errors/404');

        $report->delete();

        //return redirect('site/inspection/electrical/');

    }

    public function deleteAttachment($id, $doc_id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.site.inspection', $report))
            return view('errors/404');

        $doc = SiteInspectionDoc::where('id', $doc_id)->first();
        if ($doc) {
            $file = public_path($doc->AttachmentUrl);
            if (file_exists($file))
                unlink($file);
            $doc->delete();
        }


        return redirect('site/inspection/electrical/' . $report->id . '/edit');

    }


    /**
     * Generate PDF report
     *
     * @return PDF
     */
    public function reportPDF($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        if ($report) {
            $completed = 1;
            $data = [];
            $users = [];
            $companies = [];
            $site = Site::findOrFail($report->site_id);

            //dd($data);
            /*
            $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $output_file = public_path($dir . '/QA ' . sanitizeFilename($site->name) . ' (' . $site->id . ') ' . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);
            */

            //return view('pdf/site/inspection-electrical', compact('report', 'site'));
            return PDF::loadView('pdf/site/inspection-electrical', compact('report', 'site'))->setPaper('a4')->stream();
        }
    }

    /**
     * Get Accidents current user is authorised to manage + Process datatables ajax request.
     */
    public function getInspections()
    {
        if (Auth::user()->permissionLevel('view.site.inspection', 3) == 30) // User has 'Planned for' permission to requests
            $inpect_ids = SiteInspectionElectrical::where('status', request('status'))->where('assigned_to', Auth::user()->company_id)->pluck('id')->toArray();
        else
            $inpect_ids = SiteInspectionElectrical::where('status', request('status'))->pluck('id')->toArray();

        $assigned = Auth::user()->company->companies('1')->pluck('id')->toArray();
        if (request('assigned_to') != 'all')
            $assigned = [request('assigned_to')];


        $inspect_records = SiteInspectionElectrical::select([
            'site_inspection_electrical.id', 'site_inspection_electrical.site_id', 'site_inspection_electrical.inspected_name', 'site_inspection_electrical.inspected_by',
            'site_inspection_electrical.inspected_at', 'site_inspection_electrical.created_at', 'site_inspection_electrical.assigned_at', 'site_inspection_electrical.client_contacted',
            'site_inspection_electrical.status', 'sites.company_id',
            DB::raw('DATE_FORMAT(site_inspection_electrical.created_at, "%d/%m/%y") AS nicedate'),
            DB::raw('DATE_FORMAT(site_inspection_electrical.inspected_at, "%d/%m/%y") AS inspected_date'),
            DB::raw('DATE_FORMAT(site_inspection_electrical.assigned_at, "%d/%m/%y") AS assigned_date'),
            DB::raw('DATE_FORMAT(site_inspection_electrical.client_contacted, "%d/%m/%y") AS client_date'),
            DB::raw('sites.name AS sitename'), 'sites.code',
        ])
            ->join('sites', 'site_inspection_electrical.site_id', '=', 'sites.id')
            ->where('site_inspection_electrical.status', '=', request('status'))
            //->where('site_inspection_electrical.assigned_to', '<>', null)
            ->whereIn('site_inspection_electrical.assigned_to', $assigned)
            ->whereIn('site_inspection_electrical.id', $inpect_ids);

        $dt = Datatables::of($inspect_records)
            ->addColumn('view', function ($inspect) {
                return ('<div class="text-center"><a href="/site/inspection/electrical/' . $inspect->id . '"><i class="fa fa-search"></i></a></div>');
            })
            //->editColumn('sitename', function ($inspect) {
            //    return $inspect->site->nameClient;
            //})
            ->editColumn('nicedate2', function ($inspect) {
                return ($inspect->nicedate2 == '00/00/00') ? '' : $inspect->nicedate2;
            })
            ->editColumn('assigned_to', function ($inspect) {
                $r = SiteInspectionElectrical::find($inspect->id);

                return ($r->assigned_to) ? $r->assignedTo->name : '-';
            })
            ->addColumn('action', function ($inspect) {
                $r = SiteInspectionElectrical::find($inspect->id);
                if (Auth::user()->allowed2("del.site.inspection", $r)) {
                    return '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete" data-id="' . $r->id . '" data-name="' . $r->site->name . '"><i class="fa fa-trash"></i></button>';
                }

                return '';
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
