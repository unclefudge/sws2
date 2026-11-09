<?php

namespace App\Models\Site;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SiteCdcItem extends Model
{
    public const STATUS_COMPLETED = 0;
    public const STATUS_OUTSTANDING = 1;
    public const STATUS_DEFECTIVE = -1;

    protected $table = 'site_cdc_items';
    protected $fillable = [
        'cdc_id', 'name', 'notes', 'category_id', 'assigned_to', 'planner_id', 'order', 'status',
        'sign_by', 'sign_at', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['sign_at' => 'datetime'];

    public function cdc()
    {
        return $this->belongsTo('App\Models\Site\SiteCdc', 'cdc_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Category');
    }

    public function planner()
    {
        return $this->belongsTo('App\Models\Site\Planner\SitePlanner', 'planner_id');
    }

    public function assigned()
    {
        return $this->belongsTo('App\Models\Company\Company', 'assigned_to');
    }

    public function isInspections(): bool
    {
        return strcasecmp(trim((string) $this->category?->name), 'Inspections') === 0;
    }

    public function todos($status = '')
    {
        return collect();
    }

    public function closeToDo($type = 'cdc_item')
    {
        // Disabled while CDC Requirements is being trialled.
    }

    public function createAssignSupervisorToDo($user_list)
    {
        // Disabled while CDC Requirements is being trialled.
    }


    /**
     * Email Assigned
     */
    public function emailAssigned($user)
    {
        // Disabled while CDC Requirements is being trialled.
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
