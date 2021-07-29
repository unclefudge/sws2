<?php

namespace App\Models\Site\Incident;

use Mail;
use App\User;
use App\Models\Misc\FormQuestion;
use App\Models\Misc\FormResponse;
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SiteIncidentPeople extends Model {

    protected $table = 'site_incidents_people';
    protected $fillable = [
        'incident_id', 'user_id', 'type', 'type_other', 'name', 'address', 'contact', 'dob', 'occupation', 'engagement', 'employer', 'supervisor',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected $dates = ['dob'];

    /**
     * A SiteIncidentPeople belongs to a SiteIncident
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incident()
    {
        return $this->belongsTo('App\Models\Site\Incident\SiteIncident');
    }

    /**
     * A SiteIncidentPeople type response
     *
     * @return FormResponse
     */
    public function typePPPP()
    {
        return FormResponse::where('question_id', '1')->where('table', 'site_incidents_people')->where('table_id', $this->id)->first();
    }


    /**
     * A SiteIncidentPeople belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A SiteIncidentPeople belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\User', 'updated_by');
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
     * Get the type name  (getter)
     */
    public function getTypeNameAttribute()
    {
        return ($this->type_other) ? $this->type_other : FormQuestion::find($this->type)->name;
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