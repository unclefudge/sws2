<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledReportAttachment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function message()
    {
        return $this->belongsTo(ScheduledReportMessage::class, 'scheduled_report_message_id');
    }
}
