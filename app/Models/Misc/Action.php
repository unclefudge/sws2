<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class Action extends Model
{

    protected $table = 'actions';
    protected $fillable = ['table', 'table_id', 'todo_id', 'action', 'attachment', 'created_by', 'created_at', 'updated_at', 'updated_by'];

    /**
     * A Action belongs to a Parent Record
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record()
    {
        if ($this->table == 'site_hazards')
            return $this->belongsTo('App\Models\Site\SiteHazards', 'table_id');
        if ($this->table == 'site_qa')
            return $this->belongsTo('App\Models\Site\SiteQa', 'table_id');
        if ($this->table == 'site_asbestos')
            return $this->belongsTo('App\Models\Site\SiteAsbestos', 'table_id');
        if ($this->table == 'site_maintenance')
            return $this->belongsTo('App\Models\Site\SiteMaintenance', 'table_id');
        if ($this->table == 'site_inspection_plumbing')
            return $this->belongsTo('App\Models\Site\SiteInspectionPlumbing', 'table_id');
        if ($this->table == 'site_inspection_electrical')
            return $this->belongsTo('App\Models\Site\SiteInspectionElectrical', 'table_id');
        if ($this->table == 'company_docs_review')
            return $this->belongsTo('App\Models\Company\CompanyDoc', 'table_id');
        if ($this->table == 'companys')
            return $this->belongsTo('App\Models\Company\Company', 'table_id');

    }

    /**
     * A Action 'may' belong to a Todoo
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function todo()
    {
        return ($this->todo_id) ? $this->belongsTo('App\Models\Comms\Todo', 'todo_id') : null;
    }

    /**
     * A Action belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by');
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->record->owned_by;
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
        } else {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = 1;
                $table->updated_by = 1;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = 1;
            });
        }
    }
}