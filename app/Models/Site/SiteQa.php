<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\User;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Site\Planner\Task;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Controllers\CronCrontroller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiteQa extends Model {

    protected $table = 'site_qa';
    protected $fillable = [
        'name', 'site_id', 'version', 'master', 'master_id', 'category_id',
        'supervisor_sign_by', 'supervisor_sign_at', 'manager_sign_by', 'manager_sign_at',
        'notes', 'company_id', 'status', 'share', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $dates = ['supervisor_sign_at', 'manager_sign_at'];

    /**
     * A Site QA Doc belongs to a Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Site\SiteQaCategory', 'category_id');
    }

    /**
     * A Site QA Doc belongs to a Site - if it's not a template.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }

    /**
     * A Site QA Doc has many Items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteQaItem', 'doc_id');
    }

    /**
     * Determine if a user has read 'completed' the Toolbox Talk and return date
     */
    public function itemsCompleted()
    {
        $completed = $this->items->filter(function ($item) {
            return $item->status != 0;
        });

        return $completed;
    }

    /**
     * A Site QA Doc ht has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        //return $this->hasMany('App\Models\Site\SiteQaAction', 'doc_id');
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A Site QA Doc is owned by a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function owned_by()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A Site QA Doc 'may' have been signed by a Supervisor user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function signedSupervisor()
    {
        return $this->belongsTo('App\User', 'supervisor_signed_id');
    }

    /**
     * A Site QA Doc 'may' have been signed by a Manager user.
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
     * A Site QA Doc has tasks that 'trigger' it depending on it's items.
     *
     * @return collection
     */
    public function tasks()
    {
        $task_ids = [];
        foreach ($this->items as $item) {
            if (!in_array($item->task_id, $task_ids))
                $task_ids[] = $item->task_id;
        }

        return Task::find($task_ids);
    }

    /**
     * A Site QA Doc has tasks that 'trigger' it depending on it's items - separated by Comma
     * @return string
     */
    public function tasksSBC()
    {
        $string = '';
        foreach ($this->tasks() as $task)
            $string .= $task->code . ', ';

        return rtrim($string, ', ');
    }

    /**
     * Move QA to On Hold by given user
     */
    public function moveToHold($user)
    {
        $action = Action::create(['action' => 'Moved report to On Hold', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 2;
        $this->save();

        // Close current ToDoo for QA
        $this->closeToDo($user);
    }

    /**
     * Move QA to Active by given user
     */
    public function moveToActive($user)
    {
        $action = Action::create(['action' => 'Moved report to Active', 'table' => $this->table, 'table_id' => $this->id]);
        $this->status = 1;
        $this->save();

        // Create ToDoo for QA
        $site = Site::findOrFail($this->site_id);
        $this->createToDo($site->supervisors->pluck('id')->toArray());
    }

    /**
     * Create ToDoo for QA Report and assign to given user(s)
     */
    public function createToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'qa',
            'type_id'    => $this->id,
            'name'       => 'Quality Assurance - ' . $this->name . ' (' . $site->name . ')',
            'info'       => 'Please sign off on completed items',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        //$todo->emailToDo();
    }

    /**
     * Create ToDoo for QA Report and assign to given user(s)
     */
    public function createManagerSignOffToDo($user_list)
    {
        $site = Site::findOrFail($this->site_id);
        $todo_request = [
            'type'       => 'qa',
            'type_id'    => $this->id,
            'name'       => 'QA Sign Off - ' . $this->name . ' (' . $site->name . ')',
            'info'       => 'Please sign off on completed items',
            'priority'   => '1',
            'due_at'     => nextWorkDate(Carbon::today(), '+', 2)->toDateTimeString(),
            'company_id' => $this->company_id,
        ];

        // Create ToDoo and assign to Site Supervisors
        $todo = Todo::create($todo_request);
        $todo->assignUsers($user_list);
        //$todo->emailToDo();
    }

    /**
     * Close any outstanding ToDoo for this QA
     */
    public function closeToDo($user)
    {
        $todos = Todo::where('type', 'qa')->where('type_id', $this->id)->where('status', '1')->get();
        foreach ($todos as $todo) {
            $todo->status = 0;
            $todo->done_at = Carbon::now();
            $todo->done_by = $user->id;
            $todo->save();
        }
    }


    /**
     * Email Overdue
     */
    /*
    public function emailOverdue()
    {
        if (\App::environment('prod')) {
            $email_roles = $this->site->company->notificationsUsersEmailType('site.qa');
            $email_seniors = $this->site->areaSupervisorsEmails();
            $email_to = array_unique(array_merge($email_roles, $email_seniors), SORT_REGULAR);
        } else if (\App::environment('local', 'dev')) {
            $email_to = [env('EMAIL_ME')];
        }

        $user_fullname = 'Safeworksite';
        $user_company_name = 'Safeworksite';

        $data = [
            'id'                => $this->id,
            'name'              => $this->name,
            'site_name'         => $this->site->name,
            'supers'            => $this->site->supervisorsSBC(),
            'url'               => URL::to('/') . '/site/qa/' . $this->id,
            'user_fullname'     => $user_fullname,
            'user_company_name' => $user_company_name,
        ];

        Mail::send('emails/siteQA-overdue', $data, function ($m) use ($email_to) {
            $m->from('do-not-reply@safeworksite.com.au');
            $m->to($email_to);
            $m->subject('Quality Assurance Overdue Notification');
        });
    }*/


    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            //$email_list = $this->site->company->notificationsUsersEmailType('site.qa');
            //$email_supers = $this->site->supervisorsEmails();
            //$email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            $email_to = $this->site->supervisorsEmails();
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteQaAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteQaAction($this, $action));
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

