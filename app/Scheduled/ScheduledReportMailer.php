<?php

namespace App\Scheduled;

use Illuminate\Mail\Mailable;

/**
 * Sends a scheduled report synchronously inside its existing queue job while
 * exposing report-specific recipients to the central mail listener.
 */
class ScheduledReportMailer
{
    public function __construct(private ScheduledDynamicRecipientContext $context)
    {
    }

    public function send(Mailable $mailable, array $dynamicRecipients = []): void
    {
        foreach (collect($dynamicRecipients)
            ->filter(fn($recipient) => !empty($recipient['email']))
            ->unique(fn(array $recipient) => mb_strtolower($recipient['email'])) as $recipient) {
            $method = ($recipient['type'] ?? 'to') === 'cc' ? 'cc' : 'to';
            $mailable->{$method}($recipient['email'], $recipient['name'] ?? null);
        }

        $this->context->run(
            $dynamicRecipients,
            fn() => $mailable->send(app('mailer'))
        );
    }
}
