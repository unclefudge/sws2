<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\User;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Controllers\CronCrontroller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiteMaintenance extends Model {

    protected $table = 'site_maintenance';
    protected $fillable = [
        'site_id', 'code', 'super_id', 'assigned_super_at', 'supervisor', 'completed', 'category_id', 'warranty', 'goodwill', 'assigned_to', 'assigned_at', 'planner_id', 'further_works',
        'contact_name', 'contact_phone', 'contact_email', 'step', 'reported', 'resolved', 'client_contacted', 'client_appointment', 'ac_form_sent',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $dates = ['completed', 'reported', 'resolved', 'assigned_super_at', 'assigned_at', 'supervisor_sign_at', 'manager_sign_at',
        'client_contacted', 'client_appointment', 'ac_form_sent'];


    /**
     * A Site Maintenance belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }

    /**
     * A Site Maintenance belongs to a SiteMaintenanceCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Site\SiteMaintenanceCategory', 'category_id');
    }

    /**
     * A Site Maintenance 'may' have been signed by a Supervisor user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function taskOwner()
    {
        return $this->belongsTo('App\User', 'super_id');
    }

    /**
     * A Site Maintenance 'may' have been assigned to a Company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo('App\Models\Company\Company', 'assigned_to');
    }

    /**
     * A Site Maintenance 'may' have a Planner task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function planner()
    {
        return $this->belongsTo('App\Models\Site\Planner\SitePlanner', 'planner_id');
    }

    /**
     * A Site Maintenance has many Docs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Site\SiteMaintenanceDoc', 'main_id');
    }

    /**
     * A Site Maintenance has many Items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteMaintenanceItem', 'main_id');
    }

    /**
     * A Site Maintenance Item.
     *
     */
    public function item($order)
    {
        return SiteMaintenanceItem::where('main_id', $this->id)->where('order', $order)->first();
    }

    /**
     * Determine if a all items are completed
     */
    public function itemsCompleted()
    {
        $completed = $this->items->filter(function ($item) {
            return $item->status != 0;
        });

        return $completed;
    }

    /**
     * Determine if a all items are completed
     */
    public function itemsChecked()
    {
        $checked = $this->items->filter(function ($item) {
            return $item->sign_by != null;
        });

        return $checked;
    }

    /**
     * A Site Maintenance has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A SiteMaintenance 'may' have multiple ToDoos
     *
     * @return Collection
     */
    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'maintenance_task')->where('type_id', $this->id)->get();

        return Todo::where('type', 'maintenance_task')->where('type_id', $this->id)->get();
    }

    /**
     * A Site Maintenance 'may' have been signed by a Supervisor user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedSupervisor()
    {
        return $this->belongsTo('App\User', 'supervisor_signed_id');
    }

    /**
     * A Site Maintenance 'may' have been signed by a Manager user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedManager()
    {
        return $this->belongsTo('App\User', 'manager_signed_id');
    }

    /**
     * Determine if a report has been signed by 1 or 2
     *
     * @return integer
     */
    public function isSigned()
    {
        $count = 0;
        if ($this->supervisor_sign_by)
            $count ++;
        if ($this->manager_sign_by)
            $count ++;

        return $count;
    }

    /**
     * Determine Next Client Visit
     *
     * @return integer
     */
    public function nextClientVisit()
    {
        $today = Carbon::now();
        $visit = SitePlanner::where('from', '>=', $today->format('Y-m-d'))->where('site_id', $this->site_id)->where('task_id', 524)->orderBy('from')->first();

        return $visit;
        //return ($visit) ? $visit->from : null;
    }


    /**
     * Move Maintenance to On Hold by given user
     */
    public function moveToHold($user)
    {
        $action = Action::create(['action' => 'Moved report to On Hold', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 4;
        $this->save();

        // Close current ToDoo for QA
        $this->closeToDo($user);
    }

    /**
     * Move Maintenance to Active by given user
     */
    public function moveToActive($user)
    {
        $action = Action::create(['action' => 'Moved report to Active', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 1;
        $this->save();

        // Create ToDoo for QA
        $site = Site::findOrFail($this->site_id);
        $this->createToDo($site->supervisor_id);
    }

    /**
     * Create ToDoo for Maintenance Report and assign to given user(s)
     */
    public function createManagerSignOffToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'maintenance',
            'type_id'    => $this->id,
            'name'       => "Maintenance Request Sign Off - $site->name",
            'info'       => 'Please sign off on completed items',
            'priority'   => '1',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Con Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Create ToDoo for Maintenance Report and assign to given user(s)
     */
    public function createSupervisorAssignedToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'maintenance',
            'type_id'    => $this->id,
            'name'       => "Maintenance Request Assigned - $site->name",
            'info'       => 'Please review request and assign a company to carry out the work if required.',
            'priority'   => '1',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this SiteMaintenance
     */
    public function closeToDo($type = 'maintenance')
    {
        $todos = Todo::where('type', $type)->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = Auth::user()->id;
            $todo->save();
        }
    }


    /**
     * Email Assigned
     */
    public function emailAssigned($user)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = (validEmail($user->email)) ? $user->email : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceAssigned($this));

    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = $this->site->supervisorEmail;
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        /*
        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteMaintenanceAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceAction($this, $action));
        */
    }

    /**
     * Last updated (record or action)
     *
     * @return Carbon date
     */
    public function lastUpdated()
    {
        $lastAction = $this->actions->sortBy('updated_at')->last();

        if ($lastAction && $lastAction->updated_at->gt($this->updated_at))
            return $lastAction->updated_at;
        else
            return $this->updated_at;
    }

    /**
     * Last Action
     *
     * @return instance of Action
     */
    public function lastAction()
    {
        return $this->actions->sortBy('updated_at')->last();
    }

    /**
     * Last Action Note
     *
     * @return string
     */
    public function lastActionNote()
    {
        $lastAction = $this->actions->sortBy('updated_at')->last();

        return ($lastAction) ? $lastAction->action : '';

    }
    /**
     * Get the planner task date if it exists  (getter)
     *
     * @return string;
     */
    public function getPlannerTaskDateAttribute()
    {
        return ($this->planner) ? $this->planner->from : null;
    }

    /**
     * Display records last update_by + date
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

