<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteUpcomingSettings;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;

/**
 * Class SiteUpcomingComplianceController
 * @package App\Http\Controllers\Site
 */
class SiteUpcomingComplianceController extends Controller
{

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

        //dd('here');
        $types = ['opt', 'cfest', 'cfadm'];
        foreach ($types as $type) {
            $settings_select[$type] = ['' => 'Select stage'] + SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('name', 'order')->toArray();
            $colours = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('colour', 'order')->toArray();
            $settings_colours[$type] = [];
            if ($colours) {
                foreach ($colours as $order => $colour) {
                    if ($colour) {
                        list($col1, $col2, $hex) = explode('-', $colour);
                        $settings_colours[$type][$order] = "#$hex";
                    } else
                        $settings_colours[$type][$order] = '';
                }
            }
            $settings_text[$type] = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('value', 'order')->toArray();
        }
        //var_dump($settings_select);
        //var_dump($settings_colours);
        //dd($settings_text);
        //dd($startdata);


        return view('site/upcoming/compliance/list', compact('startdata', 'settings', 'settings_select', 'settings_text', 'settings_colours'));
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
        $settings = SiteUpcomingSettings::whereIn('field', ['opt', 'cfest', 'cfadm'])->where('status', 1)->get();

        $settings_sites = SiteUpcomingSettings::where('field', 'sites')->where('status', 1)->first();
        $special_sites = ($settings_sites) ? explode(',', $settings_sites->value) : [];

        $settings_email = SiteUpcomingSettings::where('field', 'email')->where('status', 1)->first();
        $email_list = ($settings_email) ? explode(',', $settings_email->value) : [];

        //dd($email_list);

        return view('site/upcoming/compliance/settings', compact('settings', 'email_list', 'special_sites', 'cc', 'fc_plans', 'fc_struct'));
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

            // Drafting
            if (Auth::user()->hasAnyRole2('dra-draftsperson|dra-drafting-manager|mgt-general-manager|web-admin')) {
                $site->cc = (request('cc')) ? request('cc') : null;
                $site->cc_stage = (request('cc_stage')) ? request('cc_stage') : null;
                $site->fc_plans = (request('fc_plans')) ? request('fc_plans') : null;
                $site->fc_plans_stage = (request('fc_plans_stage')) ? request('fc_plans_stage') : null;
                $site->fc_struct = (request('fc_struct')) ? request('fc_struct') : null;
                $site->fc_struct_stage = (request('fc_struct_stage')) ? request('fc_struct_stage') : null;
            }

            // Estimators
            if (Auth::user()->hasAnyRole2('est-estimator|est-estimating-manager|mgt-general-manager|web-admin')) {
                $site->cf_est = (request('cf_est')) ? request('cf_est') : null;
                $site->cf_est_stage = (request('cf_est_stage')) ? request('cf_est_stage') : null;

            }

            // Admins
            if (Auth::user()->hasAnyRole2('gen-administrator|gen-admin-manager|con-administrator|mgt-general-manager|web-admin')) {
                $site->cf_adm = (request('cf_adm')) ? request('cf_adm') : null;
                $site->cf_adm_stage = (request('cf_adm_stage')) ? request('cf_adm_stage') : null;
            }

            $site->save();
        }

        Toastr::success("Updated compliance");

        return redirect("/site/upcoming/compliance");
    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.upcoming.compliance'))
            return view('errors/404');

        //dd(request()->all());

        // Add Extra Field
        $types = ['opt', 'cfest', 'cfadm'];
        foreach ($types as $type) {

            // Validate if adding a option
            if (request("$type-addfield")) {
                $rules = ["$type-addfield-name" => 'required'];
                $mesg = ["$type-addfield-name.required" => 'The stage name field is required.'];
                request()->validate($rules, $mesg); // Validate
            }

            $settings = SiteUpcomingSettings::where('field', $type)->where('status', 1)->get();
            // Get field values from request
            foreach ($settings as $setting) {
                if (request()->has("$type-$setting->id")) {
                    if (request("$type-$setting->id")) {
                        $setting->name = request("$type-$setting->id");
                        // Default text
                        if (request("$type-$setting->id-text"))
                            $setting->value = request("$type-$setting->id-text");
                        // Colour
                        if (request("$type-$setting->id-colour"))
                            $setting->colour = request("$type-$setting->id-colour");
                        $setting->save();
                    } else
                        return back()->withErrors(["$type-$setting->id" => "The stage name field is required."]);
                }
            }

            // Add Field
            if (request("$type-addfield")) {
                $add_colour = (request("$type-addfield-colour")) ? request("$type-addfield-colour") : null;
                $add_order = count($settings) + 1;
                SiteUpcomingSettings::create(['field' => $type, 'name' => request("$type-addfield-name"), 'value' => request("$type-addfield-text"), 'colour' => $add_colour, 'order' => $add_order, 'status' => 1, 'company_id' => Auth::user()->company_id]);
            }
        }

        // Update Special Sites
        $settings_sites = SiteUpcomingSettings::where('field', 'sites')->where('status', 1)->first();
        if (request('special_sites')) {
            $special_sites = implode(',', request('special_sites'));
            if ($settings_sites) {
                $settings_sites->value = $special_sites;
                $settings_sites->save();
            } else
                $settings_sites = SiteUpcomingSettings::create(['field' => 'sites', 'value' => $special_sites, 'status' => 1, 'company_id' => Auth::user()->company_id]);
        } else {
            $settings_sites->value = '';
            $settings_sites->save();
        }


        // Update Email List
        /*
        if (request('email_list')) {
            $email_list = implode(',', request('email_list'));
            $settings_email = SiteUpcomingSettings::where('field', 'email')->where('status', 1)->first();
            if ($settings_email) {
                $settings_email->value = $email_list;
                $settings_email->save();
            } else
                $settings_email = SiteUpcomingSettings::create(['field' => 'email', 'value' => $email_list, 'status' => 1, 'company_id' => Auth::user()->company_id]);
        }*/

        Toastr::success("Updated settings");

        return redirect("/site/upcoming/compliance/settings");
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteSetting($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.upcoming.compliance'))
            return view('errors/404');

        //dd(request()->all());

        // Delete setting
        $setting = SiteUpcomingSettings::findOrFail($id);
        $field = $setting->field;
        $setting->delete();

        // Re-order settings
        $settings = SiteUpcomingSettings::where('field', $field)->where('status', 1)->orderBy('order')->get();
        $order = 1;
        foreach ($settings as $setting) {
            $setting->order = $order++;
            $setting->save();
            //echo "updated [$setting->id][$field] $order<br>";
        }


        Toastr::success("Updated settings");

        return redirect("/site/upcoming/compliance/settings");
    }

    /**
     * Create upcoming PDF
     */
    public function showPDF()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            return view('errors/404');

        $email_list = Auth::user()->company->reportsTo()->notificationsUsersTypeArray('site.upcoming.compliance');

        return view('site/upcoming/compliance/pdf', compact('email_list'));
    }

    /**
     * Create upcoming PDF
     */
    public function createPDF()
    {
        //dd(request()->all());

        $types = ['opt', 'cfest', 'cfadm'];
        foreach ($types as $type) {
            $colours = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('colour', 'order')->toArray();
            $settings_colours[$type] = [];
            if ($colours) {
                foreach ($colours as $order => $colour) {
                    if ($colour) {
                        list($col1, $col2, $hex) = explode('-', $colour);
                        $settings_colours[$type][$order] = "#$hex";
                    } else
                        $settings_colours[$type][$order] = '';
                }
            }
            $settings_text[$type] = SiteUpcomingSettings::where('field', $type)->where('status', 1)->pluck('value', 'order')->toArray();
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

        //
        // Sort by Start Job
        //
        $startdata = [];
        $sites_started = [];
        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            if ($site->status == 1) {
                $entity_name = "-";
                if ($plan->entity_type == 'c') {
                    $company = Company::find($plan->entity_id);
                    $entity_name = ($company->abbr) ? $company->abbr : $company->name;
                }

                $cc = $cc_stage = null;
                if ($site->cc) {
                    $cc = $site->cc;
                    $cc_stage = $site->cc_stage;
                } elseif ($site->construction_rcvd) {
                    $cc = "CC Received " . $site->construction_rcvd->format('d/m/y');
                    $cc_stage = 1;
                }
                $sites_started[] = $site->id;
                $startdata[] = [
                    'id' => $site->id,
                    'status' => $site->status,
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M-d'),
                    'date_est' => '',
                    'date_ymd' => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('Ymd'),
                    'code' => $site->code,
                    'name' => $site->name,
                    'company' => $entity_name,
                    'supervisor' => $site->supervisorInitials,
                    'deposit_paid' => ($site->deposit_paid) ? $site->deposit_paid->format('M-d') : '-',
                    'eng' => ($site->engineering) ? 'Y' : '-',
                    'hbcf' => ($site->hbcf_start) ? $site->hbcf_start->format('M-d') : '-',
                    'design_con' => $site->consultantInitials(),
                    'project_mgr' => $site->projectManagerInitials,
                    'estimator_fc' => $site->estimator_fc,
                    'cc' => $cc,
                    'cc_stage' => $cc_stage,
                    'fc_plans' => $site->fc_plans,
                    'fc_plans_stage' => $site->fc_plans_stage,
                    'fc_struct' => $site->fc_struct,
                    'fc_struct_stage' => $site->fc_struct_stage,
                    'cf_est' => $site->cf_est,
                    'cf_est_stage' => $site->cf_est_stage,
                    'cf_adm' => $site->cf_adm,
                    'cf_adm_stage' => $site->cf_adm_stage,
                ];
            }
        }

        $site_list = [];
        //dd($startdata);

        // Add Sites with (contract_signed, deposit_paid)
        $extra_sites = Site::where('status', '-1')->whereNotNull('contract_signed')->whereNotNull('deposit_paid')->where('company_id', 3)->orderBy('deposit_paid')->pluck('id')->toArray();
        foreach ($extra_sites as $sid)
            if (!in_array($sid, $site_list))
                $site_list[] = $sid;

        // Add Sites with (deposit_paid)
        $extra_sites = Site::where('status', '-1')->whereNotNull('deposit_paid')->where('company_id', 3)->orderBy('deposit_paid')->pluck('id')->toArray();
        foreach ($extra_sites as $sid)
            if (!in_array($sid, $site_list))
                $site_list[] = $sid;

        // Add Sites with (contract_signed)
        $extra_sites = Site::where('status', '-1')->whereNotNull('contract_signed')->where('company_id', 3)->orderBy('contract_signed')->pluck('id')->toArray();
        foreach ($extra_sites as $sid)
            if (!in_array($sid, $site_list))
                $site_list[] = $sid;

        // Add Sites with (council_approval)
        /*$extra_sites = Site::where('status', '-1')->whereNotNull('council_approval')->where('company_id', 3)->orderBy('council_approval')->pluck('id')->toArray();
        foreach ($extra_sites as $sid)
            if (!in_array($sid, $site_list))
                $site_list[] = $sid;
        */

        // Add Active Sites without a JobStart
        $extra_sites = Site::where('status', '1')->where('special', null)->where('company_id', 3)->get();
        foreach ($extra_sites as $site)
            if (!$site->jobStart && !in_array($site->id, $site_list))
                $site_list[] = $site->id;

        // Add Specially Requested Sites to List
        $settings_sites = SiteUpcomingSettings::where('field', 'sites')->where('status', 1)->first();
        $special_sites = ($settings_sites) ? explode(',', $settings_sites->value) : [];
        foreach ($special_sites as $sid)
            if (!(in_array($sid, $site_list) || in_array($sid, $sites_started)))
                $site_list[] = $sid;

        //dd($site_list);

        foreach ($site_list as $site_id) {
            $site = Site::find($site_id);

            if ($site) {
                $cc = $cc_stage = null;
                if ($site->cc) {
                    $cc = $site->cc;
                    $cc_stage = $site->cc_stage;
                } elseif ($site->construction_rcvd) {
                    $cc = "CC Received " . $site->construction_rcvd->format('d/m/y');
                    $cc_stage = 1;
                }


                // Consultant Initials
                $startdata[] = [
                    'id' => $site->id,
                    'status' => $site->status,
                    'date' => '',
                    'date_est' => ($site->jobstart_estimate) ? $site->jobstart_estimate->format('M-d') : '',
                    'date_ymd' => ($site->jobstart_estimate) ? $site->jobstart_estimate->format('Ymd') : '',
                    'code' => $site->code,
                    'name' => $site->name,
                    'company' => '-',
                    'supervisor' => $site->supervisorInitials,
                    'deposit_paid' => ($site->deposit_paid) ? $site->deposit_paid->format('M-d') : '-',
                    'eng' => ($site->engineering) ? 'Y' : '-',
                    'hbcf' => ($site->hbcf_start) ? $site->hbcf_start->format('M-d') : '-',
                    'design_con' => $site->consultantInitials(),
                    'project_mgr' => $site->projectManagerInitials,
                    'estimator_fc' => $site->estimator_fc,
                    'cc' => $cc,
                    'cc_stage' => $cc_stage,
                    'fc_plans' => $site->fc_plans,
                    'fc_plans_stage' => $site->fc_plans_stage,
                    'fc_struct' => $site->fc_struct,
                    'fc_struct_stage' => $site->fc_struct_stage,
                    'cf_est' => $site->cf_est,
                    'cf_est_stage' => $site->cf_est_stage,
                    'cf_adm' => $site->cf_adm,
                    'cf_adm_stage' => $site->cf_adm_stage,
                ];
            }
        }

        //dd('hhh');

        // Sort by start date
        usort($startdata, function ($a, $b) {
            //return $a['name'] <=> $b['name'];
            return $a['date_ymd'] > $b['date_ymd'];
        });
        //dd($startdata);

        return $startdata;
    }
}
