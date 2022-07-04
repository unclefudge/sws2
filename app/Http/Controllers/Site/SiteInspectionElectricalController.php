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
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionDoc;
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteInspectionElectricalController
 * @package App\Http\Controllers
 */
class SiteInspectionElectricalController extends Controller {

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

        $non_assigned = SiteInspectionElectrical::where('status', 2)->orWhere('assigned_to', null)->get();
        $under_review = SiteInspectionElectrical::where('status', 3)->get();

        return view('site/inspection/electrical/list', compact('non_assigned', 'under_review'));
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
        elseif ($report->status == 2)
            return view('/site/inspection/electrical/docs', compact('report'));
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
            'site_id.required'        => 'The site field is required.',
            'client_name.required'    => 'The client name field is required.',
            'client_address.required' => 'The client address field is required.'
        ];
        request()->validate($rules, $mesg); // Validate

        $report_request = request()->all();
        $report_request['status'] = 2;
        //dd($report_request);

        // Create Report
        $report = SiteInspectionElectrical::create($report_request);
        Toastr::success("Created inspection report");

        return redirect('/site/inspection/electrical/' . $report->id . '/edit');
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
     */
    public function documents($id)
    {
        $report = SiteInspectionElectrical::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.inspection'))
            return view('errors/404');

        $report->status = 1;
        $report->save();
        $report->createContructionToDo(DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray());
        Toastr::success("Updated Report");

        return redirect('site/inspection/electrical');
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

        $rules = ['client_name'    => 'required',
                  'client_address' => 'required',
                  'inspected_name' => 'required_if:status,0',
                  'inspected_lic'  => 'required_if:status,0'];
        $mesg = ['client_name.required'       => 'The client name field is required.',
                 'client_address.required'    => 'The client address field is required.',
                 'inspected_name.required_if' => 'The inspection carried out by field is required.',
                 'inspected_lic.required_if'  => 'The licence no. field is required.'];

        if (in_array(Auth::user()->id, DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray())) {
            $rules = $rules + ['assigned_to' => 'required'];
            $mesg = $mesg + ['assigned_to.required' => 'The assigned to company field is required.'];
        }

        request()->validate($rules, $mesg); // Validate

        //dd(request()->all());
        $report_request = request()->all();

        // Format date from datetime picker to mysql format
        $inspected_at = new Carbon (preg_replace('/-/', '', request('inspected_at')));
        $report_request['inspected_at'] = $inspected_at->toDateTimeString();
        $report_request['client_contacted'] = (request('client_contacted')) ? Carbon::createFromFormat('d/m/Y H:i', request('client_contacted') . '00:00')->toDateTimeString() : null;

        if (request('status') == 0 && $report->status == 1) {
            // Reported completed by trade - close any outstanding ToDoos
            $report->closeToDo();
            $report_request['inspected_by'] = Auth::user()->id;
            $report_request['status'] = 3;

            // Create ToDoo for Electrical Review
            $report->createContructionReviewToDo([464]); // Alethea
        } elseif (request('status') == 1) {
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
                //$report->createAssignedToDo($company->staffStatus(1)->pluck('id')->toArray());

                // Email assigned notification
                $email_list = (\App::environment('prod')) ? ['michelle@capecod.com.au'] : [env('EMAIL_DEV')];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalAssigned($report));
            }
        }

        //dd($report_request);
        $report->update($report_request);
        Toastr::success("Updated inspection report");

        if (request('assigned_to') && $assigned_to_previous == null)
            return redirect('site/inspection/electrical');
        elseif (in_array($report->status, [1, 2]))
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

        // Electrical Mgr Signoff
        if (request('supervisor_sign_by')) {
            if (request('supervisor_sign_by') == 'y') {
                $report->supervisor_sign_by = Auth::User()->id;
                $report->supervisor_sign_at = Carbon::now();
                $report->status = 3;
                $action = Action::create(['action' => "Report signed off by Electrical Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);

                // Create ToDoo for Con Mgr
                $report->closeToDo();
                $report->createContructionReviewToDo(DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray());
            } else {
                $action = Action::create(['action' => "Report rejected by Electrical Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);
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

        // Con Mgr Signoff
        if (request('manager_sign_by')) {
            if (request('manager_sign_by') == 'y') {
                $report->manager_sign_by = Auth::User()->id;
                $report->manager_sign_at = Carbon::now();
                $report->status = 0;
                $action = Action::create(['action' => "Report signed off by Construction Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);

                // Email completed notification
                $email_list = (\App::environment('prod')) ? $report->site->company->notificationsUsersEmailType('site.inspection.completed') : [env('EMAIL_DEV')];
                if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteInspectionElectricalCompleted($report));
                Toastr::success("Report Signed Off");

                //dd($email_list);
            } else {
                $action = Action::create(['action' => "Report rejected by Construction Manager ($current_user)", 'table' => 'site_inspection_electrical', 'table_id' => $report->id]);
                $report->supervisor_sign_by = null;
                $report->supervisor_sign_at = null;
                $report->status = 3;

                // Create ToDoo for Electrical Review
                $report->closeToDo();
                $report->createContructionReviewToDo([464]); // Alethea
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
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadAttachment(Request $request)
    {
        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report)))
        //    return json_encode("failed");

        //dd('here');
        //dd(request()->all());
        // Handle file upload
        $files = $request->file('multifile');
        foreach ($files as $file) {
            $path = "filebank/site/" . $request->get('site_id') . '/inspection';
            $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);

            $doc_request = $request->only('site_id');
            $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $doc_request['company_id'] = Auth::user()->company_id;
            $doc_request['type'] = (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'gif', 'png'])) ? 'photo' : 'doc';

            // Create SiteMaintenanceDoc
            $doc = SiteInspectionDoc::create($doc_request);
            $doc->table = 'electrical';
            $doc->inspect_id = $request->get('report_id');
            $doc->attachment = $name;
            $doc->save();
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
            ->where('site_inspection_electrical.assigned_to', '<>', null)
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
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
