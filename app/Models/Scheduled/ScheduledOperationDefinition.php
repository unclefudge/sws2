<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledOperationDefinition extends Model
{
    protected $fillable = [
        'task_key', 'handler_key', 'name', 'category', 'description',
        'recipient_summary', 'enabled', 'schedule_type', 'schedule_data',
        'tries', 'timeout_seconds', 'client_configurable',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'schedule_data' => 'array',
        'client_configurable' => 'boolean',
        'tries' => 'integer',
        'timeout_seconds' => 'integer',
    ];

    public function recipientRules()
    {
        return $this->hasMany(ScheduledOperationRecipientRule::class)
            ->orderBy('sort_order')->orderBy('id');
    }

    public function changeLogs()
    {
        return $this->hasMany(ScheduledOperationChangeLog::class)
            ->latest('id');
    }
}
