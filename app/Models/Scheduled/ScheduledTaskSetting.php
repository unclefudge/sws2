<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskSetting extends Model
{
    protected $fillable = ['task_key', 'enabled', 'schedule_override', 'recipient_override', 'updated_by'];

    protected $casts = [
        'enabled' => 'boolean',
        'schedule_override' => 'array',
        'recipient_override' => 'array',
    ];
}
