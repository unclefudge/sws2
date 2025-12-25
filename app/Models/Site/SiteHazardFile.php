<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class SiteHazardFile extends Model
{

    protected $table = 'site_hazards_files';
    protected $fillable = ['hazard_id', 'type', 'name', 'attachment', 'notes', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['updated_at' => 'datetime'];

    /**
     * A SiteHazardFile belongs to a sitehazard
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hazard()
    {
        return $this->belongsTo('App\Models\Site\SiteHazard', 'hazard_id');
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