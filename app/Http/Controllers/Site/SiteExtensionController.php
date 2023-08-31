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
use App\Jobs\SiteExtensionPdf;
use App\Comms\Todo;
use App\Models\Site\Site;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
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

        // Set Supervisor ID for known supervisors to limit sites to their own
        $super_id = 0;
        if (Auth::user()->isSupervisor() && Auth::user()->company_id == 3 && Auth::user()->id != 7) // ie Not Gary
            $super_id = Auth::user()->id;

        $extension = SiteExtension::where('status', 1)->latest()->first();
        if ($extension)
            return redirect("/site/extension/$extension->id/$super_id");

        return view('errors/404');
        //$data = $this->getData($extension);
        //$extend_reasons = SiteExtensionCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        //dd($data);
        //return view('site/extension/list', compact('extension', 'data', 'extend_reasons'));
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showExtensions($id, $supervisor_id)
    {
        $extension = SiteExtension::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.extension', $extension))
            return view('errors/404');


        $data = $this->getData($extension);
        $extend_reasons = SiteExtensionCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        //dd($data);

        return view('site/extension/show', compact('supervisor_id', 'extension', 'data', 'extend_reasons'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function past()
    {

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.extension'))
            return view('errors/404');

        $extensions = SiteExtension::where('status', 0)->orderBy('date', 'desc')->get();

        return view('site/extension/past', compact('extensions'));
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
        if (!Auth::user()->hasPermission2('edit.site.extension'))
            return view('errors/404');

        //dd(request()->all());

        if (request('site_id')) {
            $site_ext = SiteExtensionSite::findOrFail(request('site_id'));
            $site_ext->days = request('days');
            $site_ext->notes = request('extension_notes');
            $site_ext->reasons = (request('reasons')) ? implode(',', request('reasons')) : null;
            $site_ext->save();
            $site_ext->extension->createPDF();

            // Close ToDoo task for Supervisor if all completed
            if ($site_ext->site->supervisor_id && $site_ext->extension->sitesNotCompletedBySupervisor($site_ext->site->supervisor_id)->count() == 0) {
                $todo = $site_ext->site->supervisor->todoType('extension', 1)->first();
                if ($todo)
                    $todo->close();
            }

            // Create ToDoo task for Con Mgr if all sites completed
            if ($site_ext->extension->sites->count() == $site_ext->extension->sitesCompleted()->count())
                $site_ext->extension->createSignOffToDo(getUserIdsWithRoles('con-construction-manager'));
            //$site_ext->extension->createSignOffToDo(['325']);  // Michelle 325, Courtney 1359
        }

        Toastr::success("Updated extension");

        return redirect("/site/extension");
    }

    /**
     * Sign Off Item
     */
    public function signoff($id)
    {
        $extension = SiteExtension::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'))
            return view('errors/404');

        $extension->approved_by = Auth::user()->id;
        $extension->approved_at = Carbon::now();

        $extension->closeToDo();

        $email_cc = '';
        $email_list = (\App::environment('prod')) ? ['michelle@capecod.com.au', 'courtney@capecod.com.au'] : [env('EMAIL_DEV')];
        $email_list = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
        //$email_cc = (\App::environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
        if ($email_list && $email_cc) Mail::to($email_list)->cc($email_cc)->send(new \App\Mail\Site\SiteExtensionsReport($extension, public_path($extension->attachmentUrl)));
        elseif ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteExtensionsReport($extension, public_path($extension->attachmentUrl)));
        Toastr::success("Report Signed Off");

        $extension->save();

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
    public function createPDF()
    {
        $extension = SiteExtension::where('status', 1)->latest()->first();
        $data = $this->getData($extension);
        //dd($data);

        //return view('pdf/site/contract-extension', compact('data', 'extension'));
        $pdf = PDF::loadView('pdf/site/contract-extension', compact('data', 'extension'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    static public function getData($extension)
    {
        $data = [];
        if ($extension) {
            foreach ($extension->sites as $site) {
                $data[] = [
                    'id'                   => $site->id,
                    'name'                 => $site->site->name,
                    'super_initials'       => $site->site->supervisorInitials,
                    'super_id'             => $site->site->supervisor_id,
                    'completion_date'      => ($site->completion_date) ? $site->completion_date->format('d/m/y') : '',
                    'extend_reasons'       => $site->reasons,
                    'extend_reasons_text'  => $site->reasonsSBC(),
                    'extend_reasons_array' => $site->reasonsArray(),
                    'days'                 => $site->days,
                    'notes'                => $site->notes,
                    'total_days'           => $site->totalExtensionDays(),
                    'past_extentions'      => $site->pastExtensions()
                ];
            }
        }

        usort($data, function ($a, $b) {
            //return $a['name'] <=> $b['name'];
            return $a['name'] < $b['name'];
        });

        /*
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
        */

        return $data;
    }
}
