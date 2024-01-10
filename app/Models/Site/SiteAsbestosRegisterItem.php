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

class SiteAsbestosRegisterItem extends Model
{

    protected $table = 'site_asbestos_register_items';
    protected $fillable = [
        'register_id', 'date', 'type', 'location', 'friable', 'amount', 'condition', 'assessment', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $casts = ['date' => 'datetime'];

    /**
     * A SiteAsbestosRegisterItem belongs to a SiteAsbestosRegister
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function register()
    {
        return $this->belongsTo('App\Models\Site\SiteAsbestosRegister', 'register_id');
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