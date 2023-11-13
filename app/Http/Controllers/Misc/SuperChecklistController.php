<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use File;
use Mail;
use Session;
use App\User;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistCategory;
use App\Models\Misc\Supervisor\SuperChecklistQuestion;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Misc\Supervisor\SuperChecklistNote;
use App\Models\Misc\Supervisor\SuperChecklistSettings;
use App\Models\Site\Site;
use App\Models\Comms\Todo;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Intervention\Image\Facades\Image;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SuperChecklistController
 * @package App\Http\Controllers
 */
class SuperChecklistController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('super.checklist'))
            return view('errors/404');

        $mon = new Carbon('monday this week');
        $mon_prev = new Carbon('monday last week');
        $fri = new Carbon('friday this week');
        $today = Carbon::now();
        $checklists = SuperChecklist::select(['supervisor_checklist.*', 'users.firstname as super_name'])
            ->join('users', 'supervisor_checklist.super_id', '=', 'users.id')
            ->whereDate('date', $mon->format('Y-m-d'))->orderBy('users.firstname')->get();

        $checklists_previous_week = SuperChecklist::select(['supervisor_checklist.*', 'users.firstname as super_name'])
            ->join('users', 'supervisor_checklist.super_id', '=', 'users.id')
            ->whereDate('date', $mon_prev->format('Y-m-d'))->where('supervisor_checklist.status', 1)->orderBy('users.firstname')->get();

        // Set Supervisor seen
        if (Auth::user()->permissionLevel('view.super.checklist', 3) == 99)
            $supervisors = Auth::user()->company->supervisors()->pluck('id')->toArray(); // All supers
        else
            $supervisors = [Auth::user()->id]; // Only 'own' id

        // Create classes for each cell in table
        $classes = [];
        for ($i = 1; $i < 6; $i ++) {
            $classes[$i] = '';
            if ($today->format('w') > $i)
                $classes[$i] = 'viewChecklist';
            if ($today->format('w') == $i)
                $classes[$i] = 'hoverDiv todayBG editChecklist';

        }

        return view('supervisor/checklist/list', compact('checklists', 'checklists_previous_week', 'fri', 'today', 'classes', 'supervisors'));
    }

    /**
     * Display the specified resource.
     */
    public function pastWeeks()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('super.checklist'))
            return view('errors/404');

        $mon = new Carbon('monday this week');
        $pastWeeks = [];
        foreach (SuperChecklist::whereDate('date', '<', $mon->format('Y-m-d'))->get() as $checklist) {
            if (!in_array($checklist->date->format('Y-m-d'), $pastWeeks))
                $pastWeeks[$checklist->date->format('d/m/Y')] = $checklist->date->format('Y-m-d');
        }

        //
        // Create list of Checklist for each Past Week
        //
        $data = [];
        foreach ($pastWeeks as $week => $date) {
            //$ymd = Carbon::createFromFormat('d/m/Y H:i', $date . '00:00')->toDateTimeString()
            $checklists = SuperChecklist::select(['supervisor_checklist.*', 'users.firstname as super_name'])
                ->join('users', 'supervisor_checklist.super_id', '=', 'users.id')
                ->whereDate('date', $date)->orderBy('users.firstname')->get();

            $checklists = SuperChecklist::whereDate('date', $date)->orderBy('super_id')->get();
            $str = '';
            foreach ($checklists as $checklist) {
                // Only add Supervisor Checklist authorised to view
                if (Auth::user()->permissionLevel('view.super.checklist', 3) == 99 || Auth::user()->id == $checklist->super_id)
                    $str .= $checklist->supervisor->initials . " (" . $checklist->weeklySummary() . "), ";
            }
            $str = rtrim($str, ', ');
            $data[$date] = $str;
        }
        //dd($data);
        return view('supervisor/checklist/past', compact('data'));
    }

    public function pastWeek($date)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('super.checklist'))
            return view('errors/404');

        $fri = Carbon::createFromDate($date)->addDays(5);
        $checklists = SuperChecklist::select(['supervisor_checklist.*', 'users.firstname as super_name'])
            ->join('users', 'supervisor_checklist.super_id', '=', 'users.id')
            ->whereDate('date', $date)->orderBy('users.firstname')->get();


        // Set Supervisor seen
        if (Auth::user()->permissionLevel('view.super.checklist', 3) == 99)
            $supervisors = Auth::user()->company->supervisors()->pluck('id')->toArray(); // All supers
        else
            $supervisors = [Auth::user()->id]; // Only 'own' id

        return view('supervisor/checklist/pastweek', compact('checklists', 'fri', 'supervisors'));
    }

    /**
     * Display the specified resource.
     */
    public function showResponse($checklist_id, $day)
    {
        $checklist = SuperChecklist::findOrFail($checklist_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.super.checklist', $checklist))
            return view('errors/404');

        $cat_ids = [];
        foreach ($checklist->responses as $response) {
            if (!in_array($response->question->category->id, $cat_ids))
                $cat_ids[] = $response->question->category->id;
        }
        $categories = SuperChecklistCategory::whereIn('id',$cat_ids)->where('status', 1)->orderBy('order')->get();

        return view("/supervisor/checklist/show", compact('checklist', 'day', 'categories'));
    }

    /**
     * Show Weekly Responses for Supervisor
     */
    public function showSuperWeekly($checklist_id)
    {
        $checklist = SuperChecklist::findOrFail($checklist_id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.super.checklist', $checklist))
            return view('errors/404');

        $mon = new Carbon('monday this week');
        $cat_ids = [];
        foreach ($checklist->responses as $response) {
            if (!in_array($response->question->category->id, $cat_ids))
                $cat_ids[] = $response->question->category->id;
        }
        $categories = SuperChecklistCategory::whereIn('id',$cat_ids)->where('status', 1)->orderBy('order')->get();

        return view("/supervisor/checklist/weekly", compact('checklist', 'categories', 'mon'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.super.checklist'))
            return view('errors/404');

        //return redirect('/misc/form/' . $form->id . /edit);
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $checklist = SuperChecklist::findOrFail($id);
        $day = request('day');

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.super.checklist', $checklist))
            return view('errors/404');

        //dd(request()->all());

        foreach ($checklist->responses->where('day', $day) as $response) {
            $response->value = request("r$response->id");
            $response->save();
        }
        $total = $checklist->responses->where('day', $day)->count();
        $completed = $checklist->responsesCompleted($day)->count();

        if ($total == $completed) {
            $checklist->closeToDo();
            return redirect("supervisor/checklist");
        }

        return redirect("supervisor/checklist/$checklist->id/$day");
    }

    /**
     * Sign Off Item
     */
    public function signoff($id)
    {
        $checklist = SuperChecklist::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.super.checklist', $checklist))
        //    return view('errors/404');

        if (!$checklist->supervisor_sign_by) {
            $checklist->supervisor_sign_by = Auth::user()->id;
            $checklist->supervisor_sign_at = Carbon::now();

            // Send Con Mgr ToDoo task to sign off
            $checklist->closeToDo();
            //$checklist->createSignOffToDo(getUserIdsWithRoles('con-construction-manager'));
        } else {
            $checklist->closeToDo();
            $checklist->manager_sign_by = Auth::user()->id;
            $checklist->manager_sign_at = Carbon::now();
            $checklist->status = 0;

            // Email completion
            //$email_list = (\App::environment('prod')) ? ['michelle@capecod.com.au', 'kirstie@capecod.com.au'] : [env('EMAIL_DEV')];
            //$report_file = ($checklist->attachment) ? public_path($checklist->attachmentUrl) : '';
            //if ($email_list) Mail::to($email_list)->send(new \App\Mail\Site\SiteProjectSupplyCompleted($project, $report_file));
        }
        $checklist->save();
        Toastr::success("Signed off");


        if ($checklist->status)
            return redirect("/supervisor/checklist/$checklist->id/weekly");

        return redirect("/supervisor/checklist");

    }

    /**
     * Settings
     */
    public function settings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.super.checklist'))
            return view('errors/404');

        $settings_supers = SuperChecklistSettings::where('field', 'supers')->where('status', 1)->first();
        $super_list = ($settings_supers) ? explode(',', $settings_supers->value) : [];

        return view("/supervisor/checklist/settings", compact('super_list'));
    }

    /**
     * Settings
     */
    public function updateSettings()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('del.super.checklist'))
            return view('errors/404');

        //dd(request()->all());

        // Update Supervisor List
        if (request('super_list')) {
            $super_list = implode(',', request('super_list'));
            $settings_supers = SuperChecklistSettings::where('field', 'supers')->where('status', 1)->first();
            if ($settings_supers) {
                $settings_supers->value = $super_list;
                $settings_supers->save();
            } else
                $settings_supers = SuperChecklistSettings::create(['field' => 'supers', 'value' => $super_list, 'status' => 1]);
        }


        return redirect("/supervisor/checklist/settings");
    }

    /**
     * Get Templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getChecklists()
    {
        //$template_ids = SuperChecklist::where('parent_id', request('template_id'))->pluck('id')->toArray();;


        $records = SuperChecklist::select([
            'supervisor_checklist.id', 'supervisor_checklist.super_id', 'forms.site_name', 'forms.inspected_by_name', 'forms.inspected_at', 'forms.company_id', 'forms.status', 'forms.updated_at', 'forms.created_at',
            DB::raw('DATE_FORMAT(forms.inspected_at, "%d/%m/%y") AS inspecteddate'),
            DB::raw('DATE_FORMAT(forms.completed_at, "%d/%m/%y") AS completeddate')])
            ->where('forms.company_id', Auth::user()->company->reportsTo()->id)
            ->where('forms.status', request('status'));

        //$records = Form::where('template_id',request('template_id'))->where('company_id', Auth::user()->company->reportsTo()->id)->where('forms.status', request('status'));

        $dt = Datatables::of($records)
            ->addColumn('view', function ($report) {
                return ('<div class="text-center"><a href="/site/inspection/' . $report->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->addColumn('action', function ($report) {
                $actions = '';
                if (Auth::user()->allowed2("del.site.inspection.whs", $report))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/inspection/' . $report->id . '" data-name="' . $report->site_name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'name', 'updated_at', 'created_at', 'action'])
            ->make(true);

        return $dt;
    }
}
