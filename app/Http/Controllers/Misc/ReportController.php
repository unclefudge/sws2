<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use File;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Company\Company;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;

class ReportController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the report list.
     *
     * @return Response
     */
    public function index()
    {
        return view('manage/report/list');
    }

    public function recent()
    {
        return view('manage/report/recent');
    }

    public function recentFiles()
    {
        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);

        $files = scandir_datesort(public_path($dir));

        //dd($files);
        $reports = [];
        foreach ($files as $file) {
            if (($file[0] != '.')) {
                $processed = false;
                if (filesize(public_path("$dir/$file")) > 0)
                    $processed = true;

                $date = Carbon::createFromFormat('YmdHis', substr($file, - 18, 4) . substr($file, - 14, 2) . substr($file, - 12, 2) . substr($file, - 10, 2) . substr($file, - 8, 2) . substr($file, - 6, 2));
                $deleted = false;
                if ($date->lt(Carbon::today()->subDays(10))) {
                    unlink(public_path("$dir/$file"));
                    $deleted = true;
                }

                if (!$deleted)
                    $reports[$file] = filesize(public_path("$dir/$file"));
            }
        }

        return $reports;
    }

    public function nightly()
    {
        $files = array_reverse(array_diff(scandir(public_path('/filebank/log/nightly')), array('.', '..')));

        return view('manage/report/nightly', compact('files'));
    }

    /*
     * Payroll Report
     */
    public function payroll()
    {
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();
        $companies = Auth::user()->company->companies();

        return view('manage/report/payroll', compact('companies'));
    }

    /*
     * Site Attendance Report
     */
    public function attendance()
    {
        //$companies = \App\Models\Company\Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();

        return view('manage/report/attendance'); // compact('companies'));
    }

    /**
     * Get Site Attendance user is authorise to view
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAttendance()
    {

        $site_id_all = (request('site_id_all') == 'all') ? '' : request('site_id_all');
        $site_id_active = (request('site_id_active') == 'all') ? '' : request('site_id_active');
        $site_id_completed = (request('site_id_completed') == 'all') ? '' : request('site_id_completed');
        $company_id = (request('company_id') == 'all') ? '' : request('company_id');

        if (request('status') == 1)
            $site_ids = ($site_id_active) ? [$site_id_active] : Auth::user()->company->sites(1)->pluck('id')->toArray();
        elseif (request('status') == '0')
            $site_ids = ($site_id_completed) ? [$site_id_completed] : Auth::user()->company->sites(0)->pluck('id')->toArray();
        else
            $site_ids = ($site_id_all) ? [$site_id_all] : Auth::user()->company->sites()->pluck('id')->toArray();

        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');


        //dd(request('site_id_all'));

        $company_ids = ($company_id) ? [$company_id] : Auth::user()->company->companies()->pluck('id')->toArray();

        $attendance_records = SiteAttendance::select([
            'site_attendance.site_id', 'site_attendance.user_id', 'site_attendance.date', 'sites.name',
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.company_id', 'companys.id', 'companys.name',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('sites', 'sites.id', '=', 'site_attendance.site_id')
            ->join('users', 'users.id', '=', 'site_attendance.user_id')
            ->join('companys', 'users.company_id', '=', 'companys.id')
            ->whereIn('site_attendance.site_id', $site_ids)
            ->whereIn('companys.id', $company_ids)
            ->whereDate('site_attendance.date', '>=', $date_from)
            ->whereDate('site_attendance.date', '<=', $date_to);

        //dd($attendance_records);
        $dt = Datatables::of($attendance_records)
            ->editColumn('date', function ($attendance) {
                return $attendance->date->format('d/m/Y H:i a');
            })
            ->editColumn('sites.name', function ($attendance) {
                return '<a href="/site/' . $attendance->site->slug . '">' . $attendance->site->name . '</a>';
            })
            ->editColumn('full_name', function ($attendance) {
                return '<a href="/user/' . $attendance->user->id . '">' . $attendance->user->full_name . '</a>';
            })
            ->editColumn('companys.name', function ($attendance) {
                return '<a href="/company/' . $attendance->user->company_id . '">' . $attendance->user->company->name . '</a>';
            })
            ->rawColumns(['id', 'full_name', 'companys.name', 'sites.name'])
            ->make(true);

        return $dt;
    }

    /****************************************************
     * Quality Assurance
     ***************************************************/

    /*
     * QA Debug
     */
    public function QAdebug($id)
    {
        $qa = SiteQa::find($id);
        $task_ids = [];
        foreach ($qa->items as $item) {
            if (!in_array($item->task_id, $task_ids))
                $task_ids[] = $item->task_id;
        }
        $planner = SitePlanner::where('site_id', $qa->site_id)->whereIn('task_id', $task_ids)->get();
        $todos = ToDo::where('type', 'qa')->where('type_id', $id)->get();

        return view('manage/report/qa_debug', compact('qa', 'planner', 'todos'));
    }

    /*
     *  On Hold QA
     */
    public function OnholdQA()
    {
        $today = Carbon::now();
        $qas = SiteQa::where('status', 2)->where('master', 0)->orderBy('updated_at')->get();

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        return view('manage/report/qa_onhold', compact('qas', 'supers'));
    }

    /*
     *  On Hold QA PDF
     */
    public function OnholdQAPDF()
    {
        $today = Carbon::now();
        $qas = SiteQa::where('status', 2)->where('master', 0)->orderBy('updated_at')->get();

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        return PDF::loadView('pdf/site/site-qa-onhold', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
    }

    /*
     *  Outstanding QA
     */
    public function OutstandingQA()
    {
        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        return view('manage/report/qa_outstanding', compact('qas', 'supers'));
    }

    /*
     *  Outstanding QA PDF
     */
    public function OutstandingQAPDF()
    {
        $today = Carbon::now();
        $weekago = Carbon::now()->subWeek();
        $qas = SiteQa::whereDate('updated_at', '<=', $weekago->format('Y-m-d'))->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();

        // Supervisors list
        $supers = [];
        foreach ($qas as $qa) {
            if (!in_array($qa->site->supervisorsSBC(), $supers))
                $supers[] .= $qa->site->supervisorsSBC();
        }
        sort($supers);

        return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
    }

    public function maintenanceNoAction()
    {
        $active_requests = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        $mains = [];
        foreach ($active_requests as $main) {
            if ($main->lastUpdated()->lt(Carbon::now()->subDays(14)))
                $mains[] = $main;
        }

        return view('manage/report/maintenance_no_action', compact('mains'));
    }

    public function maintenanceOnHold()
    {
        $mains = SiteMaintenance::where('status', 3)->orderBy('reported')->get();

        return view('manage/report/maintenance_onhold', compact('mains'));
    }


    /*
    * Inspection List Report
    */
    public function siteInspections()
    {
        //$equipment = Equipment::where('status', 1)->orderBy('name')->get();

        return view('manage/report/site_inspections');
    }

    /**
     * Get Accidents current user is authorised to manage + Process datatables ajax request.
     */
    public function getSiteInspections()
    {
        if (request('type') == 'electrical') {
            $inspect_records = SiteInspectionElectrical::select([
                'site_inspection_electrical.id', 'site_inspection_electrical.site_id', 'site_inspection_electrical.inspected_name', 'site_inspection_electrical.inspected_by',
                'site_inspection_electrical.inspected_at', 'site_inspection_electrical.created_at',
                'site_inspection_electrical.status', 'sites.company_id', 'companys.name',
                DB::raw('DATE_FORMAT(site_inspection_electrical.inspected_at, "%d/%m/%y") AS nicedate'),
                DB::raw('sites.name AS sitename'), 'sites.code',
                DB::raw('companys.name AS companyname'),
            ])
                ->join('sites', 'site_inspection_electrical.site_id', '=', 'sites.id')
                ->join('companys', 'site_inspection_electrical.assigned_to', '=', 'companys.id')
                ->where('site_inspection_electrical.status', '=', 0);

            $dt = Datatables::of($inspect_records)
                ->addColumn('view', function ($inspect) {
                    return ('<div class="text-center"><a href="/site/inspection/electrical/' . $inspect->id . '"><i class="fa fa-search"></i></a></div>');
                })
                ->addColumn('action', function ($inspect) {
                    return ('<a href="/site/inspection/electrical/' . $inspect->id . '/report" target="_blank"><i class="fa fa-file-pdf-o"></i></a>');
                })
                ->rawColumns(['view', 'action'])
                ->make(true);
        } else {
            $inspect_records = SiteInspectionPlumbing::select([
                'site_inspection_plumbing.id', 'site_inspection_plumbing.site_id', 'site_inspection_plumbing.inspected_name', 'site_inspection_plumbing.inspected_by',
                'site_inspection_plumbing.inspected_at', 'site_inspection_plumbing.created_at',
                'site_inspection_plumbing.status', 'sites.company_id', 'companys.name',
                DB::raw('DATE_FORMAT(site_inspection_plumbing.inspected_at, "%d/%m/%y") AS nicedate'),
                DB::raw('sites.name AS sitename'), 'sites.code',
                DB::raw('companys.name AS companyname'),
            ])
                ->join('sites', 'site_inspection_plumbing.site_id', '=', 'sites.id')
                ->join('companys', 'site_inspection_plumbing.assigned_to', '=', 'companys.id')
                ->where('site_inspection_plumbing.status', '=', 0);

            $dt = Datatables::of($inspect_records)
                ->addColumn('view', function ($inspect) {
                    return ('<div class="text-center"><a href="/site/inspection/plumbing/' . $inspect->id . '"><i class="fa fa-search"></i></a></div>');
                })
                ->addColumn('action', function ($inspect) {
                    return ('<a href="/site/inspection/plumbing/' . $inspect->id . '/report" target="_blank"><i class="fa fa-file-pdf-o"></i></a>');
                })
                ->rawColumns(['view', 'action'])
                ->make(true);

        }

        return $dt;
    }

}
