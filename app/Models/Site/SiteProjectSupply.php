<?php

namespace App\Models\Site;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteProjectSupply extends Model {

    protected $table = 'project_supply';
    protected $fillable = [
        'site_id', 'version', 'approved_by', 'approved_at', 'attachment', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected $dates = ['approved_at'];

    /**
     * A SiteProjectSupply belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }


    /**
     * A SiteProjectSupply has many Items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteProjectSupplyItem', 'supply_id');
    }

    /*
     * List of items ordered
     */
    public function itemsOrdered()
    {
        $ordered = [];
        foreach ($this->items as $item) {
            $order = SiteProjectSupplyProduct::find($item->product_id)->order;
            $ordered[$order] = $item;
        }

        return $ordered;
    }


    /**
     * Email Notification
     */
    /*
    public function emailNotification()
    {
        $email_to = [env('EMAIL_DEV')];
        $email_user = '';

        if (\App::environment('prod')) {
            $email_list = $this->site->company->notificationsUsersEmailType('site.asbestos');
            $email_supers = $this->site->supervisorsEmails();
            $email_to = array_unique(array_merge($email_list, $email_supers), SORT_REGULAR);
            $email_user = (Auth::check() && validEmail(Auth::user()->email)) ? Auth::user()->email : '';
        }

        if ($email_to && $email_user)
            Mail::to($email_to)->cc([$email_user])->send(new \App\Mail\Site\SiteAsbestosCreated($this));
        elseif ($email_to)
            Mail::to($email_to)->send(new \App\Mail\Site\SiteAsbestosCreated($this));
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
     * Get the Attachment URL (setter)
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attributes['attachment'] && file_exists(public_path('/filebank/site/' . $this->site_id . '/docs/' . $this->attributes['attachment'])))
            return '/filebank/site/' . $this->site_id . '/docs/' . $this->attributes['attachment'];

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