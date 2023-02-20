<?php

namespace App\Http\Controllers\Site\Planner;

use Illuminate\Http\Request;
use Validator;

use DB;
use Mail;
use Session;
use App\Models\Site\Site;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteProjectSupply;
use App\User;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Company\Company;
use App\Models\Company\CompanyLeave;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;

/**
 * Class SitePlannerController
 * @package App\Http\Controllers
 */
class SitePlannerController extends Controller {

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        // Required even if empty
        if ($request->ajax())
            return SitePlanner::find($id);

        return view('errors/404');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax())
            return SitePlanner::create($request->all());

        return view('errors/404');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $task = SitePlanner::findOrFail($id);
            $task->update($request->all());

            return $task;
        }

        return view('errors/404');
    }

    /**
     * Delete the specified resource in storage.
     */
    public function destroy(Request $request, $id)
    {

        if ($request->ajax()) {
            $task = SitePlanner::findOrFail($id);
            $task->delete($request->all());

            return;
        }

        return view('errors/404');
    }

    /*
     *  Add user to the Roster
     */
    public function addUserRoster(Request $request)
    {
        return SiteRoster::create($request->all());
    }

    /*
     * Delete user from the Roster
     */
    public function delUserRoster(Request $request, $id)
    {
        $roster = SiteRoster::findOrFail($id);
        $roster->delete();

        return;
    }

    /*
     * Delete all users for given Entity from the Roster
     */
    public function delCompanyRoster($cid, $site_id, $date)
    {
        $staff = Company::findOrFail($cid)->staff->pluck('id')->toArray();
        $deleted = SiteRoster::where('site_id', $site_id)->where('date', '=', $date)->whereIn('user_id', $staff)->delete();
        //foreach ($deleted as $d)
        //    echo "deleted rid:$d->id user:$d->user_id date:$date<br>";

        return $deleted;
    }

    /*
     * Allocate a Site to a Supervisor
     */
    public function allocateSiteSupervisor($site_id, $user_id)
    {
        $site = Site::findOrFail($site_id);
        $site->supervisors()->detach();
        $site->supervisors()->attach($user_id);
        $site->status = 1;
        $site->save();

        return;
    }

    /*
     * Add all users for given Entity from the Roster
     */
    public function addCompanyRoster($cid, $site_id, $date)
    {
        $staff = Company::findOrFail($cid)->staffStatus(1)->pluck('id')->toArray();
        foreach ($staff as $user_id) {
            $newRoster = SiteRoster::create(array(
                'site_id' => $site_id,
                'user_id' => $user_id,
                'date'    => $date . ' 00:00:00',
            ));
        }

        return;
    }

    /**
     * Show Weekly Planner
     */
    public function showWeekly()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('weekly.planner'))
            return view('errors/404');


        // Set Date
        if (request('date') == '') {
            $date = new Carbon('monday this week');
            $date = $date->format('Y-m-d');
        } else
            $date = request('date');


        // Set Supervisor_id
        $supervisor_id = 'all';
        if (request('supervisor_id'))
            $supervisor_id = request('supervisor_id');
        elseif (Auth::user()->isSupervisor() && Auth::user()->company_id == 3 && Auth::user()->id != 7) // ie Not Gary
            $supervisor_id = Auth::user()->id;

        $site_id = request('site_id');

        // Supervisors Dropdown Selection
        $supervisors = [];
        if (Auth::user()->company->addon('planner')) {
            if (Auth::user()->isSupervisor()) {
                // User is Supervisor / Area Supervisor so only show sites they supervise
                if (Auth::user()->isAreaSupervisor()) {
                    $supervisors = Auth::user()->subSupervisorsSelect();
                    $supervisors = [Auth::user()->id => Auth::user()->fullname] + $supervisors;
                } else
                    $supervisors = [Auth::user()->id => Auth::user()->fullname];
            } else
                $supervisors = Auth::user()->company->supervisorsSelect();
        }
        if (Auth::user()->isCC()) {
            $supervisors = ['all' => 'Active Sites', 'maint' => 'Maintenance Sites'] + $supervisors;
        } else
            $supervisors = ['all' => 'All Sites'] + $supervisors;

        $site_start = request('site_start');

        return view('planner/weekly', compact('date', 'site_id', 'supervisor_id', 'site_start', 'supervisors'));
    }

    /**
     * Show Site Planner
     */
    public function showSite($site_id = null)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.planner'))
            return view('errors/404');

        $date = request('date');
        $supervisor_id = request('supervisor_id');
        $site_id = ($site_id) ? $site_id : request('site_id');
        if (request('site_start'))
            $site_start = request('site_start');
        else
            $site_start = 'week';

        $site = Site::find($site_id);

        return view('planner/site', compact('date', 'site_id', 'supervisor_id', 'site_start', 'site'));
    }

    /**
     * Show Attendance Planner
     */
    public function showAttendance()
    {
        $date = request('date');
        $supervisor_id = request('supervisor_id');
        $site_id = request('site_id');
        if (request('site_start'))
            $site_start = request('site_start');
        else
            $site_start = 'week';

        $site = Site::find($site_id);

        return view('planner/attend', compact('date', 'site_id', 'supervisor_id', 'site_start', 'site'));
    }

    /**
     * Show Roster Planner
     */
    public function showRoster()
    {
        $date = request('date');
        $site_id = request('site_id');
        if (request('site_start'))
            $site_start = request('site_start');
        else
            $site_start = 'week';

        $site = Site::find($site_id);

        // Set Supervisor_id
        $supervisor_id = 'all';
        if (request('supervisor_id'))
            $supervisor_id = request('supervisor_id');
        elseif (Auth::user()->isSupervisor() && Auth::user()->company_id == 3 && Auth::user()->id != 7) // ie Not Gary
            $supervisor_id = Auth::user()->id;

        $site_id = request('site_id');

        // Supervisors Dropdown Selection
        $supervisors = [];
        if (Auth::user()->company->addon('planner')) {
            if (Auth::user()->isSupervisor()) {
                // User is Supervisor / Area Supervisor so only show sites they supervise
                if (Auth::user()->isAreaSupervisor()) {
                    $supervisors = Auth::user()->subSupervisorsSelect();
                    $supervisors = [Auth::user()->id => Auth::user()->fullname] + $supervisors;
                } else
                    $supervisors = [Auth::user()->id => Auth::user()->fullname];
            } else
                $supervisors = Auth::user()->company->supervisorsSelect();
        }
        if (Auth::user()->isCC()) {
            $supervisors = ['all' => 'Active Sites', 'maint' => 'Maintenance Sites'] + $supervisors;
        } else
            $supervisors = ['all' => 'All Sites'] + $supervisors;

        return view('planner/roster', compact('date', 'site_id', 'supervisor_id', 'site_start', 'site', 'supervisors'));
    }

    /**
     * Show Trade Planner
     */
    public function showTrade()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('trade.planner'))
            return view('errors/404');

        // Set Date
        if (request('date') == '') {
            $date = new Carbon('monday this week');
            $date = $date->format('Y-m-d');
        } else
            $date = request('date');

        $site_id = request('site_id');
        $supervisor_id = request('supervisor_id');
        $site_start = request('site_start');
        $trade_id = request('trade_id');

        // Set trade_id to 'Carpenter' as default for Cape Cod
        if (!$trade_id && Auth::user()->isCC()) $trade_id = 2;

        return view('planner/trade', compact('date', 'site_id', 'supervisor_id', 'site_start', 'trade_id'));
    }

    /**
     * Show Trade Planner
     */
    public function showTransient()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('trade.planner'))
            return view('errors/404');

        // Set Date
        if (request('date') == '') {
            $date = new Carbon('monday this week');
            $date = $date->format('Y-m-d');
        } else
            $date = request('date');

        $site_id = request('site_id');
        $supervisor_id = request('supervisor_id');
        $site_start = request('site_start');
        $trade_id = 21;

        return view('planner/labour', compact('date', 'site_id', 'supervisor_id', 'site_start', 'trade_id'));
    }

    /**
     * Show Pre-construction Planner
     */
    public function showPreconstruction($site_id = null)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('preconstruction.planner'))
            return view('errors/404');

        $date = request('date');
        $supervisor_id = request('supervisor_id');
        $site_id = ($site_id) ? $site_id : request('site_id');
        if (request('site_start'))
            $site_start = request('site_start');
        else
            $site_start = 'week';

        $site = Site::find($site_id);

        return view('planner/preconstruction', compact('date', 'site_id', 'supervisor_id', 'site_start', 'site'));
    }

    /**
     * Show Up and Coming Projects
     */
    public function showUpcoming($site_id = null)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('preconstruction.planner'))
            return view('errors/404');

        $date = request('date');
        $supervisor_id = request('supervisor_id');
        $site_id = ($site_id) ? $site_id : request('site_id');
        if (request('site_start'))
            $site_start = request('site_start');
        else
            $site_start = 'week';

        $site = Site::find($site_id);

        // Sites ordered - Jobstart/Council Approval/Contracts Sent/Deposit Paid/Job#
        //    - prioritise sites with more fields completed

        $site_list = [];
        $pre_sites = Auth::user()->company->sites('-1')->pluck('id')->toArray();

        // Add Sites that have START JOB to list in date order
        $job_starts = SitePlanner::where('task_id', 11)->whereIn('site_id', $pre_sites)->orderBy('from')->get();
        foreach ($job_starts as $plan)
            if (!in_array($sid, $site_list))
            $site_list[] = $plan->site_id;

        // Add Sites with (council_approval, contract_sent, contract_signed, deposit_paid)
        $pre_sites = Auth::user()->company->sites('-1')->whereNotNull('council_approval')->whereNotNull('contract_sent')->whereNotNull('contract_signed')->whereNotNull('deposit_paid')->sortBy('council_approval')->pluck('id')->toArray();
        foreach ($pre_sites as $sid)
            if (!in_array($sid, $site_list))
            $site_list[] = $sid;

        // Add Sites with (council_approval, contract_sent, contract_signed)
        $pre_sites = Auth::user()->company->sites('-1')->whereNotNull('council_approval')->whereNotNull('contract_sent')->whereNotNull('contract_signed')->sortBy('council_approval')->pluck('id')->toArray();
        foreach ($pre_sites as $sid)
            if (!in_array($sid, $site_list))
            $site_list[] = $sid;

        // Add Sites with (council_approval, contract_sent)
        $pre_sites = Auth::user()->company->sites('-1')->whereNotNull('council_approval')->whereNotNull('contract_sent')->sortBy('council_approval')->pluck('id')->toArray();
        foreach ($pre_sites as $sid)
            if (!in_array($sid, $site_list))
            $site_list[] = $sid;

        // Add Sites with (council_approval)
        $pre_sites = Auth::user()->company->sites('-1')->whereNotNull('council_approval')->sortBy('council_approval')->pluck('id')->toArray();
        foreach ($pre_sites as $sid)
            if (!in_array($sid, $site_list))
            $site_list[] = $sid;

        // Add Remaining Pre-construct jobs to the list
        $pre_sites = Auth::user()->company->sites('-1')->sortBy('code')->pluck('id')->toArray();
        foreach ($pre_sites as $sid) {
            if (!in_array($sid, $site_list))
                $site_list[] = $sid;
        }

        /*

        // Add Remaining Pre-construct jobs to the list
        $pre_sites = Auth::user()->company->sites('-1')->sortBy('code')->pluck('id')->toArray();
        $non_start_sites = [];
        foreach ($pre_sites as $site_id) {
            if (!in_array($site_id, $site_list)) {
                $site = Site::find($site_id);
                //$site_list[] = $site_id;
                if ($site->council_approval && $site->contract_sent) {
                    $non_start_sites[$site_id] = ($site->council_approval->lte($site->contract_sent)) ? $site->council_approval->format('Ymd') : $site->contract_sent->format('Ymd');
                } elseif ($site->council_approval) {
                    $non_start_sites[$site_id] = $site->council_approval->format('Ymd');
                } elseif ($site->contract_sent) {
                    $non_start_sites[$site_id] = $site->contract_sent->format('Ymd');
                } else {
                    $non_start_sites[$site_id] = "99999999-$site->code";
                }
            }
        }
        asort($non_start_sites);
        dd($non_start_sites);
        foreach ($non_start_sites as $site_id => $date)
            $site_list[] = $site_id;
        */

        //dd($site_list);

        return view('planner/upcoming', compact('date', 'site_id', 'supervisor_id', 'site_start', 'site', 'site_list'));
    }

    /**
     * Show Up and Coming Projects
     */
    public function showForecast()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->hasAnyPermissionType('forcast.planner'))
        //    return view('errors/404');

        // Set Supervisor_id
        $supervisor_id = '';
        if (request('supervisor_id'))
            $supervisor_id = request('supervisor_id');
        elseif (Auth::user()->isSupervisor() && Auth::user()->company_id == 3 && Auth::user()->id != 7) // ie Not Gary
            $supervisor_id = Auth::user()->id;

        $first_month = new Carbon('first day of this month');
        $six_months = new Carbon('first day of this month');
        $six_months = $six_months->addMonths(6)->subDay();

        $site_list = [];
        $sites = Site::where('status', 1)->where('special', null)->where('company_id', Auth::user()->company_id)->pluck('id')->toArray();
        $planner = SitePlanner::whereDate('from', '>=', $first_month)->whereDate('to', '<=', $six_months)->whereIn('site_id', $sites)->orderBy('from')->pluck('site_id')->toArray();
        $site_list = array_unique($planner);

        //dd($site_list);

        $site_data = [];
        $supers = [];
        foreach ($site_list as $site_id) {
            $site = Site::findOrFail($site_id);
            $job_start = SitePlanner::where('site_id', $site_id)->where('task_id', 11)->first();
            $prac_complete = SitePlanner::where('site_id', $site_id)->where('task_id', 265)->first();
            $supervisor_ids = $site->supervisors->pluck('id')->toArray();
            $supervisor_ids = rtrim(implode(',', $supervisor_ids), ',');
            $array = [
                'site_id'           => $site_id,
                'site_name'         => $site->name,
                'super_initials'    => $site->supervisorsInitialsSBC(),
                'supervisor_id'     => $supervisor_ids,
                'job_start'         => ($job_start) ? $job_start->from->format('d/m/Y') : '',
                'job_start_ym'      => ($job_start) ? $job_start->from->format('Ym') : '',
                'job_start_ymd'     => ($job_start) ? $job_start->from->format('Ymd') : '',
                'job_start_day'     => ($job_start) ? $job_start->from->format('j S') : '',
                'prac_complete'     => ($prac_complete) ? $prac_complete->from->format('d/m/Y') : '',
                'prac_complete_ym'  => ($prac_complete) ? $prac_complete->from->format('Ym') : '',
                'prac_complete_ymd' => ($prac_complete) ? $prac_complete->from->format('Ymd') : '',
                'prac_complete_day' => ($prac_complete) ? $prac_complete->from->format('j S') : '',

            ];
            $site_data[] = $array;

            if (!in_array($array['supervisor_id'], $supers))
                $supers[$array['supervisor_id']] = $site->supervisorsSBC();
        }

        //dd($site_data);

        asort($supers);
        $site_data_sorted = [];
        foreach ($supers as $super_id => $super_name) {
            $site_data_sorted[] = ['site_name'     => $super_name, 'supervisor_id' => $super_id,
                                   'site_id'       => '', 'job_start' => '', 'job_start_ym' => '', 'job_start_day' => '',
                                   'prac_complete' => '', 'prac_complete_ym' => '', 'prac_complete_day' => ''];

            // Sites ordered - Prac Complete
            usort($site_data, function ($a, $b) {
                return $a['prac_complete_ymd'] <=> $b['prac_complete_ymd'];
            });

            // For Sites with Prac Complete set
            foreach ($site_data as $array) {
                if ($array['prac_complete_ym'] && $super_id == $array['supervisor_id'])
                    $site_data_sorted[] = $array;
            }
            // Sites without Prac Complete or Start (only Active)
            foreach ($site_data as $array) {
                if (!$array['prac_complete_ym'] && !$array['job_start'] && $super_id == $array['supervisor_id'])
                    $site_data_sorted[] = $array;
            }

            // Sites ordered - Jobstart
            usort($site_data, function ($a, $b) {
                return $a['job_start_ymd'] <=> $b['job_start_ymd'];
            });

            //Sites without Prac Complete but have Start (rest)
            foreach ($site_data as $array) {
                if (!$array['prac_complete_ym'] && $array['job_start'] && $super_id == $array['supervisor_id'])
                    $site_data_sorted[] = $array;
            }

            // Add a totals row (to be calcularte later
            $site_data_sorted[] = ['site_name'     => "Totals", 'supervisor_id' => $super_id,
                                   'site_id'       => '', 'job_start' => '', 'job_start_ym' => '', 'job_start_day' => '',
                                   'prac_complete' => '', 'prac_complete_ym' => '', 'prac_complete_day' => ''];
        }


        $months = [];
        $data = [];
        foreach ($site_data_sorted as $array) {
            $col = [];
            $col['site_id'] = $array['site_id'];
            $col['site_name'] = $array['site_name'];
            $col['supervisor_id'] = $array['supervisor_id'];
            $col['job_start'] = $array['job_start'];
            $col['job_start_day'] = $array['job_start_day'];
            $col['prac_complete'] = $array['prac_complete'];
            $col['prac_complete_day'] = $array['prac_complete_day'];

            // Determine
            $key_task = false;
            if ($array['site_id'] != '') {
                for ($i = 0; $i < 6; $i ++) {
                    $this_month = new Carbon('first day of this month');
                    $this_month = $this_month->addMonths($i);
                    $months[$i] = $this_month->format('M');
                    $this_month = $this_month->format('Ym');
                    //echo "M: $this_month  JB:" . $array['job_start_ym'] . " PC:" . $array['prac_complete_ym'] . "<br>";
                    if ($this_month == $array['job_start_ym']) {
                        $col["m$i"] = 'START';
                        $key_task = true;
                    } else if ($this_month == $array['prac_complete_ym']) {
                        $col["m$i"] = 'PRAC';
                        $key_task = true;
                    } elseif ($this_month > $array['job_start_ym'] && $this_month < $array['prac_complete_ym']) {
                        $col["m$i"] = 'Active';
                        $key_task = true;
                    } elseif ($this_month > $array['job_start_ym'] && $array['prac_complete_ym'] == '') {
                        $col["m$i"] = 'Active';
                        $key_task = true;
                    } else
                        $col["m$i"] = '';
                }
            } else {
                $col = ['site_id' => $array['site_id'], 'site_name' => $array['site_name'], 'supervisor_id' => $array['supervisor_id'], 'm0' => '', 'm1' => '', 'm2' => '', 'm3' => '', 'm4' => '', 'm5' => ''];
                $key_task = true;
            }
            //$col['key'] = ($key_task) ? 'Y' : 'N';
            if ($key_task)
                $data[] = $col;
        }

        $current_super = '';
        foreach ($data as $key => $row) {
            //echo "$key: ";
            //print_r($row);
            //echo "<br>";
            if (!$row['site_id'] && $row['site_name'] != 'Totals') {
                //echo "Super: ".$row['super_initials']."<br>";
                $current_super = $row['supervisor_id'];
                $m0 = $m1 = $m2 = $m3 = $m4 = $m5 = 0;
            } elseif (!$row['site_id'] && $row['site_name'] == 'Totals') {
                //echo "$m0 : $m1 : $m2 : $m3 : $m4 : $m5 <br>";
                $data[$key]['m0'] = $m0;
                $data[$key]['m1'] = $m1;
                $data[$key]['m2'] = $m2;
                $data[$key]['m3'] = $m3;
                $data[$key]['m4'] = $m4;
                $data[$key]['m5'] = $m5;
            } else {
                if ($row['m0'] != '') $m0 ++;
                if ($row['m1'] != '') $m1 ++;
                if ($row['m2'] != '') $m2 ++;
                if ($row['m3'] != '') $m3 ++;
                if ($row['m4'] != '') $m4 ++;
                if ($row['m5'] != '') $m5 ++;
            }
        }

        //dd($data);

        return view('planner/forecast', compact('data', 'months', 'supervisor_id'));
    }

    /**
     * Get Weekly Planner for date
     */
    public function getWeeklyPlan(Request $request, $date, $super_id)
    {
        $plan_type = 'weekly';

        // As Weekly + Trade Planner call this function we can determine which one it is by provided 'super_id'
        if ($super_id == 'alltrade') {
            $super_id = 'all';
            $plan_type = 'trade';
        }

        if (Auth::user()->company->addon('planner')) {
            if ($super_id == 'all')
                $allowedSites = Auth::user()->company->reportsTo()->sites([1, 2])->pluck('id')->toArray();
            elseif ($super_id == 'maint')
                $allowedSites = Auth::user()->company->reportsTo()->sites([2])->pluck('id')->toArray();
            else
                $allowedSites = DB::table('site_supervisor')->select('site_id')->where('user_id', $super_id)->pluck('site_id')->toArray();
        } else {
            $this_mon = new Carbon('monday this week');
            $this_mon_2 = new Carbon('monday this week');
            $this_mon_2->addDays(62);  // was 34
            $allowedSites = Auth::user()->company->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();

            // Hack to allow Split Companies NRW (57,202,255) + Solid Foundations (120,121) to see their other Sites
            if (in_array(Auth::user()->company_id, [57, 202, 255])) {
                $c1 = Company::find(57)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $c2 = Company::find(202)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $c3 = Company::find(255)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $allowedSites = array_merge($c1, $c2);
            }
            if (in_array(Auth::user()->company_id, [120, 121])) {
                $c1 = Company::find(120)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $c2 = Company::find(121)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $allowedSites = array_merge($c1, $c2);
            }
        }

        $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00')->addDays(7);

        //dd($allowedSites);

        //
        // Full Plan
        //
        $planner = $this->getPlannerForWeek($date_from, $date_to, $allowedSites, []);
        //dd($planner);
        $fullplan = [];
        foreach ($planner as $plan)
            $fullplan[] = $this->getPlanData($plan);

        //
        // Non Rostered Users who attended
        //
        $non_rostered = [];
        if ($plan_type == 'weekly') {
            $attendance = SiteAttendance::whereDate('date', '>=', $date_from->format('Y-m-d'))->whereDate('date', '<=', $date_to->format('Y-m-d'))->get();
            $allowed_companies = Auth::user()->company->companies()->pluck('id')->toArray();
            foreach ($attendance as $attend) {
                //$site = Site::find($attend->site_id);
                //if (!$site->isUserOnRoster($attend->user_id, $attend->date->format('Y-m-d'))) {
                if (!$attend->site->isUserOnRoster($attend->user_id, $attend->date->format('Y-m-d'))) {
                    // For non subscription companies limit to their users only
                    //if (Auth::user()->company->subscription || $user->isCompany(Auth::user()->company)) {
                    if (in_array($attend->user->company_id, $allowed_companies)) {
                        $key = $attend->site_id . '.' . $attend->date->format('Y-m-d');
                        if (isset($non_rostered[$key]))
                            $non_rostered[$key][$attend->user->id] = $attend->user->fullname;
                        else
                            $non_rostered[$key] = [$attend->user->id => $attend->user->fullname];
                    }
                }
            }
        }

        //
        // Get a list of Companys on planner that have exceeded their 'maxjobs' - Conflicts
        //

        // Exclude Quotes (tasks with code 'Q' from maxjobs
        $excludeTasks = [];
        if (Auth::user()->company->reportsTo()->id == '3')
            $excludeTasks = Task::where('code', 'Q')->pluck('id')->toArray();

        $planner2 = $this->getPlannerForWeek($date_from, $date_to, $allowedSites, $excludeTasks);
        $conflicts = $this->getPlanConflicts($request, $planner2, 'any', 'json');
        //$conflicts = [];

        // Get Companies on leave
        $company_leave = $this->getCompanyLeave();
        //$company_leave = [];


        //
        // Get attendance for Today and verify if Company all onsite
        //
        $company_onsite = [];
        if ($plan_type == 'weekly') {
            $today = Carbon::today();
            $planner = $this->getPlannerForWeek($date_from, $date_to, $allowedSites, []);
            // Initialise all companies on the planner to be onsite ie. true
            foreach ($planner as $plan) {
                $site = Site::find($plan->site_id);
                $current_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
                for ($x = 0; $x < 5; $x ++) {
                    if ($plan->entity_type == 'c' && $site->isCompanyOnPlanner($plan->entity_id, $current_date->format('Y-m-d')))
                        $company_onsite[$current_date->format('Y-m-d') . '.' . $plan->site_id . '.' . $plan->entity_type . '.' . $plan->entity_id] = 1;
                    $current_date->addDay(1);
                    if ($current_date->gt($today))
                        break;
                }
            }

            // Now determine those who aren't all onsite ie. false
            foreach ($company_onsite as $key => $value) {
                list($day_date, $site_id, $etype, $eid) = explode('.', $key);
                $staff = Company::findOrFail($eid)->staffStatus(1)->pluck('id')->toArray();
                $roster = SiteRoster::where('site_id', $site_id)->whereDate('date', '=', $day_date)->whereIn('user_id', $staff)->get();
                if (!$roster->isEmpty()) {
                    foreach ($roster as $rost) {
                        $site = Site::find($rost->site_id);
                        if (!$site->isUserOnsite($rost->user_id, $rost->date->format('Y-m-d')))
                            $company_onsite[$key] = 0;

                    }
                } else
                    $company_onsite[$key] = - 1;

            }
        }


        //
        // Get Site Supervisor 'Select' options
        //
        if (Auth::user()->isSupervisor()) {
            // User is Supervisor / Area Supervisor so only show sites they supervise
            if (Auth::user()->isAreaSupervisor()) {
                $supervisors = Auth::user()->subSupervisorsSelect();
                $supervisors = [Auth::user()->id => Auth::user()->fullname] + $supervisors;
            } else
                $supervisors = [Auth::user()->id => Auth::user()->fullname];
        } else
            $supervisors = Auth::user()->company->supervisorsSelect();
        $supervisors = ['all' => 'All Sites'] + $supervisors;

        $sel_super = [];
        foreach ($supervisors as $user_id => $fullname) {
            $sel_super[] = ['value' => $user_id, 'text' => $fullname];
        }

        // Get Users permissions
        $permission = '';
        if (Auth::user()->hasPermission2('view.weekly.planner'))
            $permission = 'view';
        if ($plan_type == 'weekly' && Auth::user()->hasPermission2('view.site.planner'))
            $permission = 'edit';
        if ($plan_type == 'trade' && Auth::user()->hasPermission2('edit.trade.planner'))
            $permission = 'edit';

        $json = [];
        $json[] = $fullplan;
        $json[] = $non_rostered;
        $json[] = $conflicts;
        $json[] = $company_leave;
        $json[] = $company_onsite;
        $json[] = $sel_super;
        $json[] = $permission;

        return $json;
    }


    /**
     * Get Site Planner for specific site
     */
    public function getSitePlan(Request $request, $site_id)
    {
        $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
            ->where('site_id', $site_id)->orderBy('from')->get();

        $vars = ['first_date' => '', 'start_date' => '', 'start_carp' => '', 'carp_prac' => ''];
        $fullplan = [];

        $site = Site::find($site_id);
        $first_date = Carbon::now()->addDays(9999);
        if ($site->status < 0) {
            if ($site->council_approval) $first_date = $site->council_approval;
            if ($site->contract_sent && $site->contract_sent->lt($first_date)) $first_date = $site->contract_sent;
            if ($site->contract_signed && $site->contract_signed->lt($first_date)) $first_date = $site->contract_signed;
            if ($site->deposit_paid && $site->deposit_paid->lt($first_date)) $first_date = $site->deposit_paid;
        }
        foreach ($planner as $plan) {
            $array = $this->getPlanData($plan);

            // Determine start dates
            if (!$vars['first_date']) {
                $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $array['from'] . ' 00:00:00');
                $vars['first_date'] = ($date_from->lt($first_date)) ? $array['from'] : $first_date->format('Y-m-d');
            }
            if (!$vars['start_date'] && $array['task_code'] == 'START') $vars['start_date'] = $array['from'];
            if (!$vars['start_carp'] && $array['task_code'] == 'STARTCarp') $vars['start_carp'] = $array['from'];
            if (!$vars['carp_prac'] && $array['task_id'] == '5') $vars['carp_prac'] = $array['from'];
            $vars['final_date'] = $plan->from->format('Y-m-d');

            $fullplan[] = $array;
        };

        //
        // Get a list of Companys on planner that have exceeded their 'maxjobs'
        //
        $quote_ids = [];
        if (Auth::user()->company->reportsTo()->id == '3')
            $quote_ids = Task::where('code', 'Q')->pluck('id')->toArray();

        $today_14 = Carbon::now()->subDays(14);
        $planner2 = SitePlanner::where('entity_type', 'c')
            ->where('from', '>=', $today_14->format('Y-m-d'))->whereNotIn('task_id', $quote_ids)
            ->orderBy('entity_id')->orderBy('from')->get();

        $conflicts = $this->getPlanConflicts($request, $planner2, $site_id, '');

        // Get Companies on leave
        $company_leave = $this->getCompanyLeave();

        // Get Users permissions
        $permission = '';
        if (Auth::user()->hasPermission2('view.site.planner'))
            $permission = 'view';
        if (Auth::user()->allowed2('edit.site.planner', Site::find($site_id)))
            $permission = 'edit';

        $json = [];
        $json[] = $vars;
        $json[] = $fullplan;
        $json[] = $conflicts;
        $json[] = $company_leave;
        $json[] = $permission;

        return $json;
    }


    /**
     * Get Site Roster for specific site
     */
    public function getSiteRoster($date, $super_id)
    {
        if ($super_id == 'all')
            $allowedSites = Auth::user()->company->reportsTo()->sites([1, 2])->pluck('id')->toArray();
        elseif ($super_id == 'maint')
            $allowedSites = Auth::user()->company->reportsTo()->sites([2])->pluck('id')->toArray();
        else
            $allowedSites = DB::table('site_supervisor')->select('site_id')->where('user_id', $super_id)->pluck('site_id')->toArray();


        $today = Carbon::now()->format('Y-m-d');
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);


        // Site with current tasks on given date
        $site_list = [];
        //$sites = Auth::user()->authSites('edit.roster');
        $sites = Site::whereIn('id', $allowedSites)->get();
        foreach ($sites as $site) {
            if ($site->anyTasksOnDate($date) && !in_array($site->id, $site_list))
                $site_list[] = $site->id;
        }
        //$site_list = [177, 532, 655];

        $site_roster = [];
        foreach ($site_list as $site_id) {
            $site = Site::find($site_id);
            $site_array = [];
            $site_array['id'] = $site_id;
            $site_array['name'] = $site->name;

            $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
                ->where('site_id', $site_id)->where('weekend', $weekend)->get();

            //$dayplan = [];
            $r_entities = [];
            $planner_ids = [];
            $user_list = [];
            foreach ($planner as $plan) {
                $planner_ids[] = $plan->id;
                $array = $this->getPlanData($plan);
                $key = $plan->entity_type . '.' . $plan->entity_id;

                // Add task to Entity's existing task else add Entity to list
                if (isset($r_entities[$key])) {
                    $r_entities[$key]['tasks'] .= ', ' . $array['task_name'];
                    $r_entities[$key]['plan_ids'] .= ', ' . $plan->id;
                } else {
                    $attendance = [];
                    $allonsite = 1;
                    if ($plan->entity_type == 'c') {
                        // Get Staff Attendance on Current Site
                        $staff = Company::find($plan->entity_id)->staff->pluck('id')->toArray();
                        $roster = SiteRoster::where('site_id', $plan->site_id)->where('date', '=', $date)->whereIn('user_id', $staff)->get();

                        // If today Roster will be all active company to give us the ability to add/remove them from Roster database
                        if ($date == $today)
                            $roster = Company::findOrFail($plan->entity_id)->staffStatus(1);

                        foreach ($roster as $rostered) {
                            // If today then determine if user is on planner otherwise we know they already are
                            if ($date == $today) {
                                $user = User::find($rostered->id);
                                $roster_id = $plan->site->isUserOnRoster($user->id, $date);
                                //echo "rid:" . $roster_id . ' user:' . $rostered->id . ' date:' . $date . "<br>";
                            } else {
                                $user = User::find($rostered->user_id);
                                $roster_id = $rostered->id;
                                //echo "rid:" . $rostered->id . ' user:' . $rostered->user_id . ' date:' . $rostered->date . "<br>";
                            }

                            $user_list[] = $user->id; // add to user_list to determine non-rostered users later
                            // Current Site attendance
                            $attended = ($onsite = $plan->site->isUserOnsite($user->id, $date)) ? $onsite->date->format('H:i:s') : '';
                            if (!$attended)
                                $allonsite = 0; // At least one user for company isn't onsite that was rostered
                            // Other Site attendance
                            $attend_other = SiteAttendance::where('user_id', $user->id)
                                ->where('site_id', '<>', $plan->site->id)->whereDate('date', '=', $date)
                                ->orderBy('date')->get();
                            $other_sites = '';
                            foreach ($attend_other as $attend) {
                                $other_site = Site::find($attend->site_id);
                                ($other_sites) ? $other_sites .= ', ' . $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')' :
                                    $other_sites = $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')';
                            }
                            $attendance[] = ['user_id' => $user->id, 'name' => $user->fullname, 'roster_id' => $roster_id, 'attended' => $attended, 'other_sites' => $other_sites];

                        }
                    }
                    $r_entities[$key] = [
                        'site_id'     => $plan->site_id,
                        'key'         => $key,
                        'entity_type' => $plan->entity_type,
                        'entity_id'   => $plan->entity_id,
                        'entity_name' => $array['entity_name'],
                        'tasks'       => $array['task_name'],
                        'plan_ids'    => $plan->id,
                        'allonsite'   => $allonsite,
                        'attendance'  => $attendance,
                        'open'        => false
                    ];
                }
                //$dayplan[] = $array;

            }

            // Non-Rostered attendees
            $n_entities = [];
            $non_rostered = SiteAttendance::where('site_id', $site_id)->whereDate('date', '=', $date)->whereNotIn('user_id', $user_list)->get();
            foreach ($non_rostered as $non) {
                $company = User::find($non->user_id)->company;
                $key = 'c.' . $company->id;

                $attendance = [];
                // Get All Staff Non Rostered Attendance
                $staff = $company->staff->pluck('id')->toArray();
                foreach ($staff as $s) {
                    $user = User::find($s);
                    // Current Site attendance
                    $attended = ($onsite = $non->site->isUserOnsite($user->id, $date)) ? $onsite->date->format('H:i:s') : '';
                    if ($attended) {
                        // Other Site attendance
                        $attend_other = SiteAttendance::where('user_id', $user->id)
                            ->where('site_id', '<>', $non->site->id)->whereDate('date', '=', $date)->orderBy('date')->get();
                        $other_sites = '';
                        foreach ($attend_other as $attend) {
                            $other_site = Site::find($attend->site_id);
                            ($other_sites) ? $other_sites .= ', ' . $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')' :
                                $other_sites = $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')';
                        }
                        $attendance[] = ['user_id' => $user->id, 'name' => $user->fullname, 'attended' => $attended, 'other_sites' => $other_sites];
                    }
                }

                if (!isset($n_entities[$key])) {
                    $n_entities[$key] = [
                        'site_id'     => $plan->site_id,
                        'key'         => $key,
                        'entity_type' => 'c',
                        'entity_id'   => $company->id,
                        'entity_name' => $company->name_alias,
                        'tasks'       => 'Unrostered',
                        'attendance'  => $attendance,
                        'open'        => false
                    ];
                }
            }

            // Sort Rostered
            $roster = [];
            foreach ($r_entities as $entity) {
                usort($entity['attendance'], 'sortName');
                $roster[] = $entity;
            }
            usort($roster, 'sortEntityName');

            // Sort Non Rostered
            $non_roster = [];
            foreach ($n_entities as $entity) {
                usort($entity['attendance'], 'sortName');
                $non_roster[] = $entity;
            }
            usort($non_roster, 'sortEntityName');

            $site_array['roster'] = $roster;
            $site_array['non_roster'] = $non_roster;

            $site_roster[] = $site_array;
        }


        // Supervisors Dropdown Selection
        $sel_super = [];
        $sel_super[] = ['value' => 'all', 'text' => 'Active Sites'];
        //if (Auth::user()->isCC()) $sel_super[] =  ['value' => 'maint', 'text' => 'Maintenance Sites'];

        if (Auth::user()->company->addon('planner')) {
            if (Auth::user()->isSupervisor()) {
                // User is Supervisor / Area Supervisor so only show sites they supervise
                if (Auth::user()->isAreaSupervisor()) {
                    $sel_super[] = ['value' => Auth::user()->id, 'text' => Auth::user()->fullname];
                    foreach (Auth::user()->subSupervisorsSelect() as $uid => $name)
                        $sel_super[] = ['value' => $uid, 'text' => $name];
                } else
                    $sel_super[] = ['value' => Auth::user()->id, 'text' => Auth::user()->fullname];
            } else {
                foreach (Auth::user()->company->supervisorsSelect() as $uid => $name)
                    $sel_super[] = ['value' => $uid, 'text' => $name];
            }
        }

        // Get Users permissions
        $permission = '';
        if (Auth::user()->hasPermission2('view.roster'))
            $permission = 'view';
        if (Auth::user()->hasPermission2('edit.roster'))
            $permission = 'edit';

        $json = [];
        //$json[] = []; //$dayplan;
        //$json[] = []; //$roster;
        //$json[] = []; //$non_roster;
        $json[] = $site_roster;
        $json[] = $permission;
        $json[] = $sel_super;

        return $json;
    }

    /**
     * Get Site Attendance for specific site
     */
    public function getSiteAttendance($site_id, $date)
    {
        $today = Carbon::now()->format('Y-m-d');
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);

        $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
            ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
            ->where('site_id', $site_id)->where('weekend', $weekend)->get();

        $site = Site::find($site_id);
        $dayplan = [];
        $r_entities = [];
        $planner_ids = [];
        $user_list = [];
        foreach ($planner as $plan) {
            $planner_ids[] = $plan->id;
            $array = $this->getPlanData($plan);
            $key = $plan->entity_type . '.' . $plan->entity_id;

            // Add task to Entity's existing task else add Entity to list
            if (isset($r_entities[$key])) {
                $r_entities[$key]['tasks'] .= ', ' . $array['task_name'];
                $r_entities[$key]['plan_ids'] .= ', ' . $plan->id;
            } else {
                $attendance = [];
                if ($plan->entity_type == 'c') {
                    // Get Staff Attendance on Current Site
                    $staff = Company::find($plan->entity_id)->staff->pluck('id')->toArray();
                    $roster = SiteRoster::where('site_id', $plan->site_id)->where('date', '=', $date)->whereIn('user_id', $staff)->get();

                    // If today Roster will be all active company to give us the ability to add/remove them from Roster database
                    if ($date == $today)
                        $roster = Company::findOrFail($plan->entity_id)->staffStatus(1);

                    foreach ($roster as $rostered) {
                        // If today then determine if user is on planner otherwise we know they already are
                        if ($date == $today) {
                            $user = User::find($rostered->id);
                            $roster_id = $site->isUserOnRoster($user->id, $date);
                            //echo "rid:" . $roster_id . ' user:' . $rostered->id . ' date:' . $date . "<br>";
                        } else {
                            $user = User::find($rostered->user_id);
                            $roster_id = $rostered->id;
                            //echo "rid:" . $rostered->id . ' user:' . $rostered->user_id . ' date:' . $rostered->date . "<br>";
                        }

                        $user_list[] = $user->id; // add to user_list to determine non-rostered users later
                        // Current Site attendance
                        $attended = ($onsite = $site->isUserOnsite($user->id, $date)) ? $onsite->date->format('H:i:s') : '';
                        // Other Site attendance
                        $attend_other = SiteAttendance::where('user_id', $user->id)
                            ->where('site_id', '<>', $site_id)->whereDate('date', '=', $date)
                            ->orderBy('date')->get();
                        $other_sites = '';
                        foreach ($attend_other as $attend) {
                            $other_site = Site::find($attend->site_id);
                            ($other_sites) ? $other_sites .= ', ' . $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')' :
                                $other_sites = $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')';
                        }
                        $attendance[] = ['user_id' => $user->id, 'name' => $user->fullname, 'roster_id' => $roster_id, 'attended' => $attended, 'other_sites' => $other_sites];
                        //echo "Company:".$array['entity_name']."<br>";
                    }
                }
                $r_entities[$key] = [
                    'key'         => $key,
                    'entity_type' => $plan->entity_type,
                    'entity_id'   => $plan->entity_id,
                    'entity_name' => $array['entity_name'],
                    'tasks'       => $array['task_name'],
                    'plan_ids'    => $plan->id,
                    'attendance'  => $attendance,
                    'open'        => false
                ];
            }
            $dayplan[] = $array;
        }

        // Non-Rostered attendees
        $n_entities = [];
        $non_rostered = SiteAttendance::where('site_id', $site_id)->whereDate('date', '=', $date)->whereNotIn('user_id', $user_list)->get();
        foreach ($non_rostered as $non) {
            $company = User::find($non->user_id)->company;
            $key = 'c.' . $company->id;

            $attendance = [];
            // Get All Staff Non Rostered Attendance
            $staff = $company->staff->pluck('id')->toArray();
            foreach ($staff as $s) {
                $user = User::find($s);
                // Current Site attendance
                $attended = ($onsite = $site->isUserOnsite($user->id, $date)) ? $onsite->date->format('H:i:s') : '';
                if ($attended) {
                    // Other Site attendance
                    $attend_other = SiteAttendance::where('user_id', $user->id)
                        ->where('site_id', '<>', $site_id)->whereDate('date', '=', $date)
                        ->orderBy('date')->get();
                    $other_sites = '';
                    foreach ($attend_other as $attend) {
                        $other_site = Site::find($attend->site_id);
                        ($other_sites) ? $other_sites .= ', ' . $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')' :
                            $other_sites = $other_site->nameShort . ' (' . $attend->date->format('g:i a') . ')';
                    }
                    $attendance[] = ['user_id' => $user->id, 'name' => $user->fullname, 'attended' => $attended, 'other_sites' => $other_sites];
                }
            }

            if (!isset($n_entities[$key])) {
                $n_entities[$key] = [
                    'key'         => $key,
                    'entity_type' => 'c',
                    'entity_id'   => $company->id,
                    'entity_name' => $company->name_alias,
                    'tasks'       => 'Unrostered',
                    'attendance'  => $attendance,
                    'open'        => false
                ];
            }
        }

        $sel_site = [];
        $sel_site[] = ['value' => '', 'text' => 'Select Site'];
        $sites = Auth::user()->authSites('view.roster');
        foreach ($sites as $site) {
            if (Auth::user()->company->addon('planner')) {
                if ($site->anyTasksOnDate($date))
                    $sel_site[] = ['value' => $site->id, 'text' => $site->name];
            } else
                if ($site->isCompanyOnPlanner(Auth::user()->company_id, $date))
                    $sel_site[] = ['value' => $site->id, 'text' => $site->name];
        }

        // Sort Rostered
        $roster = [];
        foreach ($r_entities as $entity) {
            usort($entity['attendance'], 'sortName');
            $roster[] = $entity;
        }
        usort($roster, 'sortEntityName');

        // Sort Non Rostered
        $non_roster = [];
        foreach ($n_entities as $entity) {
            usort($entity['attendance'], 'sortName');
            $non_roster[] = $entity;
        }
        usort($non_roster, 'sortEntityName');

        // Get Users permissions
        $permission = '';
        if (Auth::user()->hasPermission2('view.roster'))
            $permission = 'view';
        if (Auth::user()->hasPermission2('edit.roster'))
            $permission = 'edit';

        $json = [];
        $json[] = $dayplan;
        $json[] = $roster;
        $json[] = $non_roster;
        $json[] = $sel_site;
        $json[] = $permission;

        return $json;
    }

    /*
     * Get plan data for a specific entry and return it as an array
     */
    private function getPlanData($plan)
    {
        $array = [];
        $array['id'] = $plan->id;
        $array['site_id'] = $plan->site_id;

        //$site = Site::find($plan->site_id);
        $array['site_name'] = $plan->site->name;
        $array['site_status'] = $plan->site->status;
        //if ($plan->id == 99017)
        //    dd($plan->site->name);

        $array['entity_type'] = $plan->entity_type;
        $array['entity_id'] = $plan->entity_id;

        if ($plan->entity_type == 'c') {
            $company = Company::find($plan->entity_id);
            if ($company)
                $array['entity_name'] = $company->name_alias;
        } else {
            $trade = Trade::find($plan->entity_id);
            if ($trade)
                $array['entity_name'] = $trade->name;
        }

        // Get task info
        $array['task_id'] = '';
        $array['task_code'] = '';
        $array['task_name'] = 'Task Unassigned';
        $array['trade_id'] = '';
        $array['trade_name'] = '';
        if ($plan->task_id) {
            $array['task_id'] = $plan->task_id;
            $task = Task::find($plan->task_id);
            $array['task_code'] = $task->code;
            $array['task_name'] = $task->name;

            $trade_id = DB::table('trade_task')->select('trade_id')->where('id', $plan->task_id)->pluck('trade_id')->toArray();
            $trade = Trade::find($trade_id[0]);
            if ($trade) {
                $array['trade_id'] = $trade->id;
                $array['trade_name'] = $trade->name;
            }

        }
        $array['from'] = $plan->from->format('Y-m-d');
        $array['to'] = $plan->to->format('Y-m-d');
        $array['days'] = $plan->days;

        return $array;
    }

    /*
     * Get all plan conflicts and return as an array
     */
    private function getPlanConflicts($request, $planner, $site_id, $format)
    {
        $alljobs = [];
        $company_sites = []; // Used to ensure company only added once for each site they on and not same site / multiple tasks
        foreach ($planner as $plan) {
            // Only Check conflict for companies
            if ($plan->entity_type == 'c') {
                $array = [];
                $current_date = $plan->from;

                if ($plan->entity_id == '114') {
                    //echo "<b>site:$plan->site_id</b> f:$plan->from t:$plan->to task:$plan->task_id <br>";
                }

                // Loop through current task 'from' -> 'to' skipping weekends
                // and add each date to array
                while ($current_date->lte($plan->to)) {
                    if (array_key_exists($plan->entity_id, $alljobs)) {
                        // Only add it to all jobs if this is company's for task for the site ie. don't add multiple tasks
                        if (!in_array($plan->entity_id . '.' . $plan->site_id . '.' . $current_date->format('Y-m-d'), $company_sites)) {
                            // if not in array then add otherwise increment number of occurances
                            $company_sites[] = $plan->entity_id . '.' . $plan->site_id . '.' . $current_date->format('Y-m-d');
                            if (array_key_exists($current_date->format('Y-m-d'), $alljobs[$plan->entity_id])) {
                                $alljobs[$plan->entity_id][$current_date->format('Y-m-d')] ++;
                                if ($plan->entity_id == '114') {
                                    //echo "date: " . $current_date->format('Y-m-d') . " = " . $alljobs[$plan->entity_id][$current_date->format('Y-m-d')] . " site:$plan->site_id<br><br>";
                                }
                            } else {
                                $alljobs[$plan->entity_id][$current_date->format('Y-m-d')] = 1;
                                if ($plan->entity_id == '114') {
                                    //echo "date: " . $current_date->format('Y-m-d') . " = 1 site:$plan->site_id<br>";
                                }
                            }
                        } else {
                            if ($plan->entity_id == '114') {
                                //echo "<b>SKIPPED</b> " . $plan->entity_id . '.' . $plan->site_id . '.' . $current_date->format('Y-m-d') ."<br>";
                            }
                        }
                    } else {
                        $company = Company::find($plan->entity_id);
                        $array['max'] = $company->maxjobs;
                        $array[$current_date->format('Y-m-d')] = 1;
                        $alljobs[$plan->entity_id] = $array;
                        $company_sites[] = $plan->entity_id . '.' . $plan->site_id . '.' . $current_date->format('Y-m-d');

                        if ($plan->entity_id == '114') {
                            //echo "date: " . $current_date->format('Y-m-d') . " = 1 site:$plan->site_id<br>";
                        }
                    }
                    $current_date->addDay();
                    if ($current_date->dayOfWeek == 6) // Sat
                        $current_date->addDays(2);
                    if ($current_date->dayOfWeek == 0) // Sun
                        $current_date->addDay();
                }
            }
        }
        // Now search through alljobs and determine if any date has exceeded 'maxjobs'
        // for company and add to conflict array
        $conflicts = [];

        foreach ($alljobs as $cid => $dates) {
            $array = [];
            foreach ($dates as $date => $jobs) {
                if ($jobs > $dates['max'])
                    $array[$date] = $this->getCompanySitesOnDate($request, $cid, $site_id, $date, $format);
            }
            if ($array) {
                $array['max'] = $dates['max'];
                $conflicts[$cid] = $array;
            }
        }

        return $conflicts;
    }

    /*
     * Get Company Leave for dates after today.
     */
    private function getCompanyLeave()
    {
        $company_list = Auth::user()->company->companies()->pluck('id')->toArray();
        $leave_records = CompanyLeave::where('to', '>=', Carbon::today()->toDateTimeString())
            ->whereIn('company_id', $company_list)->orderBy('from')->get();

        $company_leave = [];
        foreach ($leave_records as $leave) {
            $company = Company::find($leave->company_id);
            $array = ['summary' => $company->leave_upcoming_dates];
            // Loop through leave 'from' -> 'to' skipping weekends and add each date to array
            $current_date = $leave->from;
            $notes = "on leave";
            if ($leave->notes)
                $notes = $leave->notes;

            while ($current_date->lte($leave->to)) {
                //echo $leave->id . " E:" . $leave->company_id . ' D:' . $current_date->format('Y-m-d') . '<br>';
                if (array_key_exists($leave->company_id, $company_leave)) {
                    // if not in array then add otherwise increment number of occurances
                    if (!array_key_exists($current_date->format('Y-m-d'), $company_leave[$leave->company_id]))
                        $company_leave[$leave->company_id][$current_date->format('Y-m-d')] = $notes;

                } else {
                    $array[$current_date->format('Y-m-d')] = $notes;
                    $company_leave[$leave->company_id] = $array;
                }
                $current_date->addDay();
            }
        }

        return $company_leave;
    }

    /**
     * Get list of Sites User is authorised to view
     */
    public function getSites()
    {
        if (Auth::user()->company->addon('planner'))
            $allowedSites = Auth::user()->company->sites([1, 2, - 1])->pluck('id')->toArray();
        else {
            $this_mon = new Carbon('monday this week');
            $this_mon_2 = new Carbon('monday this week');
            $this_mon_2->addDays(34); // was 13
            $allowedSites = Auth::user()->company->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();

            // Hack to allow Split Companies NRW (57,202) + Solid Foundations (120,121) to see their other Sites
            if (in_array(Auth::user()->company_id, [57, 202])) {
                $c1 = Company::find(57)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $c2 = Company::find(202)->sitesPlannedFor([1, 2], $this_mon->format('Y-m-d'), $this_mon_2->format('Y-m-d'))->pluck('id')->toArray();
                $allowedSites = array_merge($c1, $c2);
            }
        }

        $sites = Site::select(['id', 'name'])->whereIn('status', [1, 2, - 1])->whereIn('id', $allowedSites)->orderBy('name')->get();

        $site_details = [];
        foreach ($sites as $site) {
            $site_record = Site::find($site->id);
            if ($site_record->status == 1 || ($site_record->status == 2 && $site_record->hasMaintenanceActive()) || $site_record->status == - 1) {
                $array = [];
                $array['id'] = $site->id;
                $array['value'] = $site->id;
                $array['name'] = $site->name;
                $array['text'] = $site->name;
                $array['code'] = $site_record->code;
                $array['start'] = ($site_record->job_start) ? $site_record->job_start->format('Y-m-d') : '';

                // First task on the planner for given site
                $firstTask = SitePlanner::where('site_id', $site->id)->orderBy('from')->first();
                $array['first'] = ($firstTask) ? $firstTask->from->format('Y-m-d') : '';
                $array['first_id'] = ($firstTask) ? $firstTask->id : '';

                $array['supervisors'] = $site_record->supervisorsSelect();
                $array['supervisors_contact'] = $site_record->supervisorsContactSBC();
                $array['address'] = $site_record->address_formatted;
                $array['status'] = $site_record->status;
                $array['maintenance'] = $site_record->hasMaintenanceActive();
                $site_details[] = $array;
            }
        }

        return $site_details;
    }

    /**
     * Get Upcoming Tasks for given Trade
     */
    public function getUpcomingTasks($date)
    {
        if (!$date) $date = Carbon::now()->startOfWeek()->format('Y-m-d');

        $trade_list = Auth::user()->company->tradeListPlanner()->pluck('id')->toArray();
        $tasks = Task::whereIn('trade_id', $trade_list)->where('upcoming', '1')->where('status', '1')->orderBy('name')->get();

        // Upcoming Task Categories
        $task_cats = [];
        $task_list = [];
        foreach ($tasks as $task) {
            $array = [];
            $array['id'] = $task->id;
            $array['name'] = $task->name;
            $array['code'] = $task->code;
            $array['trade_id'] = $task->trade_id;
            $task_cats[] = $array;
            $task_list[] = $task->id;
        }

        // Upcoming Task items
        $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $date_from->addDays(7);
        $date_to->addDays(77); // 10 weeks view ahead

        $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
            ->where('from', '>=', $date_from->format('Y-m-d'))->where('from', '<=', $date_to->format('Y-m-d'))
            ->whereIn('task_id', $task_list)->orderBy('entity_type', 'desc')->orderBy('from')->get();

        $task_plan = [];
        foreach ($planner as $plan)
            $task_plan[] = $this->getPlanData($plan);

        $json = [];
        $json[] = $task_cats;
        $json[] = $task_plan;

        return $json;
    }

    /**
     * Get Companies with Specific Trade  - used for Trade Planner
     */
    public function getCompaniesWithTrade(Request $request, $trade_id)
    {
        $company_list = Auth::user()->company->companies('1')->pluck('id')->toArray();
        $companies = Company::select(['companys.id', 'companys.name'])->join('company_trade', 'companys.id', '=', 'company_trade.company_id')
            ->where('companys.status', '1')->where('company_trade.trade_id', $trade_id)
            ->whereIn('companys.id', $company_list)->orderBy('name')->get();
        /*$companies = Company::join('company_trade', 'companys.id', '=', 'company_trade.company_id')
            ->where('companys.status', '1')->where('company_trade.trade_id', $trade_id)
            ->whereIn('companys.id', $company_list)->orderBy('name')->get();*/

        $array = [];
        // Create array in specific Vuejs 'select' format.
        foreach ($companies as $company) {
            $c = Company::find($company->id);
            $array[] = ['entity' => 'c.' . $company->id, 'type' => 'c', 'id' => $company->id, 'name' => $c->name_alias, 'compliant' => ($c->isCompliant()) ? 1 : 0];
        }

        //dd($array);
        return $array;
    }


    /**
     * Get Companies options for 'select' dropdown in Vuejs format
     */
    public function getCompanies($company_id, $trade_id, $site_id)
    {
        $company_list = Auth::user()->company->companies('1')->pluck('id')->toArray();
        //$company_list = Company::where('parent_company', Auth::user()->company_id)->pluck('id')->toArray();
        //$company_list[] = Auth::user()->company_id;

        if ($company_id == 'match-trade' || $trade_id == 'match-trade') {
            //if ($trade_id == 'match-trade')
            // All authorised companies that have the given trade_id
            $companies = Company::select(['companys.id', 'companys.name'])->join('company_trade', 'companys.id', '=', 'company_trade.company_id')
                ->where('companys.status', '1')->where('company_trade.trade_id', $trade_id)
                ->whereIn('companys.id', $company_list)->orderBy('name')->get();
            /*$companies = Company::join('company_trade', 'companys.id', '=', 'company_trade.company_id')
                ->where('companys.status', '1')->where('company_trade.trade_id', $trade_id)
                ->whereIn('companys.id', $company_list)->orderBy('name')->get();*/
        } else if ($company_id == 'all') {
            // All authorised companies
            $companies = Company::where('status', '1')->whereIn('id', $company_list)->orderBy('name')->get();
        } else {
            // All authorised companies except the given company_id
            $companies = Company::where('status', '1')->whereIn('id', $company_list)->where('id', '<>', $company_id)->orderBy('name')->get();
        }

        // Unique array of companies currently on planner for given site_id
        $companiesOnPlanner = SitePlanner::distinct()->select('entity_id')
            ->where('site_id', $site_id)->where('entity_type', 'c')
            ->groupBy('entity_id')->pluck('entity_id')->toArray();

        $array = [];
        $array[] = ['value' => '', 'text' => 'Select company'];
        if ($company_id == 'match-trade')
            $array[] = ['value' => 'gen', 'text' => 'Unassigned (Generic)'];
        // Create array in specific Vuejs 'select' format.
        foreach ($companies as $company) {
            $c = Company::find($company->id);
            $text = $company->name_alias;
            if (in_array($company->id, $companiesOnPlanner))
                $text = '<b>' . $company->name_alias . '</b>';

            $array[] = ['value' => $company->id, 'text' => $text, 'name' => $c->name_alias];
        }

        //dd($array);

        return $array;
    }

    /**
     * Get Company Trades options for 'select' dropdown in Vuejs format
     */
    public function getCompanyTrades(Request $request, $company_id)
    {
        $company = Company::findOrFail($company_id);

        $array = [];
        $array[] = ['value' => '', 'text' => 'Select trade'];
        // Create array in specific Vuejs 'select' format.
        $trade_count = count($company->tradesSkilledIn);
        foreach ($company->tradesSkilledIn as $trade) {
            $array[] = ['value' => $trade->id, 'text' => $trade->name, 'name' => $trade->name];
        }

        return $array;
    }

    /**
     * Get Company Tasks options for 'select' dropdown in Vuejs format
     */
    public function getCompanyTasks(Request $request, $company_id, $trade_id)
    {
        $company = Company::findOrFail($company_id);

        //echo "tradeid:".$trade_id.'<br>';
        $array = [];
        $array[] = ['value' => '', 'text' => 'Select task'];
        // Create array in specific Vuejs 'select' format.
        $trade_count = count($company->tradesSkilledIn);
        foreach ($company->tradesSkilledIn as $trade) {
            $tasks = Task::where('trade_id', '=', $trade->id)->orderBy('name')->get();
            foreach ($tasks as $task) {
                if ($task->status) {
                    $text = $task->name;
                    //echo $task->name. ' ['.$task->trade_id.']';
                    // If Trade_id supplied then only return tasks for that trade
                    // - used for companies that have multiple trades
                    if ($trade_id != 'all') {
                        if ($trade_id != $trade->id)
                            continue;
                    } else {
                        if ($trade_count > 1)
                            $text = $trade->name . ':' . $task->name;
                    }

                    $array[] = [
                        'value'      => $task->id,
                        'text'       => $text,
                        'name'       => $task->name,
                        'code'       => $task->code,
                        'trade_id'   => $trade->id,
                        'trade_name' => $trade->name,
                    ];
                    //print_r($array);
                }
            }
        }

        return $array;
    }

    /**
     * Get Trades options for 'select' dropdown in Vuejs format
     */
    public function getTrades(Request $request)
    {
        $trades = Trade::where('status', '1')->where(function ($q) {
            $q->where('company_id', Auth::user()->company_id);
            $q->orWhere('company_id', 1);
        })->orderBy('name')->get();
        $array = [];
        $array[] = ['value' => '', 'text' => 'Select trade'];
        // Create array in specific Vuejs 'select' format.
        foreach ($trades as $trade) {
            $array[] = ['value' => $trade->id, 'text' => $trade->name, 'name' => $trade->name,];
        }

        return $array;
    }

    /**
     * Get Trades -> Tasks options for 'select' dropdown in Vuejs format
     */
    public function getTradeTasks(Request $request, $trade_id)
    {
        $tasks = Task::where('trade_id', '=', $trade_id)->where('status', '1')->orderBy('name')->get();
        $trade = Trade::find($trade_id);
        $array = [];
        $array[] = ['value' => '', 'text' => 'Select task'];
        // Create array in specific Vuejs 'select' format.
        foreach ($tasks as $task) {
            $array[] = [
                'value'      => $task->id,
                'text'       => $task->name,
                'name'       => $task->name,
                'code'       => $task->code,
                'trade_id'   => $trade->id,
                'trade_name' => $trade->name
            ];
        }

        return $array;
    }


    /**
     * Get Sites that given Company is planned for on specified date
     */
    public function getCompanySitesOnDate(Request $request, $company_id, $site_id, $date, $json = 'json')
    {
        $planner = SitePlanner::where('site_id', '<>', $site_id)
            ->where('entity_type', 'c')
            ->where('entity_id', $company_id)
            ->whereDate('from', '<=', $date)
            ->whereDate('to', '>=', $date)
            ->get();

        if ($company_id == '59') {
            //echo "cid:59 site_id:$site_id date:$date json:$json<br>";
            //var_dump($planner);
        }

        $sites = [];
        foreach ($planner as $plan) {
            $array = [];

            $task_code = '';
            if ($plan->task_id) {
                $task = Task::find($plan->task_id);
                $task_code = $task->code;
            }

            if (array_key_exists($plan->site_id, $sites)) {
                $sites[$plan->site_id] .= ', ' . $task_code;
            } else {
                $site = Site::find($plan->site_id);
                $sites[$plan->site_id] = substr($site->name, 0, 8) . ' - ' . $task_code;
            }
        }

        $str = '';
        if ($sites) {
            foreach ($sites as $key => $value) {
                if ($json == 'json')
                    $str .= $value . ', ';
                else
                    $str .= $value . '<br>';
            }

            if ($json == 'json')
                $str = rtrim($str, ', ');
            else
                $str = rtrim($str, '<br>');
        }

        if ($json == 'json')
            return $str; //json_encode($str);
        else
            return $str;
    }

    /**
     * Get List of Job Starts Without Job Starts options for 'select' dropdown in Vuejs format
     */
    public function getJobStarts(Request $request, $exists)
    {
        $today = Carbon::now();
        $allowedSites = Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();
        $sites = Site::whereIn('id', $allowedSites)->where('status', '1')->orderBy('name')->get();

        //$startJobIDs = Task::where('code', 'START')->where('status', '1')->pluck('id')->toArray();
        $with = [];
        $without = [];
        $with[] = ['value' => '', 'text' => 'Select site'];
        $without[] = ['value' => '', 'text' => 'Select site'];
        // Create array in specific Vuejs 'select' format.
        foreach ($sites as $site) {
            if (!$site->job_start)
                $without[] = ['value' => $site->id, 'text' => $site->name, 'name' => $site->name];
            else if ($site->job_start->gt($today))
                $with[] = ['value' => $site->id, 'text' => $site->name . ' - ' . $site->job_start->format('d/m/Y'), 'name' => $site->name];
        }

        return ($exists == 'true') ? $with : $without;
    }

    /**
     * Get List of Site With Job Starts options for 'select' dropdown in Vuejs format
     */
    public function getSitesWithStart(Request $request)
    {

        $allowedSites = Auth::user()->company->reportsTo()->sites('1')->pluck('id')->toArray();
        $sites = Site::whereIn('id', $allowedSites)->where('status', '1')->orderBy('name')->get();

        $startJobIDs = Task::where('code', 'START')->where('status', '1')->pluck('id')->toArray();
        $array = [];
        $array[] = ['value' => '', 'text' => 'Select site'];
        // Create array in specific Vuejs 'select' format.
        foreach ($sites as $site) {
            $planner = SitePlanner::select(['id', 'site_id', 'task_id',])->where('site_id', $site->id)->orderBy('from')->get();

            $found = false;
            foreach ($planner as $plan) {
                if (in_array($plan->task_id, $startJobIDs)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $array[] = ['value' => $site->id, 'text' => $site->name, 'name' => $site->name];
            }
        }

        return $array;
    }

    /**
     * Get List of Site Without Supervisor options for 'select' dropdown in Vuejs format
     */
    public function getSitesWithoutSuper(Request $request)
    {

        $allowedSites = Auth::user()->company->reportsTo()->sites()->pluck('id')->toArray();
        $sites = Site::whereIn('id', $allowedSites)->where('status', '<>', '0')->orderBy('name')->get();

        $array = [];
        $array[] = ['value' => '', 'text' => 'Select site'];
        // Create array in specific Vuejs 'select' format.
        $tobeallocated = User::find(136);
        foreach ($sites as $site) {
            if ($site->isUserSupervisor($tobeallocated))
                $array[] = ['value' => $site->id, 'text' => $site->name, 'name' => $site->name];
        }

        return $array;
    }

    /**
     * Get List of Site Without Job Starts options for 'select' dropdown in Vuejs format
     */
    public function getPlannerForWeek($date_from, $date_to, $allowedSites, $excludeTasks)
    {
        if (Auth::user()->company->subscription) {
            $allowedCompanies = Auth::user()->company->companies()->pluck('id')->toArray();

            return SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                // Tasks that start 'from' between mon-fri of given week
                ->where(function ($q) use ($date_from, $date_to, $allowedSites, $allowedCompanies, $excludeTasks) {
                    $q->where('from', '>=', $date_from->format('Y-m-d'));
                    $q->Where('from', '<=', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    //$q->whereIn('entity_id', $allowedCompanies);
                    $q->whereNotIn('task_id', $excludeTasks);
                })
                // Tasks that end 'to between mon-fri of given week
                ->orWhere(function ($q) use ($date_from, $date_to, $allowedSites, $allowedCompanies, $excludeTasks) {
                    $q->where('to', '>=', $date_from->format('Y-m-d'));
                    $q->Where('to', '<=', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    //$q->whereIn('entity_id', $allowedCompanies);
                    $q->whereNotIn('task_id', $excludeTasks);
                })
                // Tasks that start before mon but end after fri
                // ie they span the whole week but begin prior + end after given week
                ->orWhere(function ($q) use ($date_from, $date_to, $allowedSites, $allowedCompanies, $excludeTasks) {
                    $q->where('from', '<', $date_from->format('Y-m-d'));
                    $q->Where('to', '>', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    //$q->whereIn('entity_id', $allowedCompanies);
                    $q->whereNotIn('task_id', $excludeTasks);
                })
                ->orderBy('from')->get();
        } else {

            $allowedCompanies = [Auth::user()->company_id];
            // Hack to allow Split Companies NRW (57,202,255) + Solid Foundations (120,121) to see their other Sites
            if (in_array(Auth::user()->company_id, [57, 202, 255])) {
                $c1 = Company::find(57)->companies()->pluck('id')->toArray();
                $c2 = Company::find(202)->companies()->pluck('id')->toArray();
                $c3 = Company::find(255)->companies()->pluck('id')->toArray();
                $allowedCompanies = array_merge($c1, $c2, $c3);
            }
            if (in_array(Auth::user()->company_id, [120, 121])) {
                $c1 = Company::find(120)->companies()->pluck('id')->toArray();
                $c2 = Company::find(121)->companies()->pluck('id')->toArray();
                $allowedCompanies = array_merge($c1, $c2);
            }

            return SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                // Tasks that start 'from' between mon-fri of given week
                ->where(function ($q) use ($date_from, $date_to, $allowedSites, $excludeTasks, $allowedCompanies) {
                    $q->where('from', '>=', $date_from->format('Y-m-d'));
                    $q->Where('from', '<=', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    $q->whereNotIn('task_id', $excludeTasks);
                    $q->where('entity_type', 'c');
                    $q->whereIn('entity_id', $allowedCompanies);
                })
                // Tasks that end 'to between mon-fri of given week
                ->orWhere(function ($q) use ($date_from, $date_to, $allowedSites, $excludeTasks, $allowedCompanies) {
                    $q->where('to', '>=', $date_from->format('Y-m-d'));
                    $q->Where('to', '<=', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    $q->whereNotIn('task_id', $excludeTasks);
                    $q->where('entity_type', 'c');
                    $q->whereIn('entity_id', $allowedCompanies);
                })
                // Tasks that start before mon but end after fri
                // ie they span the whole week but begin prior + end after given week
                ->orWhere(function ($q) use ($date_from, $date_to, $allowedSites, $excludeTasks, $allowedCompanies) {
                    $q->where('from', '<', $date_from->format('Y-m-d'));
                    $q->Where('to', '>', $date_to->format('Y-m-d'));
                    $q->whereIn('site_id', $allowedSites);
                    $q->whereNotIn('task_id', $excludeTasks);
                    $q->where('entity_type', 'c');
                    $q->whereIn('entity_id', $allowedCompanies);
                })
                ->orderBy('from')->get();
        }
    }

    /**
     * Email Jobstart
     */
    public function emailJobstart()
    {
        $site = Site::find(request('site_id'));
        $newdate = Carbon::createFromFormat('Y-m-d H:i:s', request('newdate') . ' 00:00:00')->format('d/m/Y');
        $olddate = (request('olddate')) ? Carbon::createFromFormat('Y-m-d H:i:s', request('olddate') . ' 00:00:00')->format('d/m/Y') : null;
        $supers = $site->supervisorsSBC();

        if ($site->company->notificationsUsersType('site.jobstart'))
            Mail::to($site->company->notificationsUsersType('site.jobstart'))->send(new \App\Mail\Site\Jobstart($site, $newdate, $olddate, $supers));

    }

    /**
     * Update Site Status
     */
    public function updateSiteStatus($site_id, $status)
    {
        $site = Site::find($site_id);

        // Move from Pre-construction to Active
        if ($site->status == '-1' && $status == 1) {
            $site->status = 1;
            $site->save();

            // Email JobStart if it has one
            if ($site->job_start) {
                $supers = $site->supervisorsSBC();
                $date = $site->job_start->format('d/m/Y');

                if ($site->company->notificationsUsersType('site.jobstart'))
                    Mail::to($site->company->notificationsUsersType('site.jobstart'))->send(new \App\Mail\Site\Jobstart($site, $date, null, $supers));
            }

            // Create New Project Supply
            $project = SiteProjectSupply::where('site_id', $site->id)->first();
            if (!$project) {
                $project = SiteProjectSupply::create(['site_id' => $site->id, 'version' => '1.0']);
                $project->initialise();
                $project->createReviewToDo($project->site->supervisors->pluck('id')->toArray());
            }

            return redirect("/planner/site/$site->id");
        }

        // Move from Pre-construction to Cancelled
        if ($site->status == '-1' && $status == '-2') {
            $site->status = '-2';
            $site->save();
            Toastr::error("Site Cancelled");

            return redirect("/planner/preconstruction");
        }

        // Move from Active (prior Jobstart) to Pre-construction
        if ($site->status == 1 && $status == 0) {
            $site->status = '-1';
            $site->save();

            return redirect("/planner/preconstruction/$site->id");
        }
    }
}
