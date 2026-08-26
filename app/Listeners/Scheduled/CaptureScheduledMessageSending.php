<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Scheduled\ScheduledRunContext;
use App\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;

class CaptureScheduledMessageSending
{
    public function __construct(private ScheduledRunContext $context)
    {
    }

    public function handle(MessageSending $event): void
    {
        $runId = $this->context->runId();

        // Normal web-request mail must not be copied into scheduled history.
        if (!$runId) {
            return;
        }

        $message = $event->message;
        $uuid = (string) Str::uuid();
        $message->getHeaders()->addTextHeader('X-SWS-Scheduled-Message', $uuid);

        $record = ScheduledReportMessage::create([
            'scheduled_run_id' => $runId,
            'uuid' => $uuid,
            'status' => 'sending',
            'subject' => $message->getSubject(),
            'html_body' => $message->getHtmlBody(),
            'text_body' => $message->getTextBody(),
            // Retain attachment names/types, not their binary data. This keeps the
            // report audit useful without allowing the history tables to balloon.
            'attachments' => collect($message->getAttachments())->map(fn($part) => [
                'name' => method_exists($part, 'getFilename') ? $part->getFilename() : null,
                'content_type' => method_exists($part, 'getMediaType') ? $part->getMediaType() . '/' . $part->getMediaSubtype() : null,
            ])->values()->all(),
        ]);

        foreach (['to' => $message->getTo(), 'cc' => $message->getCc(), 'bcc' => $message->getBcc()] as $type => $addresses) {
            foreach ($addresses as $address) {
                if (!$address instanceof Address) {
                    continue;
                }

                $record->recipients()->create([
                    'type' => $type,
                    // Resolve known SafeWorksite users where possible while
                    // retaining external addresses exactly as they were sent.
                    'user_id' => User::where('email', $address->getAddress())->value('id'),
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: null,
                    'source' => $message->getHeaders()->has('X-SWS-Recipient-Rules')
                        ? 'scheduled recipient rules'
                        : 'legacy report recipient logic',
                ]);
            }
        }
    }
}
