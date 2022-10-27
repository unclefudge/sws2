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

        $extension = SiteExtension::where('status', 1)->latest()->first();
        if ($extension)
            return redirect("/site/extension/$extension->id");

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
    public function show($id)
    {
        $extension = SiteExtension::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.extension', $extension))
            return view('errors/404');


        $data = $this->getData($extension);
        $extend_reasons = SiteExtensionCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();

        //dd($data);

        return view('site/extension/show', compact('extension', 'data', 'extend_reasons'));
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
            $site_ext->notes = request('extension_notes');
            if (request('reasons'))
                $site_ext->reasons = implode(',', request('reasons'));
            $site_ext->save();
            $site_ext->extension->createPDF();
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
        if (!Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
            return view('errors/404');

        $extension->approved_by = Auth::user()->id;
        $extension->approved_at = Carbon::now();

        //$extension->closeToDo();
        //$project->createSignOffToDo(DB::table('role_user')->where('role_id', 8)->get()->pluck('user_id')->toArray());

        $email_list = (\App::environment('prod')) ? ['michelle@capecod.com.au'] : [env('EMAIL_DEV')];
        if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteExtensionsReport($extension, public_path($extension->attachmentUrl)));
        Toastr::success("Report Signed Off");

        $extension->save();

        return redirect("/site/extension/$extension->id");

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
    public function showPDF()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.extension'))
            return view('errors/404');

        $email_list = Auth::user()->company->reportsTo()->notificationsUsersTypeArray('site.extension');

        return view('site/extension/pdf', compact('email_list'));
    }

    /**
     * Create upcoming PDF
     */
    public function createPDF()
    {
        //dd(request()->all());

        $extension = SiteExtension::where('status', 1)->latest()->first();
        $data = $this->getData($extension);
        //dd($startdata);

        //return view('pdf/site/contract-extension', compact('data'));
        $pdf = PDF::loadView('pdf/site/contract-extension', compact('data'));
        $pdf->setPaper('A4', 'landscape');


        if (request()->has('view_pdf'))
            return $pdf->stream();

        if (request()->has('email_pdf')) {
            $file = public_path('filebank/tmp/contract-extension' . Auth::user()->id . '.pdf');
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
                    $data = ['data' => $data];
                    Mail::send('emails/site/contract-extension', $data, function ($m) use ($email_to, $data, $file) {
                        $send_from = 'do-not-reply@safeworksite.com.au';
                        $m->from($send_from, 'Safe Worksite');
                        $m->to($email_to);
                        $m->subject('SafeWorksite - Contract Time Extension');
                        $m->attach($file);
                    });
                    Toastr::success("Sent email");
                }

                return redirect("/site/extension/pdf");
            }
        }
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
                    'super_initials'       => $site->site->supervisorsInitialsSBC(),
                    'completion_date'      => ($site->completion_date) ? $site->completion_date->format('d/m/y') : '',
                    'extend_reasons'       => $site->reasons,
                    'extend_reasons_text'  => $site->reasonsSBC(),
                    'extend_reasons_array' => $site->reasonsArray(),
                    'notes'                => $site->notes
                ];
            }
        }

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
