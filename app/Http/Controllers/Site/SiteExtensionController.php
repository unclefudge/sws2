<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Misc\CategoryController;
use App\Models\Comms\Todo;
use App\Models\Misc\Category;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Site\SiteNote;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;

/**
 * Class SiteExtensionController
 * @package App\Http\Controllers\Site
 */
class SiteExtensionController extends Controller
{

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
        if (Auth::user()->isSupervisor() && Auth::user()->company_id == 3 && Auth::user()->permissionLevel('view.site.extension', 3) != 99)
            $super_id = Auth::user()->id;

        $extension = SiteExtension::where('status', 1)->latest()->first();
        if ($extension)
            return redirect("/site/extension/$extension->id/$super_id");

        return view('errors/404');
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
        $extend_reasons = Category::where('type', 'site_extension')->where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $reason_na = Category::where('type', 'site_extension')->where('status', 1)->where('name', 'N/A')->first()->id;
        $reason_publichol = Category::where('type', 'site_extension')->where('status', 1)->where('name', 'Public Holiday')->first()->id;

        $multi_site_sel = [];
        foreach ($extension->sites as $ext) {
            if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                $multi_site_sel[$ext->site->id] = $ext->site->name;
            elseif (Auth::user()->isSupervisor() && Auth::user()->id == $ext->site->supervisor_id)
                $multi_site_sel[$ext->site->id] = $ext->site->name;
        }

        ksort($multi_site_sel);
        if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            $multi_site_sel = ['all' => 'All Active Sites'] + $multi_site_sel;

        $today = Carbon::now();
        $last_mon = Carbon::now()->subDays(7)->startOfWeek();
        $last_sun = Carbon::now()->subDays(7)->endOfWeek();

        $site_ids = [];
        foreach ($extension->sites as $site_ext) {
            if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager') || $site_ext->site->isSupervisorOrAreaSupervisor(Auth::user()))
                $site_ids[] = $site_ext->site_id;
        }
        //ray($site_ids);
        $notes = SiteNote::where('category_id', 16)->where('variation_days', '>', 0)->whereIn('site_id', $site_ids)->whereBetween('created_at', [$last_mon, $last_sun])->where('parent', null)->get();

        //ray($notes);
        return view('site/extension/show', compact('supervisor_id', 'extension', 'data', 'extend_reasons', 'reason_na', 'reason_publichol', 'multi_site_sel', 'notes'));
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

        //$cats = SiteExtensionCategory::where('status', 1)->orderBy('order')->get();
        $cats = Category::where('type', 'site_extension')->where('status', 1)->orderBy('order')->get();

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

    public function show()
    {

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
        if (request('ext_id')) {
            $site_ext = SiteExtensionSite::findOrFail(request('ext_id'));

            $site_ext->days = request('days');
            $site_ext->notes = request('extension_notes');
            $site_ext->reasons = (request('reasons')) ? implode(',', request('reasons')) : null;
            $site_ext->save();

            if (request('multi_extension')) {
                if (in_array('all', request('multi_sites'))) {
                    foreach ($site_ext->extension->sites as $ext) {
                        if ($ext->site_id == $site_ext->site_id) // skip selected extension
                            continue;
                        $ext->days = $ext->days + request('days');
                        $ext->notes = $ext->notes . "\n" . request('extension_notes');
                        $reasons = (request('reasons')) ? implode(',', request('reasons')) : '';
                        $ext->reasons = ($ext->reasons) ? $ext->reasons . ",$reasons" : $reasons;
                        $ext->save();
                    }
                } else {
                    foreach (request('multi_sites') as $site_id) {
                        if ($site_id == 'all' || $site_id == $site_ext->site_id) // skip selected extension 
                            continue;
                        $ext = SiteExtensionSite::where('extension_id', $site_ext->extension_id)->where('site_id', $site_id)->first();
                        if ($ext) {
                            $ext->days = $ext->days + request('days');
                            $ext->notes = $ext->notes . "\n" . request('extension_notes');
                            $old_reasons = explode(',', $ext->reasons);
                            $old_reasons = array_diff($old_reasons, [1]); // remove N/A if present
                            $combined_reasons = array_merge($old_reasons, request('reasons')); // merge
                            $ext->reasons = implode(',', $combined_reasons);
                            $ext->save();
                        }
                    }
                }
            }

            $site_ext->extension->createPDF();

            // Close ToDoo task for Supervisor if all completed
            if ($site_ext->site->supervisor_id && $site_ext->extension->sitesNotCompletedBySupervisor($site_ext->site->supervisor_id)->count() == 0) {
                $todo = $site_ext->site->supervisor->todoType('extension', 1)->first();
                if ($todo)
                    $todo->close();
            }

            // Create ToDoo task for Con Mgr if all sites completed
            if (!$site_ext->extension->approved_by && $site_ext->extension->sites->count() == $site_ext->extension->sitesCompleted()->count()) {
                $todo = Todo::where('type', 'extension signoff')->where('type_id', $site_ext->extension->id)->where('status', '1')->first();
                if (!$todo)
                    $site_ext->extension->createSignOffToDo(array_merge(getUserIdsWithRoles('con-construction-manager'), [108]));
            } else
                $site_ext->extension->closeToDo();

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
        $email_list = (app()->environment('prod')) ? ['kirstie@capecod.com.au'] : [env('EMAIL_DEV')];

        if ($email_list && $email_cc)
            Mail::to($email_list)->cc($email_cc)->send(new \App\Mail\Site\SiteExtensionsReport($extension));
        elseif ($email_list)
            Mail::to($email_list)->send(new \App\Mail\Site\SiteExtensionsReport($extension));
        Toastr::success("Report Signed Off");

        $extension->save();

        return redirect("/site/extension");

    }


    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSettings(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.extension'))
            return view('errors/404');

        CategoryController::updateCategories('site_extension', $request);

        Toastr::success("Updated categories");

        return redirect(url()->previous());

    }

    public function deleteSiteExtension($id)
    {
        $extension = SiteExtensionSite::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.site.extension'))
            return view('errors/404');

        $extension->days = 0;
        $extension->reasons = 1;  // N/A
        $extension->notes = "original reason deleted by " . Auth::user()->name;
        $extension->save();

        return redirect(url()->previous());

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
        $pdf = PDF::loadView('pdf/site/contract-extension', compact('data', 'extension'))->setPaper('A4', 'landscape');

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
                $completion_type = '';
                $completion_date = '';
                //$prac_completion = SitePlanner::where('site_id', $site->site_id)->where('task_id', 265)->first();
                //if ($prac_completion) {
                //    $completion_date = $prac_completion->from->format('d/m/y');
                //    $completion_type = 'prac';
                //} elseif ($site->completion_date) {
                $completion_date = $site->completion_date->format('d/m/y');
                $completion_type = 'forecast';
                //}
                $data[] = [
                    'id' => $site->id,
                    'name' => $site->site->name,
                    'super_initials' => $site->site->supervisorInitials,
                    'super_id' => $site->site->supervisor_id,
                    'completion_date' => $completion_date,
                    'completion_type' => $completion_type,
                    'extend_reasons' => $site->reasons,
                    'extend_reasons_text' => $site->reasonsSBC(),
                    'extend_reasons_array' => $site->reasonsArray(),
                    'days' => $site->days,
                    'hols' => $site->site->holidays_added,
                    'notes' => $site->notes,
                    'total_days' => $site->totalExtensionDays(),
                    'past_extensions' => $site->pastExtensions()
                ];
            }
        }

        usort($data, function ($a, $b) {
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
