<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Input;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteUpcomingSettings;
use App\Models\Company\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteUpcomingComplianceController
 * @package App\Http\Controllers\Site
 */
class SiteUpcomingComplianceController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            return view('errors/404');


        $startdata = $this->getUpcomingData();
        $settings = SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->get();
        $settings_select = ['' => 'Select stage'] + SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->pluck('name', 'order')->toArray();
        $colours = SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->pluck('colour', 'order')->toArray();
        $settings_colours = [];
        if ($colours) {
            foreach ($colours as $order => $colour) {
                list($col1, $col2, $hex) = explode('-', $colour);
                $settings_colours[$order] = "#$hex";
            }
        }


        return view('site/upcoming/compliance/list', compact('startdata', 'settings', 'settings_select', 'settings_colours'));
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.upcoming.compliance'))
            return view('errors/404');

        $cc = DB::table('site_upcoming_settings')->where('field', 'cc')->get();
        $fc_plans = DB::table('site_upcoming_settings')->where('field', 'fc_plans')->get();
        $fc_struct = DB::table('site_upcoming_settings')->where('field', 'fc_struct')->get();
        $settings = SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->get();

        $settings_email = SiteUpcomingSettings::where('field', 'email')->where('status', 1)->first();
        $email_list = ($settings_email) ? explode(',', $settings_email->value) : [];

        //dd($email_list);

        return view('site/upcoming/compliance/settings', compact('settings', 'email_list', 'cc', 'fc_plans', 'fc_struct'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateJob()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            return view('errors/404');

        //dd(request()->all());

        if (request('site_id')) {
            $site = Site::findOrFail(request('site_id'));
            $site->cc = request('cc');
            $site->cc_stage = request('cc_stage');
            $site->fc_plans = request('fc_plans');
            $site->fc_plans_stage = request('fc_plans_stage');
            $site->fc_struct = request('fc_struct');
            $site->fc_struct_stage = request('fc_struct_stage');
            $site->save();
        }

        Toastr::success("Updated compliance");

        return redirect("/manage/report/upcoming_compliance");
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.project.supply'))
            return view('errors/404');

        //dd(request()->all());
        if (request('add_field')) {
            $rules = ['add_field_name' => 'required'];
            $mesg = ['add_field_name.required' => 'The stage name field is required.'];
            request()->validate($rules, $mesg); // Validate
        }

        $settings = SiteUpcomingSettings::where('status', 1)->get();
        // Get field values from request
        foreach ($settings as $setting) {
            if (request()->has("opt-$setting->id")) {
                if (request("opt-$setting->id")) {
                    $setting->name = request("opt-$setting->id");
                    if (request("opt-$setting->id-colour"))
                        $setting->colour = request("opt-$setting->id-colour");
                    $setting->save();
                } else
                    return back()->withErrors(["opt-$setting->id" => "The stage name field is required."]);
            }

        }

        // Add Extra Field
        if (request('add_field')) {
            $add_colour = (request('add-field-colour')) ? request('add-field-colour') : null;
            $add_order = count($settings) + 1;
            SiteUpcomingSettings::create(['field' => 'opt', 'name' => request('add_field_name'), 'colour' => $add_colour, 'order' => $add_order, 'status' => 1, 'company_id' => Auth::user()->company_id]);
        }

        // Update Email List
        if (request('email_list')) {
            $email_list = implode(',', request('email_list'));
            $settings_email = SiteUpcomingSettings::where('field', 'email')->where('status', 1)->first();
            if ($settings_email) {
                $settings_email->value = $email_list;
                $settings_email->save();
            } else
                $settings_email = SiteUpcomingSettings::create(['field' => 'email', 'value' => $email_list, 'status' => 1, 'company_id' => Auth::user()->company_id]);
        }

        Toastr::success("Updated settings");

        return redirect("/manage/report/upcoming_compliance/settings");
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteSetting($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.project.supply'))
            return view('errors/404');

        //dd(request()->all());

        // Delete setting
        $setting = SiteUpcomingSettings::findOrFail($id)->delete();

        // Re-orer settings
        $settings = SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->orderBy('order')->get();
        $order = 1;
        foreach ($settings as $setting) {
            $setting->order = $order ++;
            $setting->save();
        }

        Toastr::success("Updated settings");

        return redirect("/manage/report/upcoming_compliance/settings");
    }

    /**
     * Create upcoming PDF
     */
    public function showPDF(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            return view('errors/404');


        $settings_email = SiteUpcomingSettings::where('field', 'email')->where('status', 1)->first();
        $email_list = ($settings_email) ? explode(',', $settings_email->value) : [];


        return view('site/upcoming/compliance/pdf', compact('email_list'));
    }

    /**
     * Create upcoming PDF
     */
    public function createPDF()
    {
        //dd(request()->all());

        // Colours
        $colours = SiteUpcomingSettings::where('field', 'opt')->where('status', 1)->pluck('colour', 'order')->toArray();
        $settings_colours = [];
        foreach ($colours as $order => $colour) {
            list($col1, $col2, $hex) = explode('-', $colour);
            $settings_colours[$order] = "#$hex";
        }

        $startdata = $this->getUpcomingData();
        //dd($startdata);

        //return view('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf = PDF::loadView('pdf/site/upcoming-compliance', compact('startdata', 'settings_colours'));
        $pdf->setPaper('A4', 'landscape');


        if (request()->has('view_pdf'))
            return $pdf->stream();

        if (request()->has('email_pdf')) {
            $file = public_path('filebank/tmp/upcoming-' . Auth::user()->id . '.pdf');
            if (file_exists($file))
                unlink($file);
            $pdf->save($file);

            if (request('email_list')) {
                $email_to = [];
                foreach (request('email_list') as $user_id) {
                    $user = User::findOrFail($user_id);
                    if ($user && validEmail($user->email)) {
                        $email_to[] .= $user->email;
                    }
                }
                //dd($email_to);

                if ($email_to) {
                    Mail::to($email_to)->send(new \App\Mail\Site\SiteUpcomingCompliance($file));
                    Toastr::success("Sent email");
                }

                return  redirect("/manage/report/upcoming_compliance");
            }
        }
    }

    public function createPDF2($id)
    {
        $project = SiteProjectSupply::findOrFail($id);

        // Set + create create directory if required
        $path = "filebank/site/$project->site_id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        $filename = "Project Supply Infomation-" . $project->site->code . ".pdf";

        //
        // Generate PDF
        //
        //return view('pdf/site/supply-info', compact('project'));
        //return PDF::loadView('pdf/site/supply-info', compact('project'))->setPaper('a4', 'landscape')->stream();
        $pdf = PDF::loadView('pdf/site/supply-info', compact('project'));
        $pdf->setPaper('A4');
        $pdf->save(public_path("$path/$filename"));

        return $filename;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUpcomingData()
    {
        $today = Carbon::now()->format('Y-m-d');
        $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.code', 'START')
            ->orderBy('p.from')->orderBy('p.site_id')->get();

        //dd($planner);
        $startdata = [];
        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            if ($site->status == 1) {
                $entity_name = "Carpenter";
                if ($plan->entity_type == 'c')
                    $entity_name = Company::find($plan->entity_id)->name;

                $cc = $cc_stage = null;
                if ($site->cc) {
                    $cc = $site->cc;
                    $cc_stage = $site->cc_stage;
                } elseif ($site->construction_rcvd) {
                    $cc = "CC Received " . $site->construction_rcvd->format('d/m/y');
                    $cc_stage = 1;
                }
                $startdata[] = [
                    'id'              => $site->id,
                    'date'            => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M-d'),
                    'code'            => $site->code,
                    'name'            => $site->name,
                    'company'         => $entity_name,
                    'supervisor'      => $site->supervisorsSBC(),
                    'cc'              => $cc,
                    'cc_stage'        => $cc_stage,
                    'fc_plans'        => $site->fc_plans,
                    'fc_plans_stage'  => $site->fc_plans_stage,
                    'fc_struct'       => $site->fc_struct,
                    'fc_struct_stage' => $site->fc_struct_stage,
                ];
            }
        }
        //dd($startdata);
        //var_dump($startdata);

        return $startdata;
    }

    /**
     * Get Sites current user is authorised to manage + Process datatables ajax request.
     */
    public function getSites()
    {
        $site_records = Auth::user()->company->sites('-1'); // Upcoming sites only
        //dd($site_records);

        $dt = Datatables::of($site_records)
            ->editColumn('id', function ($site) {
                return '<div class="text-center"><a href="/site/' . $site->id . '"><i class="fa fa-search"></i></a></div>';
            })
            ->addColumn('supervisor', function ($site) {
                return $site->supervisorsSBC();
            })
            ->addColumn('action', function ($project) {
                return '<a href="/site/supply/' . $project->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'supervisor', 'action'])
            ->make(true);

        return $dt;
    }
}
