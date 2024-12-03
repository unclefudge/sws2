<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;


class SiteShutdownItem extends Model
{

    protected $table = 'site_shutdown_items';
    protected $fillable = [
        'shutdown_id', 'category', 'sub_category', 'name', 'order', 'type', 'response', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    public function shutdown()
    {
        return $this->belongsTo('App\Models\Site\SiteShutdown', 'shutdown_id');
    }


    /*
    *  Determine if Item is complete - has supplier + type + colour filled out
    */
    public function isComplete()
    {
        if ($this->response != null && $this->response != '')
            return true;

        return false;
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