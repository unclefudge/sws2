<?php

namespace App\Models\Site;

use DB;
use Mail;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Comms\Notify;
use App\Models\Comms\NotifyUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Models\Site\Planner\Task;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class Site extends Model {

    protected $table = 'sites';
    protected $fillable = [
        'name', 'code', 'slug',
        'address', 'address2', 'suburb', 'state', 'postcode', 'country',
        'photo', 'notes', 'client_id', 'client_intro',
        'client1_title', 'client1_firstname', 'client1_lastname', 'client1_mobile', 'client1_email',
        'client2_title', 'client2_firstname', 'client2_lastname', 'client2_mobile', 'client2_email',
        'client_phone', 'client_phone2', 'client_phone_desc', 'client_phone2_desc', 'client_email', 'client_email2',
        'contract_sent', 'contract_signed', 'deposit_paid', 'council_approval', 'engineering_cert', 'engineering', 'construction_rcvd', 'hbcf_start', 'consultant_name',
        'completion_signed', 'completed', 'status', 'company_id', 'created_by', 'updated_by'];
    protected $dates = ['completed', 'contract_sent', 'contract_signed', 'deposit_paid', 'council_approval', 'completion_signed', 'engineering_cert', 'construction_rcvd', 'hbcf_start',];

    /**
     * A Site belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * A Site belongs to a client
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Misc\Client');
    }

    /**
     * A Site has many SiteAttendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function attendance()
    {
        return $this->hasMany('App\Models\Site\Planner\SiteAttendance');
    }

    /**
     * A Site has many ClientPlannerEmails
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function clientPlannerEmails()
    {
        return $this->hasMany('App\Models\Client\ClientPlannerEmail');
    }

    /**
     * A Site has many EquipmentLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function equipmentLocation()
    {
        return $this->hasOne('App\Models\Misc\Equipment\EquipmentLocation');
    }

    /**
     * A Site has many EquipmentLocationItems
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function equipmentItems()
    {
        return ($this->equipmentLocation) ? EquipmentLocationItem::where('location_id', $this->equipmentLocation->id)->get() : [];
    }

    /**
     * A Site has many SiteHazards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hazards()
    {
        return $this->hasMany('App\Models\Site\SiteHazard');
    }

    /**
     * Does a Site has any open SiteHazards
     *
     * @return bool
     */
    public function hasHazardsOpen()
    {
        return $this->hazards->filter(function ($record) {
            return $record->status;
        })->count();
    }

    /**
     * A Site has open SiteHazards
     *
     * @return bool
     */
    public function hazardsOpen()
    {
        return $this->hazards->filter(function ($record) {
            return $record->status;
        });
    }

    /**
     * A Site has many SiteAccidents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accidents()
    {
        return $this->hasMany('App\Models\Site\SiteAccident');
    }

    /**
     * A Site has open SiteAccidents
     *
     * @return bool
     */
    public function hasAccidentsOpen()
    {
        return $this->accidents->filter(function ($record) {
            return $record->status;
        })->count();
    }

    /**
     * A Site has many Documents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Site\SiteDoc');
    }


    /**
     * A Site has many documents of certain 'type'
     */
    public function docsOfType($type, $status = '1')
    {
        return $this->docs->filter(function ($record) use ($status, $type) {
            return $record->status == $status && $record->type == $type;
        });
    }

    /**
     * A Site has many QA Reports
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function qaReports()
    {
        return $this->hasMany('App\Models\Site\SiteQa');
    }

    /**
     * A Site has QA Report ($x)
     *
     * @return bool
     */
    public function hasTemplateQa($id)
    {
        return $this->qaReports->contains('master_id', $id);
    }

    /**
     * A Site has Old or New QA
     *
     * @return bool
     */
    public function hasOldQa()
    {
        $qa = SiteQa::where('site_id', $this->id)->first();
        if ($qa && $qa->master_id < 100)
            return true;

        return false;
    }

    /**
     * A Site has many SiteMaintenance
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function maintenance()
    {
        return $this->hasMany('App\Models\Site\SiteMaintenance');
    }

    /**
     * A Site has open SiteMaintenance
     *
     * @return bool
     */
    public function hasMaintenanceOpen()
    {
        return $this->maintenance->filter(function ($record) {
            return $record->status;
        })->count();
    }

    /**
     * A Site has active SiteMaintenance
     *
     * @return bool
     */
    public function hasMaintenanceActive()
    {
        return $this->maintenance->filter(function ($record) {
            return $record->status > 0;
        })->count();
    }

    /**
     * A Site has many supervisors
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function supervisors()
    {
        return $this->belongsToMany('App\User', 'site_supervisor', 'site_id', 'user_id');
    }

    /**
     * A dropdown list of supervisors for the site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supervisorsSelect($prompt = '')
    {
        $array = [];
        foreach ($this->supervisors as $user) {
            if ($user->status)
                $array[$user->id] = $user->fullname;
        }
        asort($array);

        return ($prompt) ? $array = array('' => 'Select supervisor(s)') + $array : $array;
    }

    /**
     * An array of supervisors emails for this site
     *
     * @return string
     */
    public function supervisorsEmails()
    {
        $array = [];   // || in_array($super->id, $user->subSupervisors()->pluck('id')->toArray()
        foreach ($this->supervisors as $user) {
            if ($user->status && validEmail($user->email))
                $array[] = $user->email;
            foreach ($user->areaSupervisors() as $area) {
                if ($area->status && validEmail($area->email))
                    $array[] = $area->email;
            }
        }

        return $array;
    }

    /**
     * A list of supervisors for this site
     *
     * @return string
     */
    public function supervisorsSBC()
    {
        $string = '';
        foreach ($this->supervisors as $user) {
            if ($user->status)
                $string .= $user->fullname . ', ';
        }

        return rtrim($string, ', ');
    }

    /**
     * A list of supervisors for this site
     *
     * @return string
     */
    public function supervisorsContactSBC()
    {
        $string = '';
        foreach ($this->supervisors as $user) {
            if ($user->status)
                $string .= $user->fullname . ' (' . $user->phone . '), ';
        }

        return rtrim($string, ', ');
    }

    /**
     * A list of supervisors for this site 'first' name only
     *
     * @return string
     */
    public function supervisorsFirstNameSBC()
    {
        $string = '';
        foreach ($this->supervisors as $user) {
            if ($user->status)
                $string .= $user->firstname . ', ';
        }

        return rtrim($string, ', ');
    }

    /**
     * Determines if a User is a Supervisor of this site
     *
     * @return string
     */
    public function isUserSupervisor($user)
    {
        foreach ($this->supervisors as $super) {
            if ($user->id == $super->id)
                return true;
        }

        return false;
    }

    /**
     * Determines if a User is an Area Supervisor of this site
     *
     * @return string
     */
    public function isSupervisorOrAreaSupervisor($user)
    {
        foreach ($this->supervisors as $super) {
            if ($user->id == $super->id || in_array($super->id, $user->subSupervisors()->pluck('id')->toArray()))
                return true;
        }

        return false;
    }

    /**
     * A Site may have Area Supervisors
     *
     * @return collection
     */
    public function areaSupervisors()
    {
        $area_super_ids = [];
        foreach ($this->supervisors as $super) {
            foreach ($super->areaSupervisors() as $area_super) {
                $area_super_ids[] = $area_super->id;
            }
        }

        return User::whereIn('id', array_unique($area_super_ids))->get();
    }

    /**
     * An array of area supervisors emails for this site
     *
     * @return string
     */
    public function areaSupervisorsEmails()
    {
        $array = [];
        foreach ($this->areaSupervisors() as $user) {
            if ($user->status && validEmail($user->email))
                $array[] = $user->email;
        }

        return $array;
    }

    /**
     * A SiteAttendance for specific date yyyy-mm-dd
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function onsite($date = '')
    {
        if (!$date)
            $date = Carbon::today()->format('Y-m-d');

        $onsite = SiteAttendance::where('site_id', $this->id)->where('date', 'LIKE', $date . '%')->get();

        return ($onsite->isEmpty()) ? 0 : $onsite;
    }


    /**
     * Is a specific user onsite
     *
     * @return record
     */
    public function isUserOnsite($user_id, $date = '')
    {
        if (!$date)
            $date = Carbon::today()->format('Y-m-d');

        return SiteAttendance::where('site_id', $this->id)->where('user_id', $user_id)->whereDate('date', '=', $date)->first();
    }

    /**
     * Is a specific user on site roster
     *
     * @return record
     */
    public function isUserOnRoster($user_id, $date = '')
    {
        if (!$date)
            $date = Carbon::today()->format('Y-m-d');

        $onsite = SiteRoster::where('site_id', $this->id)->where('user_id', $user_id)->whereDate('date', '=', $date)->first();

        return ($onsite) ? $onsite->id : 0;
    }

    /**
     * Is a specific user on site non-attendee list (Compliance table)
     *
     * @return record
     */
    public function isUserOnCompliance($user_id, $date = '')
    {
        if (!$date)
            $date = Carbon::today()->format('Y-m-d');

        $oncomply = SiteCompliance::where('site_id', $this->id)
            ->where('user_id', $user_id)
            ->whereDate('date', '=', $date)
            ->first();

        return ($oncomply) ? $oncomply->id : 0;
    }

    /**
     * Is a company on site planner
     *
     * @return record
     */
    public function isCompanyOnPlanner($company_id, $date = '')
    {
        if (!$date)
            $date = Carbon::today()->format('Y-m-d');

        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);

        $onplan = SitePlanner::where('site_id', $this->id)
            ->where('entity_type', 'c')
            ->where('entity_id', $company_id)
            ->whereDate('from', '<=', $date)
            ->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)
            ->first();

        return ($onplan) ? true : false;
    }

    /**
     * Company Tasks for given site + date
     *
     * @return array
     */
    public function entityTasksOnDate($entity_type, $entity_id, $date)
    {
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);
        $planner = SitePlanner::where('site_id', $this->id)
            ->where('entity_type', $entity_type)->where('entity_id', $entity_id)
            ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)->get();

        $tasks = [];
        foreach ($planner as $plan) {
            if ($plan->task_id) {
                $task = Task::find($plan->task_id);
                $tasks[$task->id] = ($task) ? $task->name : 'Task Unassigned';
            }

        }

        return $tasks;
    }

    /**
     * Trades for given site + date
     *
     * @return array
     */
    public function entityTradesOnDate($entity_type, $entity_id, $date)
    {
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);
        $planner = SitePlanner::where('site_id', $this->id)
            ->where('entity_type', $entity_type)->where('entity_id', $entity_id)
            ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)->get();

        $trades = [];
        foreach ($planner as $plan) {
            if ($plan->task_id) {
                $task = Task::find($plan->task_id);
                if (!isset($trades[$task->trade_id]))
                    $trades[$task->trade_id] = $task->trade->name;
            }

        }

        return $trades;
    }

    /**
     * Company Tasks for given site + date
     *
     * @return array
     */
    public function anyTasksOnDate($date)
    {
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);
        $planner = SitePlanner::where('site_id', $this->id)
            ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)->first();
        $onplan = SitePlanner::where('site_id', $this->id)
            ->whereDate('from', '<=', $date)
            ->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)
            ->first();

        return ($planner) ? true : false;
    }

    /**
     * A Site has multiple Notify Alerts
     */
    public function notify()
    {
        $today = Carbon::today();
        $notifys = Notify::where('type', 'site')->where('type_id', $this->id)->where('from', '<=', $today)->where('to', '>=', $today)->get();

        $notify_ids = [];
        foreach ($notifys as $notify) {
            if ($notify->action == 'many')
                $notify_ids[] = $notify->id;
            else if (!$notify->isOpenedBy(Auth::user()))
                $notify_ids[] = $notify->id;
        }

        return Notify::find($notify_ids);
    }

    /**
     * Return status text name
     *
     * @return string
     */
    public function statusText($colour = '')
    {
        $status_text = ['0' => 'Completed', '1' => "Active", '-1' => "Upcoming", '-2' => "Cancelled"];

        if ($colour) {
            switch ($this->status) {
                case '0':
                    return '<span class="label label-sm label-danger">' . $status_text[$this->status] . '</span>';
                case '1':
                    return '<span class="label label-sm label-success">' . $status_text[$this->status] . '</span>';
                case '-1':
                    return '<span class="label label-sm label-warning">' . $status_text[$this->status] . '</span>';
                case '-2':
                    return '<span class="label label-sm label-danger">' . $status_text[$this->status] . '</span>';
            }

        }

        return $status_text[$this->status];
    }

    /**
     * Email Site
     */
    public function emailSite($action = '')
    {
        $email_to = [env('EMAIL_DEV')];

        if (\App::environment('prod')) {
            $email_list = $this->company->notificationsUsersEmailType('site.status');
            $email_supers = $this->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
        }

        Mail::to($email_to)->send(new \App\Mail\Site\SiteUpdated($this, $action));

    }

    /**
     * Get the first task date if it exists
     *
     * @return string;
     */
    public function JobFirstTaskOfType($task_id)
    {
        $firstTask = SitePlanner::where('site_id', $this->id)->where('task_id', $task_id)->orderBy('from')->first();

        return ($firstTask) ? $firstTask->from : null;
    }

    /**
     * Create WHS Managemnent Plan PDF
     *
     * @return string;
     */
    public function createWhsManagementPlanPDF()
    {

        $site = $this;
        return view('pdf/site/whs-management-plan-cover', compact('site'));
        //return PDF::loadView('pdf/site-qa', compact('site', 'data'))->setPaper('a4')->stream();
    }


    /**
     * Get the 'START' job task date if it exists  (getter)
     *
     * @return string;
     */
    public function getJobStartAttribute()
    {
        $startTask = SitePlanner::where('site_id', $this->id)->where('task_id', '11')->first();

        return ($startTask) ? $startTask->from : null;
    }

    /**
     * Get the first task date if it exists  (getter)
     *
     * @return string;
     */
    public function getJobFirstTaskAttribute()
    {
        $firstTask = SitePlanner::where('site_id', $this->id)->orderBy('from')->first();

        return ($firstTask) ? $firstTask->from : null;
    }


    /**
     * Get the owner of record  (getter)
     *
     * @return string;
     */
    /*
    public function getOwnedByAttribute()
    {
        return $this->client->owned_by;
    }*/

    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * Return records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::findOrFail($this->updated_by);

        return '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
        '<span style="font-weight: 400">By:</span> ' . $user->fullname;
    }

    /**
     * Determine if Model has a $attr defined ie it exists
     * @param $attr
     * @return bool
     */
    /*public function hasAttribute($attr)
    {
        return property_exists($this, $attr);
        return array_key_exists($attr, $this->attributes);
    }*/

    /**
     * Set the resolved_date  (mutator)
     *
     *  - Fix for Carbon saving 0000-00-00 00:00:00 format
     *  - otherwise trys to save as -0001-11-30 06:12:32
     */
    public function setCompletedAttribute($date)
    {
        $date == "0000-00-00 00:00:00" ? "0000-00-00 00:00:00" : $date;
        $this->attributes['completed'] = $date;
    }

    /**
     * Set the name + create slug attributes  (mutator)
     */
    /*public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim(strtoupper($value));
        //$this->attributes['slug'] = getUniqueSlug($this, $value);
    }*/

    /**
     * Set the suburb to uppercase format  (mutator)
     */
    /*
    public function setSuburbAttribute($value)
    {
        $this->attributes['suburb'] = strtoupper($value);
    }*/

    /**
     * Set the phone number to AU format  (mutator)
     *
     * @param $phone
     */
    public function setClientPhoneAttribute($phone)
    {
        $this->attributes['client_phone'] = format_phone('au', $phone);
    }

    /**
     * Set the phone number to AU format  (mutator)
     *
     * @param $phone
     */
    public function setClientPhone2Attribute($phone)
    {
        $this->attributes['client_phone2'] = format_phone('au', $phone);
    }

    /**
     * Get Site Name (Client only)   (getter)
     *
     * @return string;
     */

    public function getNameClientAttribute()
    {
        if ($this->company_id == 3) {
            list($code, $client, $suburb) = explode('-', $this->name);

            return $client;
        }

        return $this->name;
    }

    /**
     * Get Site Name (Client+Suburb only)   (getter)
     *
     * @return string;
     */

    public function getNameClientSuburbAttribute()
    {
        if ($this->company_id == 3) {
            list($code, $client, $suburb) = explode('-', $this->name);

            return "$client-$suburb";
        }

        return $this->name;
    }

    /**
     * Get Shorten Name   (getter)
     *
     * @return string;
     */

    public function getNameShortAttribute()
    {
        return substr($this->name, 0, 15);
    }

    /**
     * Get site reference (code + name + suburb)   (getter)
     *
     * @return string;
     */

    public function getRefAttribute()
    {
        return '#' . $this->code . ' ' . $this->name . ' @' . $this->suburb;
    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getSuburbStatePostcodeAttribute()
    {
        //$string = strtoupper($this->attributes['suburb']);
        $string = $this->attributes['suburb'];
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
    public function getFullAddressAttribute($value)
    {
        $string = $this->getSuburbStatePostcodeAttribute();
        if ($this->attributes['address'])
            $string = $this->attributes['address'] . ', ' . $string;

        return $string;
    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getAddressFormattedAttribute()
    {
        $string = '';

        if ($this->attributes['address'])
            $string = $this->attributes['address'] . '<br>';
        //$string = strtoupper($this->attributes['address']) . '<br>';

        $string .= $this->attributes['suburb'];
        //$string .= strtoupper($this->attributes['suburb']);
        if ($this->attributes['suburb'] && $this->attributes['state'])
            $string .= ', ';
        if ($this->attributes['state'])
            $string .= $this->attributes['state'];
        if ($this->attributes['postcode'])
            $string .= ' ' . $this->attributes['postcode'];

        return ($string) ? $string : '-';
    }

    /**
     * Get the Client 1 Full Name with title  (getter)
     */
    public function getClient1NameAttribute()
    {
        $string = '';

        if ($this->attributes['client1_title'])
            $string .= $this->attributes['client1_title'] . ' ';

        if ($this->attributes['client1_firstname'])
            $string .= $this->attributes['client1_firstname'] . ' ';

        if ($this->attributes['client1_lastname'])
            $string .= $this->attributes['client1_lastname'];

        return trim($string);
    }

    /**
     * Get the Client 2 Full Name with title  (getter)
     */
    public function getClient2NameAttribute()
    {
        $string = '';

        if ($this->attributes['client2_title'])
            $string .= $this->attributes['client2_title'] . ' ';

        if ($this->attributes['client2_firstname'])
            $string .= $this->attributes['client2_firstname'] . ' ';

        if ($this->attributes['client2_lastname'])
            $string .= $this->attributes['client2_lastname'];

        return trim($string);
    }

    /**
     * Get the Status Text Both  (getter)
     */
    public function getStatusTextAttribute()
    {

        if ($this->status == 1)
            return '<span class="font-green">ACTIVE</span>';

        if ($this->status == 0)
            return '<span class="font-red">COMPLETED</span>';

        if ($this->status == - 1)
            return '<span class="font-yellow">UPCOMING</span>';

        if ($this->status == 2)
            return '<span class="font-yellow">MAINTENANCE</span>';

        if ($this->status == - 2)
            return '<span class="font-red">CANCELLED</span>';

    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getAddressFormattedSingleAttribute()
    {
        $string = '';

        if ($this->attributes['address'])
            $string = $this->attributes['address'] . ', '; //strtoupper($this->attributes['address']) . ', ';

        //$string .= strtoupper($this->attributes['suburb']);
        $string .= $this->attributes['suburb'];
        if ($this->attributes['suburb'] && $this->attributes['state'])
            $string .= ', ';
        if ($this->attributes['state'])
            $string .= $this->attributes['state'];
        if ($this->attributes['postcode'])
            $string .= ' ' . $this->attributes['postcode'];

        return ($string) ? $string : '-';
    }

    /**
     * Get the suburb, state, postcode  (getter)
     */
    public function getLocationIdAttribute()
    {
        $string = '';

        if ($this->attributes['address'])
            $string = $this->attributes['address'] . ', '; //strtoupper($this->attributes['address']) . ', ';

        //$string .= strtoupper($this->attributes['suburb']);
        $string .= $this->attributes['suburb'];
        if ($this->attributes['suburb'] && $this->attributes['state'])
            $string .= ', ';
        if ($this->attributes['state'])
            $string .= $this->attributes['state'];
        if ($this->attributes['postcode'])
            $string .= ' ' . $this->attributes['postcode'];

        return ($string) ? $string : '-';
    }

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
}