<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use Illuminate\Mail\Events\MessageSent;

class CaptureScheduledMessageSent
{
    public function handle(MessageSent $event): void
    {
        // MessageSent wraps Symfony's SentMessage. Read the original Email so
        // this works consistently for both synchronous and queued mailables.
        $sentMessage = $event->sent->getSymfonySentMessage();
        $message = $sentMessage->getOriginalMessage();
        $header = $message->getHeaders()->get('X-SWS-Scheduled-Message');

        if (!$header) {
            return;
        }

        $record = ScheduledReportMessage::where('uuid', $header->getBodyAsString())->first();

        if (!$record) {
            return;
        }

        $record->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => $sentMessage->getMessageId(),
        ]);

        // Keep only the requested number of actual email previews for each report.
        // Run rows remain available for the longer operational history period.
        $keep = max(1, (int) config('scheduled_operations.email_history_per_task'));
        $oldIds = ScheduledReportMessage::query()
            ->whereHas('run', fn($query) => $query->where('task_key', $record->run->task_key))
            ->latest('id')
            ->skip($keep)
            ->take(1000)
            ->pluck('id');

        if ($oldIds->isNotEmpty()) {
            \App\Models\Scheduled\ScheduledReportRecipient::whereIn('scheduled_report_message_id', $oldIds)->delete();
            ScheduledReportMessage::whereIn('id', $oldIds)->delete();
        }
    }
}
