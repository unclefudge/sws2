<?php

namespace App\Traits;

use App\Models\Company\Company;
use App\Models\Company\CompanyDocCategory;
use App\Models\Misc\Permission2;
use App\Models\Misc\Role2;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteMaintenance;
use App\Models\User\UserDocCategory;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Session;


trait UserRolesPermissions
{

    /*
    |--------------------------------------------------------------------------
    | Performance notes
    |--------------------------------------------------------------------------
    | This trait is used across most of SafeWorkSite to decide what a user can
    | view, add, edit, delete or sign off. A lot of pages call these helpers many
    | times in loops, so small repeated queries here can slow the whole site down.
    |
    | The static caches below are request-level caches only. They live for the
    | current PHP request, then reset automatically on the next page load. They do
    | NOT permanently cache permissions, so permission/role changes in the database
    | will still be picked up on the next request.
    */

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

    /*
     * Return all role slugs for this user, cached for this PHP request.
     *
     * Why: hasRole2() and hasAnyRole2() are often called many times while
     * rendering menus/buttons. The old approach queried the roles relationship
     * each time. This loads the user's role slugs once, then checks the array.
     */
    public function roleSlugs2()
    {
        static $cache = [];

        $key = $this->id;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        return $cache[$key] = DB::table('role_user')->join('roles', 'role_user.role_id', '=', 'roles.id')->where('role_user.user_id', $this->id)->pluck('roles.slug')->toArray();
    }

    /*
     * Check if a user has a certain role slug.
     *
     * Uses roleSlugs2(), so repeated role checks do not keep hitting the DB.
     *
     * @return boolean
     */
    public function hasRole2($role)
    {
        return in_array($role, $this->roleSlugs2());
    }

    /*
     * Check if a user has any of the pipe-separated role slugs.
     * Example: web-admin|mgt-general-manager
     *
     * Uses one cached role-slug array instead of one DB query per role.
     *
     * @return boolean
     */
    public function hasAnyRole2($roles)
    {
        $roles_array = explode('|', $roles);

        return count(array_intersect($roles_array, $this->roleSlugs2())) > 0;
    }

    /*
     * Check if a user has a 'role' with a company
     *
     * @return boolean
     */
    public function hasRoleCompany($company_id)
    {
        $company_role_ids = Role2::where('company_id', $company_id)->pluck('id')->toArray();

        return (DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->first()) ? true : false;
    }

    /*
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

    /*
     * Detach role from a user for company 'company'
     *
     * @param $permission
     * @return int
     */
    public function detachRole2($role)
    {
        return DB::table('role_user')->where(['user_id' => $this->id, 'role_id' => $role])->delete();
    }

    /*
     * Detach all roles from a user for company 'company'
     *
     * @return int
     */
    public function detachAllRoles2($company_id)
    {
        $company_role_ids = Role2::where('company_id', $company_id)->pluck('id')->toArray();

        return DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->delete();
    }

    /*
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

    /*
     * User roles separated by Comma
     * @return string
     */
    public function parentRolesSBC()
    {
        $role_ids = Role2::where('company_id', $this->company->reportsTo()->id)->pluck('id')->toArray();
        $roles = DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $role_ids)->join('roles', 'role_user.role_id', '=', 'roles.id')->orderBy('roles.name')->get();

        $string = '';
        foreach ($roles as $role)
            $string .= $role->name . ', ';

        return rtrim($string, ', ');
    }



    /*   --                 --   */
    /*   --   Permissions   --   */
    /*   --                 --   */


    /*
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

    /*
     * Detach permission from a user for company 'company'
     *
     * @param $permission
     * @return int
     */
    public function detachPermission2($permission, $company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'permission_id' => $permission, 'company_id' => $company_id])->delete();
    }

    /*
     * Detach all permissions from a user for 'company'
     *
     * @return int
     */
    public function detachAllPermissions2($company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'company_id' => $company_id])->delete();
    }

    /*
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

    /*
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

    /*
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

    /*
     * Get a permission record by slug, using a request-level lookup table.
     *
     * Why: permissionLevel() is called constantly across the app. The old code
     * did Permission2::where('slug', ...)->first() over and over. This loads the
     * small permissions table once for this request and then reads from memory.
     */
    public function permissionBySlug($slug)
    {
        static $permissions = null;

        if ($permissions === null) {
            $permissions = Permission2::all()->keyBy('slug');
        }

        return $permissions->get($slug);
    }

    /*
     * Get a permission record by id from a request-level lookup table.
     *
     * Used by extraUserPermissionsText() so a list of extra permissions does not
     * do Permission2::find() once for every row.
     */
    public function permissionById($id)
    {
        static $permissions = null;

        if ($permissions === null) {
            $permissions = Permission2::all()->keyBy('id');
        }

        return $permissions->get($id);
    }

    /*
     * Get a Company Document category by id from a request-level lookup table.
     *
     * allowed2('*.company.doc') may run in a loop over many documents. Loading
     * all categories once avoids one CompanyDocCategory::find() per document.
     */
    public function companyDocCategoryById($id)
    {
        static $categories = null;

        if ($categories === null) {
            $categories = CompanyDocCategory::all()->keyBy('id');
        }

        return $categories->get($id);
    }

    /*
     * Get a User Document category by id from a request-level lookup table.
     *
     * Same idea as companyDocCategoryById(), but for user document permission
     * checks. This prevents category N+1 queries on user-document-heavy pages.
     */
    public function userDocCategoryById($id)
    {
        static $categories = null;

        if ($categories === null) {
            $categories = UserDocCategory::all()->keyBy('id');
        }

        return $categories->get($id);
    }

    /*
     * Additional permissions given to a user 'on top' granted by their role
     *
     * @return collection
     */
    public function extraUserPermissions($company_id)
    {
        return DB::table('permission_user')->where(['user_id' => $this->id, 'company_id' => $company_id])->get();
    }

    /*
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
                // Use cached permission lookup instead of Permission2::find() per row.
                $permission = $this->permissionById($e->permission_id);
                if ($permission) {
                    $str .= "<li>$permission->name (" . $levels[$e->level] . ")</li>";
                }
            }
            $str .= '</ul>';

            return $str;
        }

        return '';
    }


    /*
     * Determine level of a permission for a 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function userPermissionLevel($permission, $company_id)
    {
        // Cache all direct user permissions for this user/company pair.
        // This replaces repeated permission_user lookups for each permission slug.
        static $permissionUserCache = [];

        $permission_id = $permission;

        if (!is_int($permission)) {
            $perm = $this->permissionBySlug($permission);

            if (!$perm) {
                return 0;
            }

            $permission_id = $perm->id;
        }

        $cacheKey = $this->id . '|' . $company_id;

        if (!array_key_exists($cacheKey, $permissionUserCache)) {
            // Load all direct permissions once, then use keyBy('permission_id')
            // so each permission level check is just a collection lookup.
            $permissionUserCache[$cacheKey] = DB::table('permission_user')->where('user_id', $this->id)->where('company_id', $company_id)->get()->keyBy('permission_id');
        }

        $permissionUser = $permissionUserCache[$cacheKey]->get($permission_id);

        return $permissionUser ? (int)$permissionUser->level : 0;
    }

    /*
     * Determine level of a permission for a 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function rolesPermissionLevel($permission, $company_id)
    {
        // These caches break role permission lookup into three reusable pieces:
        // 1) roles that belong to the company
        // 2) this user's roles within that company
        // 3) permission levels attached to those roles
        static $companyRoleIdsCache = [];
        static $userRoleIdsCache = [];
        static $permissionRolesCache = [];

        $permission_id = $permission;

        if (!is_int($permission)) {
            $perm = $this->permissionBySlug($permission);

            if (!$perm) {
                return 0;
            }

            $permission_id = $perm->id;
        }

        $companyRoleKey = (string)$company_id;

        if (!array_key_exists($companyRoleKey, $companyRoleIdsCache)) {
            // Load company role IDs once per company_id.
            $companyRoleIdsCache[$companyRoleKey] = Role2::where('company_id', $company_id)->pluck('id')->toArray();
        }

        $company_role_ids = $companyRoleIdsCache[$companyRoleKey];

        $userRoleKey = $this->id . '|' . $company_id;

        if (!array_key_exists($userRoleKey, $userRoleIdsCache)) {
            // Load this user's matching roles once per user/company pair.
            $userRoleIdsCache[$userRoleKey] = DB::table('role_user')->where('user_id', $this->id)->whereIn('role_id', $company_role_ids)->pluck('role_id')->toArray();
        }

        $user_role_ids = $userRoleIdsCache[$userRoleKey];

        if (!count($user_role_ids)) {
            return 0;
        }

        $permissionRoleKey = $this->id . '|' . $company_id;

        if (!array_key_exists($permissionRoleKey, $permissionRolesCache)) {
            // Load all permission_role rows for the user's roles once, grouped by
            // permission_id so each check can quickly get the max level.
            $permissionRolesCache[$permissionRoleKey] = DB::table('permission_role')->whereIn('role_id', $user_role_ids)->where('company_id', $company_id)->get()->groupBy('permission_id');
        }

        $permissionRoles = $permissionRolesCache[$permissionRoleKey]->get($permission_id, collect());

        return (int)($permissionRoles->max('level') ?: 0);
    }

    /*
     * Determine level of a permission for 'company'
     *
     * @param  $permission , company_id
     * @return int
     */
    public function permissionLevel($permission, $company_id)
    {
        // Final level cache for this user + permission slug/id + company.
        // This keeps repeated hasPermission2()/allowed2() calls fast.
        static $cache = [];

        $key = $this->id . '|' . $permission . '|' . $company_id;

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $user_level = $this->userPermissionLevel($permission, $company_id);
        $role_level = $this->rolesPermissionLevel($permission, $company_id);

        return $cache[$key] = ($user_level > $role_level) ? $user_level : $role_level;
    }

    /*
     * A list of users this user has authority over
     * ie user has authority own themselves + maybe own companies/child users (if appropriate permission granted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authUsers($permission, $status = '')
    {
        // Returns the user records this user can act on for the given permission.
        // Cached because menu/buttons/list rows often ask the same question.
        static $cache = [];

        $key = $this->id . '|authUsers|' . $permission . '|' . json_encode($status);

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

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

        $merged_ids = array_unique(array_merge($company_ids, $parent_ids, [$this->id]));

        return $cache[$key] = ($status != '')
            ? User::where('status', $status)->whereIn('id', $merged_ids)->get()
            : User::whereIn('id', $merged_ids)->get();
    }

    /*
     * A list of company this user has authority over
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authCompanies($permission, $status = '')
    {
        // Returns the company records this user can act on for the given permission.
        // Cached per request to avoid recalculating child/parent company access.
        static $cache = [];

        $key = $this->id . '|authCompanies|' . $permission . '|' . json_encode($status);

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

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

        $merged_ids = array_unique(array_merge($company_ids, $parent_ids));

        return $cache[$key] = ($status != '')
            ? Company::where('status', $status)->whereIn('id', $merged_ids)->get()
            : Company::whereIn('id', $merged_ids)->get();
    }

    /*
     * A list of sites this user has authority over
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function authSites($permission, $status = '')
    {
        // Returns the site records this user can act on for the given permission.
        // Cached because allowed2() can call this many times in table/list loops.
        static $cache = [];

        $key = $this->id . '|authSites|' . $permission . '|' . json_encode($status);

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        // Alter Permission to View Site to supersede the Sitelist permission
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
        $parent_parent_ids = [];
        if ($parent_level == '99') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // All
        if ($parent_level == '50') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Our Company
        if ($parent_level == '30') $parent_parent_ids = $this->company->reportsTo()->sitesPlannedFor()->pluck('id')->toArray(); // Planned for
        if ($parent_level == '20') $parent_parent_ids = []; // Own Company
        if ($parent_level == '1') $parent_parent_ids = $this->company->reportsTo()->reportsTo()->sites()->pluck('id')->toArray(); // Delete / Sign Off All

        $merged_ids = array_unique(array_merge($company_ids, $parent_ids, $parent_parent_ids));

        if ($status != '') {
            if (is_array($status)) {
                return $cache[$key] = Site::whereIn('status', $status)->whereIn('id', $merged_ids)->get();
            }

            return $cache[$key] = Site::where('status', $status)->whereIn('id', $merged_ids)->get();
        }

        return $cache[$key] = Site::whereIn('id', $merged_ids)->orderBy('name')->get();
    }

    /*
     * A dropdown list of sites this user has authority over
     *
     * @parms Permission, Status (site), Prompt, Started (whether Site has tasks on it)
     * @return array
     */
    public function authSitesSelect($permission, $status = '', $prompt = '', $started = '')
    {
        $sites = $this->authSites($permission, $status);

        $startedSiteIds = collect();

        if ($started) {
            // This single query grabs all started/planned site IDs up front.
            $startedSiteIds = SitePlanner::whereIn('site_id', $sites->pluck('id'))->pluck('site_id')->map(function ($id) {return (int)$id;})->flip();
        }

        $array = [];

        foreach ($sites as $site) {
            if ($started && !$startedSiteIds->has((int)$site->id)) {
                // When $started is requested, skip sites with no planner record.
                continue;
            }

            $array[$site->id] = $site->name; //"$site->code:$site->name";
        }

        asort($array);

        if ($prompt == 'ALL') {
            return ($prompt && count($array) > 1) ? ['all' => 'All Sites'] + $array : $array;
        }

        return ($prompt && count($array) > 1) ? ['' => 'Select Site'] + $array : $array;
    }

    /*
     * A dropdown list of sites this user has authority over
     *
     * @parms Permission, Status (site), Prompt, Started (whether Site has tasks on it)
     * @return array
     */
    public function authSitesSelect2Options($permission, $selected = null, $status = 1)
    {
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


    /*
     * Verify if the user is allowed to perform certain action on a specific record
     *
     * @return boolean
     */
    public function allowed2($permission, $record = '')
    {
        // Main record-level permission gate used across the site.
        //
        // Format: action.permission.type, e.g. view.site.hazard or edit.user.doc.
        // This method first handles special-case rules, then falls back to the
        // cached permission/auth helper methods above.
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
                // Category determines whether the doc permission is public/private
                // and admin/construction/WHS/accounting. Use cached lookup to avoid
                // CompanyDocCategory::find() for every document in a list.
                $category = $this->companyDocCategoryById($record->category_id);
                if (!$category) return false;

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
                // Keep this as a strict comparison so we don't mutate the user model.
                if (($permission == 'add.user.doc' && (int)$this->id === (int)Auth::id()) || $this->hasAnyPermission2('add.docs.acc.pub|add.docs.acc.pri|add.docs.adm.pub|add.docs.adm.pri|add.docs.con.pub|add.docs.con.pri|add.docs.whs.pub|add.docs.whs.pri')) {
                    return true;
                }
            } else {
                // Same category lookup cache as company docs, but for user docs.
                $category = $this->userDocCategoryById($record->category_id);
                if (!$category) return false;

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
            if ($record->site_id == '809' && $this->id == '458') return true; // Georgie (458) access to site 0003-vehicles (809)
            $reviewsBy = $record->reviewsBy();
            if (isset($reviewsBy[$this->id])) return true; // Reviewed by
        }


        // SDS add - Only Fudge, Demi, Kirstie, Ross
        if (in_array($permission, ['add.sds', 'edit.sds', 'del.sds']) && in_array($this->id, ['3', '424', '108', '1155'])) return true;

        // Construction Standards add - Only Fudge, Demi, Kirstie, Ross
        if (in_array($permission, ['add.construction.doc', 'edit.construction.doc', 'del.construction.doc']) && in_array($this->id, ['3', '424', '108', '1155'])) return true;

        // Site QA Master templates
        //if ($permissiontype == 'site.qa' && $record && $record->site_id == null && $record->master == 1 && $this->hasPermission2('add.site.qa')) return true;    //in_array($this->id, ['3', '108', '1155'])) return true;


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

            // Company Accounting + Leave + Notes
            if ($permissiontype == 'company.acc' || $permissiontype == 'company.leave' || $permissiontype == 'company.note') {
                if ($this->authCompanies($permission)->contains('id', $record->id)) return true;
                return false;
            }

            // Company WHS + construction
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
                    // Fudge, Kirstie, Georgie, Ross
                    if ($action == 'view' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '108', '458', '1155'])) return true;
                    // Fudge, Kirstie, Ross
                    if ($action == 'edit' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '108', '1155'])) return true;
                    // Fudge, Kirstie, Ross
                    if ($action == 'del' && $permissiontype == 'site.hazard' && in_array($this->id, ['3', '108', '1155'])) return true;
                } else {
                    // User always allowed to view own Incident / Hazard
                    if ($action == 'view' && $this->id == $record->created_by) return true;

                    // User always allowed to view Hazard of site they currently logged into
                    if ($action == 'view' && $permissiontype == 'site.hazard' && Session::has('siteID') && Session::get('siteID') == $record->site_id) return true;
                }

                return false;
            }

            // Site Maintenance + Prac Completion + FOC Requirements
            if ($permissiontype == 'site.maintenance' || $permissiontype == 'prac.completion' || $permissiontype == 'site.foc') {
                if ($action == 'view' && $this->permissionLevel($permission, 3) == 30 && in_array($this->company_id, $record->assignedTo())) return true; // Request is Assigned to user's company
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($this->permissionLevel($permission, 3) == 40 && $record->super_id == $this->id) return true; // User has 'Supervisor For' permission to this record
                // Super has access to completed Requests if they have been a supervisor of another request same site
                if ($this->permissionLevel($permission, 3) == 40 && $record->status == 0) {
                    $site_ids = SiteMaintenance::where('super_id', $this->id)->pluck('site_id')->toArray();
                    if (in_array($record->site_id, $site_ids)) return true;
                }
                return false;
            }

            // Site Inspection Reports (Electrical/Plumbing)
            if ($permissiontype == 'site.inspection') {
                if ($this->permissionLevel($permission, 3) == 30 && $record->assigned_to == $this->company_id) return true; // Request is Assigned to user's company
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                //if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

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
                if ($this->permissionLevel($permission, 3) == 30 && $record->created_by == $this->id) return true; // User has 'Planned For' permission to this record
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;

                return false;
            }

            // Site Notes
            if ($permissiontype == 'site.note') {
                if ($this->id == $record->created_by) return true;  // Created by User
                if ($this->authSites($permission)->contains('id', $record->site_id)) return true;  // Supervisor for site

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

            // Safetytip + Notify + SDS + Construction Doc
            if ($permissiontype == 'safetytip' || $permissiontype == 'notify' || $permissiontype == 'sds' || $permissiontype == 'construction.doc') {
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

            // Site Extension (Contract Time)
            if ($permissiontype == 'site.extension') {
                if ($this->hasPermission2($permission)) return true; // User has the permission
                return false;
            }

            // Weekly Supervisor Checklist
            if ($permissiontype == 'super.checklist') {
                if ($this->permissionLevel($permission, 3) == 99 || $this->permissionLevel($permission, 3) == 1) return true;  // User has 'All' permission to this record
                if ($record->super_id == $this->id) return true; // Super permission so record must be 'for' themelves
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