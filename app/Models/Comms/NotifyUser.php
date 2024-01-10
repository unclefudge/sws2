<?php

namespace App\Models\Comms;

use DB;
use Illuminate\Database\Eloquent\Model;
use Mail;

class NotifyUser extends Model
{

    protected $table = 'notify_user';
    protected $fillable = [
        'notify_id', 'user_id', 'opened', 'opened_at'
    ];

    public $timestamps = false;
    protected $casts = ['opened_at' => 'datetime'];

    /**
     * A NotifyUser belongs to a Notify
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function todo()
    {
        return $this->belongsTo('App\Models\Comms\Notify');
    }

    /**
     * A NotifyUser belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->user_id;
    }


}