<?php
namespace App\Traits;

use DB;
use Auth;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteHazard;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Misc\Role2;
use App\Models\Misc\Permission2;
use App\Models\Company\CompanyDocCategory;
use App\Models\User\UserDocCategory;
use App\Http\Utilities\CompanyDocTypes;
use Carbon\Carbon;


trait UserRolesPermissions {

    /**
     * A user belongs to many roles
     */
    public function roles2()
    {
        return $this->belongsToMany('App\Models\Misc\Role2', 'role_user', 'user_id', 'role_id');
    }

    /**
     * A user belongs to many permission
     */
    public function permissions2($company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'company_id' => $company_id])->get();
    }

    /**
     * Check if a user has a certain 'role'
     *
     * @return boolean
     */
    public function hasRole2($role)
    {
        return ($this->roles2()->where('slug', $role)->first()) ? true : false;
    }

    /**
     * Check if a user has any of the given 'roles'
     *
     * @return boolean
     */
    public function hasAnyRole2($roles)
    {
        $roles_array = explode('|', $roles);
        foreach ($roles_array as $role) {
            if ($this->hasRole2($role))
                return true;
        }

        return false;
    }

    /**
     * Check if a user has a 'role' with a company
     *
     * @return boolean
     */
    public function hasRoleCompany($company_id)
    {
        $company_role_ids = Role2::where('company_id', $company_id)->pluck('id')->toArray();

        return (DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->first()) ? true : false;
    }

    /**
     * Attach role to a user for company 'company'
     *
     * @param  $permission
     * @return int|bool
     */
    public function attachRole2($role)
    {
        // Determine if exists
        $exists = DB::table('role_user')->where(['user_id' => $this->id, 'role_id' => $role])->first();

        return ($exists) ? true : DB::table('role_user')->insert(['user_id' => $this->id, 'role_id' => $role]);
    }

    /**
     * Detach role from a user for company 'company'
     *
     * @param $permission
     * @return int
     */
    public function detachRole2($role)
    {
        return DB::table('role_user')->where(['user_id' => $this->id, 'role_id' => $role])->delete();
    }

    /**
     * Detach all roles from a user for company 'company'
     *
     * @return int
     */
    public function detachAllRoles2($company_id)
    {
        $company_role_ids = Role2::where('company_id', $company_id)->pluck('id')->toArray();

        return DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->delete();
    }

    /**
     * User roles separated by Comma
     * @return string
     */
    public function rolesSBC()
    {
        $role_ids = Role2::where('company_id', $this->company_id)->pluck('id')->toArray();
        $roles = DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $role_ids)
            ->join('roles', 'role_user.role_id', '=', 'roles.id')->orderBy('roles.name')->get();

        $string = '';
        foreach ($roles as $role)
            $string .= $role->name . ', ';

        return rtrim($string, ', ');
    }

    /**
     * User roles separated by Comma
     * @return string
     */
    public function parentRolesSBC()
    {
        $role_ids = Role2::where('company_id', $this->company->reportsTo()->id)->pluck('id')->toArray();
        $roles = DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $role_ids)
            ->join('roles', 'role_user.role_id', '=', 'roles.id')->orderBy('roles.name')->get();

        $string = '';
        foreach ($roles as $role)
            $string .= $role->name . ', ';

        return rtrim($string, ', ');
    }



    /*   --                 --   */
    /*   --   Permissions   --   */
    /*   --                 --   */


    /**
     * Attach permission to a user for company 'company'
     *
     * @param  $permission
     * @return int|bool
     */
    public function attachPermission2($permission, $level, $company_id)
    {
        // Determine if exists and exact same
        $exists = DB::table('permission_user')->where(['user_id' => $this->id, 'permission_id' => $permission, 'level' => $level, 'company_id' => $company_id])->first();
        if ($exists)
            return true;

        // Delete if exists but different level
        DB::table('permission_user')->where(['user_id' => $this->id, 'permission_id' => $permission, 'company_id' => $company_id])->delete();

        return DB::table('permission_user')->insert(['user_id' => $this->id, 'permission_id' => $permission, 'level' => $level, 'company_id' => $company_id]);
    }

    /**
     * Detach permission from a user for company 'company'
     *
     * @param $permission
     * @return int
     */
    public function detachPermission2($permission, $company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'permission_id' => $permission, 'company_id' => $company_id])->delete();
    }

    /**
     * Detach all permissions from a user for 'company'
     *
     * @return int
     */
    public function detachAllPermissions2($company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'company_id' => $company_id])->delete();
    }

    /**
     * Determine if user has any Permission of 'type'
     *
     * @return int
     */
    public function hasAnyPermissionType($type)
    {
        $permission_types = ['view', 'edit', 'add', 'del', 'sig'];
        $permission_array = explode('|', $type);
        foreach ($permission_array as $permission) {
            foreach ($permission_types as $ptype) {
                if ($this->permissionLevel("$ptype.$permission", $this->company_id) || $this->permissionLevel("$ptype.$permission", $this->company->reportsTo()->id))
                    return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has a certain 'permission'
     *
     * @return boolean
     */
    public function hasPermission2($permission)
    {
        // Get permission level attached to user
        if ($this->permissionLevel($permission, $this->company_id)) return true;
        if ($this->permissionLevel($permission, $this->company->reportsTo()->id)) return true;

        return false;
    }

    /**
     * Check if a user has any of the given 'permission'
     *
     * @return boolean
     */
    public function hasAnyPermission2($permissions)
    {
        $permissions_array = explode('|', $permissions);
        foreach ($permissions_array as $permission) {
            if ($this->hasPermission2($permission))
                return true;
        }

        return false;
    }

    /**
     * Additional permissions given to a user 'on top' granted by their role
     *
     * @return collection
     */
    public function extraUserPermissions($company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'company_id' => $company_id])->get();
    }

    /**
     * Additional permissions given to a user 'on top' granted by their role - HTML
     *
     * @return string
     */
    public function extraUserPermissionsText($company_id)
    {
        $extra = $this->extraUserPermissions($company_id);

        $levels = ['0' => 'No', '1' => "All", '99' => "All", '50' => "Our Company", '40' => 'Supervisor for', '30' => 'Planned for', '20' => 'Own Company', '10' => "Individual Only"];
        if (count($extra)) {
            $str = 'The following <b>additional permissions</b> have been granted to the user on top of ones granted by their role(s):<ul>';
            foreach ($extra as $e) {
                $permission = Permission2::find($e->permission_id);
                $str .= "<li>$permission->name (" . $levels[$e->level] . ")</li>";
            }
            $str .= '</ul>';

            return $str;
        }

        return '';
    }


    /**
     * Determine level of a permission for a 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function userPermissionLevel($permission, $company_id)
    {
        $permission_id = $permission;
        if (!is_int($permission)) {
            // Get Permission ID
            $perm = Permission2::where('slug', $permission)->first();
            if (!$perm)
                return 0;
            $permission_id = $perm->id;
        }

        // Get permission level attached to user
        $permssion_user = DB::table('permission_user')->where('permission_id', $permission_id)->where('user_id', $this->id)->where('company_id', $company_id)->first();
        $level = ($permssion_user) ? $permssion_user->level : 0;

        return $level;
    }

    /**
     * Determine level of a permission for a 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function rolesPermissionLevel($permission, $company_id)
    {
        $permission_id = $permission;
        if (!is_int($permission)) {
            // Get Permission ID
            $perm = Permission2::where('slug', $permission)->first();
            if (!$perm)
                return 0;
            $permission_id = $perm->id;
        }

        // Array of role ids the users has with given company 'company_id'
        $company_role_ids = Role2::where('company_id', $company_id)->pluck('id')->toArray();
        $user_role_ids = DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->pluck('role_id')->toArray();

        // Get permission level attached to users role
        $level = 0;
        $permssion_role = DB::table('permission_role')->where('permission_id', $permission_id)->whereIn('role_id', $user_role_ids)->where('company_id', $company_id)->get();
        //dd($permssion_role);
        foreach ($permssion_role as $p) {
            if ($p->level > $level)
                $level = $p->level;
        }

        return $level;
    }

    /**
     * Determine level of a permission for 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function permissionLevel($permission, $company_id)
    {
        $user_level = $this->userPermissionLevel($permission, $company_id);
        $role_level = $this->rolesPermissionLevel($permission, $company_id);

        return ($user_level > $role_level) ? $user_level : $role_level;
    }

    /**
     * A list of users this user has authority over
     * ie user has authority own themselves + maybe own companies/child users (if appropriate permission granted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authUsers($permission, $status = '')
    {
        // Company
        $company_level = $this->permissionLevel($permission, $this->company_id);
        $company_ids = [];
        if ($company_level == '99') $company_ids = $this->company->users()->pluck('id')->toArray(); // All
        if ($company_level == '50') $company_ids = $this->company->staff->pluck('id')->toArray(); // Our Company
        if ($company_level == '20') $company_ids = $this->company->staff->pluck('id')->toArray(); // Own Company
        if ($company_level == '10') $company_ids = [$this->id]; // Individual Only
        if ($company_level == '1') $company_ids = $this->company->users()->pluck('id')->toArray(); // Delete / Sign Off All

        // Parent Company
        $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
        $parent_ids = [];
        if ($parent_level == '99') $parent_ids = $this->company->reportsTo()->users()->pluck('id')->toArray(); // All
        if ($parent_level == '50') $parent_ids = $this->company->reportsTo()->staff->pluck('id')->toArray(); // Our Company
        if ($parent_level == '20') $parent_ids = $this->company->users()->pluck('id')->toArray(); // Own Company
        if ($parent_level == '10') $parent_ids = [$this->id]; // Individual Only
        if ($parent_level == '1') $parent_ids = $this->company->reportsTo()->users()->pluck('id')->toArray(); // Delete / Sign Off All

        $merged_ids = array_merge($company_ids, $parent_ids, [$this->id]);

        return ($status != '') ? User::where('status', $status)->whereIn('id', $merged_ids)->get() : User::whereIn('id', $merged_ids)->get();
    }

    /**
     * A list of company this user has authority over
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authCompanies($permission, $status = '')
    {
        // Company
        $company_level = $this->permissionLevel($permission, $this->company_id);
        $company_ids = [];
        if ($company_level == '99') $company_ids = $this->company->companies()->pluck('id')->toArray(); // All
        if ($company_level == '20') $company_ids = $this->company->companies()->pluck('id')->toArray(); // Own Company
        if ($company_level == '1') $company_ids = $this->company->companies()->pluck('id')->toArray(); // Delete / Sign Off All

        // Parent Company
        $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
        $parent_ids = [];
        if ($parent_level == '99') $parent_ids = $this->company->reportsTo()->companies()->pluck('id')->toArray(); // All
        if ($parent_level == '20') $parent_ids = $this->company->companies()->pluck('id')->toArray(); // Own Company
        if ($parent_level == '1') $parent_ids = $this->company->reportsTo()->companies()->pluck('id')->toArray(); // Delete / Sign Off All

        $merged_ids = array_merge($company_ids, $parent_ids);

        return ($status != '') ? Company::where('status', $status)->whereIn('id', $merged_ids)->get() : Company::whereIn('id', $merged_ids)->get();
    }

    /**
     * A list of sites this user has authority over
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authSites($permission, $status = '')
    {
        // Alter Permission to View Site to supersede the Sitelist permission for employees with View Site but Not View Site.List
        //if ($permission == 'view.site.list' && $this->hasPermission2('view.site'))
        //    $permission = 'view.site';
        $permission_company = ($permission == 'view.site.list' && $this->hasPermission2('view.site')) ? 'view.site' : $permission;

        // Company
        $company_level = $this->permissionLevel($permission_company, $this->company_id);
        $company_ids = [];
        if ($company_level == '99') $company_ids = $this->company->sites()->pluck('id')->toArray(); // All
        if ($company_level == '50') $company_ids = $this->company->sites()->pluck('id')->toArray(); // Our Company
        if ($company_level == '40') $company_ids = $this->areaSites()->pluck('id')->toArray(); // Supervisor for
        if ($company_level == '30') $company_ids = $this->company->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
        if ($company_level == '1') $company_ids = $this->company->sites()->pluck('id')->toArray(); // Delete / Sign Off All

        // Parent Company
        $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
        $parent_ids = [];
        if ($parent_level == '99') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // All
        if ($parent_level == '50') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // Our Company
        if ($parent_level == '30') $parent_ids = $this->company->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
        if ($parent_level == '20') $parent_ids = []; // Own Company
        if ($parent_level == '1') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // Delete / Sign Off All

        // Parent Parent Company
        $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
        $parent_parent_ids = [];
        if ($parent_level == '99') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // All
        if ($parent_level == '50') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Our Company
        if ($parent_level == '30') $parent_parent_ids = $this->company->reportsTo()->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
        if ($parent_level == '20') $parent_parent_ids = []; // Own Company
        if ($parent_level == '1') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Delete / Sign Off All

        $merged_ids = array_merge($company_ids, $parent_ids, $parent_parent_ids);

        if ($status != '') {
            if (is_array($status))
                return Site::whereIn('status', $status)->whereIn('id', $merged_ids)->get();
            else
                return Site::where('status', $status)->whereIn('id', $merged_ids)->get();
        } else
            return Site::whereIn('id', $merged_ids)->orderBy('name')->get();


        //return ($status != '') ? Site::where('status', $status)->whereIn('id', $merged_ids)->orderBy('name')->get() : Site::whereIn('id', $merged_ids)->orderBy('name')->get();
    }

    /**
     * A dropdown list of sites this user has authority over
     *
     * @parms Permission, Status (site), Prompt, Started (whether Site has tasks on it)
     * @return array
     */
    public function authSitesSelect($permission, $status = '', $prompt = '', $started = '')
    {
        $sites = $this->authSites($permission, $status);

        $array = [];
        foreach ($sites as $site) {
            // Determine if Job Started is required or not
            $start = ($started) ? SitePlanner::where('site_id', $site->id)->first() : true;
            if ($start)
                $array[$site->id] = $site->name; //"$site->code:$site->name";
        }
        asort($array);

        if ($prompt == 'ALL')
            return ($prompt && count($array) > 1) ? $array = array('all' => 'All Sites') + $array : $array;

        return ($prompt && count($array) > 1) ? $array = array('' => 'Select Site') + $array : $array;
    }

    /**
     * A dropdown list of sites this user has authority over
     *
     * @parms Permission, Status (site), Prompt, Started (whether Site has tasks on it)
     * @return array
     */
    public function authSitesSelect2Options($permission, $selected = null, $status = 1)
    {
        //dd($status);
        // Status can be passed as int or array - Convert to array
        if (isset($status) && !is_array($status))
            $status = [$status];

        $headers = false;
        $options = '<option></option>';

        if ($permission == 'checkin') {
            $permission = 'view.site.list';
            if ($this->company->parent_company && $this->company->reportsTo()->addon('planner')) {
                //app('log')->debug("=== AuthSites ===");
                // Site Checkin and either Company or Parent Company has Planner
                $sites_planned = [];
                foreach ($this->company->sitesPlannedFor([1, 2], Carbon::today(), Carbon::today()) as $site) {
                    $site = Site::findOrFail($site->id);
                    if (in_array($site->status, [1, 2]) && $site->show_checkin)
                        $sites_planned[$site->id] = "$site->name ($site->address, $site->suburb)";
                }
                asort($sites_planned);

                if (count($sites_planned)) {
                    $options .= '<optgroup label="Planned for today">';
                    foreach ($sites_planned as $site_id => $text)
                        $options .= "<option value='$site_id' >$text</option>";
                    $options .= '</optgroup>';
                    $headers = true;
                }
            }
        } elseif (Session::has('siteID')) {
            // Current Site logged into
            $site = Site::findOrFail(Session::get('siteID'));
            $options .= '<optgroup label="Current Site Logged In">';
            $sel_tag = ($selected == $site->id) ? ' selected ' : '';
            //$options .= "<option value='$site->id' $sel_tag>$site->suburb - $site->address ($site->code:$site->name)</option>";
            $options .= "<option value='$site->id' $sel_tag>$site->name ($site->address, $site->suburb)</option>";
            $options .= '</optgroup>';
            $headers = true;
        }

        // For CC users Alter Permission to View Site to supersede the Sitelist permission for employees with View Site but Not View Site.List
        if ($this->isCC() && $permission == 'view.site.list' && $this->hasPermission2('view.site'))
            $permission = 'view.site';

        // Company
        $company_level = $this->permissionLevel($permission, $this->company_id);
        $company_ids = [];
        if ($company_level == '99') $company_ids = $this->company->sites()->pluck('id')->toArray(); // All
        if ($company_level == '50') $company_ids = $this->company->sites()->pluck('id')->toArray(); // Our Company
        if ($company_level == '40') $company_ids = $this->areaSites()->pluck('id')->toArray(); // Supervisor for
        if ($company_level == '30') $company_ids = $this->company->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
        if ($company_level == '1') $company_ids = $this->company->sites()->pluck('id')->toArray(); // Delete / Sign Off All
        $sites_company = Site::whereIn('status', $status)->whereIn('id', $company_ids)->get();

        $sites_company_array = [];
        foreach ($sites_company as $site)
            $sites_company_array[$site->id] = "$site->name ($site->address, $site->suburb)";

        asort($sites_company_array);

        if (count($sites_company_array)) {
            if ($headers || ($this->company->parent_company && $this->company->subscription))
                $options .= '<optgroup label="' . $this->company->name . '">';
            foreach ($sites_company_array as $site_id => $text) {
                $sel_tag = ($selected == $site_id) ? ' selected ' : '';
                $options .= "<option value='$site_id' $sel_tag>$text </option>";
            }
            if ($headers || ($this->company->parent_company && $this->company->subscription))
                $options .= '</optgroup>';
        }

        // Parent Company
        if ($this->company->parent_company) {
            $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
            $parent_ids = [];
            if ($parent_level == '99') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // All
            if ($parent_level == '50') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // Our Company
            if ($parent_level == '30') $parent_ids = $this->company->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
            if ($parent_level == '20') $parent_ids = []; // Own Company
            if ($parent_level == '1') $parent_ids = $this->company->reportsTo()->sites()->pluck('id')->toArray(); // Delete / Sign Off All
            $sites_parent = Site::whereIn('status', $status)->whereIn('id', $parent_ids)->get();

            $sites_parent_array = [];
            if ($sites_parent) {
                foreach ($sites_parent as $site)
                    $sites_parent_array[$site->id] = "$site->name ($site->address, $site->suburb)";
            }
            asort($sites_parent_array);

            if (count($sites_parent_array)) {
                if ($headers || ($this->company->parent_company && $this->company->subscription))
                    $options .= '<optgroup label="' . $this->company->reportsTo()->name . '">';
                foreach ($sites_parent_array as $site_id => $text) {
                    $sel_tag = ($selected == $site_id) ? ' selected ' : '';
                    $options .= "<option value='$site_id' $sel_tag>$text</option>";
                }
                if ($headers || ($this->company->parent_company && $this->company->subscription))
                    $options .= '</optgroup>';
            }
        }

        // Parent Company Parent
        if ($this->company->parent_company && $this->company->reportsTo()->parent_company) {
            $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);
            $parent_ids = [];
            if ($parent_level == '99') $parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // All
            if ($parent_level == '50') $parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Our Company
            if ($parent_level == '30') $parent_ids = $this->company->reportsTo()->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
            if ($parent_level == '20') $parent_ids = []; // Own Company
            if ($parent_level == '1') $parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Delete / Sign Off All
            $sites_parent = Site::whereIn('status', $status)->whereIn('id', $parent_ids)->get();

            $sites_parent_array = [];
            if ($sites_parent) {
                foreach ($sites_parent as $site)
                    $sites_parent_array[$site->id] = "$site->name ($site->address, $site->suburb)";
            }
            asort($sites_parent_array);

            if (count($sites_parent_array)) {
                if ($headers || ($this->company->reportsTo()->parent_company && $this->company->reportsTo()->subscription))
                    $options .= '<optgroup label="' . $this->company->reportsTo()->reportsTo()->name . '">';
                foreach ($sites_parent_array as $site_id => $text) {
                    $sel_tag = ($selected == $site_id) ? ' selected ' : '';
                    $options .= "<option value='$site_id' $sel_tag>$text</option>";
                }
                if ($headers || ($this->company->reportsTo()->parent_company && $this->company->reportsTo()->subscription))
                    $options .= '</optgroup>';
            }
        }

        return $options;
    }


    /**
     * Verify if the user is allowed to perform certain action on a specific record
     *
     * @return boolean
     */
    public function allowed2($permission, $record = '')
    {
        list($action, $permissiontype) = explode('.', $permission, 2);

        // User can always view/edit own profile + add/view own doc
        if (($permission == 'view.user' || $permission == 'edit.user' || $permission == 'view.user.contact' || $permission == 'edit.user.contact'
                || $permission == 'view.user.construction' || $permission == 'view.user.security') && $record->id == $this->id
        )
            return true;

        //dd($permission);

        // ToDoo
        if ($permissiontype == 'todo') {
            if ($action == 'add') return true; // User can always add todoo
            if ($record->assignedTo()->contains('id', $this->id)) return true; // Todoo is assigned to user
            if ($record->created_by == $this->id) return true; // Todoo is assigned to user
            if ($record->type == 'hazard') {
                $hazard = SiteHazard::find($record->type_id);
                if ($action == 'view' && $this->allowed2('view.site.hazard', $hazard)) return true; // User is allowed to view Site Hazard
                if ($action == 'edit' && ($hazard->site->isSupervisorOrAreaSupervisor($this) || $this->allowed2('view.site.hazard', $hazard))) return true; // User Supervisor of Site
            }
            if ($record->type == 'equipment' && $action == 'view')
                if ($this->hasPermission2('view.equipment')) return true; // User has the permission to view
            if ($record->type == 'equipment' && $action == 'edit')
                if ($this->hasPermission2('edit.equipment') && $this->id == $record->created_by) return true; // User created equipment ToDoo
            if ($record->type == 'incident prevent' || $record->type == 'incident' || $record->type == 'incident review') {
                $incident = SiteIncident::find($record->type_id);
                if ($this->allowed2('edit.site.incident', $incident)) return true; // User is allowed to view Site Incident ToDoo
            }

            return false;
        }

        // Support Tickets
        if ($permission == 'view.support.ticket' || $permission == 'edit.support.ticket') {
            if ($record->created_by == $this->id) return true; // User can always view/edit own record
            if ($this->hasPermission2('edit.user.security') && in_array($record->created_by, $this->company->users()->pluck('id')->toArray())) return true; // User belongs to own or child company
            if (in_array($this->id, [3, 109])) return true; // Fudge, Jo

            return false;
        }

        // Company Documents
        if ($permissiontype == 'company.doc') {
            if ($action == 'add') {
                if ($this->hasAnyPermission2('add.docs.acc.pub|add.docs.acc.pri|add.docs.adm.pub|add.docs.adm.pri|add.docs.con.pub|add.docs.con.pri|add.docs.whs.pub|add.docs.whs.pri')) return true;
            } else {
                $category = CompanyDocCategory::find($record->category_id);
                $doc_permission = ($category->private) ? "$action.docs.$category->type.pri" : "$action.docs.$category->type.pub";
                // User has 'All' permission to this record
                if ($this->permissionLevel($doc_permission, $record->company_id) == 99 || $this->permissionLevel($doc_permission, $record->company_id) == 1) return true;  // User has 'All' permission to this record

                // Document is For User Company but isn't the owner of it
                // Only allowed to edit/delete documents with status pending/rejected ie. 2 or 3
                if ($record->for_company_id == $this->company_id && $record->company_id != $this->company_id) {
                    if ($action == 'view' || $record->status == '2' || $record->status == '3') {
                        if ($this->permissionLevel($doc_permission, $record->company_id) == 20) return true; // User has 'Own Company' permission so record must be 'for' their company
                    }
                }
            }

            return false;
        }

        // User Documents
        if ($permissiontype == 'user.doc') {
            if ($action == 'add') {
                if (($permission == 'add.user.doc' && $this->id = Auth::user()->id) || $this->hasAnyPermission2('add.docs.acc.pub|add.docs.acc.pri|add.docs.adm.pub|add.docs.adm.pri|add.docs.con.pub|add.docs.con.pri|add.docs.whs.pub|add.docs.whs.pri')) return true;
            } else {
                $category = UserDocCategory::find($record->category_id);
                $doc_permission = ($category->private) ? "$action.docs.$category->type.pri" : "$action.docs.$category->type.pub";
                // User has 'All' permission to this record
                if ($this->permissionLevel($doc_permission, $record->company_id) == 99 || $this->permissionLevel($doc_permission, $record->company_id) == 1) return true;  // User has 'All' permission to this record

                // CC has authority over User doc
                $cc = Company::find(3);
                $cc_child = (in_array($record->company_id, flatten_array($cc->subCompanies(3)))) ? true : false;
                if ($cc_child && ($this->permissionLevel($doc_permission, 3) == 99 || $this->permissionLevel($doc_permission, 3) == 1)) return true;  // User has 'All' permission to this record

                // BlueEcho has authority over User doc
                $be = Company::find(210);
                $be_child = (in_array($record->company_id, flatten_array($be->subCompanies(210)))) ? true : false;
                if ($be_child && ($this->permissionLevel($doc_permission, 210) == 99 || $this->permissionLevel($doc_permission, 210) == 1)) return true;  // User has 'All' permission to this record


                // Document is For User Company but isn't the owner of it
                // Only allowed to edit/delete documents with status pending/rejected ie. 2 or 3
                if ($record->for_company_id == $this->company_id && $record->company_id != $this->company_id) {
                    if ($action == 'view' || $record->status == '2' || $record->status == '3') {
                        if ($this->permissionLevel($doc_permission, $record->company_id) == 20) return true; // User has 'Own Company' permission so record must be 'for' their company
                    }
                }

                // Document is for User, User is Primary Contact for User
                if ($record->user_id == Auth::user()->id || $record->company->primary_user == Auth::user()->id) {
                    if ($action == 'view' || $record->company->primary_user == Auth::user()->id)
                        return true;

                    if ($record->user_id == Auth::user()->id && ($record->status == '2' || $record->status == '3'))
                        return true;
                }

            }

            return false;
        }

        // Site Incident
        // - User allowed to view Incident if they are involved in it, witness or assigned a task or review
        if ($action == 'view' && $permissiontype == 'site.incident') {
            if ($record->people->where('user_id', $this->id)->first()) return true;  // Involved Person
            if ($record->witness->where('user_id', $this->id)->first()) return true;  // Witness Statement
            if ($record->hasAssignedTask($this->id)) return true; // Assigned task
            $reviewsBy = $record->reviewsBy();
            if (isset($reviewsBy[$this->id])) return true; // Reviewed by
        }


        // SDS add - Only Fudge, Jo, Tara, Rob, Demi
        if (in_array($permission, ['add.sds', 'edit.sds', 'del.sds']) && in_array($this->id, ['3', '109', '351', '6', '424'])) return true;

        // Site QA Master templates
        //if ($permissiontype == 'site.qa' && $record && $record->site_id == null && $record->master == 1 && $this->hasPermission2('add.site.qa')) return true;    //in_array($this->id, ['3', '109', '351', '6'])) return true;


        // Get permission levels
        $company_level = $this->permissionLevel($permission, $this->company_id);
        $parent_level = $this->permissionLevel($permission, $this->company->reportsTo()->id);

        // Return false if Company + Parent levels == 0
        if ($company_level == 0 && $parent_level == 0)
            return false;

        if ($action == 'add') // Don't need any further checking because 'add' doesn't affect any specific record.
            return true;      //  - also we know they must have 'add' permission if they reached this far.
        else {
            //  ['0' => 'No', '99' => "All", '50' => "Our Company", '40' => 'Supervisor for', '30' => 'Planned for', '20' => 'Own Company', '10' => "Individual Only"]

            // Users
            if ($permissiontype == 'user' || $permissiontype == 'user.contact' || $permissiontype == 'user.security' || $permissiontype == 'user.construction') {
                if ($this->authUsers($permission)->contains('id', $record->id)) return true;

                return false;
            }

            // Companies
            if ($permissiontype == 'company') {
                if ($action == 'del' && $record->id == $this->company_id) return false; // User can't delete own company
                if ($action == 'sig' && $record->id == $this->company_id && $record->parent_company) return false; // User can't sign off own company if has parent
                if ($this->authCompanies($permission)->contains('id', $record->id)) return true;

                return false;
            }

            // Company Accounting + Leave
            if ($permissiontype == 'company.acc' || $permissiontype == 'company.leave') {
                if ($this->authCompanies($permission)->contains('id', $record->id)) return true;

                return false;
            }

            // Company WHS + Construction
            if ($permissiontype == 'company.con' || $permissiontype == 'compliance.manage') {
                // Company has no parent or Uses doesn't belong to this company
                // ie Users can't edit their own company record if they have a parent
                if ((!$record->parent_company || $this->company_id != $record->id) && $this->authCompanies($permission)->contains('id', $record->id)) return true;

                return false;
            }

            // Client Planner Email
            if ($permissiontype == 'client.planner.email') {
                if ($this->authSites($permission)->contains('id', $record->id)) return true;
            }

            // Sites + Planners (Weekly/Site/Trade)
            if ($permissiontype == 'site' || $permissiontype == 'site.admin' || $permissiontype == 'site.zoho.fields' || $permissiontype == 'site.attendance' || $permissiontype == 'weekly.planner' || $permissiontype == 'site.planner' || $permissiontype == 'trade.planner' || $permissiontype == 'preconstruction.planner') {
                if ($this->authSites($permission)->contains('id', $record->id)) return true;
            }

            // Sites + Planners (Site/Trade)
            if ($permissiontype == 'site.planner' || $permissiontype == 'trade.planner') {
                if ($action == 'edit' && $this->permissionLevel($permission, 3) == 40 && $record->status == 2 && $record->company_id == $this->company_id) return true; // Allow supervisors edit access to all maintenance sites
            }

            // Site Incident + Accident + Hazard
            if ($permissiontype == 'site.accident' || $permissiontype == 'site.incident' || $permissiontype == 'site.hazard') {
                if ($company_level == 30 || $company_level == 40 || $parent_level == 30 || $parent_level == 40) {
                    // Planned For '30' or Supervisor For '40' so check site
                    if ($this->authSites($permission)->contains('id', $record->site_id)) return true;
                }
                // check users
                if ($this->authUsers($permission)->contains('id', $record->created_by)) return true;

                if ($record->site_id == '809') { // 0003-Vehicles Cape Cod
                    // Fudge, Gary, Kirstie, Tara, Georgie, Ross
                    if ($action == 'view' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '7', '108', '351', '458', '1155'])) return true;
                    // Fudge, Gary, Kirstie, Tara, Ross
                    if ($action == 'edit' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '7', '108', '351', '1155'])) return true;
                    // Fudge, Kirstie, Tara, Ross
                    if ($action == 'del' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '108', '351', '1155'])) return true;
                } else {
                    // User always allowed to view own Incident / Hazard
                    if ($action == 'view' && $this->id == $record->created_by) return true;

                    // User always allowed to view Hazard of site they currently logged into
                    if ($action == 'view' && $permissiontype == 'site.hazard' && Session::has('siteID') && Session::get('siteID') == $record->site_id) return true;
                }

                return false;
            }

            // Site Maintenance
            if ($permissiontype == 'site.maintenance') {
                if ($action == 'view' && $this->permissionLevel($permission, 3) == 30 && $record->assigned_to == $this->company_id) return true; // Request is Assigned to user's company
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($this->permissionLevel($permission, 3) == 40 && $record->super_id == $this->id) return true; // User has 'Supervisor For' permission to this record
                return false;
            }

            // Site Inspection Reports (Electrical/Plumbing)
            if ($permissiontype == 'site.inspection') {
                if ($action == 'view' && $this->permissionLevel($permission, 3) == 30 && $record->assigned_to == $this->company_id) return true; // Request is Assigned to user's company
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

                return false;
            }

            // Site Inspection Reports (WHS)
            if ($permissiontype == 'site.inspection.whs') {
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($this->permissionLevel($permission, 3) == 40 && $record->super_id == $this->id) return true; // User has 'Supervisor For' permission to this record
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

                return false;
            }


            // Site Scaffold Handover
            if ($permissiontype == 'site.scaffold.handover') {
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

                return false;
            }

            // Site QA Templates
            if ($permissiontype == 'site.qa.templates') {
                if ($this->hasPermission2($permission) && $record->company_id == $this->company_id) return true; // User belong to same company record
                return false;
            }

            // Site (Doc, QA, Asbestos, Export, ProjectSupply) + Attendance + Compliance + Safety Doc
            if ($permissiontype == 'site.doc' || $permissiontype == 'site.qa' || $permissiontype == 'site.asbestos' || $permissiontype == 'site.export' || $permissiontype == 'site.project.supply' ||
                $permissiontype == 'roster' || $permissiontype == 'compliance' || $permissiontype == 'safety.doc'
            ) {
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

                return false;

            }

            // Toolbox + WMS
            if ($permissiontype == 'toolbox' || $permissiontype == 'wms') {
                if ($permissiontype == 'toolbox' && $action == 'view' && $record->isAssignedToUser($this)) return true; // Toolbox Assigned to user
                if ($action == 'view' && $record->master && $record->company_id == '3') return true; // User can view library
                if ($this->permissionLevel($permission, $record->company_id) == 99 || $this->permissionLevel($permission, $record->company_id) == 1) return true;  // User has 'All' permission to this record
                if ($this->permissionLevel($permission, $record->company_id) == 20 && $record->for_company_id == $this->company_id) return true; // User has 'Own Company' permission so record must be 'for' their company
                return false;
            }

            // Safetytip + Notify + SDS
            if ($permissiontype == 'safetytip' || $permissiontype == 'notify' || $permissiontype == 'sds') {
                if ($this->hasPermission2($permission)) return true;

                return false;
            }

            // Equipment
            if ($permissiontype == 'equipment' || $permissiontype == 'equipment.stocktake') {
                if ($this->hasPermission2($permission)) return true; // User has the permission
                return false;
            }

            // Company Doc Review
            if ($permissiontype == 'company.doc.review') {
                if ($this->hasPermission2($permission)) return true; // User has the permission
                return false;
            }

            // Site Extension (Contract Time
            if ($permissiontype == 'site.extension') {
                if ($this->hasPermission2($permission)) return true; // User has the permission
                return false;
            }

            // Settings
            if ($permissiontype == 'settings') {
                if ($this->hasPermission2($permission) && $record->company_id == $this->company_id) return true; // User belong to same company record
                return false;
            }

            // Area Super - Needs to be fixed for Multiple level 2 companies
            if ($permissiontype == 'area.super') {
                if ($this->permissionLevel($permission, $record->company_id) && $record->company_id == $this->company_id) return true; // User belong to same company record
                return false;
            }

            return false;
        }
    }
}