<?php

namespace App\Http\Controllers\Site\Planner;

use App\Http\Controllers\Controller;
use App\Jobs\CompanyAttendancePdf;
use App\Jobs\SiteAttendancePdf;
use App\Jobs\SitePlannerCompanyPdf;
use App\Jobs\SitePlannerPdf;
use App\Models\Company\Company;
use App\Models\Misc\Report;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Site;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;

/**
 * Class SitePlannerExportController
 * @package App\Http\Controllers
 */
class SitePlannerExportController extends Controller
{

    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.export'))
            return view('errors/404');

        return view('site/export/list');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // Required even if empty
    }

    /**
     * Display the specified resource.
     */
    public function exportPlanner()
    {
        $date = new Carbon('next Monday');
        $date = $date->format('d/m/Y');

        return view('site/export/plan', compact('date'));
    }

    /**
     * Display the specified resource.
     */
    public function exportStart()
    {
        return view('site/export/start');
    }

    /**
     * Display the specified resource.
     */
    public function exportCompletion()
    {
        return view('site/export/completion');
    }

    /**
     * Display the specified resource.
     */
    public function exportAttendance()
    {
        return view('site/export/attendance');
    }

    /*
     * Create Export Site PDF
     */
    static public function sitePDF()
    {
        $rules = ['date' => 'required', 'weeks' => 'required'];
        $mesg = [];
        request()->validate($rules, $mesg); // Validate

        //dd(request()->all());

        $date = Carbon::createFromFormat('d/m/Y H:i:s', request('date') . ' 00:00:00')->format('Y-m-d');
        $weeks = request('weeks');
        $returnFile = (request('outputPDF') == 'pdf') ? true : false;

        /*
         * Export by Site
         */
        if (request()->has('export_site') || request()->has('export_site_client') || request()->has('export_supervisor')) {
            //$site_id = (request()->has('export_site')) ? request('site_id') : request('site_id_client');

            if (request()->has('export_site')) {
                $site_id = request('site_id');
                $reportType = 'site';
            } else if (request()->has('export_site_client')) {
                $site_id = request('site_id_client');
                $reportType = 'client';
            } else if (request()->has('export_supervisor')) {
                $site_id = [];
                $reportType = 'supervisor';
            }


            if ($site_id)
                $sites = $site_id;
            else {
                if (request()->has('export_supervisor')) {
                    $superIDS = (request()->has('supervisor_id')) ? request('supervisor_id') : Company::find(3)->supervisors()->where('status', 1)->pluck('id')->toArray();
                    $superSites = Auth::user()->company->reportsTo()->sites('1')->wherein('supervisor_id', $superIDS)->pluck('id')->toArray();
                    $sites = [];
                    foreach ($superSites as $sid) {
                        $site = Site::find($sid);
                        if ($site && $site->JobStart && $site->JobStart->lt(Carbon::today()))
                            $sites[] = $sid;
                    }

                } else
                    $sites = Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();
            }

            // Create Site List & Sort Sites by Site Name
            $site_list = [];
            $site_list_csv = '';
            foreach ($sites as $siteID) {
                if ($siteID == 'all') {
                    $site_list = Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();
                    $site_list_csv = 'All';
                    break;
                }
                $site = Site::find($siteID);
                if ($site) {
                    $site_list[$siteID] = $site->name;
                    $site_list_csv .= $site->code . ", ";
                }

            }
            asort($site_list);
            if ($site_id)
                $site_list_csv = (count($sites) > 5) ? 'multiple 5+' : rtrim($site_list_csv, ', ');
            else if (request()->has('export_supervisor') && request('supervisor_id')) {
                $site_list_csv = '';
                $supers = User::find(request('supervisor_id'));
                foreach ($supers as $super)
                    $site_list_csv .= $super->initials . ", ";
                $site_list_csv = rtrim($site_list_csv, ', ');
            } else
                $site_list_csv = 'All';

            // For each Site get Tasks om Planner
            $data = [];
            foreach ($site_list as $siteID => $siteName) {
                $site = Site::findOrFail($siteID);
                $obj_site = (object)[];
                $obj_site->site_id = $site->id;
                $obj_site->site_name = $site->name;
                $obj_site->supervisor = ($site->supervisor_id) ? $site->supervisor->name : '';
                $obj_site->prac_complete = ($site->completion_signed) ? true : false;
                $obj_site->reportType = $reportType;
                $obj_site->weeks = [];

                // For each week get Entities on the Planner
                $current_date = $date;
                for ($w = 1; $w <= $weeks; $w++) {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                    if ($date_from->isWeekend()) $date_from->addDays(1);
                    if ($date_from->isWeekend()) $date_from->addDays(1);

                    // Calculate Date to ensuring not a weekend
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
                    $dates = [$date_from->format('Y-m-d')];
                    for ($i = 2; $i < 6; $i++) {
                        $date_to->addDays(1);
                        if ($date_to->isWeekend())
                            $date_to->addDays(2);
                        $dates[] = $date_to->format('Y-m-d');
                    }
                    //echo "From: " . $date_from->format('d/m/Y') . " To:" . $date_to->format('d/m/Y') . "<br>";

                    $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                        // Tasks that start 'from' between mon-fri of given week
                        ->where(function ($q) use ($date_from, $date_to, $site) {
                            $q->where('from', '>=', $date_from->format('Y-m-d'));
                            $q->Where('from', '<=', $date_to->format('Y-m-d'));
                            $q->where('site_id', $site->id);
                        })
                        // Tasks that end 'to between mon-fri of given week
                        ->orWhere(function ($q) use ($date_from, $date_to, $site) {
                            $q->where('to', '>=', $date_from->format('Y-m-d'));
                            $q->Where('to', '<=', $date_to->format('Y-m-d'));
                            $q->where('site_id', $site->id);
                        })
                        // Tasks that start before mon but end after fri
                        // ie they span the whole week but begin prior + end after given week
                        ->orWhere(function ($q) use ($date_from, $date_to, $site) {
                            $q->where('from', '<', $date_from->format('Y-m-d'));
                            $q->Where('to', '>', $date_to->format('Y-m-d'));
                            $q->where('site_id', $site->id);
                        })
                        ->orderBy('from')->get();

                    // Get Unique list of Entities for current week
                    $entities = [];
                    foreach ($planner as $plan) {
                        $key = $plan->entity_type . '.' . $plan->entity_id;
                        if (!isset($entities[$key])) {
                            // Task & Trade
                            $task = Task::find($plan->task_id);
                            $entity_task = ($task) ? $task->name : "Onsite";
                            $entity_trade = ($task) ? $task->trade->name : "-";

                            if ($plan->entity_type == 'c') {
                                $company = Company::find($plan->entity_id);
                                $entity_name = ($company) ? $company->name : "Company $plan->entity_id";
                            } else {
                                $trade = Trade::find($plan->entity_id);
                                $entity_name = ($trade) ? $trade->name : "Trade $plan->entity_id";
                            }
                            $entities[$key] = ['key' => $key, 'entity_type' => $plan->entity_type, 'entity_id' => $plan->entity_id, 'entity_name' => $entity_name, 'entity_task' => $entity_task, 'entity_trade' => $entity_trade,];
                            for ($i = 0; $i < 5; $i++)
                                $entities[$key][$dates[$i]] = '';
                        }
                    };
                    usort($entities, 'sortEntityName');
                    //dd($entities);

                    // Create Header Row for Current Week
                    $obj_site->weeks[$w] = [];
                    $i = 1;
                    $offset = 1; // Used to set column 0/1 for client export
                    if (request()->has('export_site') || request()->has('export_supervisor'))
                        $obj_site->weeks[$w][0][] = 'COMPANY';
                    else
                        $obj_site->weeks[$w][0][] = 'TRADE';

                    $offset = 0;
                    foreach ($dates as $d)
                        $obj_site->weeks[$w][0][$i++] = strtoupper(Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->format('l d/m'));

                    // For each Entity on for current week get their Tasks for each day of the week
                    $entity_count = 1;
                    if ($entities) {
                        foreach ($entities as $e) {
                            if (request()->has('export_site') || request()->has('export_supervisor'))
                                $obj_site->weeks[$w][$entity_count][] = $e['entity_name'];
                            else
                                $obj_site->weeks[$w][$entity_count][] = $e['entity_trade'];
                            for ($i = 1; $i <= 5; $i++) {
                                $tasks = $site->entityTasksOnDate($e['entity_type'], $e['entity_id'], $dates[$i - 1]);
                                if ($tasks) {
                                    $str = '';
                                    foreach ($tasks as $task_id => $task_name)
                                        $str .= $task_name . '<br>';
                                } else
                                    $str = '&nbsp;';

                                $obj_site->weeks[$w][$entity_count][$i] = $str;
                            }
                            $entity_count++;
                        }
                    } else {
                        $obj_site->weeks[$w][1][$offset] = 'NOTHING-ON-PLAN';
                        $obj_site->weeks[$w][1][$offset + 1] = '';
                    }

                    $date_next = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00')->addDays(7);;
                    $current_date = $date_next->format('Y-m-d');
                }
                $data[] = $obj_site;
            }
            if (request()->has('export_supervisor')) {
                array_multisort(array_column($data, 'supervisor'), SORT_ASC, array_column($data, 'prac_complete'), SORT_ASC, $data);
                //array_multisort($sort['order'], SORT_ASC, $sort['name'], SORT_ASC, $site_details);
            } else
                array_multisort(array_column($data, 'site_name'), SORT_ASC, $data);
            //dd($data);

            $view = 'pdf.plan-site';
            $client = '';
            if (request()->has('export_site_client')) {
                $view = 'pdf/plan-site-client';
                $client = 'Client ';
            }

            if (request()->has('export_supervisor')) {
                $client = 'Supervisor ';
            }

            // Create PDF
            $name = "{$client}Site Plan ($site_list_csv).pdf";
            $path = "report/" . Auth::user()->company_id;
            $report = Report::create(['user_id' => Auth::id(), 'company_id' => Auth::user()->company_id, 'name' => $name, 'path' => $path, 'type' => 'site-plan', 'status' => 'pending',]);
            SitePlannerPdf::dispatch($report->id, $data, $view);

            // Return just the output PDF filename for Batch reporting
            if ($returnFile) {
                return $output_file;
            }

            return redirect('/manage/report/recent');
        }


        /*
         * Export by Company
         */
        if (request()->has('export_company')) {
            $company_id = request('company_id');
            if ($company_id)
                $companies = $company_id;
            else
                $companies = Auth::user()->company->companies('1')->pluck('id')->toArray();

            // Create Company List & Sort by Name
            $company_list = [];
            $company_list_csv = '';
            foreach ($companies as $cid) {
                if ($cid == 'all') {
                    $company_list = Auth::user()->company->companies('1')->pluck('id')->toArray();
                    $company_list_csv = 'All';
                    break;
                }
                $company = Company::find($cid);
                if ($company) {
                    $company_list[$cid] = $company->name;
                    $company_list_csv .= $company->id . ", ";
                }
            }
            asort($company_list);

            if ($company_id)
                $company_list_csv = (count($companies) > 5) ? 'multiple 5+' : rtrim($company_list_csv, ', ');
            else
                $company_list_csv = 'All';


            //dd($company_list);
            // For each Company get Tasks om Planner
            $data = [];
            foreach ($company_list as $cid => $cname) {
                $company = Company::find($cid);
                $obj_site = (object)[];
                $obj_site->company_id = $company->id;
                $obj_site->company_name = $company->name_alias;
                $obj_site->weeks = [];
                $obj_site->upcoming = [];

                // For each week get Sites on the Planner
                $current_date = $date;
                for ($w = 1; $w <= $weeks; $w++) {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                    if ($date_from->isWeekend()) $date_from->addDays(1);
                    if ($date_from->isWeekend()) $date_from->addDays(1);

                    // Calculate Date To ensuring not a weekend
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
                    $dates = [$date_from->format('Y-m-d')];
                    for ($i = 2; $i < 6; $i++) {
                        $date_to->addDays(1);
                        if ($date_to->isWeekend())
                            $date_to->addDays(2);
                        $dates[] = $date_to->format('Y-m-d');
                    }
                    //echo "From: " . $date_from->format('d/m/Y') . " To:" . $date_to->format('d/m/Y') . "<br>";

                    $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                        // Tasks that start 'from' between mon-fri of given week
                        ->where(function ($q) use ($date_from, $date_to, $company) {
                            $q->where('from', '>=', $date_from->format('Y-m-d'));
                            $q->Where('from', '<=', $date_to->format('Y-m-d'));
                            $q->where('entity_type', 'c');
                            $q->where('entity_id', $company->id);
                        })
                        // Tasks that end 'to between mon-fri of given week
                        ->orWhere(function ($q) use ($date_from, $date_to, $company) {
                            $q->where('to', '>=', $date_from->format('Y-m-d'));
                            $q->Where('to', '<=', $date_to->format('Y-m-d'));
                            $q->where('entity_type', 'c');
                            $q->where('entity_id', $company->id);
                        })
                        // Tasks that start before mon but end after fri
                        // ie they span the whole week but begin prior + end after given week
                        ->orWhere(function ($q) use ($date_from, $date_to, $company) {
                            $q->where('from', '<', $date_from->format('Y-m-d'));
                            $q->Where('to', '>', $date_to->format('Y-m-d'));
                            $q->where('entity_type', 'c');
                            $q->where('entity_id', $company->id);
                        })
                        ->orderBy('from')->get();

                    // Get Unique list of Sites for current week
                    $sites = [];
                    foreach ($planner as $plan) {
                        if (!isset($sites[$plan->site_id])) {
                            $site = Site::find($plan->site_id);
                            $sites[$plan->site_id] = ['site_id' => $plan->site_id, 'site_name' => $site->name, 'site_supervisor' => $site->supervisorInitials];
                            for ($i = 0; $i < 5; $i++)
                                $sites[$plan->site_id][$dates[$i]] = '';
                        }
                    };
                    usort($sites, 'sortSiteName');

                    // Create Header Row for Current Week
                    $obj_site->weeks[$w] = [];
                    $obj_site->weeks[$w][0][] = 'SITE';
                    foreach ($dates as $d)
                        $obj_site->weeks[$w][0][] = strtoupper(Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->format('l d/m'));

                    // For each Site on for current week get the Company Tasks for each day of the week
                    $site_count = 1;
                    if ($sites) {
                        foreach ($sites as $s) {
                            $obj_site->weeks[$w][$site_count][] = $s['site_name'] . ' (' . $s['site_supervisor'] . ')';
                            for ($i = 1; $i <= 5; $i++) {
                                $site = Site::find($s['site_id']);
                                $tasks = $site->entityTasksOnDate('c', $company->id, $dates[$i - 1]);
                                if ($tasks) {
                                    $str = '';
                                    foreach ($tasks as $task_id => $task_name)
                                        $str .= $task_name . '<br>';
                                } else
                                    $str = '&nbsp;';

                                $obj_site->weeks[$w][$site_count][$i] = $str;
                            }
                            $site_count++;
                        }
                    } else {
                        $obj_site->weeks[$w][1][] = 'NOTHING-ON-PLAN';
                        $obj_site->weeks[$w][1][1] = '';
                    }

                    $date_next = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00')->addDays(7);;
                    $current_date = $date_next->format('Y-m-d');
                }

                /*
                 * Upcoming
                 */
                //for ($w = 1; $w <= 2; $w++) {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                $date_to->addDays(14);
                //echo "From: " . $date_from->format('d/m/Y') . " To:" . $date_to->format('d/m/Y') . "<br>";
                $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                    // Tasks that start 'from' between mon-fri of given week
                    ->where(function ($q) use ($date_from, $date_to, $company) {
                        $q->where('from', '>=', $date_from->format('Y-m-d'));
                        $q->Where('from', '<=', $date_to->format('Y-m-d'));
                        $q->where('entity_type', 'c');
                        $q->where('entity_id', $company->id);
                    })
                    // Tasks that end 'to between mon-fri of given week
                    ->orWhere(function ($q) use ($date_from, $date_to, $company) {
                        $q->where('to', '>=', $date_from->format('Y-m-d'));
                        $q->Where('to', '<=', $date_to->format('Y-m-d'));
                        $q->where('entity_type', 'c');
                        $q->where('entity_id', $company->id);
                    })
                    // Tasks that start before mon but end after fri
                    // ie they span the whole week but begin prior + end after given week
                    ->orWhere(function ($q) use ($date_from, $date_to, $company) {
                        $q->where('from', '<', $date_from->format('Y-m-d'));
                        $q->Where('to', '>', $date_to->format('Y-m-d'));
                        $q->where('entity_type', 'c');
                        $q->where('entity_id', $company->id);
                    })
                    ->orderBy('from')->get();

                //dd($planner);
                $sites = [];
                foreach ($planner as $plan) {
                    if (!in_array($plan->site_id, $sites))
                        $sites[] = $plan->site_id;
                }


                $current_date = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                if ($sites) {
                    for ($x = 1; $x <= 14; $x++) {
                        // Skip Weekends
                        if ($current_date->isWeekend()) $current_date->addDays(1);
                        if ($current_date->isWeekend()) $current_date->addDays(1);

                        foreach ($sites as $s) {
                            $site = Site::find($s);
                            $tasks = $site->entityTasksOnDate('c', $company->id, $current_date->format('Y-m-d'));
                            if ($tasks) {
                                $task_list = '';
                                foreach ($tasks as $task_id => $task_name)
                                    $task_list .= $task_name . ', ';
                                $task_list = rtrim($task_list, ', ');
                                $obj_site->upcoming[] = ['date' => $current_date->format('M j'), 'site' => $site->name, 'tasks' => $task_list];
                            }
                        }
                        $current_date->addDay(1);
                    }
                }

                //}
                $data[] = $obj_site;
            }

            // Create PDF
            $name = "Company Site Plan ($company_list_csv).pdf";
            $path = "report/" . Auth::user()->company_id;
            $report = Report::create(['user_id' => Auth::id(), 'company_id' => Auth::user()->company_id, 'name' => $name, 'path' => $path, 'type' => 'company-plan', 'status' => 'pending',]);
            SitePlannerCompanyPdf::dispatch($report->id, $data);

            return redirect('/manage/report/recent');
        }
    }


    /*
     * Create Export Site Attendance PDF
     */
    public function attendancePDF(Request $request)
    {
        //$siteID = $request->get('site_id');

        $site_id = '';
        $company = (request('company_id') != 'all') ? Company::find(request('company_id')) : null;
        if (request('status') == '1' && request('site_id_active') != 'all')
            $site_id = request('site_id_active');
        elseif (request('status') == '0' && request('site_id_completed') != 'all')
            $site_id = request('site_id_completed');
        elseif (request('site_id_all') != 'all')
            $site_id = request('site_id_all');

        $from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00') : null;
        $to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00') : null;;

        //dd($site_id);
        //dd(request()->all());
        //
        // Export Single Site
        //
        if ($site_id) {
            $site = Site::findOrFail($site_id);
            $obj_site = (object)[];
            $obj_site->site_id = $site->id;
            $obj_site->site_name = $site->name;
            $obj_site->attendance = [];

            $attendance = ($from) ? SiteAttendance::where('site_id', $site_id)->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->orderBy('date')->get() : SiteAttendance::where('site_id', $site_id)->orderBy('date')->get();

            foreach ($attendance as $attend) {
                $date = $attend->date->format('D M d, Y');
                $user = User::find($attend->user_id);
                if (!$company || ($company && $user && $user->company_id == $company->id))
                    $obj_site->attendance[$date][$user->company->name_alias][$user->id] = $user->full_name;
            }

            $data[] = $obj_site;
            //dd($sitedata);

            // Create PDF
            $name = 'Site Attendance ' . sanitizeFilename($site->name) . '.pdf';
            $path = "report/" . Auth::user()->company_id;
            $report = Report::create(['user_id' => Auth::id(), 'company_id' => Auth::user()->company_id, 'name' => $name, 'path' => $path, 'type' => 'site-attendance', 'status' => 'pending',]);
            SiteAttendancePdf::dispatch($report->id, $data, $site_id, $company, $from, $to);
        } else {
            //dd('company rep');
            $user_ids = $company->staff->pluck('id')->toArray();
            $attendance = ($from) ? SiteAttendance::whereIn('user_id', $user_ids)->whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->orderBy('date')->get() : SiteAttendance::where('site_id', $site_id)->orderBy('date')->get();

            $data = [];

            //dd($attendance);
            foreach ($attendance as $attend) {
                $date = $attend->date->format('D M d, Y');
                $user = $attend->user;
                if (isset($data[$date]))
                    $data[$date][$attend->site->name][$user->id] = $user->full_name;
                else
                    $data[$date][$attend->site->name][$user->id] = $user->full_name;

            }

            if ($company) {
                $name = 'Company Attendance ' . sanitizeFilename($company->name) . '.pdf';
                $path = "report/" . Auth::user()->company_id;
                $report = Report::create(['user_id' => Auth::id(), 'company_id' => Auth::user()->company_id, 'name' => $name, 'path' => $path, 'type' => 'company-attendance', 'status' => 'pending',]);
                CompanyAttendancePdf::dispatch($report->id, $data, $company, $from, $to);
            }
        }

        return redirect('/manage/report/recent');
    }

    /**
     * Create Job Start PDF
     */
    public function jobstartPDF(Request $request)
    {
        //
        //  This is a copy of the cron job each thursday
        //
        $today = Carbon::now()->format('Y-m-d');
        $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.code', 'START')
            ->orderBy('p.from')->get();

        //dd($planner);
        $startdata = [];
        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            if ($site->status == 1) {
                $entity_name = "Carpenter";
                if ($plan->entity_type == 'c')
                    $entity_name = Company::find($plan->entity_id)->name;
                $startdata[] = [
                    'date' => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                    'code' => $site->code,
                    'name' => $site->name,
                    'company' => $entity_name,
                    'supervisor' => $site->supervisorName,
                    'contract_sent' => ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-',
                    'contract_signed' => ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-',
                    'deposit_paid' => ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-',
                    'eng' => ($site->engineering) ? 'Y' : '-',
                    'cc' => ($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : '-',
                    'hbcf' => ($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : '-',
                    'consultant' => $site->consultant_name,
                ];
            }
        }

        /*
         $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.id', 51)
            ->orderBy('p.from')->get();

        $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.id', 86)
            ->orderBy('p.from')->get();
        */

        //return view('pdf/plan-jobstart', compact('startdata'));
        $pdf = PDF::loadView('pdf/plan-jobstart', compact('startdata'));
        $pdf->setPaper('A4', 'landscape');
        //->setOption('page-width', 200)->setOption('page-height', 287)
        //->setOption('margin-bottom', 10)
        //->setOrientation('landscape');

        if ($request->has('view_pdf'))
            return $pdf->stream();

        if ($request->has('email_pdf')) {
            $file = storage_path('app/tmp/jobstart-' . Auth::user()->id . '.pdf');
            $pdf->save($file);

            if ($request->get('email_list')) {
                //$email_list = explode(';', $request->get('email_list'));
                //$email_list = array_map('trim', $email_list); // trim white spaces
                $email_to = [];
                foreach ($request->get('email_list') as $user_id) {
                    $user = User::findOrFail($user_id);
                    if ($user && validEmail($user->email)) {
                        $email_to[] .= $user->email;
                    }
                }
                //dd($email_to);

                $data = [
                    'user_fullname' => Auth::user()->fullname,
                    'user_company_name' => Auth::user()->company->name,
                    'startdata' => $startdata
                ];
                if ($email_to) {
                    Mail::send('emails/jobstart', $data, function ($m) use ($email_to, $data, $file) {
                        $user_email = Auth::user()->email;
                        ($user_email) ? $send_from = $user_email : $send_from = 'do-not-reply@safeworksite.com.au';

                        $m->from($send_from, Auth::user()->fullname);
                        $m->to($email_to);
                        $m->subject('Upcoming Job Start Dates');
                        $m->attach($file);
                    });

                    // Comment out code as Mail::failures no longer works in laravel 9
                    //if (count(Mail::failures()) > 0) {
                    //    foreach (Mail::failures as $email_address)
                    //        Toastr::error("Failed to send to $email_address");
                    //} else
                    Toastr::success("Sent email");
                }

                return view('site/export/start');
            }
        }
    }

    /**
     * Create Prac Completion PDF
     */
    public function completionPDF(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $planner = DB::table('site_planner AS p')
            ->select(['p.id', 'p.site_id', 'p.entity_type', 'p.entity_id', 'p.task_id', 'p.from', 't.code'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->whereDate('p.from', '>=', $today)
            ->where('t.id', '265')// ie 265 = Prac Completion
            ->orderBy('p.from')->get();

        //dd($planner);
        $startdata = [];
        foreach ($planner as $plan) {
            $site = Site::findOrFail($plan->site_id);
            $entity_name = "Carpenter";
            if ($plan->entity_type == 'c')
                $entity_name = Company::find($plan->entity_id)->name;
            $startdata[] = [
                'date' => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                'code' => $site->code,
                'name' => $site->name,
                'company' => $entity_name,
                'supervisor' => $site->supervisorName,
                'completion_signed' => ($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : '-',
            ];
        }

        //return view('pdf/plan-completion', compact('startdata'));
        $pdf = PDF::loadView('pdf/plan-completion', compact('startdata'));
        $pdf->setPaper('A4', 'landscape');
        //->setOption('page-width', 200)->setOption('page-height', 287)
        //->setOption('margin-bottom', 10)
        //->setOrientation('landscape');

        if ($request->has('view_pdf'))
            return $pdf->stream();

        if ($request->has('email_pdf')) {
            if ($request->get('email_list')) {
                $email_list = explode(';', $request->get('email_list'));
                $email_list = array_map('trim', $email_list); // trim white spaces

                $data = [
                    'user_fullname' => Auth::user()->fullname,
                    'user_company_name' => Auth::user()->company->name,
                    'startdata' => $startdata
                ];
                if ($email_list) {
                    Mail::send('emails/site-plan-completion', $data, function ($m) use ($email_list, $data) {
                        $user_email = Auth::user()->email;
                        ($user_email) ? $send_from = $user_email : $send_from = 'do-not-reply@safeworksite.com.au';

                        $m->from($send_from, Auth::user()->fullname);
                        $m->to($email_list);
                        $m->subject('Practical Completion List');
                    });
                    // Comment out code as Mail::failures no longer works in laravel 9
                    //if (count(Mail::failures()) > 0) {
                    //    foreach (Mail::failures as $email_address)
                    //        Toastr::error("Failed to send to $email_address");
                    //} else
                    Toastr::success("Sent email");
                }

                return view('site/export/completion');
            }
        }
    }

}
