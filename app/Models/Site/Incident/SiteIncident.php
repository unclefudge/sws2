<?php

namespace App\Models\Site\Incident;

use Mail;
use App\User;
use App\Models\Misc\FormQustion;
use App\Models\Misc\FormResponse;
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SiteIncident extends Model {

    protected $table = 'site_incidents';
    protected $fillable = [
        'site_id', 'site_name', 'site_supervisor', 'location', 'date', 'describe', 'actions_taken',
        'risk_potential', 'risk_actual', 'exec_summary', 'exec_describe', 'exec_actions', 'exec_notes',
        'notifiable', 'notifiable_reason', 'regulator', 'regulator_ref', 'regulator_date', 'inspector',
        //'injured_part', 'injured_nature', 'injured_mechanism', 'injured_agency',
        //'conditions', 'factors_absent', 'factors_actions', 'factors_workplace', 'factors_human', 'root_cause',
        'damage', 'damage_cost', 'damage_repair', 'risk_register', 'notes', 'step', 'status', 'company_id',
        'resolved_at', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected $dates = ['date', 'regulator_date', 'resolved_at'];

    /**
     * A SiteIncident belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteIncident has many docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentDoc', 'incident_id');
    }

    /**
     * A SiteIncident has many people
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function people()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentPeople', 'incident_id');
    }

    /**
     * A SiteIncident has many witness statements
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function witness()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentWitness', 'incident_id');
    }

    /**
     * A SiteIncident has many conversation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conversations()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentConversation', 'incident_id');
    }

    /**
     * A SiteIncident has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * A SiteIncident 'may' have multiple ToDoos
     *
     * @return Collection
     */
    public function todos($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'incident')->where('type_id', $this->id)->get();

        return Todo::where('type', 'incident')->where('type_id', $this->id)->get();
    }

    /**
     * Check if a user is a assigned a ToDoo
     *
     * @return Collection
     */
    public function hasAssignedTask($user_id, $status = '')
    {
        $todos = ($status) ? Todo::where('status', $status)->where('type', 'incident')->where('type_id', $this->id)->get() : Todo::where('type', 'incident')->where('type_id', $this->id)->get();

        if ($todos) {
            foreach ($todos as $todo) {
                foreach ($todo->assignedTo() as $user)
                    if ($user->id == $user_id)
                        return true;
            }
        }

        return false;
    }

    /**
     * A SiteIncident 'may' have multiple ToDoos - Preventive Actions
     *
     * @return Collection
     */
    public function preventActions($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'incident prevent')->where('type_id', $this->id)->get();

        return Todo::where('type', 'incident prevent')->where('type_id', $this->id)->get();
    }

    /**
     * A SiteIncident 'may' have multiple ToDoos - Reviews
     *
     * @return Collection
     */
    public function reviews($status = '')
    {
        if ($status)
            return Todo::where('status', $status)->where('type', 'incident review')->where('type_id', $this->id)->get();

        return Todo::where('type', 'incident review')->where('type_id', $this->id)->get();
    }

    /**
     * A SiteIncident Reviews Users List
     */
    public function reviewsBy($status = '')
    {
        $reviews = [];
        $Todos = ($status) ? Todo::where('status', $status)->where('type', 'incident review')->where('type_id', $this->id)->get() : Todo::where('type', 'incident review')->where('type_id', $this->id)->get();

        foreach ($Todos as $todo)
            $reviews[$todo->users()->first()->user_id] = ($todo->done_at) ? $todo->done_at->format('d/m/Y') : '';

        return $reviews;
    }


    /**
     * SiteIncident Responses to questions
     */
    public function formResponse($question_id)
    {
        return FormResponse::where('question_id', $question_id)->where('table', $this->table)->where('table_id', $this->id)->get();
    }

    /**
     * SiteIncident Responses to questions (Array format)
     */
    public function formResponseArray($question_id)
    {
        return FormResponse::where('question_id', $question_id)->where('table', $this->table)->where('table_id', $this->id)->get()->pluck('id')->toArray();
    }

    /**
     * SiteIncident has a 'Injury' response
     */
    public function isInjury()
    {
        return (FormResponse::where('question_id', 1)->where('option_id', 2)->first()) ? true : false;
    }

    /**
     * SiteIncident has a 'Damage' response
     */
    public function isDamage()
    {
        return (FormResponse::where('question_id', 1)->where('option_id', 3)->first()) ? true : false;
    }

    /**
     * Get the Risk Rating Text
     */
    public function riskRatingText($field)
    {
        if ($this->attributes[$field] == '1')
            return 'Low';
        if ($this->attributes[$field] == '2')
            return 'Medium';
        if ($this->attributes[$field] == '3')
            return 'High';
        if ($this->attributes[$field] == '4')
            return 'Extreme';

        return '-';
    }

    /**
     * Get the Risk Rating Text (setter)
     */
    public function riskRatingTextColoured($field)
    {
        if ($this->attributes[$field] == '1')
            return '<span style="background:#32c5d2; color:#fff; padding:5px 10px">Low</span>';
        if ($this->attributes[$field] == '2')
            return '<span style="background:#ffcc66; color:#fff; padding:5px 10px">Medium</span>';
        if ($this->attributes[$field] == '3')
            return '<span style="background:#ff9900; color:#fff; padding:5px 10px">High</span>';
        if ($this->attributes[$field] == '4')
            return '<span style="background:#ff0000; color:#fff; padding:5px 10px">Extreme</span>';

        return '-';
    }


    /**
     * A SiteIncident belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A SiteIncident belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }


    /**
     * Email Incident
     */
    public function emailIncident()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            // If incident happened on a Job site get Site owners details else use parent company details
            if ($this->site_id) {
                $email_list = $this->site->company->notificationsUsersEmailType('site.accident');
                $email_supers = $this->site->supervisorsEmails();
                $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            } else
                $email_to = Auth::user()->company->reportTo()->notificationsUsersEmailType('site.accident');

            // CC Email user that logged request
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteIncidentCreated($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteIncidentCreated($this));
    }

    /**
     * Email Action Notification
     */
    public function emailAction($action, $important = false)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_list = $this->site->company->notificationsUsersEmailType('site.accident');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteIncidentAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteIncidentAction($this, $action));
    }

    /**
     * Get the Status Text Both  (getter)
     */
    public function getStatusTextAttribute()
    {
        if ($this->status == 1)
            return '<span class="font-green">OPEN</span>';

        if ($this->status == 0)
            return '<span class="font-red">RESOLVED</span>';

        if ($this->status == 2)
            return '<span class="font-yellow">IN PROGRESS</span>';
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
     * Set the resolved_at  (mutator)
     *
     *  - Fix for Carbon saving 0000-00-00 00:00:00 format
     *  - otherwise trys to save as -0001-11-30 06:12:32
     */
    /*
    public function setResolvedDateAttribute($date)
    {
        $date == "0000-00-00 00:00:00" ? "0000-00-00 00:00:00" : $date;
        $this->attributes['resolved_at'] = $date;
    }*/

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