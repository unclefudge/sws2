<?php

namespace App;

use App\Http\Utilities\CompanyEntityTypes;
use App\Models\Comms\Notify;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Company\Company;
use App\Models\Company\CompanySupervisor;
use App\Models\Safety\ToolboxTalk;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Site;
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SitePracCompletionItem;
use App\Traits\UserDocs;
use App\Traits\UserRolesPermissions;
use Carbon\Carbon;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Session;

//use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

    use Authenticatable, CanResetPassword;
    use Authorizable;
    use UserRolesPermissions;
    use UserDocs;
    use HasApiTokens;

    // The database table used by the model.
    protected $table = 'users';

    // The attributes that are mass assignable.
    protected $fillable = [
        'username', 'email', 'password', 'phone', 'firstname', 'lastname',
        'address', 'address2', 'suburb', 'state', 'postcode', 'country', 'jobtitle',
        'employment_type', 'subcontractor_type', 'onsite', 'apprentice', 'apprentice_start',
        'approved_by', 'approved_at', 'photo', 'notes', 'company_id', 'client_id',
        'last_ip', 'last_login', 'password_reset', 'security',
        'status', 'created_by', 'updated_by',
    ];

    // The attributes excluded from the model's JSON form.
    protected $hidden = ['password', 'remember_token'];

    // The date fields to be converted to Carbon instances
    protected $dates = ['last_login', 'apprentice_start', 'approved_at'];

    /**
     * The "booting" method of the model.
     *
     * Overrides parent function
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        if (Auth::check()) {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = Auth::user()->id;
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }

    /**
     * A User belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');

    }

    /**
     * A User was created by a User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A User has many trades (trades they are skilled in).
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function tradesSkilledIn()
    {
        return $this->belongsToMany('App\Models\Site\Planner\Trade', 'user_trade', 'user_id', 'trade_id');
    }

    /**
     * A list of trades that user is skilled in separated by ,
     *
     * @return string
     */
    public function tradesSkilledInSBC()
    {
        $string = '';
        foreach ($this->tradesSkilledIn as $trade) {
            if ($trade->status)
                $string .= $trade->name . ', ';
        }

        return rtrim($string, ', ');
    }

    /**
     * A User has many SiteAttendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function siteAttendance()
    {
        return $this->hasMany('App\Models\Site\Planner\SiteAttendance', 'user_id');
    }

    /**
     * A user may have one or more Area Supervisors
     */
    public function areaSupervisors()
    {
        $parent_ids = DB::table('company_supervisors')->where('user_id', $this->id)->where('parent_id', '<>', 0)->pluck('parent_id')->toArray();
        $user_ids = DB::table('company_supervisors')->whereIn('id', $parent_ids)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->get();
    }

    /**
     * A dropdown list of Sub Supervisors that this user manages
     *
     * @return array
     */
    public function subSupervisorsSelect($prompt = '')
    {
        $array = [];
        foreach ($this->subSupervisors() as $user)
            $array[$user->id] = $user->fullname;

        asort($array);

        return ($prompt) ? $array = array('' => 'Select supervisor') + $array : $array;
    }

    /**
     * A user may have one or more Sub Supervisors they manage
     */
    public function subSupervisors()
    {
        $record = DB::table('company_supervisors')->where('user_id', $this->id)->where('parent_id', 0)->first();
        $user_ids = [];

        if ($record)
            $user_ids = DB::table('company_supervisors')->where('parent_id', $record->id)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->get();
    }

    /**
     * A list of sites this user is supervisor for
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function supervisorsSites($status = '')
    {
        $primary = Site::where('supervisor_id', $this->id)->pluck('id')->toArray();
        $secondary = DB::table('site_supervisor')->where('user_id', $this->id)->pluck('site_id')->toArray();
        $site_list = array_merge($primary, $secondary);

        return ($status != '') ? Site::where('status', $status)->whereIn('id', $site_list)->get() : Site::whereIn('id', $site_list)->get();
    }

    /**
     * A list of sites this user is Area supervisor for
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function areaSites($status = null)
    {
        // list of users (themselves + any users they supervise)
        $user_list = $this->isAreaSupervisor() ? array_merge([$this->id], $this->subSupervisors()->pluck('id')->toArray()) : [$this->id];
        // List of sites they or any of the subSupervisors supervise
        $primary = Site::whereIn('supervisor_id', $user_list)->pluck('id')->toArray();
        $secondary = DB::table('site_supervisor')->whereIn('user_id', $user_list)->pluck('site_id')->toArray();
        $site_list = array_merge($primary, $secondary);

        return ($status) ? Site::where('status', $status)->whereIn('id', $site_list)->orderBy('name')->get() : Site::whereIn('id', $site_list)->orderBy('name')->get();
    }

    /**
     * User is a Area Supervisor
     * @return boolean
     */
    public function isAreaSupervisor()
    {
        return (CompanySupervisor::where('user_id', $this->id)->where('parent_id', 0)->first()) ? true : false;
    }

    /**
     * A dropdown list of types of Site Document user can access
     *
     * @return array
     */
    public function siteDocTypeSelect($action, $prompt = '')
    {
        $array = [];
        if ($this->hasPermission2("$action.safety.doc")) {
            $array['RISK'] = "Risk";
            $array['HAZ'] = "Hazard";
        }
        if ($this->hasPermission2("$action.site.doc"))
            $array['PLAN'] = "Plan";

        if ($prompt == 'all')
            return ($prompt && count($array) > 1) ? $array = array('ALL' => 'All types') + $array : $array;

        return ($prompt && count($array) > 1) ? $array = array('' => 'Select Type') + $array : $array;
    }

    /**
     * A list of Site Hazards this user is allowed to view
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function siteHazards($status = '')
    {
        $site_list = (Session::has('siteID')) ? [Session::get('siteID')] : [];
        $user_list = [$this->id];
        $company_level = $this->permissionLevel('view.site.hazard', $this->company_id);
        $parent_level = $this->permissionLevel('view.site.hazard', $this->company->reportsTo()->id);
        if ($company_level == 30 || $company_level == 40 || $parent_level == 30 || $parent_level == 40)
            $site_list = $site_list + $this->authSites('view.site.hazard')->pluck('id')->toArray(); // Planned For or Supervisor For so  - check site
        else {
            $user_list = $user_list + $this->authUsers('view.site.hazard')->pluck('id')->toArray(); // Else - check users
        }

        // For special site 0003-Vehicles Cape Cod '809' allow specific users access to it.
        if (in_array($this->id, ['3', '108', '458', '1155'])) // Fudge, Kirstie, Georgie, Ross
            $site_list[] = '809';

        if ($status != '')
            return SiteHazard::where('status', '=', $status)
                ->where(function ($q) use ($site_list, $user_list) {
                    $q->whereIn('created_by', $user_list);
                    $q->orWhereIn('site_id', $site_list);
                })->get();

        return SiteHazard::where(function ($q) use ($site_list, $user_list) {
            $q->whereIn('created_by', $user_list);
            $q->orWhereIn('site_id', $site_list);
        })->get();

    }

    /**
     * A list of Site Accidents this user is allowed to view
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function siteAccidents($status = '')
    {
        $site_list = (Session::has('siteID')) ? [Session::get('siteID')] : [];
        $user_list = [$this->id];
        $company_level = $this->permissionLevel('view.site.accident', $this->company_id);
        $parent_level = $this->permissionLevel('view.site.accident', $this->company->reportsTo()->id);
        if ($company_level == 30 || $company_level == 40 || $parent_level == 30 || $parent_level == 40)
            $site_list = $site_list + $this->authSites('view.site.accident')->pluck('id')->toArray(); // Planned For or Supervisor For so  - check site
        else
            $user_list = $user_list + $this->authUsers('view.site.accident')->pluck('id')->toArray(); // Else - check users

        if ($status != '')
            return SiteAccident::where('status', '=', $status)
                ->where(function ($q) use ($site_list, $user_list) {
                    $q->whereIn('created_by', $user_list);
                    $q->orWhereIn('site_id', $site_list);
                })->get();

        return SiteAccident::where(function ($q) use ($site_list, $user_list) {
            $q->whereIn('created_by', $user_list);
            $q->orWhereIn('site_id', $site_list);
        })->get();
    }

    /**
     * A list of Site Incidents this user is allowed to view
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function siteIncidents($status = '')
    {
        $site_list = (Session::has('siteID')) ? [Session::get('siteID')] : [];
        $user_list = [$this->id];
        if ($this->company_id == 3 && $this->hasAnyRole2('whs-manager|whs-administrator|mgt-general-manager|mgt-company-director|web-admin'))
            $user_list = $user_list = $this->authUsers('view.user')->pluck('id')->toArray();

        $company_level = $this->permissionLevel('view.site.incident', $this->company_id);
        $parent_level = $this->permissionLevel('view.site.incident', $this->company->reportsTo()->id);
        if ($company_level == 99 || $parent_level == 99)
            $site_list = [0] + Site::where('status', 1)->pluck('id')->toArray();
        elseif ($company_level == 30 || $company_level == 40 || $parent_level == 30 || $parent_level == 40)
            $site_list = $site_list + $this->authSites('view.site.incident')->pluck('id')->toArray(); // Planned For or Supervisor For so  - check site
        else
            $user_list = $user_list + $this->authUsers('view.site.incident')->pluck('id')->toArray(); // Else - check users

        // Georgie (458) access to site 0003-vehicles (809)
        if ($this->id == '458')
            $site_list = [809] + $site_list;

        //dd($site_list);
        if ($status != '')
            return SiteIncident::where('status', '=', $status)
                ->where(function ($q) use ($site_list, $user_list) {
                    $q->whereIn('created_by', $user_list);
                    $q->orWhereIn('site_id', $site_list);
                })->get();

        return SiteIncident::where(function ($q) use ($site_list, $user_list) {
            $q->whereIn('created_by', $user_list);
            $q->orWhereIn('site_id', $site_list);
        })->get();
    }

    /**
     * A list of Maintenance Requests this user is allowed to view
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function maintenanceRequests($status = '')
    {
        if ($this->permissionLevel('view.site.maintenance', 3) == 99) // User has 'All' permission to requests
            return SiteMaintenance::where('status', '=', $status)->get();

        if ($this->permissionLevel('view.site.maintenance', 3) == 40) // User is 'Supervisor For' requests
            return SiteMaintenance::where('status', '=', $status)->where('super_id', $this->id)->get();

        if ($this->permissionLevel('view.site.maintenance', 3) == 30) { // User is 'Planned For' requests
            $ids = SiteMaintenanceItem::where('assigned_to', $this->company_id)->pluck('main_id')->toArray();
            return SiteMaintenance::where('status', '=', $status)->whereIn('id', $ids)->get();
        }

        return;
    }

    /**
     * A list of Prac Completion  this user is allowed to view
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function pracCompletion($status = '')
    {
        if ($this->permissionLevel('view.prac.completion', 3) == 99) // User has 'All' permission to requests
            return SitePracCompletion::where('status', '=', $status)->get();

        if ($this->permissionLevel('view.prac.completion', 3) == 40) // User is 'Supervisor For' requests
            return SitePracCompletion::where('status', '=', $status)->where('super_id', $this->id)->get();

        if ($this->permissionLevel('view.prac.completion', 3) == 30) { // User is 'Planned For' requests
            $ids = SitePracCompletionItem::where('assigned_to', $this->company_id)->pluck('prac_id')->toArray();
            return SitePracCompletion::where('status', '=', $status)->whereIn('id', $ids)->get();
        }

        return;
    }

    /**
     * User is a Supervisor
     * @return boolean
     */
    public function isSupervisor()
    {
        return (CompanySupervisor::where('user_id', $this->id)->first()) ? true : false;
    }

    /**
     * User is from same company as [company]
     * @param $company
     * @return boolean
     */
    public function isCompany($company)
    {
        // Get company record if integer
        if (is_int($company))
            $company = Company::find($company);

        if ($company)
            return ($this->company_id == $company->id) ? true : false;

        return false;
    }

    /**
     * User is from Cape Cod
     * @param $company
     * @return boolean
     */
    public function isCC()
    {
        return ($this->company_id == 3) ? true : false;
    }

    /**
     * User is from Cape Cod Sub-company
     * @param $company
     * @return boolean
     */
    public function isCCcompany()
    {
        $cc_companies = Company::find(3)->companies()->pluck('id')->toArray();

        return ($this->company_id == 3 || in_array($this->company_id, $cc_companies)) ? true : false;
    }

    /**
     * A list of Sites a user attended on a certain date
     * @param $date
     * @return mixed
     */
    public function attendSitesOnDate($date)
    {
        return SiteAttendance::whereDate('date', $date)->where('user_id', $this->id)->get();
    }

    /**
     * A list of times User has been 'Non-Compliant' on work sites
     * @return collection
     */
    public function nonCompliant($status = '')
    {
        $one_year_ago = Carbon::now()->subYear();
        if ($status != '')
            return siteCompliance::where('user_id', $this->id)->where('reason', '1')->where('status', $status)->where('archive', '0')->whereDate('date', '>', $one_year_ago)->orderBy('date')->get();

        return siteCompliance::where('user_id', $this->id)->where('reason', '1')->where('archive', '0')->whereDate('date', '>', $one_year_ago)->orderBy('date')->get();
    }

    /**
     * A User has multiple ToDoo tasks of Type (x)
     */
    public function todoType($type, $status = null)
    {
        $todo_ids = TodoUser::where('user_id', $this->id)->pluck('todo_id')->toArray();

        if ($status)
            $status = (!is_array($status)) ? [$status] : $status; // convert status to an array if not

        return ($status != '') ? Todo::whereIn('id', $todo_ids)->where('type', $type)->whereIn('status', $status)->orderBy('due_at')->get() : Todo::whereIn('id', $todo_ids)->where('type', $type)->orderBy('due_at')->get();
    }

    /**
     * Delete all ToDoo tasks (except Toolbox)
     */
    public function todoDeleteAllActive()
    {
        $todo_active = $this->todo(1);
        foreach ($todo_active as $todo) {
            if ($todo->type != 'toolbox') {
                // If user is only one assigned the ToDoo delete whole ToDoo else only remove user from Todoo
                if (TodoUser::where('todo_id', $todo->id)->count() == 1)
                    Todo::find($todo->id)->delete();
                else
                    TodoUser::where('todo_id', $todo->id)->where('user_id', $this->id)->delete();
            }
        }
    }

    /**
     * A User has multiple ToDoo tasks
     */
    public function todo($status = null)
    {
        $todo_ids = TodoUser::where('user_id', $this->id)->pluck('todo_id')->toArray();

        if ($status)
            $status = (!is_array($status)) ? [$status] : $status; // convert status to an array if not


        return ($status) ? Todo::whereIn('id', $todo_ids)->wherein('status', $status)->orderBy('due_at')->get() : Todo::whereIn('id', $todo_ids)->orderBy('due_at')->get();
    }

    /**
     * A User has multiple Toolbox Talks
     */
    public function toolboxs($status = '')
    {
        $todos = ($status) ? Todo::where('type', 'toolbox')->where('status', $status)->get() : Todo::where('type', 'toolbox')->get();

        $toolbox_assigned = [];
        foreach ($todos as $todo) {
            if (in_array($this->id, $todo->assignedTo()->pluck('id')->toArray()))
                $toolbox_assigned[] = $todo->type_id;
        }

        return ToolboxTalk::find($toolbox_assigned);
    }

    /**
     * A User has multiple Notify Alerts
     */
    public function notify()
    {
        $today = Carbon::today();
        $notifys = Notify::where('type', 'user')->where('from', '<=', $today)->where('to', '>=', $today)->get();

        $notify_ids = [];
        foreach ($notifys as $notify) {
            if ($notify->action == 'many' && in_array($this->id, $notify->assignedTo()->pluck('id')->toArray()))
                $notify_ids[] = $notify->id;
            else if (!$notify->isOpenedBy($this) && in_array($this->id, $notify->assignedTo()->pluck('id')->toArray()))
                $notify_ids[] = $notify->id;
        }

        return Notify::find($notify_ids);
    }

    /**
     * A list of sites user is roster for on specified date
     */
    public function rosteredSites($date = null)
    {
        if (!$date)
            $date = Carbon::today();

        $site_ids = SiteRoster::whereDate('date', $date)->where('user_id', $this->id)->pluck('site_id')->toArray();

        return ($site_ids) ? Site::find($site_ids) : null;
    }

    public function createApiToken()
    {
        $token = $this->createToken('API Token');
        //ray($token);

        return ['token' => $token->plainTextToken];
    }

    /**
     * Get the owner of record  (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->company;
    }

    /**
     * Display records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::find($this->updated_by);

        return ($user) ? '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
            '<span style="font-weight: 400">By:</span> ' . $user->fullname : "$this->updated_by";
    }

    /**
     * Set the phone number to AU format  (mutator)
     *
     * @param $phone
     */
    public function setPhoneAttribute($phone)
    {
        $this->attributes['phone'] = format_phone('au', $phone);
    }

    /**
     * Set the suburb to uppercase format  (mutator)
     *
     * @param $value
     */
    public function setSuburbAttribute($value)
    {
        $this->attributes['suburb'] = strtoupper($value);
    }

    /**
     * Get the Full name (first + last)   (getter)
     *
     * @return string;
     */
    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get the Full name (first + last)   (getter)
     *
     * @return string;
     */
    public function getNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get the User Initials (first + last)   (getter)
     *
     * @return string;
     */
    public function getInitialsAttribute()
    {
        if ($this->id == 136)
            return 'TBC';
        return strtoupper($this->firstname[0]) . strtoupper($this->lastname[0]);
    }

    /**
     * Get the Employment Typetext   (getter)
     *
     * @return string;
     */
    public function getEmploymentTypeTextAttribute()
    {
        if ($this->employment_type == 1) return 'Employee';
        if ($this->employment_type == 2) return 'External Employment Company';
        if ($this->employment_type == 3) return 'Subcontractor';

        return '';
    }

    /**
     * Get the Employment Typetext   (getter)
     *
     * @return string;
     */
    public function getSubcontractorEntityTextAttribute()
    {
        return ($this->subcontractor_type) ? CompanyEntityTypes::name($this->subcontractor_type) : '';
    }

    /**
     * Get the Company Id   (getter)
     *
     * @return string;
     */
    public function getCidAttribute()
    {
        return $this->company_id;
    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getSuburbStatePostcodeAttribute()
    {
        $string = strtoupper($this->attributes['suburb']);
        if ($this->attributes['suburb'] && $this->attributes['state'])
            $string .= ', ';
        if ($this->attributes['state'])
            $string .= $this->attributes['state'];
        if ($this->attributes['postcode'])
            $string .= ' ' . $this->attributes['postcode'];

        return $string;
    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getAddressFormattedAttribute()
    {
        $string = '';

        if ($this->attributes['address'])
            $string = strtoupper($this->attributes['address']) . '<br>';

        $string .= strtoupper($this->attributes['suburb']);
        if ($this->attributes['suburb'] && $this->attributes['state'])
            $string .= ', ';
        if ($this->attributes['state'])
            $string .= $this->attributes['state'];
        if ($this->attributes['postcode'])
            $string .= ' ' . $this->attributes['postcode'];

        return ($string) ? $string : '-';
    }

    /**
     * Get the parent permission  (getter)
     */
    public function getParentPermissionsAttribute()
    {
        $array = DB::table('permission_user AS pu')
            ->select('permission_id')
            ->where('pu.user_id', $this->id)
            ->where('p.model', 'p')
            ->join('permissions AS p', 'pu.permission_id', '=', 'p.id')
            ->pluck('pu.permission_id')->toArray();

        return $array;
    }

    /**
     * Get the Status Text Both  (getter)
     */
    public function getStatusTextAttribute()
    {
        if ($this->status == 1)
            return '<span class="font-green">ACTIVE</span>';

        if ($this->status == 1)
            return '<span class="font-yellow">PENDING</span>';

        if ($this->status == 0)
            return '<span class="font-red">INACTIVE</span>';
    }
}

