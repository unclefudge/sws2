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
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteExtension;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Safety\SafetyDoc;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
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
        $todos = Todo::where('type', 'qa')->where('type_id', $id)->get();

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
            if (!in_array($qa->site->supervisorName, $supers))
                $supers[] .= $qa->site->supervisorName;
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
            if (!in_array($qa->site->supervisorName, $supers))
                $supers[] .= $qa->site->supervisorName;
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
            if (!in_array($qa->site->supervisorName, $supers))
                $supers[] .= $qa->site->supervisorName;
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
            if (!in_array($qa->site->supervisorName, $supers))
                $supers[] .= $qa->site->supervisorName;
        }
        sort($supers);

        return PDF::loadView('pdf/site/site-qa-outstanding', compact('qas', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
    }


    /****************************************************
     * Site
     ***************************************************/

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
        $user_id = (request('user_id') == 'all') ? '' : request('user_id');

        if (request('status') == '1')
            $site_ids = ($site_id_active) ? [$site_id_active] : Auth::user()->company->sites(1)->pluck('id')->toArray();
        elseif (request('status') == '0')
            $site_ids = ($site_id_completed) ? [$site_id_completed] : Auth::user()->company->sites(0)->pluck('id')->toArray();
        else
            $site_ids = ($site_id_all) ? [$site_id_all] : Auth::user()->company->sites()->pluck('id')->toArray();

        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $company_ids = ($company_id) ? [$company_id] : Auth::user()->company->companies()->pluck('id')->toArray();
        $user_ids = ($user_id) ? [$user_id] : Auth::user()->company->users()->pluck('id')->toArray();

        //var_dump($site_ids);
        //dd(request()->all());

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
            ->whereIn('users.id', $user_ids)
            ->whereDate('site_attendance.date', '>=', $date_from)
            ->whereDate('site_attendance.date', '<=', $date_to);

        //dd($attendance_records);
        $dt = Datatables::of($attendance_records)
            ->editColumn('date', function ($attendance) {
                return $attendance->date->format('d/m/Y H:i a');
            })
            ->editColumn('sites.name', function ($attendance) {
                return '<a href="/site/' . $attendance->site->id . '">' . $attendance->site->name . '</a>';
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

    /****************************************************
     * Maintenance
     ***************************************************/

    public function maintenanceNoAction()
    {
        $active_requests = SiteMaintenance::where('status', 1)->orderBy('reported')->get();
        $mains = [];
        foreach ($active_requests as $main) {
            if ($main->lastUpdated()->lt(Carbon::now()->subDays(14)))
                $mains[$main->lastAction()->updated_at->format('Ymd')] = $main;
        }
        ksort($mains);

        return view('manage/report/site/maintenance_no_action', compact('mains'));
    }

    public function maintenanceOnHold()
    {
        $mains = SiteMaintenance::where('status', 3)->orderBy('reported')->get();

        return view('manage/report/site/maintenance_onhold', compact('mains'));
    }

    public function maintenanceAppointment()
    {
        $mains = SiteMaintenance::where('status', 1)->where('client_appointment', null)->orderBy('reported')->get();
        $mains2 = SiteMaintenance::where('status', 1)->where('client_contacted', null)->orderBy('reported')->get();

        return view('manage/report/site/maintenance_appointment', compact('mains', 'mains2'));
    }

    public function maintenanceAftercare()
    {
        $mains = SiteMaintenance::where('status', 0)->where('ac_form_sent', null)->orderBy('updated_at')->get();

        return view('manage/report/site/maintenance_aftercare', compact('mains'));
    }

    public function maintenanceAssignedCompany()
    {
        $mains = SiteMaintenance::where('status', 1)->orderBy('reported')->get();

        return view('manage/report/site/maintenance_assigned_company', compact('mains'));
    }

    public function maintenanceSupervisorNoAction()
    {
        $today = Carbon::now();
        $mains = SiteMaintenance::where('status', 1)->orderBy('reported')->get();

        // Supervisors list
        $supers = [];
        foreach ($mains as $main) {
            if ($main->super_id) {
                if (!isset($supers[$main->super_id]))
                    $supers[$main->super_id] = $main->taskOwner->fullname;
            } else
                $supers[0] = 'Unassigned';
        }
        asort($supers);

        return PDF::loadView('pdf/site/maintenance-supervisor-noaction', compact('mains', 'supers', 'today'))->setPaper('a4', 'landscape')->stream();
    }

    public function maintenanceExecutive()
    {
        if (!request('date_from')) {
            $from = Carbon::now()->subDays(90);
            $to = Carbon::now();
        } else {
            $from = Carbon::createFromFormat('d/m/Y H:i', request('date_from') . '00:00');
            $to = Carbon::createFromFormat('d/m/Y H:i', request('date_to') . '00:00');
        }

        if (!request('categories') || (request('categories') && in_array('all', request('categories')))) {
            $categories = ['all']; //SiteMaintenanceCategory::all()->pluck('id')->toArray();
            $category_ids = SiteMaintenanceCategory::where('status', 1)->pluck('id')->toArray();
        } else {
            $categories = request('categories');
            $category_ids = request('categories');
        }

        //dd(request()->all());

        $mains = SiteMaintenance::whereIn('category_id', $category_ids)->whereDate('updated_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->where('status', '<>', 2)->get();
        $mains_old = SiteMaintenance::whereIn('category_id', $category_ids)->whereDate('updated_at', '<', $from->format('Y-m-d'))->whereIn('status', [1, 3])->get();
        $mains_created = SiteMaintenance::whereIn('category_id', $category_ids)->whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('updated_at', '<=', $to->format('Y-m-d'))->get();

        $count = $excluded = 0;
        $total_allocated = $total_completed = $total_contacted = $total_appoint = 0;
        $cats = [];
        $supers = [];

        foreach ([$mains, $mains_old] as $mains_collect) {
            foreach ($mains_collect as $main) {
                if ($main->created_at->gte(Carbon::createFromFormat('Y-m-d', '2021-05-01'))) {
                    $days = ($main->status == 1) ? $main->reported->diffInWeekDays($to) : $main->reported->diffInWeekDays($main->updated_at);
                    $total_completed = $total_completed + $days;

                    // Avg Assigned Days
                    if ($main->assigned_super_at) {
                        $assigned_at = Carbon::createFromFormat('d/m/Y H:i', $main->assigned_super_at->format('d/m/Y') . '00:00'); // Need to set assigned_at time to 00:00 so we don't add and extra 'half' day if reported at 9am but assigned at 10am next day
                        $assigned_days = $assigned_at->diffInWeekDays($main->reported);
                    } elseif ($main->status == 0 || $main->status == 3)
                        $assigned_days = $main->reported->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $assigned_days = $main->reported->diffInWeekDays($to);

                    //echo "id:$main->id s:$main->status c:$total_allocated d:$days " . $main->reported->format('d/m/y g:i') . ' - ' . ($main->assigned_at) ? $main->assigned_at->format('d/m/Y g:i') : '*' . "<br>";
                    $total_allocated = $total_allocated + $assigned_days;

                    // Avg Client Contacted Days
                    if ($main->client_contacted)
                        $total_contacted = $total_contacted + $main->client_contacted->diffInWeekDays($main->reported);
                    elseif ($main->status == 0 || $main->status == 3)
                        $total_contacted = $total_contacted + $main->reported->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $total_contacted = $total_contacted + $main->reported->diffInWeekDays($to);

                    // Avg Appointment to Completion Days
                    $appoint_from = ($main->client_appointment) ? $main->client_appointment : $main->reported;
                    if ($main->status == 0 || $main->status == 3)
                        $total_appoint = $total_appoint + $appoint_from->diffInWeekDays($main->updated_at);
                    elseif ($main->status == 1)
                        $total_appoint = $total_appoint + $appoint_from->diffInWeekDays($to);

                    $count ++;
                } else {
                    //echo "$main->id : ". $main->created_at->format('d/m/Y') . "<br>";
                    $excluded ++;
                }


                // Count Categories
                $name = ($main->category_id) ? SiteMaintenanceCategory::find($main->category_id)->name : 'N/A';
                if (!array_key_exists($name, $cats))
                    $cats[$name] = 1;
                else
                    $cats[$name] = $cats[$name] + 1;

                // Count Supers
                $name = ($main->super_id) ? User::find($main->super_id)->name : 'N/A';
                if (!array_key_exists($name, $supers)) {
                    $active = ($main->status == 1) ? 1 : 0;
                    $completed = ($main->status == 0) ? 1 : 0;
                    $onhold = ($main->status == 3) ? 1 : 0;
                    $supers[$name] = [$active, $completed, $onhold];
                } else {
                    $active = ($main->status == 1) ? $supers[$name][0] + 1 : $supers[$name][0];
                    $completed = ($main->status == 0) ? $supers[$name][1] + 1 : $supers[$name][1];
                    $onhold = ($main->status == 3) ? $supers[$name][2] + 1 : $supers[$name][2];
                    $supers[$name] = [$active, $completed, $onhold];
                }
            }
        }

        ksort($cats);
        ksort($supers);
        //dd($supers);

        $avg_completed = ($count) ? round($total_completed / $count) : 0;
        $avg_allocated = ($count) ? round($total_allocated / $count) : 0;
        $avg_contacted = ($count) ? round($total_contacted / $count) : 0;
        $avg_appoint = ($count) ? round($total_appoint / $count) : 0;

        //dd($mains->groupBy('site_id')->count());

        // Create PDF
        $file = public_path('filebank/tmp/maintenace-executive-cron.pdf');
        if (file_exists($file))
            unlink($file);

        $pdf = PDF::loadView('pdf/site/maintenance-executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'avg_completed', 'avg_allocated', 'avg_contacted', 'avg_appoint', 'cats', 'supers', 'excluded'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save($file);

        return view('manage/report/site/maintenance_executive', compact('mains', 'mains_old', 'mains_created', 'to', 'from', 'categories', 'avg_completed', 'avg_allocated', 'avg_contacted', 'avg_appoint', 'cats', 'supers', 'excluded'));

    }

    /****************************************************
     * Maintenance
     ***************************************************/

    public function inspectionReports()
    {
        $today = Carbon::now();
        $electrical = SiteInspectionElectrical::where('status', 1)->get();
        $plumbing = SiteInspectionPlumbing::where('status', 1)->get();

        return view('manage/report/site/inspection_electrical_plumbing_open', compact('electrical', 'plumbing'));

    }

    /****************************************************
     * Accounting
     ***************************************************/

    /*
     * Payroll Report
     */
    public function payroll()
    {
        $exclude_ids = [5, 11, 12, 15, 16, 16, 20, 22];  //generic companies
        $companies = Auth::user()->company->companies()->whereNotIn('id', $exclude_ids);

        // Current FinYear + Dropdown last 3 years
        $start_year = Carbon::createFromFormat('Y-m-d H:i:s', date('Y') . '-07-01 00:00:00');
        $end_year = Carbon::createFromFormat('Y-m-d H:i:s', date('Y') . '-06-30 23:59:59')->addYear();
        $fin_year = $start_year->format('Y') . '-' . $end_year->format('Y');
        $from = $start_year->format('M Y');
        $from_ymd = $start_year->format('Y-m-d');
        $to = $end_year->format('M Y');
        $to_ymd = $end_year->format('Y-m-d');

        $select_fin = [];
        for ($i = 0; $i < 4; $i ++)
            $select_fin[$start_year->subYear($i)->format('Y') . '-' . $end_year->subYear($i)->format('Y')] = $start_year->format('M Y') . ' - ' . $end_year->format('M Y');

        //dd($from_ymd.' - '.$to_ymd.' * '. $from. ' - '. $to);


        return view('manage/report/payroll', compact('companies', 'select_fin', 'fin_year', 'from', 'to', 'from_ymd', 'to_ymd'));
    }

    /*
     * Payroll Report Dates
     */
    public function payrollDates()
    {
        $exclude_ids = [5, 11, 12, 15, 16, 16, 20, 22];  //generic companies
        $companies = Auth::user()->company->companies()->whereNotIn('id', $exclude_ids);

        //dd(request()->all());
        $from = $to = $from_ymd = $to_ymd = '';
        $fin_year = request('fin_year');
        if ($fin_year) {
            list($year1, $year2) = explode('-', request('fin_year'));
            $from = Carbon::createFromFormat('Y-m-d H:i:s', $year1 . '-07-01 00:00:00')->format('M Y');
            $from_ymd = Carbon::createFromFormat('Y-m-d H:i:s', $year1 . '-07-01 00:00:00')->format('Y-m-d');
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $year2 . '-06-30 23:59:59')->format('M Y');
            $to_ymd = Carbon::createFromFormat('Y-m-d H:i:s', $year2 . '-06-30 23:59:59')->format('Y-m-d');
        }

        // Current FinYear + Dropdown last 3 years
        $start_year = Carbon::createFromFormat('Y-m-d H:i:s', date('Y') . '-07-01 00:00:00');
        $end_year = Carbon::createFromFormat('Y-m-d H:i:s', date('Y') . '-06-30 23:59:59')->addYear();

        $select_fin = [];
        for ($i = 0; $i < 4; $i ++)
            $select_fin[$start_year->subYear($i)->format('Y') . '-' . $end_year->subYear($i)->format('Y')] = $start_year->format('M Y') . ' - ' . $end_year->format('M Y');

        return view('manage/report/payroll', compact('companies', 'select_fin', 'fin_year', 'from', 'to', 'from_ymd', 'to_ymd'));
    }

    /*
     * public function getPayroll()
    {
        $company_ids = Auth::user()->company->companies()->pluck('id')->toArray();
        $exclude_ids = [5, 11, 12, 15, 16, 16, 20, 22];  //generic companies

        //var_dump($site_ids);
        //dd(request()->all());

        $compan = SiteAttendance::select([
            'site_attendance.site_id', 'site_attendance.user_id', 'site_attendance.date', 'sites.name',
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.company_id', 'companys.id', 'companys.name',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('sites', 'sites.id', '=', 'site_attendance.site_id')
            ->join('users', 'users.id', '=', 'site_attendance.user_id')
            ->join('companys', 'users.company_id', '=', 'companys.id')
            ->whereIn('site_attendance.site_id', $site_ids)
            ->whereIn('companys.id', $company_ids)
            ->whereIn('users.id', $user_ids)
            ->whereDate('site_attendance.date', '>=', $date_from)
            ->whereDate('site_attendance.date', '<=', $date_to);

        //dd($attendance_records);
        $dt = Datatables::of($attendance_records)
            ->editColumn('date', function ($attendance) {
                return $attendance->date->format('d/m/Y H:i a');
            })
            ->editColumn('sites.name', function ($attendance) {
                return '<a href="/site/' . $attendance->site->id . '">' . $attendance->site->name . '</a>';
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
    }*/


    /****************************************************
     * Website Admin
     ***************************************************/

    public function nightly()
    {
        $files = array_reverse(array_diff(scandir(public_path('/filebank/log/nightly')), array('.', '..')));

        return view('manage/report/nightly', compact('files'));
    }

    public function zoho()
    {
        $files = array_reverse(array_diff(scandir(public_path('/filebank/log/zoho')), array('.', '..')));

        return view('manage/report/zoho', compact('files'));
    }

    public function cronjobs()
    {
        //$nightlyjobs = ['Daily', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        //$reportjobs =  ['Daily', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Fortnightly', 'Monthly'];

        // Nightly
        $day = 'Daily';
        $nightlyjobs = [];
        $file = base_path() . "/app/Http/Controllers/Misc/CronController.php";
        if (($handle = fopen($file, "r")) !== false) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                // Finish reading Nightly function
                if (preg_match("/NIGHTLY COMPLETE/", $line)) break;

                // Update Day
                if (preg_match("/Carbon::today\(\)->is/", $line))
                    $day = substr($line, 23, '-5');

                if (preg_match("/^CronController::/", $line))
                    $cronjobs[$day][substr($line, 16, '-3')] = rtrim($line, ';');
            }
        }
        fclose($handle);

        // Reports
        $day = 'Daily';
        $reportjobs = [];
        $file = base_path() . "/app/Http/Controllers/Misc/CronReportController.php";
        if (($handle = fopen($file, "r")) !== false) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                // Finish reading Report function
                if (preg_match("/Monday Reports/", $line)) break;


                // Update Day
                if (preg_match("/start_monday/", $line)) {
                    $start_monday = Carbon::createFromFormat('Y-m-d', '2020-10-26');
                    $day = ($start_monday->diffInDays(Carbon::now()) % 2 == 0) ? 'Fortnightly Monday' : 'Fortnightly Monday (Off)';
                } elseif (preg_match("/first_tues/", $line)) {
                    $day = 'Monthly First Tuesday';
                } elseif (preg_match("/last_fri/", $line)) { // Carbon::today()->isSameDay($first_tues)
                    $day = 'Monthly Last Friday';
                } elseif (preg_match("/quarterly_months/", $line)) { // Carbon::today()->isSameDay($first_tues)
                    $day = 'Quarterly First of Month';
                } elseif (preg_match("/Carbon::today\(\)->is/", $line))
                    $day = substr($line, 23, '-5');

                if (preg_match("/^CronReportController::/", $line))
                    $reportjobs[$day][substr($line, 22, '-3')] = rtrim($line, ';');
            }
        }
        fclose($handle);
        //dd($reportjobs);

        return view('manage/report/cronjobs', compact('cronjobs', 'reportjobs'));
    }

    public function cronjobsExecute($action)
    {
        list($controller, $rest) = explode('::', $action);
        $method = substr($rest, 0, '-2');
        $string = '\App\Http\Controllers\Misc\\'.$controller; //."@$method";
        //dd($string);
        //echo "[$string]<br>";
        //\App::call($string, $method, ['index']);
        app()->call($string."@$method", [$method]);
        //dd(request()->all());
    }
}
