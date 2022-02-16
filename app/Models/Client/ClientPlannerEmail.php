<?php

namespace App\Models\Client;

use URL;
use Mail;
use App\User;
use App\Http\Controllers\CronCrontroller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientPlannerEmail extends Model {

    protected $table = 'client_planner_emails';
    protected $fillable = [
        'client_id', 'site_id', 'type', 'name', 'email1', 'email2', 'intro', 'sent_to', 'sent_cc', 'sent_bcc', 'subject', 'body',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $dates = [];


    /**
     * A Client Planner Email belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }


    /**
     * A Client Planner Email has many Docs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Client\ClientPlannerEmailDoc', 'email_id');
    }


    /**
     * Email Report
     */
    public function emailReport($user)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = (validEmail($user->email)) ? $user->email : '';
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));
        elseif ($email_user)
            Mail::to($email_user)->send(new \App\Mail\Site\SiteScaffoldHandoverEmail($this));

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
     * Get the Licence URL (setter)
     */
    public function getInspectorLicenceUrlAttribute()
    {
        if ($this->attributes['inspector_licence'])
            return '/filebank/site/' . $this->attributes['site_id'] . "/scaffold/" . $this->attributes['inspector_licence'];

        return '';
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

