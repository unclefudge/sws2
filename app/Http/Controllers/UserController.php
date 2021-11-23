<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use DB;
use Mail;
use Carbon\Carbon;
use App\User;
use App\Models\Company\Company;
use App\Models\Misc\Role2;
use App\Models\Misc\Permission2;
use App\Models\Misc\ComplianceOverride;
use App\Models\Misc\PermissionRoleCompany;
use App\Models\Misc\SettingsNotificationCategory;
use App\Http\Utilities\SettingsNotificationTypes;
use App\Http\Requests;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;


/**
 * Class CompaniesController
 * @package App\Http\Controllers
 */
class UserController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('user'))
            return view('errors/404');

        return view('user/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.user'))
            return view('errors/404');

        $role_type = '';

        return view('user/create', compact('role_type'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.user'))
            return view('errors/404');

        $user_request = removeNullValues(request()->all());
        $user_request['company_id'] = Auth::user()->company_id;
        $user_request['password'] = bcrypt($user_request['password']);  // encrypt password from form
        $user_request['password_reset'] = 1;
        $user_request['status'] = 1;

        // Empty State field if rest of address fields are empty
        if (!$request->filled('address') && !$request->filled('suburb') && !$request->filled('postcode'))
            $user_request['state'] = null;

        // Null email field if empty  - for unique validation
        if (!$user_request['email'])
            $user_request['email'] = null;

        // Create User
        $user = User::create($user_request);
        Toastr::success("Created new user");

        // Attach trades
        if (request('trades'))
            $user->tradesSkilledIn()->sync(request('trades'));

        // Attach parent company default child role
        if ($user->company->parent_company) {
            $default_user_role = Role2::where('company_id', $user->company->reportsTo()->id)->where('child', 'default')->first();
            if ($default_user_role)
                $user->attachRole2($default_user_role->id);
        }

        // Attached Company Own Roles
        if ($request->filled('roles'))
            foreach ($request->get('roles') as $role_id)
                $user->attachRole2($role_id);


        // Send out Welcome Email to user
        Mail::to($user)->send(new \App\Mail\User\UserWelcome($user, request('password')));

        // Notify company + parent company new user created
        if ($user->company->subscription && $user->company->notificationsUsersType('user.created'))
            Mail::to($user->company->notificationsUsersType('user.created'))->send(new \App\Mail\User\UserCreated($user, Auth::user()));
        if ($user->company->parent_company && $user->company->reportsTo()->notificationsUsersType('user.created'))
            Mail::to($user->company->reportsTo()->notificationsUsersType('user.created'))->send(new \App\Mail\User\UserCreated($user, Auth::user()));

        // Signup Process - Initial update
        if ($user->company->signup_step == 3) {
            return redirect("/signup/workers/" . $user->company_id);
        }

        return redirect('/company/' . $user->company_id . '/user');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.user', $user))
            return view('errors/404');

        return view('user/show', compact('user'));
    }

    /**
     * Display the settings for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showSecurity($id)
    {
        $user = User::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        return view('user/security', compact('user'));
    }

    /**
     * Display the settings for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showResetPassword($id)
    {
        $user = User::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        return view('user/resetpassword', compact('user'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $tab = 'info')
    {
        $user = User::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        if (Auth::user()->password_reset)
            Toastr::warning("Your password was reset by an admin and you are required to choose an new one");

        return redirect("user/{{ $user->id }}/resetpassword");
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        // Validate
        $rules = [
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required_if:status,1|email|max:255|unique:users,email,' . $user->id . ',id',
            //'roles'     => 'required_if:subscription,1',
        ];

        $mesgs = [
            'email.required_if' => 'The email field is required if user active ie. Login Enabled.',
            //'roles.required_if'              => 'You must select at least one role',
        ];
        $this->validate(request(), $rules, $mesgs);

        if (request()->filled('password') || request()->filled('password_force'))
            $this->validate(request(), ['password' => 'required:|confirmed|min:3',]);

        $user_request = removeNullValues(request()->all());

        // Empty State field if rest of address fields are empty
        if (!request('address') && !request('suburb') && !request('postcode'))
            $user_request['state'] = null;

        // Null email field if empty  - for unique validation
        if (!$user_request['email']) $user_request['email'] = null;

        //dd($user_request);

        // Update User
        $user->update($user_request);
        Toastr::success("Saved changes");

        return redirect('/user/' . $user->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateLogin($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        //
        // Validate
        //
        $rules = ['username' => 'required|min:3|max:50|unique:users,username,' . $user->id];

        // Add Password rule if password_update is set
        if (request('password_update') == 1 && Auth::user()->id == $user->id)
            $rules['password'] = (Auth::user()->id == $user->id) ? 'required|confirmed|min:3' : 'required||min:3';

        $validator = Validator::make(request()->all(), $rules);

        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'login');
            Toastr::error("Failed to save changes");

            return back()->withErrors($validator)->withInput();
        }

        $old_status = $user->status;
        $user_request = removeNullValues(request()->all());
        //dd(request('$user_request'));

        // If user being made inactive then update email
        if (request('status') == 0) {
            // Delete outstanding ToDoos (except Toolbox)
            $user->todoDeleteAllActive();

            // If user being made inactive and has email then append 'achived-userid' to front to allow
            // for the email to be potentially reused by another user
            if ($user->status && $user->email) {
                Toastr::warning("Updated email");
                $user_request['email'] = 'archived-' . $user->id . '-' . $user->email;
                if (request('notes'))
                    $user_request['notes'] .= "\nupdated email to " . $user_request['email'] . ' due to archiving';
                else
                    $user_request['notes'] = "updated email to " . $user_request['email'] . ' due to archiving';
            }
            // Remove user from any Notification emails
            $notifications= DB::table('settings_notifications')->where('user_id', $user->id)->get();
            if ($notifications) {
                if ($user->isCC()) {
                    $email_list = $user->company->notificationsUsersEmailType('user.archived.notifications');
                    $notifys = [];
                    foreach ($notifications as $n)
                        $notifys[] = SettingsNotificationCategory::find($n->type)->name;

                    Mail::to($email_list)->send(new \App\Mail\User\UserArchivedNotifys($user, Auth::user(), $notifys));
                }
                DB::table('settings_notifications')->where('user_id', $user->id)->delete();
            }


        } else {
            // User is being re-activated so re-enable email if possible
            $pattern = "/^archived-$user->id-/";
            if (preg_match($pattern,$user->email)) {
                $len = strlen($pattern)-3;
                $new_email = substr($user->email,$len);
                $existing_email = User::where('email', $new_email)->first();
                if (!$existing_email)
                    $user->email = $new_email;
            }
        }

        // Encrypt password
        if (request('password_update')) {
            $user_request['password'] = bcrypt($user_request['password']);
            // Password has ben set by someone other then user so force user to reset after login
            if (Auth::user()->id != $user->id)
                $user_request['password_reset'] = 1;

            // Password has been reset by user after being set by another
            if (Auth::user()->password_reset && Auth::user()->id == $user->id)
                $user_request['password_reset'] = 0;
        }

        //dd($user_request);
        $user->update($user_request);
        Toastr::success("Saved changes");

        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        // Validate
        $this->validate(request(), ['password' => 'required|confirmed|min:3']);
        $user_request = request()->all();

        // Encrypt password
        $user_request['password'] = bcrypt($user_request['password']);
        $user_request['password_reset'] = 0;

        //dd($user_request);
        $user->update($user_request);
        Toastr::success("Saved changes");

        return redirect('/');
    }

    /**
     * Update the security for user resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSecurity($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user.security', $user))
            return view('errors/404');


        // Update Security but ensure at least one user from company has security access
        $remove_security_access = true;
        if (Auth::user()->isCompany($user->company_id)) {
            $security_count = $user->company->securityUsers(1)->count();
            if ($user->hasPermission2('edit.user.security') && !request('p385') && $security_count < 2) {
                Toastr::warning("Unable to remove Edit User Security Details because at least one user within company must have it.");
                $remove_security_access = false;
            }
        }

        // Update Permissions
        $permissions = Permission2::all();
        $user->detachAllPermissions2(Auth::user()->company_id);
        foreach ($permissions as $permission) {
            // Re-add 'edit.user.security' if last user within company has permission
            if ($permission->id == 385 && !$remove_security_access)
                $user->attachPermission2(385, 99, Auth::user()->company_id);

            // Add Permission if user given higher level then one of their roles.
            if (request("p$permission->id") != 0 && request("p$permission->id") > $user->rolesPermissionLevel($permission->id, Auth::user()->company_id)) {
                //echo "added $permission->id [".$request->get("p$permission->id")."/".$user->rolesPermissionLevel($permission->id, Auth::user()->company_id)."] to $user->fullname for company[".Auth::user()->company_id."]<br>";
                $user->attachPermission2($permission->id, request("p$permission->id"), Auth::user()->company_id);
            }
        }

        // Update Roles
        $roles = request('roles');
        $user->detachAllRoles2(Auth::user()->company_id);
        if ($roles) {
            foreach ($roles as $role)
                $user->attachRole2($role);
        }

        //dd($request->all());

        Toastr::success("Saved changes");

        return redirect('/user/' . $user->id . '/security');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateConstruction($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user.security', $user))
            return view('errors/404');

        // Validate
        $validator = Validator::make(request()->all(), ['apprentice_start' => 'required_if:apprentice,1'], ['apprentice_start.required_if' => 'The apprenticeship start date is required.']);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'construction');
            Toastr::error("Failed to save changes");

            return back()->withErrors($validator)->withInput();
        }

        $user_request = request()->all();
        $user_request['apprentice_start'] = (request('apprentice_start')) ? Carbon::createFromFormat('d/m/Y H:i', request('apprentice_start') . '00:00')->toDateTimeString() : null;

        //dd($user_request);
        $user->update($user_request);

        // Update trades for company
        if (request('trades')) {
            $user->tradesSkilledIn()->sync(request('trades'));
        } else
            $user->tradesSkilledIn()->detach();

        $user->save();
        Toastr::success("Saved changes");

        return redirect("/user/$user->id");

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeCompliance($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.compliance.manage', $user->company))
            return view('errors/404');

        // Validate
        $validator = Validator::make(request()->all(), ['reason' => 'required'], ['reason.required' => 'Please specify a reason']);
        if ($validator->fails()) {
            $validator->errors()->add('FORM', 'compliance.add');
            Toastr::error("Failed to save compliance override");

            return back()->withErrors($validator)->withInput();
        }

        $existing_same_type = ComplianceOverride::where('user_id', $user->id)->where('type', request('compliance_type'))->where('status', 1)->first();
        if ($existing_same_type) {
            Toastr::error("User already has a Compliance Override of same type");

            $type_name = OverrideTypes::name(request('compliance_type'));

            return back()->withErrors(['FORM' => 'compliance.add', 'duplicate_override' => "This user currently has a override of same type and the old one MUST be deleted first."])->withInput();
        }


        // Format date from daterange picker to mysql format
        $compliace_request['type'] = request('compliance_type');
        $compliace_request['required'] = (request('compliance_type') != 'cdu') ? request('required') : null;
        $compliace_request['user_id'] = $user->id;
        $compliace_request['company_id'] = Auth::user()->company_id;
        $compliace_request['reason'] = request('reason');
        $compliace_request['expiry'] = (request('expiry')) ? Carbon::createFromFormat('d/m/Y H:i', request('expiry') . '00:00')->toDateTimeString() : null;
        $compliace_request['status'] = 1;

        //dd($compliace_request);
        // Create Compliance Override
        ComplianceOverride::create($compliace_request);
        Toastr::success("Created new compliance override");

        return redirect("user/$user->id");
    }

    /**
     * Update Compliance resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCompliance($id)
    {
        $user = User::findOrFail($id);

        /// Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.compliance.manage', $user))
        //    return view('errors/404');

        foreach (request()->all() as $key => $val) {
            if (preg_match('/compliance_type-/', $key)) {
                list($crap, $over_id) = explode('-', $key);
                $compliace_request['expiry'] = (request("expiry-$over_id")) ? Carbon::createFromFormat('d/m/Y H:i', request("expiry-$over_id") . '00:00')->toDateTimeString() : null;
                $compliace_request['required'] = (request("compliance_type-$over_id") != 'cdu') ? request("required-$over_id") : null;
                $compliace_request['reason'] = request("reason-$over_id");
                $compliance = ComplianceOverride::findOrFail($over_id);
                //var_dump($compliace_request);
                $compliance->update($compliace_request);
            }
        }

        // Delete Marked records
        $records2del = (request('co_del')) ? request('co_del') : [];
        if ($records2del && count($records2del)) {
            foreach ($records2del as $del_id) {
                $rec = ComplianceOverride::findOrFail($del_id);
                $rec->status = 0;
                $rec->save();
                Toastr::error("Deleted override");
            }
        }
        Toastr::success("Saved changes");

        return redirect("user/$user->id");
    }

    /**
     * Reset permission back to ones given by user roles only
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPermissions($id)
    {
        $user = User::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user.security', $user))
            return view('errors/404');

        $user->detachAllPermissions2(Auth::user()->company_id);
        Toastr::success("Saved changes");

        return redirect('/user/' . $user->id . '/security');
    }

    /**
     * Get the security permissions for user resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    /*
public function getSecurityPermissions(Request $request, $id)
{
   $user = User::findOrFail($id);
   $permissions = DB::table('permission_user')
       ->where('user_id', $user->id)
       ->lists('permission_id');
   foreach ($permissions as $permission)
       $array[] = "$permission";

   return $array;
}*/

    /**
     * Update the photo on user model resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function updatePhoto(UserRequest $request, $username)
    {
        $user = User::where(compact('username'))->firstOrFail();

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        $file = $request->file('photo');
        $path = "filebank/users/" . $user->id;
        $name = "photo." . strtolower($file->getClientOriginalExtension());
        $path_name = $path . '/' . $name;
        $file->move($path, $name);

        Image::make(url($path_name))
            ->fit(740)
            ->save($path_name);

        $user->photo = $path_name;
        $user->save();
        Toastr::success("Saved changes");

        return redirect('/user/' . $user->username . '/settings/photo');
    }*/


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contractorList()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->isSupervisor())
            return view('errors/404');

        return view('user/list-contractors');
    }

    /**
     * Get basic User details.
     */
    public function getUserDetails($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Get Users current user is authorised to manage + Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {
        $user_list = [Auth::user()->id];
        if (Auth::user()->hasAnyPermissionType('user'))
            $user_list = Auth::user()->authUsers('view.user')->pluck('id')->toArray();

        $user_records = User::select([
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.company_id', 'users.security', 'users.company_id',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name'),
            'companys.name', 'users.address', 'users.last_login', 'users.status'])
            ->join('companys', 'users.company_id', '=', 'companys.id')
            ->whereIn('users.id', $user_list)
            ->where('users.status', request('status'));

        $dt = Datatables::of($user_records)
            //->filterColumn('full_name', 'whereRaw', "CONCAT(users.firstname,' ',users.lastname) like ?", ["%$1%"])
            ->editColumn('id', '<div class="text-center"><a href="/user/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('full_name', function ($user) {
                $string = $user->firstname . ' ' . $user->lastname;
                if ($user->id == $user->company->primary_user)
                    $string .= " <span class='badge badge-info badge-roundless'>P</span>";
                if ($user->id == $user->company->secondary_user)
                    $string .= " <span class='badge badge-info badge-roundless'>S</span>";
                if ($user->hasPermission2('edit.user.security'))
                    $string .= " <span class='badge badge-warning badge-roundless'>Sec</span>";
                return $string;
            })
            ->editColumn('name', function ($user) {
                $cname = $user->company->name;
                $cid = $user->company->id;

                return '<a href="company/' . $cid . '">' . $cname . '</a>';
            })
            ->editColumn('last_login', function ($user) {
                return ($user->last_login != '-0001-11-30 00:00:00') ? with(new Carbon($user->last_login))->format('d/m/Y') : 'never';
            })
            ->removeColumn('slug')
            ->rawColumns(['id', 'full_name', 'name'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Users current user is authorised to manage + Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getContractors(Request $request)
    {
        $user_list = Auth::user()->company->users($request->get('status'))->pluck('id')->toArray();

        $user_records = User::select([
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.phone', 'users.email', 'users.company_id', 'users.security',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name'),
            'companys.name',
            'users.address', 'users.last_login', 'users.status'])
            ->join('companys', 'users.company_id', '=', 'companys.id')
            ->where('users.company_id', '<>', Auth::user()->company_id)
            ->whereIn('users.id', $user_list);

        $dt = Datatables::of($user_records)
            //->filterColumn('full_name', 'whereRaw', "CONCAT(users.firstname,' ',users.lastname) like ?", ["%$1%"])
            ->editColumn('full_name', function ($user) {
                $company = Company::find($user->company_id);
                $string = $user->firstname . ' ' . $user->lastname;

                if ($user->id == $company->primary_user)
                    $string .= " <span class='badge badge-info badge-roundless'>P</span>";
                if ($user->id == $company->secondary_user)
                    $string .= " <span class='badge badge-info badge-roundless'>S</span>";
                if ($user->hasPermission2('edit.user.security'))
                    $string .= " <span class='badge badge-warning badge-roundless'>Sec</span>";

                return $string;
            })
            ->editColumn('name', '<a href="company/{{$id}}">{{$name}}</a>')
            ->editColumn('last_login', function ($user) {
                return ($user->last_login != '-0001-11-30 00:00:00') ? with(new Carbon($user->last_login))->format('d/m/Y') : 'never';
            })
            ->removeColumn('slug')
            ->rawColumns(['id', 'full_name', 'name'])
            ->make(true);

        //var_dump($dt);

        return $dt;
    }
}
