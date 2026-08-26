<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledRun extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failure_notified_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(ScheduledRunGroup::class, 'scheduled_run_group_id');
    }

    public function messages()
    {
        return $this->hasMany(ScheduledReportMessage::class);
    }

    public function retryOf()
    {
        return $this->belongsTo(self::class, 'retry_of_id');
    }
}
