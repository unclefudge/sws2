<?php

namespace App\Models\Site;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SiteInspectionDoc extends Model
{

    protected $table = 'site_inspection_docs';
    protected $fillable = [
        'type', 'inspect_id', 'table', 'name', 'attachment',
        'notes', 'status', 'created_by', 'updated_by'];

    /**
     * A Site Inspection Doc belongs to a Site Inspection
     */

    public function inspection()
    {
        if ($this->attributes['table'] == 'electrical')
            return SiteInspectionElectrical::find($this->attributes['inspect_id']);
        elseif ($this->attributes['table'] == 'plumbing')
            return SiteInspectionPlumbing::find($this->attributes['inspect_id']);
        else
            return NULL;
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