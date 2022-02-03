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

class SiteScaffoldHandover extends Model {

    protected $table = 'site_scaffold_handover';
    protected $fillable = [
        'site_id', 'location', 'use', 'duty', 'decks', 'inspector_name', 'inspector_licence', 'handover_date',
        'notes', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $dates = ['handover_date'];


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
     * A Site Maintenance has many Docs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function docs()
    {
        return $this->hasMany('App\Models\Site\SiteScaffoldHandoverDoc', 'scaffold_id');
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

        // Gary didn't want to be email when assigning - so comment out email_user :)
        //
        //if ($email_to && $email_user)
        //    Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteMaintenanceAssigned($this));
        //elseif ($email_to)
        Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceAssigned($this));

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

