<?php

namespace App\Models\Site\Planner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiteAttendance extends Model
{

    protected $table = 'site_attendance';
    protected $fillable = ['site_id', 'user_id', 'date', 'safe_site', 'accept_whs'];
    protected $casts = ['date' => 'datetime'];
    public $timestamps = false;

    /**
     * A SiteAttendance belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteAttendance belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * A SiteAttendance for specific date
     *
     */
    public function dates($date)
    {
        $today = Carbon::today()->format('ymd');

        return $today;

        $d = $this->filter(function ($r) {
            return $r->date->format('ymd') == Carbon::today()->format('ymd');
        });

        return $d;
    }

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
            // create a event to happen on saving
            static::saving(function ($table) {
                $table->user_id = Auth::user()->id;
            });
        }
    }
}