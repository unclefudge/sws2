<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Scheduled\ScheduledReportArchive;
use Illuminate\Mail\Events\MessageSent;

class CaptureScheduledMessageSent
{
    public function __construct(private ScheduledReportArchive $archive)
    {
    }

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

        // Pruning is throttled per report. It keeps the last month of files and
        // always preserves at least five email-producing runs for slower reports.
        $this->archive->pruneOnceDaily($record->run->task_key);
    }
}
