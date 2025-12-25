<?php

namespace App\Models\Client;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ClientPlannerEmailDoc extends Model
{

    protected $table = 'client_planner_emails_docs';
    protected $fillable = ['email_id', 'name', 'attachment', 'notes', 'status', 'created_by', 'updated_by'];

    /**
     * A ClientPlannerEmailDoc belongs to a ClientPlannerEmail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email()
    {
        return $this->belongsTo('App\Models\Client\ClientPlannerEmail', 'email_id');
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