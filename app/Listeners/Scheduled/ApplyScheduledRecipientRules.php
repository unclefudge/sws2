<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledRecipientRuleResolver;
use App\Scheduled\ScheduledRunContext;
use Illuminate\Mail\Events\MessageSending;
use RuntimeException;
use Symfony\Component\Mime\Address;

class ApplyScheduledRecipientRules
{
    public function __construct(
        private ScheduledRunContext $context,
        private ScheduledRecipientRuleResolver $resolver
    ) {
    }

    public function handle(MessageSending $event): void
    {
        $message = $event->message;

        // Outside production, redirect every email from the website to the
        // development address before any scheduled recipient rules are checked.
        if (!app()->environment('prod')) {
            $devEmail = config('mail.email_dev');
            if (!$devEmail || !filter_var($devEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('A valid mail.email_dev address is required outside production.');
            }

            $this->replaceAddresses($message, [['type' => 'to', 'email' => $devEmail, 'name' => null]]);
            $message->getHeaders()->addTextHeader('X-SWS-Dev-Redirect', 'true');
            return;
        }

        $runId = $this->context->runId();
        if (!$runId) {
            return;
        }

        $taskKey = ScheduledRun::whereKey($runId)->value('task_key');
        $definition = ScheduledOperationDefinition::with('recipientRules')
            ->where('task_key', $taskKey)->first();
        $existing = $this->existingAddresses($message);

        // Legacy mode leaves every existing report exactly as it is today.
        if (!$definition || $definition->recipient_mode === 'legacy') {
            if (!$this->deduplicate($existing)) {
                throw new RuntimeException("Scheduled operation [$taskKey] has no valid To, CC or BCC recipients.");
            }
            return;
        }

        $configured = $this->resolver->resolve($definition);
        $addresses = $definition->recipient_mode === 'managed'
            ? $configured
            : array_merge($existing, $configured);
        $addresses = $this->deduplicate($addresses);

        if (!$addresses) {
            throw new RuntimeException("Scheduled operation [$taskKey] has no valid To, CC or BCC recipients.");
        }

        $this->replaceAddresses($message, $addresses);
        $message->getHeaders()->addTextHeader('X-SWS-Recipient-Rules', $definition->recipient_mode);
    }

    private function existingAddresses($message): array
    {
        $addresses = [];
        foreach (['to' => $message->getTo(), 'cc' => $message->getCc(), 'bcc' => $message->getBcc()] as $type => $items) {
            foreach ($items as $item) {
                if ($item instanceof Address) {
                    $addresses[] = ['type' => $type, 'email' => $item->getAddress(), 'name' => $item->getName() ?: null];
                }
            }
        }
        return $addresses;
    }

    private function replaceAddresses($message, array $addresses): void
    {
        foreach (['To', 'Cc', 'Bcc'] as $header) {
            $message->getHeaders()->remove($header);
        }

        foreach ($addresses as $address) {
            $value = new Address($address['email'], $address['name'] ?? '');
            match ($address['type']) {
                'cc' => $message->addCc($value),
                'bcc' => $message->addBcc($value),
                default => $message->addTo($value),
            };
        }
    }

    private function deduplicate(array $addresses): array
    {
        $priority = ['to' => 1, 'cc' => 2, 'bcc' => 3];
        usort($addresses, fn($a, $b) => ($priority[$a['type']] ?? 9) <=> ($priority[$b['type']] ?? 9));
        $seen = [];

        return array_values(array_filter($addresses, function ($address) use (&$seen) {
            $email = mb_strtolower($address['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || isset($seen[$email])) {
                return false;
            }
            $seen[$email] = true;
            return true;
        }));
    }
}
