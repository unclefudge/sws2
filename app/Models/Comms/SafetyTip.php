<?php

namespace App\Models\Comms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SafetyTip extends Model
{

    protected $table = 'safety_tips';
    protected $fillable = [
        'title', 'body', 'last_published', 'action_required', 'status',
        'company_id', 'created_by', 'updated_by',
    ];
    protected $casts = ['last_published' => 'datetime'];

    /**
     * A Safety Tip belongs to a Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->company;
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