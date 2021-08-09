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
        'notifiable', 'notifiable_reason', 'regulator', 'regulator_ref', 'inspector',
        //'injured_part', 'injured_nature', 'injured_mechanism', 'injured_agency',
        //'conditions', 'factors_absent', 'factors_actions', 'factors_workplace', 'factors_human', 'root_cause',
        'damage', 'damage_cost', 'damage_repair', 'risk_register', 'notes', 'step', 'status', 'company_id',
        'resolved_at', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected $dates = ['date', 'resolved_at'];

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
     * A SiteIncident has many people
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function people()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentPeople', 'incident_id');
    }

    /**
     * A SiteIncident has many witness
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function witness()
    {
        return $this->hasMany('App\Models\Site\Incident\SiteIncidentWitness', 'incident_id');
    }

    /**
     * A SiteIncident has many conversation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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
            $email_list = $this->site->company->notificationsUsersEmailType('site.incident');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
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
            $email_list = $this->site->company->notificationsUsersEmailType('site.incident');
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