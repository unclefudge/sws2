<?php

namespace App\Models\Site;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteProjectSupplyItem extends Model {

    protected $table = 'project_supply_items';
    protected $fillable = [
        'supply_id', 'product_id', 'product', 'supplier', 'type', 'colour', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];

    /**
     * A SiteProjectSupplyItemItem belongs to a SiteProjectSupplyItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supply()
    {
        return $this->belongsTo('App\Models\Site\SiteProjectSupply', 'supply_id');
    }

    /**
     * A SiteProjectSupplyItemItem belongs to a SiteProjectSupplyProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRef()
    {
        return $this->belongsTo('App\Models\Site\SiteProjectSupplyProduct', 'product_id');
    }


    /*
    *  Determine if Item is complete - has supplier + type + colour filled out
    */
    public function isComplete()
    {
        if ($this->supplier && $this->type && $this->colour)
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