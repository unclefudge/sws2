<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;


class SiteUpcomingSettings extends Model
{

    protected $table = 'site_upcoming_settings';
    protected $fillable = [
        'field', 'name', 'value', 'colour', 'order', 'notes', 'status', 'company_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];


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