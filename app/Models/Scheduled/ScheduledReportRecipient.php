<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledReportRecipient extends Model
{
    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(ScheduledReportMessage::class, 'scheduled_report_message_id');
    }
}
