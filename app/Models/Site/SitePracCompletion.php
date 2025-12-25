<?php

namespace App\Models\Site;


use App\Http\Controllers\CronCrontroller;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Site\Planner\SitePlanner;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class SitePracCompletion extends Model
{

    protected $table = 'site_prac_completion';
    protected $fillable = [
        'site_id', 'super_id', 'client_contacted', 'client_appointment',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['supervisor_sign_at' => 'datetime', 'manager_sign_at' => 'datetime', 'client_contacted' => 'datetime', 'client_appointment' => 'datetime'];


    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    public function supervisor()
    {
        return $this->belongsTo('App\User', 'super_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Site\SitePracCompletionItem', 'prac_id');
    }


    /**
     * A Site Prac Completiom 'may' have been assigned to a Company.
     */
    public function assignedTo()
    {
        $assigned = SitePracCompletionItem::where('prac_id', $this->id)->pluck('assigned_to')->toArray();
        return array_unique($assigned);

    }

    public function assignedToNames()
    {
        $names = '';
        foreach ($this->assignedTo() as $cid) {
            $company = Company::find($cid);
            if ($company)
                $names .= $company->name . ", ";
        }
        return rtrim($names, ', ');

    }


    /**
     * Determine if a all items are completed
     */
    public function itemsCompleted()
    {
        $completed = $this->items->filter(function ($item) {
            return $item->status == 0;
        });

        return $completed;
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'table_id')->where('table', 'site_prac_completion');
    }


    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }


    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'prac_completion_task')->where('type_id', $this->id)->get();

        return Todo::where('type', 'prac_completion_task')->where('type_id', $this->id)->get();
    }


    public function signedSupervisor()
    {
        return $this->belongsTo('App\User', 'supervisor_signed_id');
    }

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
            $count++;
        if ($this->manager_sign_by)
            $count++;

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
    }

    /**
     * Move to On Hold by given user
     */
    public function moveToHold($user)
    {
        $action = Action::create(['action' => 'Moved Prac Completion to On Hold', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 4;
        $this->save();

        // Close current ToDoo for QA
        $this->closeToDo($user);
    }

    /**
     * Close any outstanding ToDoo
     */
    public function closeToDo($type = 'prac_completion')
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
     * Move to Active by given user
     */
    public function moveToActive($user)
    {
        $action = Action::create(['action' => 'Moved Prac Completion to Active', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 1;
        $this->save();

        // Create ToDoo for QA
        $site = Site::findOrFail($this->site_id);
        $this->createToDo($site->supervisor_id);
    }


    public function createAssignSupervisorToDo($user_list)
    {
        // Create ToDoo for assignment to Supervisor
        $todo_request = [
            'type' => 'prac_completion',
            'type_id' => $this->id,
            'name' => 'Prac Completion created - ' . $this->site->name,
            'info' => 'Please review request and assign to supervisor',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->site->owned_by->id,
        ];

        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }


    public function createManagerSignOffToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type' => 'prac_completion',
            'type_id' => $this->id,
            'name' => "Prac Completion Sign Off - $site->name",
            'info' => 'Please sign off on completed items',
            'priority' => '1',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Con Manager
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Create ToDoo for Report and assign to given user(s)
     */
    public function createSupervisorAssignedToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type' => 'prac_completion',
            'type_id' => $this->id,
            'name' => "Prac Completion Assigned - $site->name",
            'info' => 'Please review request and assign a company to carry out the work if required.',
            'priority' => '1',
            'due_at' => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => '3',
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        $todo->emailToDo();
    }

    /**
     * Email Assigned
     */
    public function emailAssigned($user)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (app()->environment('prod')) {
            $email_to = (validEmail($user->email)) ? $user->email : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        Mail::to($email_to)->send(new \App\Mail\Site\SitePracCompletionAssigned($this));

    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (app()->environment('prod')) {
            $email_to = $this->site->supervisorEmail;
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }
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

