<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledDispatchHeartbeat extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];
}
