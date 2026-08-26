<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledRunGroup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'alert_sent_at' => 'datetime',
    ];

    public function runs()
    {
        return $this->hasMany(ScheduledRun::class);
    }
}
