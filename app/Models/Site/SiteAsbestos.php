<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\Models\Misc\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use nilsenj\Toastr\Facades\Toastr;

class SiteAsbestos extends Model {

    protected $table = 'site_asbestos';
    protected $fillable = [
        'site_id', 'amount', 'friable', 'type', 'location', 'date_from', 'date_to', 'hours_from', 'hours_to', 'workers',
        'client_name', 'client_phone', 'super_id', 'super_phone', 'workplace', 'coalmine', 'hygiene', 'hygiene_report',
        'assessor_name','assessor_phone', 'assessor_cert', 'assessor_lic', 'assessor_dept', 'assessor_state', 'safework', 'safework_ref',
        'equip_overalls', 'equip_mask', 'equip_gloves', 'equip_half_face', 'equip_full_face', 'equip_other',
        'method_fencing', 'method_signage', 'method_water', 'method_pva', 'method_barriers', 'method_plastic', 'method_vacuum', 'method_other',
        'isolation', 'register', 'swms', 'inspection', 'supervisor_id', 'removalist', 'removalist_name',
        'safework_at', 'supervisor_at', 'neighbours_at', 'removal_at', 'reg_updated_at',
        'attachment', 'company_id', 'status', 'resolved_at',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected $dates = ['date_from', 'date_to', 'resolved_at', 'safework_at', 'supervisor_at', 'neighbours_at', 'removal_at', 'reg_updated_at'];

    /**
     * A SiteAsbestos belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteAsbestos belongs to a supervisor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supervisor()
    {
        return $this->belongsTo('App\User', 'supervisor_id');
    }

    /**
     * A SiteAsbestos belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }


    /**
     * A SiteAsbestos has many Actions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\Misc\Action', 'table_id')->where('table', $this->table);
    }

    /**
     * Equipment used
     */
    public function equipment($format)
    {
        $str = '';
        if ($format == 'SBC') {
            if ($this->equip_overalls) $str .= 'Protective coveralls, ';
            if ($this->equip_gloves) $str .= 'Protective gloves, ';
            if ($this->equip_mask) $str .= 'P2 Mask, ';
            if ($this->equip_half_face) $str .= '1/2 face respirator, ';
            if ($this->equip_full_face) $str .= 'Full face air supplied, ';
            if ($this->equip_other) $str .= $this->equip_other;
        } else {
            if ($this->equip_overalls) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Protective coveralls</div>";
            if ($this->equip_gloves) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Protective gloves</div>";
            if ($this->equip_mask) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; P2 Mask</div>";
            if ($this->equip_half_face) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; 1/2 face respirator</div>";
            if ($this->equip_full_face) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Full face air supplied</div>";
            if ($this->equip_other) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; " . $this->equip_other . "</div>";
        }

        return rtrim($str, ', ');
    }

    /**
     * Methods used
     */
    public function methods($format)
    {
        $str = '';
        if ($format == 'SBC') {
            if ($this->method_fencing) $str .= 'Fencing, ';
            if ($this->method_signage) $str .= 'Signage, ';
            if ($this->method_water) $str .= 'Water, ';
            if ($this->method_pva) $str .= 'PVA, ';
            if ($this->method_barriers) $str .= 'Barriers, ';
            if ($this->method_plastic) $str .= '200 μm plastic, ';
            if ($this->method_vacuum) $str .= 'Class H asbestos vacuum cleaners, ';
            if ($this->method_other) $str .= $this->method_other;
        } else {
            if ($this->method_fencing) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Fencing</div>";
            if ($this->method_signage) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Signage</div>";
            if ($this->method_water) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Water</div>";
            if ($this->method_pva) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; PVA</div>";
            if ($this->method_barriers) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Barriers</div>";
            if ($this->method_plastic) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; 200 μm plastic</div>";
            if ($this->method_vacuum) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; Class H asbestos vacuum cleaners</div>";
            if ($this->method_other) $str .= "<div class='col-md-6' style='padding-left: 0px'>&bull; " . $this->method_other . "</div>";
        }

        return rtrim($str, ', ');
    }

    /**
     * Update Status
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updateStatus($status)
    {
        $old = $this->status;
        $this->status = $status;
        $this->save();

        if ($old != $status) {
            $mesg = ($status) ? 'Re-opened Notification' : 'Closed Notification';
            $action = Action::create(['action' => $mesg, 'table' => $this->table, 'table_id' => $this->id]);
            $this->emailAction($action, 'important');
        }
    }
    /**
     * Email Notification
     */
    public function emailNotification()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_list = $this->site->company->notificationsUsersEmailType('site.asbestos');
            $email_supers = [$this->site->supervisorEmail];
            $handy_email = ($this->removalist == '385') ? ['handyfirst01@gmail.com'] : [];
            $email_to = array_unique(array_merge($email_list, $email_supers, $handy_email), SORT_REGULAR);
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteAsbestosCreated($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteAsbestosCreated($this));
    }

    /**
     * Email Action Notification
     */
    public function emailAction($action)
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_to = $this->site->company->notificationsUsersEmailType('site.asbestos');
            if ($this->site->supervisorEmail && !in_array($this->site->supervisorEmail, $email_to))
                $email_to[] = $this->site->supervisorEmail;
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteAsbestosAction($this, $action));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteAsbestosAction($this, $action));
    }

    /**
     * Set the resolved_at  (mutator)
     *
     *  - Fix for Carbon saving 0000-00-00 00:00:00 format
     *  - otherwise trys to save as -0001-11-30 06:12:32
     */
    /*
   public function setResolvedAtAttribute($date) {
       $date == "0000-00-00 00:00:00" ? "0000-00-00 00:00:00" : $date;
       $this->attributes['resolved_at'] = $date;
    }*/

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->site->company;
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