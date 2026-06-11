<?php

namespace App\Models\Misc;


use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PublicHoliday extends Model
{
    protected $table = 'public_holidays';
    protected $fillable = ['type', 'name', 'date', 'created_at', 'updated_at'];
    protected $casts = ['date' => 'date'];


    /**
     * A Company Notification belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * Get the owner of record  (getter)
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
            });
        }
    }
}

