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
use App\Models\Site\SiteExtensionCategory;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Company\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteExtensionController
 * @package App\Http\Controllers\Site
 */
class SiteExtensionController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.extension'))
            return view('errors/404');

        $hide_site_code = ['0000', '0001', '0002', '0003', '0004', '0005', '0006', '0007', '0008', '1234', '1235'];
        $sites = Auth::user()->authSites('view.site.extension', '1')->whereNotIn('code', $hide_site_code);

        $today = Carbon::now();
        $data = [];
        $prac_yes = $prac_no = [];
        foreach ($sites as $site) {
            $start_job = SitePlanner::where('site_id', $site->id)->where('task_id', 11)->first();
            // Shon only site which Job Start has before today
            if ($start_job && $start_job->from->lte($today)) {
                $prac_completion = SitePlanner::where('site_id', $site->id)->where('task_id', 265)->first();

                $site_data = [
                    'id'                   => $site->id,
                    'name'                 => $site->name,
                    'prac_completion'      => ($prac_completion) ? $prac_completion->from->format('d/m/y') : '',
                    'prac_completion_date' => ($prac_completion) ? $prac_completion->from->format('ymd') : '',
                    'start_job'            => ($start_job) ? $start_job->from->format('d/m/Y') : '',
                    'extend_reasons'       => $site->extensionReasonsSBC(),
                    'extend_reasons_array' => $site->extensionReasons->pluck('id')->toArray(),
                    'notes'                => $site->extension_notes
                ];
                if ($prac_completion)
                    $prac_yes[] = $site_data;
                else
                    $prac_no[] = $site_data;
            }
        }

        usort($prac_yes, function ($a, $b) {
            return $a['prac_completion_date'] <=> $b['prac_completion_date'];
        });

        usort($prac_no, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        $data = $prac_yes + $prac_no;

        $extend_reasons = SiteExtensionCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        return view('site/extension/list', compact('data', 'extend_reasons'));
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
        if (!Auth::user()->hasPermission2('del.site.extension'))
            return view('errors/404');

        $cats = SiteExtensionCategory::where('status', 1)->orderBy('order')->get();

        //dd($email_list);

        return view('site/extension/settings', compact('cats'));
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
        if (!Auth::user()->hasAnyPermissionType('site.extension'))
            return view('errors/404');

        //dd(request()->all());

        if (request('site_id')) {
            $site = Site::findOrFail(request('site_id'));
            $site->extension_notes = request('extension_notes');
            $site->save();
            $site->extensionReasons()->sync(request('reasons'));
        }

        Toastr::success("Updated compliance");

        return redirect("/site/extension");
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.extension'))
            return view('errors/404');

        if (request('add_field')) {
            $rules = ['add_field_name' => 'required'];
            $mesg = ['add_field_name.required' => 'The name field is required.'];
            request()->validate($rules, $mesg); // Validate
        }

        //dd(request()->all());
        $cats = SiteExtensionCategory::where('status', 1)->get();
        // Get field values from request
        foreach ($cats as $cat) {
            if (request()->has("cat-$cat->id")) {
                if (request("cat-$cat->id")) {
                    $cat->name = request("cat-$cat->id");
                    $cat->save();
                } else
                    return back()->withErrors(["cat-$cat->id" => "The name field is required."]);
            }

        }

        // Add Extra Field
        if (request('add_field')) {
            $add_order = count($cats) + 1;
            SiteExtensionCategory::create(['name' => request('add_field_name'), 'order' => $add_order, 'status' => 1, 'company_id' => Auth::user()->company_id]);
        }


        Toastr::success("Updated settings");

        return redirect("/site/extension/settings");
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteSetting($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.extension'))
            return view('errors/404');

        //dd(request()->all());

        // Delete setting
        $setting = SiteExtensionCategory::findOrFail($id);
        $setting->status = 0;
        $setting->save();

        // Re-orer settings
        $settings = SiteExtensionCategory::where('status', 1)->orderBy('order')->get();
        $order = 1;
        foreach ($settings as $setting) {
            $setting->order = $order ++;
            $setting->save();
        }

        Toastr::success("Updated settings");

        return redirect("/site/extension/settings");
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
                    //Mail::to($email_to)->send(new \App\Mail\Site\SiteUpcomingCompliance($startdata, $file));
                    $data = ['startdata' => $startdata, 'settings_colours' => $settings_colours];
                    Mail::send('emails/site/upcoming-compliance', $data, function ($m) use ($email_to, $data, $file) {
                        $send_from = 'do-not-reply@safeworksite.com.au';
                        $m->from($send_from, 'Safe Worksite');
                        $m->to($email_to);
                        $m->subject('SafeWorksite - Upcoming Jobs Compliance Data');
                        $m->attach($file);
                    });
                    Toastr::success("Sent email");
                }

                return redirect("/site/upcoming/compliance");
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    static public function getUpcomingData()
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
}
