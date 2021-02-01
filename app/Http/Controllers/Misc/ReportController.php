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
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocCategory;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Action;
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

    public function newusers()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = \App\User::where('created_at', '>', '2016-08-27 12:00:00')->whereIn('id', $allowed_users)->orderBy('created_at', 'DESC')->get();

        return view('manage/report/newusers', compact('users'));
    }

    public function newcompanies()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = \App\Models\Company\Company::where('created_at', '>', '2016-08-27 12:00:00')->whereIn('id', $allowed_companies)->orderBy('created_at', 'DESC')->get();

        return view('manage/report/company_contactinfo', compact('companies'));
    }

    public function companyContactInfo()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = \App\Models\Company\Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company_contactinfo', compact('companies'));
    }

    public function companySWMS()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = \App\Models\Company\Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company_swms', compact('companies'));
    }

    public function users_noemail()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = \App\User::where('email', null)->where('status', 1)->whereIn('id', $allowed_users)->orderBy('company_id', 'ASC')->get();

        return view('manage/report/users_noemail', compact('users'));
    }

    public function users_nowhitecard()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $active_users = \App\User::where('status', 1)->whereIn('id', $allowed_users)->get();
        $users = [];
        $users_sorted = [];
        foreach ($active_users as $user)
            $users_sorted[$user->id] = $user->company->name_alias;

        asort($users_sorted);
        foreach ($users_sorted as $uid => $company) {
            $user = User::find($uid);
            if ($user && $user->requiresUserDoc(1) && !$user->activeUserDoc(1))
                $users[] = $user;
        }

        //dd($users);

        return view('manage/report/users_nowhitecard', compact('users'));
    }

    public function usersLastLogin()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = \App\User::where('status', 1)->whereIn('id', $allowed_users)->orderBy('company_id', 'ASC')->get();

        $date_1_week = Carbon::today()->subWeeks(1)->format('Y-m-d');
        $date_2_week = Carbon::today()->subWeeks(2)->format('Y-m-d');
        $date_3_week = Carbon::today()->subWeeks(3)->format('Y-m-d');
        $date_4_week = Carbon::today()->subWeeks(4)->format('Y-m-d');
        $date_3_month = Carbon::today()->subMonths(3)->format('Y-m-d');
        $date_6_month = Carbon::today()->subMonths(6)->format('Y-m-d');

        //echo "w1: $date_1_week w2:$date_2_week w3: $date_3_week: w4 $date_4_week m3: $date_3_month m6: $date_6_month<br>";

        $over_1_week = \App\User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_1_week)->wheredate('last_login', '>=', $date_2_week)->orderBy('company_id', 'ASC')->get();
        $over_2_week = \App\User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_2_week)->wheredate('last_login', '>=', $date_3_week)->orderBy('company_id', 'ASC')->get();
        $over_3_week = \App\User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_3_week)->wheredate('last_login', '>=', $date_4_week)->orderBy('company_id', 'ASC')->get();
        $over_4_week = \App\User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_4_week)->wheredate('last_login', '>=', $date_3_month)->orderBy('company_id', 'ASC')->get();
        $over_3_month = \App\User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_3_month)->wheredate('last_login', '>=', $date_6_month)->orderBy('company_id', 'ASC')->get();

        //echo $over_1_week->count().'<br>';
        //var_dump($over_1_week);
        //dd('stop');

        return view('manage/report/users_lastlogin', compact('users', 'over_1_week', 'over_2_week', 'over_3_week', 'over_4_week', 'over_3_month'));
    }

    public function roleusers()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = DB::table('role_user')->whereIn('user_id', $allowed_users)->orderBy('role_id')->get();

        return view('manage/report/roleusers', compact('users'));
    }

    public function usersExtraPermissions()
    {
        $permissions = DB::table('permission_user')->where('company_id', Auth::user()->company_id)->orderBy('user_id')->get();

        return view('manage/report/users_extra_permissions', compact('permissions'));
    }

    public function missingCompanyInfo()
    {
        $companies = \App\Models\Company\Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();

        return view('manage/report/missing_company_info', compact('companies'));
    }

    public function missingCompanyInfoCSV()
    {
        $companies = \App\Models\Company\Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();
        $csv = "Company, Missing Info / Document, Expiry / Last updated\r\n";

        foreach ($companies as $company) {
            if ($company->missingInfo())
                $csv .= "$company->name, " . $company->missingInfo() . ',' . $company->updated_at->format('d/m/Y') . "\r\n";
            if ($company->missingDocs()) {
                foreach ($company->missingDocs() as $type => $name) {
                    $doc = $company->expiredCompanyDoc($type);
                    $exp = ($doc != 'N/A') ? $doc->expiry->format('d/m/Y') : 'never';
                    $csv .= "$company->name, $name, $exp\r\n";
                }
            }
        }

        //echo $csv;
        $filename = '/filebank/tmp/' . Auth::user()->company_id . '/missing_company_info.csv';
        $bytes_written = File::put(public_path($filename), $csv);
        if ($bytes_written === false) die("Error writing to file");
        return redirect($filename);
    }


    public function nightly()
    {
        $files = array_reverse(array_diff(scandir(public_path('/filebank/log/nightly')), array('.', '..')));

        return view('manage/report/nightly', compact('files'));
    }

    public function companyUsers()
    {
        $companies_allowed = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $all_companies = \App\Models\Company\Company::where('status', '1')->whereIn('id', $companies_allowed)->orderBy('name')->get();
        $companies_list = DB::table('companys as c')->select(['c.id', 'c.name', 'u.company_id', 'c.updated_at', DB::raw('count(*) as users')])
            ->join('users as u', 'c.id', '=', 'u.company_id')
            ->where('u.status', '1')->whereIn('c.id', $companies_allowed)
            ->groupBy('u.company_id')->orderBy('users')->orderBy('name')->get();

        $user_companies = [];
        foreach ($companies_list as $c) {
            $company = \App\Models\Company\Company::find($c->id);

            $user_companies[] = (object) ['id'  => $company->id, 'name' => $company->name_both, 'users' => $c->users,
                                          'sec' => $company->securityUsers(1)->count(), 'pu' => $company->primary_user, 'su' => $company->secondary_user, 'updated_at' => $company->updated_at->format('d/m/Y')];

        }

        return view('manage/report/company_users', compact('all_companies', 'user_companies'));
    }

    public function companyPrivacy()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = \App\Models\Company\Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company_privacy', compact('companies'));
    }

    public function companyPrivacySend()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = \App\Models\Company\Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        $sent_to_company = [];
        $sent_to_user = [];
        foreach ($companies as $company) {
            if (!$company->activeCompanyDoc(12)) {
                $todo = Todo::where('type', 'company privacy')->where('type_id', $company->id)->where('status', '1')->first();
                if (!$todo) {
                    // Create ToDoo
                    $todo_request = [
                        'type'       => 'company privacy',
                        'type_id'    => $company->id,
                        'name'       => 'Cape Cod Privacy Policy Sign Off',
                        'info'       => 'Please read and sign you have read Cape Cod Privacy Policy',
                        'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
                        'company_id' => 3,
                    ];

                    if ($company->primary_user) {
                        // Create ToDoo and assign to Primary User
                        $todo = Todo::create($todo_request);
                        $todo->assignUsers($company->primary_user);
                        $todo->emailToDo();

                        $sent_to_user[$company->id] = $company->primary_contact()->fullname;
                        $sent_to_company[$company->id] = $company->name;
                        if ($company->nickname)
                            $sent_to_company[$company->id] .= "<span class='font-grey-cascade'><br>$company->nickname</span>";
                    }

                }

            }
        }

        return view('manage/report/company_privacy_send', compact('companies', 'sent_to_company', 'sent_to_user'));
    }

    /*
     * Payroll Report
     */
    public function payroll()
    {
        $companies = \App\Models\Company\Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();
        $companies = Auth::user()->company->companies();

        return view('manage/report/payroll', compact('companies'));
    }

    /*
     * Equipment List Report
     */
    public function equipment()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        return view('manage/report/equipment', compact('equipment'));
    }

    /**
     * Equipment List PDF
     */
    public function equipmentPDF()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment List " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        //return view('pdf/equipment', compact('equipment'));
        //return PDF::loadView('pdf/equipment', compact('equipment'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentPdf::dispatch($equipment, $output_file);

        return redirect('/manage/report/recent');
    }

    /*
     * Equipment List Report
     */
    public function equipmentSite()
    {
        // Store + Other Sites
        $locations = [1 => 'other'];
        $locations_other = EquipmentLocation::where('site_id', null)->where('status', 1)->orderBy('other')->pluck('id')->toArray();
        foreach ($locations_other as $loc)
            $locations[$loc] = 'other';

        // Locations without supervisors
        $sites_without_super = [];
        $active_sites = Site::where('status', 1)->where('company_id', 3)->get();
        foreach ($active_sites as $site) {
            if (!$site->supervisorsSBC())
                $sites_without_super[] = $site->id;
        }
        $locations_nosuper = EquipmentLocation::whereIn('site_id', $sites_without_super)->where('status', 1)->pluck('id')->toArray();
        foreach ($locations_nosuper as $loc)
            $locations[$loc] = 'no-super';

        // Locations with super
        $supervisors = Company::find(3)->supervisors()->sortBy('lastname');
        foreach ($supervisors as $super) {
            $sites = $super->supervisorsSites()->sortBy('code')->pluck('id')->toArray();
            foreach ($sites as $site) {
                $location = EquipmentLocation::where('site_id', $site)->where('status', 1)->where('site_id', '<>', 25)->first();
                if ($location)
                    $locations[$location->id] = $super->name;
            }
        }

        //dd($locations);

        return view('manage/report/equipment-site', compact('locations'));
    }

    /**
     * Equipment List PDF
     */
    public function equipmentSitePDF()
    {
        // Store + Other Sites
        $locations = [1 => 'other'];
        $locations_other = EquipmentLocation::where('site_id', null)->where('status', 1)->orderBy('other')->pluck('id')->toArray();
        foreach ($locations_other as $loc)
            $locations[$loc] = 'other';

        // Locations without supervisors
        $sites_without_super = [];
        $active_sites = Site::where('status', 1)->where('company_id', 3)->get();
        foreach ($active_sites as $site) {
            if (!$site->supervisorsSBC())
                $sites_without_super[] = $site->id;
        }
        $locations_nosuper = EquipmentLocation::whereIn('site_id', $sites_without_super)->where('status', 1)->pluck('id')->toArray();
        foreach ($locations_nosuper as $loc)
            $locations[$loc] = 'no-super';

        // Locations with super
        $supervisors = Company::find(3)->supervisors()->sortBy('lastname');
        foreach ($supervisors as $super) {
            $sites = $super->supervisorsSites()->sortBy('code')->pluck('id')->toArray();
            foreach ($sites as $site) {
                $location = EquipmentLocation::where('site_id', $site)->where('status', 1)->where('site_id', '<>', 25)->first();
                if ($location)
                    $locations[$location->id] = $super->name;
            }
        }

        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment List By Site " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        //return view('pdf/equipment-site', compact('locations'));
        //return PDF::loadView('pdf/equipment-site', compact('locations'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentSitePdf::dispatch($locations, $output_file);

        return redirect('/manage/report/recent');
    }

    /*
     * Equipment Transaction Report
     */
    public function equipmentTransactions()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        return view('manage/report/equipment-transactions', compact('equipment'));
    }

    /**
     * Equipment Transaction PDF
     */
    public function equipmentTransactionsPDF()
    {

        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');
        $transactions = EquipmentLog::whereDate('equipment_log.created_at', '>=', $date_from)->whereDate('equipment_log.created_at', '<=', $date_to)->get();

        //dd($date_from);
        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment Transactions " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        $from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00') : Carbon::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00');
        $to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00') : Carbon::tomorrow();

        //return view('pdf/equipment-transactions', compact('transactions', 'from', 'to'));
        //return PDF::loadView('pdf/equipment-transactions', compact('transactions', 'from', 'to'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentTransactionsPdf::dispatch($transactions, $from, $to, $output_file);

        return redirect('/manage/report/recent');
    }

    /**
     * Get Site Attendance user is authorise to view
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentTransactions()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');
        $actions = ['P', 'D', 'W'];

        $transactions = EquipmentLog::whereDate('created_at', '>=', $date_from)->whereDate('created_at', '<=', $date_to);
        $transactions = EquipmentLog::select([
            'equipment_log.id', 'equipment_log.equipment_id', 'equipment_log.qty', 'equipment_log.action', 'equipment_log.notes', 'equipment_log.created_at',
            'equipment.id', 'equipment.name', 'users.id', 'users.username', 'users.firstname', 'users.lastname',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('equipment', 'equipment.id', '=', 'equipment_log.equipment_id')
            ->join('users', 'users.id', '=', 'equipment_log.created_by')
            ->whereDate('equipment_log.created_at', '>=', $date_from)
            ->whereDate('equipment_log.created_at', '<=', $date_to)
            ->whereIn('equipment_log.action', $actions);


        //dd($transactions);
        $dt = Datatables::of($transactions)
            ->editColumn('created_at', function ($trans) {
                return $trans->created_at->format('d/m/Y');
            })
            ->editColumn('action', function ($trans) {
                if ($trans->action == 'P') return 'Purchase';
                if ($trans->action == 'D') return 'Disposal';
                if ($trans->action == 'W') return 'Write Off';
                if ($trans->action == 'N') return 'New Item';

                return $trans->action;
            })
            ->rawColumns(['full_name', 'created_at'])
            ->make(true);

        return $dt;
    }

    /*
    * Equipment Stocktake Report
    */
    public function equipmentStocktake()
    {
        //$equipment = EquipmentStocktake::all()->orderBy('name')->get();
        $equipment = '';

        return view('manage/report/equipment-stocktake', compact('equipment'));
    }

    /**
     * Get Equipment Stocktake
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentStocktake()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $stocktake = EquipmentStocktake::select([
            'equipment_stocktake.id', 'equipment_stocktake.location_id', 'equipment_stocktake.created_at', 'equipment_location.id AS loc_id', 'equipment_location.site_id', 'equipment_location.other',
            'users.id AS uid', 'users.username', 'users.firstname', 'users.lastname',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('equipment_location', 'equipment_stocktake.location_id', '=', 'equipment_location.id')
            ->join('users', 'users.id', '=', 'equipment_stocktake.created_by')
            ->whereDate('equipment_stocktake.created_at', '>=', $date_from)
            ->whereDate('equipment_stocktake.created_at', '<=', $date_to);

        //dd($stocktake);
        $dt = Datatables::of($stocktake)
            ->editColumn('created_at', function ($stock) {
                return "<a href='/equipment/stocktake/view/$stock->id' target=_blank >" . $stock->created_at->format('d/m/Y') . "</a>";
            })
            ->addColumn('location', function ($stock) {
                return $stock->name;
            })
            ->addColumn('summary', function ($stock) {
                return $stock->summary();
            })
            ->rawColumns(['full_name', 'created_at', 'summary'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Equipment Stocktake Not
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentStocktakeNot()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $stocktake = EquipmentStocktake::whereDate('equipment_stocktake.created_at', '>=', $date_from)->whereDate('equipment_stocktake.created_at', '<=', $date_to)
            ->pluck('location_id')->toArray();
        $locations = EquipmentLocation::where('status', 1)->whereNotIn('id', $stocktake)->get();

        $location_names = [];
        foreach ($locations as $loc)
            $location_names[$loc->id] = $loc->name5;

        asort($location_names);
        //dd($location_names);

        $objects = [];
        foreach ($location_names as $id => $name)
            $objects[] = (object) array('id' => $id, 'name' => $name);

        //dd($objects);
        //dd($transactions);
        $dt = Datatables::of($objects)
            ->editColumn('id', '<div class="text-center"><a href="/equipment/stocktake/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('name', function ($location) {
                return $location->name;
            })
            ->rawColumns(['id', 'name'])
            ->make(true);

        return $dt;
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

    /*
     * Expired Company Docs Report
     */
    public function expiredCompanyDocs()
    {
        return view('manage/report/expired_company_docs');
    }

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

    /**
     * Get Expired Company Docs user is authorise to view
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getExpiredCompanyDocs()
    {
        $company_id = (request('company_id') == 'all') ? '' : request('company_id');
        $company_ids = ($company_id) ? [$company_id] : Auth::user()->company->companies()->pluck('id')->toArray();
        $compliance = (request('compliance')) ? request('compliance') : 'all';

        $today = Carbon::today();
        $days_30 = $today->addDays(30)->format('Y-m-d');

        /* Filter Department + Categories */
        $categories = (request('category_id') == 'ALL') ? array_keys(Auth::user()->companyDocTypeSelect('view', Auth::user()->company)) : [request('category_id')];
        if (request('department') != 'all') {
            $filtered = [];
            if ($categories) {
                foreach ($categories as $cat) {
                    $category = CompanyDocCategory::find($cat);
                    if ($category && $category->type == request('department'))
                        $filtered[] = $cat;
                }
                $categories = $filtered;
            }
        }

        //dd($categories);
        $company_docs = CompanyDoc::whereIn('for_company_id', $company_ids)
            ->whereIn('category_id', $categories)
            ->whereDate('expiry', '<=', $days_30)
            ->where('for_company_id', '<>', 3)
            ->orderByDesc('expiry')
            ->get();

        //dd($company_docs->get());
        $expired_docs = [];
        $expired_docs_company_cat = [];
        foreach ($company_docs as $doc) {
            $req = ($doc->company->requiresCompanyDoc($doc->category_id)) ? 'req' : 'add';
            if ($doc->company->status) {
                $exp = 'Replaced';
                if ($compliance == 'all' || $compliance == $req) {
                    if (!$doc->company->activeCompanyDoc($doc->category_id) && !in_array("$doc->for_company_id:$doc->category_id", $expired_docs_company_cat)) {
                        $expired_docs[] = $doc->id;
                        $expired_docs_company_cat[] = "$doc->for_company_id:$doc->category_id";
                        $exp = 'Expired';
                    } elseif ($doc->expiry->gte(Carbon::today()) && !in_array("$doc->for_company_id:$doc->category_id", $expired_docs_company_cat)) {
                        $expired_docs[] = $doc->id;
                        $expired_docs_company_cat[] = "$doc->for_company_id:$doc->category_id";
                        $exp = 'Near Expiry';
                    }
                    //echo "[$doc->id] " . $doc->company->name . " - $doc->name ($doc->category_id) $exp $req<br>";
                }
            }
        }
        //dd($expired_docs);

        $expired_docs = CompanyDoc::select([
            'company_docs.id', 'company_docs.category_id', 'company_docs.name', 'company_docs.expiry',
            'company_docs.for_company_id', 'company_docs.company_id', 'company_docs.attachment', 'company_docs.status',
            'companys.status',
        ])
            ->join('companys', 'company_docs.for_company_id', '=', 'companys.id')
            ->whereIn('company_docs.id', $expired_docs)
            ->where('companys.status', 1);
        //->whereDate('company_docs.expiry', '>=', $date_from)
        //->whereDate('company_docs.expiry', '<=', $date_to);


        //dd($expired_docs->get());
        $dt = Datatables::of($expired_docs)
            ->editColumn('company_docs.id', function ($doc) {
                return ($doc->attachment) ? '<div class="text-center"><a href="' . $doc->attachment_url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>' : '';
            })
            ->editColumn('category_id', function ($doc) {
                return strtoupper($doc->category->type);
            })
            ->editColumn('companys.name', function ($doc) {
                return '<a href="/company/' . $doc->for_company_id . '/doc">' . $doc->company->name . '</a>';
            })
            ->editColumn('company_docs.name', function ($doc) {
                return ($doc->company->requiresCompanyDoc($doc->category_id)) ? $doc->name : "<span class='font-yellow-crusta'>$doc->name</span>";
            })
            ->editColumn('expiry', function ($doc) {
                $now = Carbon::now();
                $yearago = $now->subYear()->toDateTimeString();

                //if ($doc->updated_at < $yearago && Auth::user()->isCC())
                return ($doc->expiry->lt(Carbon::today())) ? "<span class='font-red'>" . $doc->expiry->format('d/m/Y') . "</span>" : $doc->expiry->format('d/m/Y');
            })
            ->rawColumns(['company_docs.id', 'full_name', 'companys.name', 'company_docs.name', 'expiry'])
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

}
