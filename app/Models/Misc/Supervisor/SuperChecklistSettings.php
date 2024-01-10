<?php

namespace App\Models\Misc\Supervisor;

use URL;
use Mail;
use App\User;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuperChecklistSettings extends Model
{

    protected $table = 'supervisor_checklist_settings';
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