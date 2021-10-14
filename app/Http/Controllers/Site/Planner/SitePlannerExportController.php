<?php

namespace App\Http\Controllers\Site\Planner;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\Models\Site\Site;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Http\Requests\Site\Planner\SitePlannerExportRequest;
use App\Jobs\SitePlannerPdf;
use App\Jobs\SitePlannerCompanyPdf;
use App\Jobs\SiteAttendancePdf;
use App\Jobs\CompanyAttendancePdf;
use App\User;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Company\Company;
use App\Models\Company\CompanyLeave;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SitePlannerExportController
 * @package App\Http\Controllers
 */
class SitePlannerExportController extends Controller {

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
    public function sitePDF(SitePlannerExportRequest $request)
    {
        $date = Carbon::createFromFormat('d/m/Y H:i:s', request('date') . ' 00:00:00')->format('Y-m-d');
        $weeks = $request->get('weeks');

        /*
         * Export by Site
         */
        if ($request->has('export_site') || $request->has('export_site_client')) {
            $site_id = ($request->has('export_site')) ? request('site_id') : request('site_id_client');
            if ($site_id)
                $sites = $site_id;
            else
                $sites = Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();

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
                    $site_list_csv .= $site->code.", ";
                }

            }
            asort($site_list);
            if ($site_id)
                $site_list_csv = (count($sites) > 5) ? 'multiple 5+' : rtrim($site_list_csv, ', ');
            else
                $site_list_csv = 'All';

            // For each Site get Tasks om Planner
            $data = [];
            foreach ($site_list as $siteID => $siteName) {
                $site = Site::findOrFail($siteID);
                $obj_site = (object) [];
                $obj_site->site_id = $site->id;
                $obj_site->site_name = $site->name;
                $obj_site->weeks = [];

                // For each week get Entities on the Planner
                $current_date = $date;
                for ($w = 1; $w <= $weeks; $w ++) {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                    if ($date_from->isWeekend()) $date_from->addDays(1);
                    if ($date_from->isWeekend()) $date_from->addDays(1);

                    // Calculate Date to ensuring not a weekend
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
                    $dates = [$date_from->format('Y-m-d')];
                    for ($i = 2; $i < 6; $i ++) {
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
                            //$entity_name = ($plan->entity_type == 'c') ? Company::find($plan->entity_id)->name : Trade::find($plan->entity_id)->name;
                            if ($plan->entity_type == 'c') {
                                $company = Company::find($plan->entity_id);
                                $entity_name = ($company) ? $company->name : "Company $plan->entity_id";
                            } else {
                                $trade = Trade::find($plan->entity_id);
                                $entity_name = ($trade) ? $trade->name : "Trade $plan->entity_id";
                            }
                            $entities[$key] = ['key' => $key, 'entity_type' => $plan->entity_type, 'entity_id' => $plan->entity_id, 'entity_name' => $entity_name,];
                            for ($i = 0; $i < 5; $i ++)
                                $entities[$key][$dates[$i]] = '';
                        }
                    };
                    usort($entities, 'sortEntityName');

                    // Create Header Row for Current Week
                    $obj_site->weeks[$w] = [];
                    $i = 1;
                    $offset = 1; // Used to set column 0/1 for client export
                    if ($request->has('export_site')) {
                        $obj_site->weeks[$w][0][] = 'COMPANY';
                        $offset = 0;
                    }
                    foreach ($dates as $d)
                        $obj_site->weeks[$w][0][$i ++] = strtoupper(Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->format('l d/m'));

                    // For each Entity on for current week get their Tasks for each day of the week
                    $entity_count = 1;
                    if ($entities) {
                        foreach ($entities as $e) {
                            if ($request->has('export_site'))
                                $obj_site->weeks[$w][$entity_count][] = $e['entity_name'];
                            for ($i = 1; $i <= 5; $i ++) {
                                if ($request->has('export_site'))
                                    $tasks = $site->entityTasksOnDate($e['entity_type'], $e['entity_id'], $dates[$i - 1]);
                                else
                                    $tasks = $site->entityTradesOnDate($e['entity_type'], $e['entity_id'], $dates[$i - 1]);
                                if ($tasks) {
                                    $str = '';
                                    foreach ($tasks as $task_id => $task_name)
                                        $str .= $task_name . '<br>';
                                } else
                                    $str = '&nbsp;';

                                $obj_site->weeks[$w][$entity_count][$i] = $str;
                            }
                            $entity_count ++;
                        }
                    } else {
                        $obj_site->weeks[$w][1][$offset] = 'NOTHING-ON-PLAN';
                        $obj_site->weeks[$w][1][$offset + 1] = '';
                    }

                    $date_next = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00')->addDays(7);;
                    $current_date = $date_next->format('Y-m-d');
                }
                $data[] = $obj_site;
                //dd($sitedata);
            }

            $view = 'pdf.plan-site';
            $client = '';
            if ($request->has('export_site_client')) {
                $view = 'pdf/plan-site-client';
                $client = 'Client ';
            }


            $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $output_file = public_path($dir . "/{$client}Site Plan ($site_list_csv) " . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);

            //return view($view, compact('data'));
            //$pdf = PDF::loadView($view, compact('data'));
            //$pdf->setPaper('a4', 'landscape');//->setOrientation('landscape');
            //return $pdf->stream();
            SitePlannerPdf::dispatch($view, $data, $output_file);

            return redirect('/manage/report/recent');
        }


        /*
         * Export by Company
         */
        if ($request->has('export_company')) {
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
                    $company_list_csv .= $company->id.", ";
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
                $obj_site = (object) [];
                $obj_site->company_id = $company->id;
                $obj_site->company_name = $company->name_alias;
                $obj_site->weeks = [];
                $obj_site->upcoming = [];

                // For each week get Sites on the Planner
                $current_date = $date;
                for ($w = 1; $w <= $weeks; $w ++) {
                    $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
                    if ($date_from->isWeekend()) $date_from->addDays(1);
                    if ($date_from->isWeekend()) $date_from->addDays(1);

                    // Calculate Date To ensuring not a weekend
                    $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
                    $dates = [$date_from->format('Y-m-d')];
                    for ($i = 2; $i < 6; $i ++) {
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
                            $sites[$plan->site_id] = ['site_id' => $plan->site_id, 'site_name' => $site->name, 'site_supervisor' => $site->supervisorsFirstNameSBC()];
                            for ($i = 0; $i < 5; $i ++)
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
                            for ($i = 1; $i <= 5; $i ++) {
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
                            $site_count ++;
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
                    for ($x = 1; $x <= 14; $x ++) {
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


            //dd($data);
            $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
            // Create directory if required
            if (!is_dir(public_path($dir)))
                mkdir(public_path($dir), 0777, true);
            $output_file = public_path($dir . "/Company Site Plan ($company_list_csv) " . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);
            SitePlannerCompanyPdf::dispatch($data, $output_file);

            return redirect('/manage/report/recent');




            //return view('pdf/plan-company', compact('company_id', 'date', 'weeks', 'sitedata'));
            //$pdf = PDF::loadView('pdf/plan-company', compact('company_id', 'date', 'weeks', 'data'));
            //->setOption('page-width', 200)->setOption('page-height', 287)
            //->setOption('margin-bottom', 10)
            //->setOrientation('landscape');

            //$file = public_path('filebank/company/' . $doc->for_company_id . '/wms/' . $doc->name . ' v' . $doc->version . ' ref-' . $doc->id . ' ' . '.pdf');
            //if (file_exists($file))
            //    unlink($file);
            //$pdf->save($file);
            return $pdf->stream();
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

        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);

        //dd($site_id);
        //dd(request()->all());
        //
        // Export Single Site
        //
        if ($site_id) {
            $site = Site::findOrFail($site_id);
            $obj_site = (object) [];
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

            $output_file = public_path($dir . '/Site Attendance ' . sanitizeFilename($site->name) . ' ' . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);

            //return view('pdf/site-attendance', compact('site', 'data', 'company', 'from', 'to'));
            //return PDF::loadView('pdf/site-attendance', compact('site', 'data', 'company', 'from', 'to'))->setPaper('a4', 'landscape')->stream();
            SiteAttendancePdf::dispatch($data, $site_id, $company, $from, $to, $output_file);
        } else {
            //dd('company rep');
            $user_ids = $company->staff->pluck('id')->toArray();
            $attendance = SiteAttendance::whereIn('user_id', $user_ids)->orderBy('date')->get();
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

            //$sitedata[] = $obj_site;
            //dd($company_attendance);

            $output_file = public_path($dir . '/Company Attendance ' . sanitizeFilename($company->name) . ' ' . Carbon::now()->format('YmdHis') . '.pdf');
            touch($output_file);

            //return view('pdf/company-attendance', compact('data', 'company', 'from', 'to'));
            //return PDF::loadView('pdf/company-attendance', compact('data', 'company', 'from', 'to'))->setPaper('a4', 'landscape')->stream();
            CompanyAttendancePdf::dispatch($data, $company, $from, $to, $output_file);
        }

        return redirect('/manage/report/recent');
    }

    /*
     * Create Export Site Attendance PDF
     */
    /*
    public function attendance2PDF(Request $request, $site_id)
    {
        $siteID = $request->get('site_id');

        $site = Site::findOrFail($siteID);
        $obj_site = (object) [];
        $obj_site->site_id = $site->id;
        $obj_site->site_name = $site->name;
        $obj_site->weeks = [];

        // First Attendance
        $first_date = SiteAttendance::where('site_id', $siteID)->orderBy('date')->first();
        $last_date = SiteAttendance::where('site_id', $siteID)->orderBy('date', 'DESC')->first();

        //$date = Carbon::createFromFormat('d/m/Y H:i:s', $request->get('date') . ' 00:00:00')->format('Y-m-d');
        if ($first_date) {
            $current_date = $first_date->date->startOfWeek()->format('Y-m-d');
            $date = $first_date->date->startOfWeek()->format('Y-m-d');
            //echo "date: " . $first_date->date->format('Y-m-d') . '<br>';
            //echo "mon: " . $current_date . '<br>';
            //echo 'last: ' . $last_date->date->format('Y-m-d') . '<br>';
            //echo $last_date->date->diffInWeeks($first_date->date) . '<br>--<br>';

            $weeks = $last_date->date->diffInWeeks($first_date->date) + 1;
            $date1 = Carbon::createFromFormat('d/m/Y H:i:s', '25/05/2017 00:00:00');
            $date2 = Carbon::createFromFormat('d/m/Y H:i:s', '29/05/2017 00:00:00');
            //echo 'd1 ' . $date1->format('Y-m-d') . '<br>';
            //echo 'm1 ' . $date1->startOfWeek()->format('Y-m-d') . '<br>';
            //echo 'd2 ' . $date2->format('Y-m-d') . '<br>';
            //echo $date2->diffInWeeks($date1->startOfWeek()) + 1 . '<br>';
        } else
            $weeks = 0;


        //dd($current_date);
        // For each week get Entities on the Planner
        for ($w = 1; $w <= $weeks; $w ++) {
            $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
            if ($date_from->isWeekend()) $date_from->addDays(1);
            if ($date_from->isWeekend()) $date_from->addDays(1);

            // Calculate Date to ensuring not a weekend
            $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
            $dates = [$date_from->format('Y-m-d')];
            for ($i = 1; $i < 7; $i ++) {
                $date_to->addDays(1);
                $dates[] = $date_to->format('Y-m-d');
            }

            // Create Header Row for Current Week
            $obj_site->weeks[$w] = [];
            $i = 1;
            $offset = 1; // Used to set column 0/1 for client export
            foreach ($dates as $d) {
                $obj_site->weeks[$w][0][$i] = strtoupper(Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->format('D d/m'));

                $attendance = SiteAttendance::select(['id', 'site_id', 'user_id', 'date'])->where('site_id', $siteID)->whereDate('date', '=', $d)->get();
                $companies_onsite = [];
                if ($attendance->count()) {
                    foreach ($attendance as $attend) {
                        $user = User::find($attend->user_id);
                        $companies_onsite[$user->company->name_alias][$user->id] = $user->full_name;
                    }
                    $string = '';
                    foreach ($companies_onsite as $company_name => $users) {
                        $string = $company_name . ' (' . count($users) . ')<br>';
                    }
                    //$obj_site->weeks[$w][1][$i++] = $companies_onsite;
                    $obj_site->weeks[$w][1][$i ++] = $string;
                } else {
                    $obj_site->weeks[$w][1][$i ++] = '&nbsp;';
                }
            }

            $date_next = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00')->addDays(7);;
            $current_date = $date_next->format('Y-m-d');
        }
        $sitedata[] = $obj_site;
        //dd($sitedata);

        $filename = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('pdf_attendance_header_', true) . '.html';
        $filename = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.html';
        //dd($filename);

        $header_html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Attendance</title>
    <link href="' . asset('/') . '/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="' . asset('/') . '/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);
        body, h1, h2, h3, h4, h5, h6 {font-family: \'PT Sans\', serif;}
        h1 {font-weight: 700;}
        body {font-size: 10px;}
    </style>
</head><body><br><br><br>br><br><h6 class="pull-right"><b>Supervisor:</b> ' . $site->supervisorsSBC() . '</h6><h3 style="margin-top: 10px">' . $site->name . ' <small>site: ' . $site->code . '</small></h3>' . $site->address . ', ' . $site->suburb_state_postcode . '</div><hr style="margin: 5px 0px"></body></html>';
        file_put_contents($filename, $header_html);
        //return view('pdf/site-attendance', compact('site', 'date', 'weeks', 'sitedata'));

        $pdf = PDF::loadView('pdf/site-attendance', compact('site', 'weeks', 'sitedata'))
            ->setOption('page-width', 200)->setOption('page-height', 287)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-top', 10)
            //->setOption('header-html', $filename)
            ->setOrientation('landscape');

        return $pdf->stream();
    }*/


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
                    'date'            => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                    'code'            => $site->code,
                    'name'            => $site->name,
                    'company'         => $entity_name,
                    'supervisor'      => $site->supervisorsSBC(),
                    'contract_sent'   => ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '-',
                    'contract_signed' => ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '-',
                    'deposit_paid'    => ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '-',
                    'eng'             => ($site->engineering) ? 'Y' : '-',
                    'cc'              => ($site->construction) ? 'Y' : '-',
                    'hbcf'            => ($site->hbcf) ? 'Y' : '-',
                    'consultant'      => $site->consultant_name,
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
            $file = public_path('filebank/tmp/jobstart-' . Auth::user()->id  . '.pdf');
            if (file_exists($file))
                unlink($file);
            $pdf->save($file);

            if ($request->get('email_list')) {
                $email_list = explode(';', $request->get('email_list'));
                $email_list = array_map('trim', $email_list); // trim white spaces

                $data = [
                    'user_fullname'     => Auth::user()->fullname,
                    'user_company_name' => Auth::user()->company->name,
                    'startdata'         => $startdata
                ];
                Mail::send('emails/jobstart', $data, function ($m) use ($email_list, $data, $file) {
                    $user_email = Auth::user()->email;
                    ($user_email) ? $send_from = $user_email : $send_from = 'do-not-reply@safeworksite.com.au';

                    $m->from($send_from, Auth::user()->fullname);
                    $m->to($email_list);
                    $m->subject('Upcoming Job Start Dates');
                    $m->attach($file);
                });
                if (count(Mail::failures()) > 0) {
                    foreach (Mail::failures as $email_address)
                        Toastr::error("Failed to send to $email_address");
                } else
                    Toastr::success("Sent email");

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
                'date'              => Carbon::createFromFormat('Y-m-d H:i:s', $plan->from)->format('M j'),
                'code'              => $site->code,
                'name'              => $site->name,
                'company'           => $entity_name,
                'supervisor'        => $site->supervisorsSBC(),
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
                    'user_fullname'     => Auth::user()->fullname,
                    'user_company_name' => Auth::user()->company->name,
                    'startdata'         => $startdata
                ];
                Mail::send('emails/site-plan-completion', $data, function ($m) use ($email_list, $data) {
                    $user_email = Auth::user()->email;
                    ($user_email) ? $send_from = $user_email : $send_from = 'do-not-reply@safeworksite.com.au';

                    $m->from($send_from, Auth::user()->fullname);
                    $m->to($email_list);
                    $m->subject('Practical Completion List');
                });
                if (count(Mail::failures()) > 0) {
                    foreach (Mail::failures as $email_address)
                        Toastr::error("Failed to send to $email_address");
                } else
                    Toastr::success("Sent email");

                return view('site/export/completion');
            }
        }
    }

}
