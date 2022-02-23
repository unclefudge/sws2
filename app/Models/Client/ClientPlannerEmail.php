<?php

namespace App\Models\Client;

use URL;
use Mail;
use App\User;
use App\Mail\Client\ClientPlanner;
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
     * Email Planner
     */
    public function emailPlanner()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_cc = '';
        $email_bcc = '';
        $files = [];

        if (\App::environment('prod')) {
            $email_to = explode('; ', $this->sent_to);
            //$email_cc = explode('; ', $this->sent_cc);
            $email_bcc = explode('; ', $this->sent_bcc);
        }

        if ($email_to && $email_cc && $email_bcc)
            Mail::to($email_to)->cc($email_cc)->bcc($email_bcc)->send(new \App\Mail\Client\ClientPlanner($this));
        elseif ($email_to && $email_cc)
            Mail::to($email_to)->cc($email_cc)->bcc($email_bcc)->send(new \App\Mail\Client\ClientPlanner($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Client\ClientPlanner($this));
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
     * Get the Last Email (setter)
     */
    public function getLastEmailAttribute()
    {
        $last_email = ClientPlannerEmail::where('status', 0)->where('site_id', $this->site_id)->orderBy('updated_at', 'DESC')->first();

        if ($last_email)
            return $last_email->updated_at->format('d/m/Y');

        return '';
    }

    /**
     * Get the Licence URL (setter)
     */
    public function getSentByAttribute()
    {
        return User::findOrFail($this->updated_by);
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

