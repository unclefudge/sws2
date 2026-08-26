<?php

namespace App\Models\Scheduled;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ScheduledOperationChangeLog extends Model
{
    protected $fillable = [
        'scheduled_operation_definition_id', 'user_id', 'action', 'before', 'after',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function definition()
    {
        return $this->belongsTo(ScheduledOperationDefinition::class, 'scheduled_operation_definition_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
