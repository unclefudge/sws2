<?php

namespace App\Models\Safety;

use App\Services\FileBank;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SafetyDataSheet extends Model
{

    protected $table = 'safety_sds_docs';
    protected $fillable = [
        'type', 'name', 'manufacturer', 'application', 'hazardous', 'dangerous', 'attachment', 'date', 'expiry',
        'reference', 'version', 'notes', 'company_id',
        'status', 'created_by', 'updated_by'];
    protected $casts = ['date' => 'datetime', 'expiry' => 'datetime'];


    /**
     * A SDS belongs to a Company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /**
     * A SDS has many categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Safety\SafetyDocCategory', 'safety_sds_cats', 'sds_id', 'cat_id');
    }

    /**
     * A list of categories for this SDS
     *
     * @return string
     */
    public function categoriesSBC()
    {
        $string = '';
        foreach ($this->categories as $cat) {
            $string .= $cat->name . ', ';
        }

        return rtrim($string, ', ');
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

    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment) return '';
        $path = "/whs/sds/{$this->attachment}";

        return FileBank::exists($path) ? FileBank::url($path) : '';
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
                //$table->created_by = Auth::user()->id;
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }

}

