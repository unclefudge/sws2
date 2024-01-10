<?php

namespace App\Models\Comms;

use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;

class Notify extends Model
{

    protected $table = 'notify';
    protected $fillable = [
        'name', 'info', 'type', 'type_id', 'from', 'to',
        'priority', 'action', 'status', 'company_id', 'created_by', 'updated_by'
    ];
    protected $casts = ['from' => 'datetime', 'to' => 'datetime'];

    /**
     * A Notify belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * A Notify belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /**
     * A Notify is assigned to many Users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\Comms\NotifyUser', 'notify_id');
    }

    /**
     * A Notify is assigned to many Users
     *
     * @return collection of users
     */
    public function assignedTo()
    {
        $user_list = $this->users->pluck('user_id')->toArray();

        return User::whereIn('id', $user_list)->get();
    }


    /**
     * Assign a list of users to the Notify
     */
    public function assignUsers($user_ids)
    {
        if (is_array($user_ids))
            foreach ($user_ids as $user_id)
                NotifyUser::create(['notify_id' => $this->id, 'user_id' => $user_id]); // Assign users
        else
            NotifyUser::create(['notify_id' => $this->id, 'user_id' => $user_ids]); // Assign users

    }

    /**
     * Has a Notify been opened by User (x)
     */
    public function isOpenedBy($user)
    {
        $record = NotifyUser::where('notify_id', $this->id)->where('user_id', $user->id)->first();

        if ($record && $record->opened)
            return true;

        return false;
    }

    /**
     * Has a Notify been opened by User Today (x)
     */
    public function isOpenedTodayBy($user)
    {
        $record = NotifyUser::where('notify_id', $this->id)->where('user_id', $user->id)->where('opened', '1')->first();

        $today_am = Carbon::today();
        $today_pm = Carbon::today()->addHours(23);
        //$opened_at = Carbon::createFromTimestamp($record->opened_at);
        if ($record && $record->opened_at->between($today_am, $today_pm))
            return true;

        return false;
    }

    /**
     * Marked Notify opened by User (x)
     */
    public function markOpenedBy($user)
    {
        $record = NotifyUser::where('notify_id', $this->id)->where('user_id', $user->id)->first();

        if (!$record)
            $record = NotifyUser::create(['notify_id' => $this->id, 'user_id' => $user->id]);

        $record->opened = 1;
        $record->opened_at = Carbon::now();
        $record->save();
    }

    /**
     * A Notify 'may' have been viewed by multiple users
     */
    public function viewedBy()
    {
        $user_ids = NotifyUser::where('notify_id', $this->id)->where('opened', 1)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->orderBy('firstname')->get();
    }

    /**
     * A Notify 'may' have been viewed by multiple users - return list separated by comma
     *
     * return string
     */
    public function viewedBySBC()
    {
        $string = '';
        foreach ($this->viewedBy() as $u) {
            $notify_user = NotifyUser::where('notify_id', $this->id)->where('user_id', $u->id)->where('opened', 1)->first();
            $string .= $u->fullname . ' (' . $notify_user->opened_at->format('j/n/y') . '), ';
        }
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * A Notify 'may' be unviewed by multiple users
     */
    public function unviewedBy()
    {
        $user_ids = NotifyUser::where('notify_id', $this->id)->where('opened', 0)->pluck('user_id')->toArray();

        return User::whereIn('id', $user_ids)->orderBy('firstname')->get();
    }

    /**
     * A Notify 'may' be unviewed by multiple users - return list separated by comma
     */
    public function unviewedBySBC()
    {
        $string = '';
        foreach ($this->unviewedBy() as $u)
            $string .= $u->fullname . ', ';
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->createdBy;
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