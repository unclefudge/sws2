<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledOperationRecipientRule extends Model
{
    protected $fillable = [
        'scheduled_operation_definition_id', 'delivery_type', 'source_type',
        'source_value', 'source_meta', 'label', 'enabled', 'sort_order',
    ];

    protected $casts = [
        'source_meta' => 'array',
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function definition()
    {
        return $this->belongsTo(ScheduledOperationDefinition::class, 'scheduled_operation_definition_id');
    }
}
