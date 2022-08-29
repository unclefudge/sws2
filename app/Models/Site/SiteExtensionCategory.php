<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteExtensionCategory extends Model {

    protected $table = 'site_extensions_categories';
    protected $fillable = [
        'name', 'parent', 'order', 'notes', 'status', 'company_id',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    protected $dates = [''];

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