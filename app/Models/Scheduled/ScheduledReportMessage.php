<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledReportMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(ScheduledRun::class, 'scheduled_run_id');
    }

    public function recipients()
    {
        return $this->hasMany(ScheduledReportRecipient::class);
    }

    public function archivedAttachments()
    {
        return $this->hasMany(ScheduledReportAttachment::class);
    }
}
