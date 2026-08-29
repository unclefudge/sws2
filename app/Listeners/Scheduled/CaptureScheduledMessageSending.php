<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Scheduled\ScheduledReportArchive;
use App\Scheduled\ScheduledRunContext;
use App\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;

class CaptureScheduledMessageSending
{
    public function __construct(private ScheduledRunContext $context, private ScheduledReportArchive $archive)
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
            'attachments' => [],
        ]);

        // Copy attachment bytes while generated temporary PDFs still exist. Any
        // archive failure is recorded but deliberately cannot stop the email.
        try {
            $record->update(['attachments' => $this->archive->capture($record, $message->getAttachments())]);
        } catch (\Throwable $exception) {
            // The audit archive is secondary to delivery. A disk/schema problem
            // must be visible in logs without preventing the real email.
            Log::warning('Scheduled report attachments could not be archived', [
                'scheduled_report_message_id' => $record->id,
                'error' => $exception->getMessage(),
            ]);
        }

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
