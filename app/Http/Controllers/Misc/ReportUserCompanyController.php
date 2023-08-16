<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use File;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocCategory;
use App\Models\Misc\Permission2;
use App\Models\Misc\Role2;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Jobs\CompanyMissingInfoCsv;
use App\Jobs\CompanyMissingInfoPlannerCsv;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;

class ReportUserCompanyController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /******************************
     * User Reports
     *****************************/
    public function newusers()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::where('created_at', '>', '2016-08-27 12:00:00')->whereIn('id', $allowed_users)->orderBy('created_at', 'DESC')->get();

        return view('manage/report/user/newusers', compact('users'));
    }


    public function users_noemail()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::where('email', null)->where('status', 1)->whereIn('id', $allowed_users)->orderBy('company_id', 'ASC')->get();

        return view('manage/report/user/users_noemail', compact('users'));
    }

    public function users_nowhitecard()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $active_users = User::where('status', 1)->whereIn('id', $allowed_users)->get();
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

        return view('manage/report/user/users_nowhitecard', compact('users'));
    }

    public function users_nowhitecardCSV()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::whereIn('id', $allowed_users)->orderBy('company_id')->get();
        $csv = "Username, User, Company, Email\r\n";

        foreach ($users as $user) {
            if ($user && $user->requiresUserDoc(1) && !$user->activeUserDoc(1)) {
                $csv .= "$user->username, ";
                $csv .= "$user->full_name, ";
                $csv .= $user->company->name_alias . ", ";
                $csv .= "$user->email, ";
                $csv .= "\r\n";
            }
        }

        //echo $csv;
        $filename = '/filebank/tmp/' . Auth::user()->company_id . '/users_no_whitecard.csv';
        $bytes_written = File::put(public_path($filename), $csv);
        if ($bytes_written === false) die("Error writing to file");

        return redirect($filename);
    }

    public function usersLastLogin()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::where('status', 1)->whereIn('id', $allowed_users)->orderBy('company_id', 'ASC')->get();

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

        return view('manage/report/user/users_lastlogin', compact('users', 'over_1_week', 'over_2_week', 'over_3_week', 'over_4_week', 'over_3_month'));
    }

    public function usersLastLoginCSV()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::where('status', 1)->whereIn('id', $allowed_users)->orderBy('company_id', 'ASC')->get();
        $date_1_week = Carbon::today()->subWeeks(1)->format('Y-m-d');
        $over_1_week = User::where('status', 1)->whereIn('id', $allowed_users)
            ->wheredate('last_login', '<', $date_1_week)->orderBy('company_id', 'ASC')->get();
        $csv = "Username, Name, Company, Company on Planner, Last Login Date\r\n";

        //dd($users);

        foreach ($over_1_week as $user) {
            $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner() : Carbon::now()->subYears(10);
            if (in_array($user->company->category, [1]) && $user->company->status == 1 && $user->last_login->lt($user->company->lastDateOnPlanner()) && $user->hasAnyRole2('ext-leading-hand|tradie|labourers')) {
                $csv .= "$user->username, ";
                $csv .= "$user->full_name, ";
                $csv .= $user->company->name_alias . ", ";
                $csv .= $lastDate->format('d/m/Y') . ", ";
                $csv .= $user->last_login->format('d/m/Y') . ", ";
                $csv .= "\r\n";
            }
        }

        foreach ($users as $user) {
            $lastDate = ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner()->format('d/m/Y') : 'Never';
            if (in_array($user->company->category, [1]) && $user->company->status == 1 && (!$user->last_login || $user->last_login->format('d/m/Y') == '30/11/-0001')) {
                $csv .= "$user->username, ";
                $csv .= "$user->full_name, ";
                $csv .= $user->company->name_alias . ", ";
                $csv .= "$lastDate, ";
                $csv .= "Never, ";
                $csv .= "\r\n";
            }
        }

        //echo $csv;
        $filename = '/filebank/tmp/' . Auth::user()->company_id . '/users_lastlogin.csv';
        $bytes_written = File::put(public_path($filename), $csv);
        if ($bytes_written === false) die("Error writing to file");

        return redirect($filename);
    }

    public function usersContactInfo()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::whereIn('id', $allowed_users)->orderBy('company_id')->get();

        return view('manage/report/user/users_contactinfo', compact('users'));
    }

    public function usersContactInfoCSV()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = User::whereIn('id', $allowed_users)->orderBy('company_id')->get();
        $csv = "User, Company, Email, Phone\r\n";

        foreach ($users as $user) {
            $csv .= "$user->full_name, ";
            $csv .= $user->company->name . ", ";
            $csv .= "$user->email, ";
            $csv .= "$user->phone, ";
            $csv .= "\r\n";
        }

        //echo $csv;
        $filename = '/filebank/tmp/' . Auth::user()->company_id . '/users_contactinfo.csv';
        $bytes_written = File::put(public_path($filename), $csv);
        if ($bytes_written === false) die("Error writing to file");

        return redirect($filename);
    }

    /******************************
     * Company Reports
     *****************************/

    public function newcompanies()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::where('created_at', '>', '2016-08-27 12:00:00')->whereIn('id', $allowed_companies)->orderBy('created_at', 'DESC')->get();

        return view('manage/report/company/company_contactinfo', compact('companies'));
    }

    public function companyContactInfo()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company/company_contactinfo', compact('companies'));
    }

    public function companyContactInfoCSV()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::whereIn('id', $allowed_companies)->orderBy('name')->get();
        $csv = "Company, Phone, Email, Primary Contact, Trades\r\n";

        foreach ($companies as $company) {
            $csv .= "$company->name, ";
            $csv .= ($company->primary_user && $company->primary_contact()->phone) ? $company->primary_contact()->phone . ', ' : $company->phone . ', ';
            $csv .= ($company->primary_user && $company->primary_contact()->email) ? $company->primary_contact()->email . ', ' : $company->email . ', ';
            $csv .= ($company->primary_user) ? $company->primary_contact()->fullname . ', ' : ', ';
            $csv .= $company->tradesSkilledInSBH();
            $csv .= "\r\n";
        }

        //echo $csv;
        $filename = '/filebank/tmp/' . Auth::user()->company_id . '/company_contactinfo.csv';
        $bytes_written = File::put(public_path($filename), $csv);
        if ($bytes_written === false) die("Error writing to file");

        return redirect($filename);
    }

    public function companySWMS()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company/company_swms', compact('companies'));
    }

    public function companyUsers()
    {
        $companies_allowed = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $all_companies = Company::where('status', '1')->whereIn('id', $companies_allowed)->orderBy('name')->get();
        $companies_list = DB::table('companys as c')->select(['c.id', 'c.name', 'u.company_id', 'c.updated_at', DB::raw('count(*) as users')])
            ->join('users as u', 'c.id', '=', 'u.company_id')
            ->where('u.status', '1')->whereIn('c.id', $companies_allowed)
            ->groupBy('u.company_id')->orderBy('users')->orderBy('name')->get();

        $user_companies = [];
        foreach ($companies_list as $c) {
            $company = Company::find($c->id);

            $user_companies[] = (object) ['id'  => $company->id, 'name' => $company->name_both, 'users' => $c->users,
                                          'sec' => $company->securityUsers(1)->count(), 'pu' => $company->primary_user, 'su' => $company->secondary_user, 'updated_at' => $company->updated_at->format('d/m/Y')];

        }

        return view('manage/report/company/company_users', compact('all_companies', 'user_companies'));
    }

    public function companyPrivacy()
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        return view('manage/report/company/company_privacy', compact('companies'));
    }

    public function companyPrivacySend($type)
    {
        $allowed_companies = Auth::user()->company->companies(1)->pluck('id')->toArray();
        $companies = Company::whereIn('id', $allowed_companies)->orderBy('name')->get();

        // If type 'ALL' then delete all existing active ToDoos
        if ($type == 'all')
            $todo = Todo::where('type', 'company privacy')->where('status', '1')->delete();

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

        return view('manage/report/company/company_privacy_send', compact('companies', 'sent_to_company', 'sent_to_user'));
    }

    public function missingCompanyInfo()
    {
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();

        $missing_info = [];
        $expired_docs1 = [];
        $expired_docs2 = [];
        $expired_docs3 = [];
        foreach ($companies as $company) {
            if (!preg_match('/cc-/', strtolower($company->name))) { // exclude fake cc- companies
                // Missing Info
                if ($company->missingInfo()) {
                    $missing_info[] = [
                        'id'               => $company->id,
                        'company_name'     => $company->name,
                        'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                        'data'             => $company->missingInfo(),
                        'date'             => $company->updated_at->format('d/m/Y'),
                        'link'             => "/company/$company->id"
                    ];
                }

                // Expired Docs
                // Doc types
                // 1  PL - Public Liabilty
                // 2  WC - Workers Comp
                // 3  SA - Sickness & Accident
                // 4  Sub - Subcontractors Statement
                // 5  PTC - Period Trade Contract
                // 6  TT - Test & Tag
                // 7  CL - Contractors Licence
                // 12 PP - Privacy Policy
                if ($company->isMissingDocs()) {
                    foreach ($company->missingDocs() as $type => $name) {
                        $doc = $company->expiredCompanyDoc($type);
                        if (in_array($type, [1, 2, 3, 7, 12])) {
                            $expired_docs1[] = [
                                'id'               => $company->id,
                                'company_name'     => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data'             => $name,
                                'date'             => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link'             => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        } elseif (in_array($type, [4, 5])) {
                            $expired_docs2[] = [
                                'id'               => $company->id,
                                'company_name'     => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data'             => $name,
                                'date'             => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link'             => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        } elseif (in_array($type, [6])) {
                            $expired_docs3[] = [
                                'id'               => $company->id,
                                'company_name'     => $company->name,
                                'company_nickname' => ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '',
                                'data'             => $name,
                                'date'             => ($doc != 'N/A' && $doc->expiry) ? $doc->expiry->format('d/m/Y') : 'never',
                                'link'             => ($doc != 'N/A') ? "/company/{{ $company->id }}/doc/{{ $doc->id }}/edit" : "/company/{{ $company->id }}/doc",
                            ];
                        }
                    }
                }
            }
        }

        //dd($expired_docs1);

        return view('manage/report/company/missing_company_info', compact('companies', 'missing_info', 'expired_docs1', 'expired_docs2', 'expired_docs3'));
    }

    public function missingCompanyInfoCSV()
    {
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();


        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/company_missinginfo " . Carbon::now()->format('YmdHis') . '.csv');
        touch($output_file);

        //dd('here');
        CompanyMissingInfoCsv::dispatch($companies, $output_file); // Queue the job to generate PDF

        return redirect('/manage/report/recent');
    }

    public function missingCompanyInfoPlanner()
    {
        $today = Carbon::today();
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->get();
        $cids = [];
        foreach ($companies as $company) {
            $planner_date = $company->nextDateOnPlanner();
            if ($planner_date)
                $cids[$company->id] = $planner_date->format('ymd');
        }

        asort($cids);
        $companies = [];
        foreach ($cids as $key => $value)
            $companies[] = Company::find($key);

        //$companies = Company::whereIn('id', $cids)->orderBy('name')->get();

        return view('manage/report/company/missing_company_info_planner', compact('companies'));
    }

    public function missingCompanyInfoPlannerCSV()
    {
        $companies = Company::where('parent_company', Auth::user()->company_id)->where('status', '1')->orderBy('name')->get();


        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/company_missinginfo_planned " . Carbon::now()->format('YmdHis') . '.csv');
        touch($output_file);

        //dd('here');
        CompanyMissingInfoPlannerCsv::dispatch($companies, $output_file); // Queue the job to generate PDF

        return redirect('/manage/report/recent');
    }

    /*
     * Expired Company Docs Report
     */
    public function expiredCompanyDocs()
    {
        return view('manage/report/company/expired_company_docs');
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


    /******************************
     * Security Reports
     *****************************/
    public
    function roleusers()
    {
        $allowed_users = Auth::user()->company->users(1)->pluck('id')->toArray();
        $users = DB::table('role_user')->whereIn('user_id', $allowed_users)->orderBy('role_id')->get();

        return view('manage/report/user/roleusers', compact('users'));
    }

    public
    function usersExtraPermissions()
    {
        $permissions = DB::table('permission_user')->where('company_id', Auth::user()->company_id)->orderBy('user_id')->get();

        return view('manage/report/user/users_extra_permissions', compact('permissions'));
    }

    public
    function usersWithPermission($type)
    {
        $permission_list = [];
        $ignore_list = ['client', 'client.doc'];
        $permissions2 = Permission2::all();
        foreach ($permissions2 as $p) {
            list($action, $rest) = explode('.', $p->slug, 2);
            if (!array_key_exists($rest, $permission_list) && !in_array($rest, $ignore_list))
                $permission_list[$rest] = str_replace(['View ', 'Edit ', 'Add ', 'Delete '], '', $p->name);
        }
        asort($permission_list);
        //dd($permission_list);

        /*
        $permission_types = ['view', 'edit', 'add', 'del', 'sig'];

            foreach ($permission_types as $ptype) {
                if ( || $this->permissionLevel("$ptype.$permission", $this->company->reportsTo()->id))
                    return true;
            }
        $users = [];
        foreach(Auth::user()->company->users(1) as $user) {
            // Own Company
            if ($user->id == Auth::user()->id) {
                $this->permissionLevel("$ptype.$permission", $this->company_id)
            } else {

            }
            if ($user->hasAnyPermissionType($type) && !array_key_exists($user->id, $users)) {
                $view = ($user->hasPermission2("view.$type")) ? 1 : 0;
                $edit = ($user->hasPermission2("edit.$type")) ? 1 : 0;
                $add = ($user->hasPermission2("add.$type")) ? 1 : 0;
                $del = ($user->hasPermission2("del.$type")) ? 1 : 0;
                $sig = ($user->hasPermission2("sig.$type")) ? 1 : 0;
                $users[$user->id] = [$view,$edit, $add, $del, $sig];
            }

        } */
        //dd($users);

        $users = Auth::user()->company->users(1);

        return view('manage/report/user/users_with_permission', compact('permission_list', 'type', 'users'));
    }


}
