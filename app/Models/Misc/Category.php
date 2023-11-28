<?php

namespace App\Models\Misc;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Category extends Model {

    protected $table = 'categories';
    protected $fillable = ['type', 'sub_type', 'name', 'value', 'brief', 'colour', 'order', 'parent', 'notify', 'notify_users', 'notes', 'company_id', 'status', 'created_by', 'updated_by'];

    /**
     * A Category belongs to a Company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company() {
        return $this->belongsTo('App\Models\Company\Company', 'company_id');
    }

    /*
     * Notify Users to Array
     */
    public function notifyUsersArray() {
        return explode(',',$this->notify_users);
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
    public static function boot() {
        parent::boot();

        if(Auth::check()) {
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

