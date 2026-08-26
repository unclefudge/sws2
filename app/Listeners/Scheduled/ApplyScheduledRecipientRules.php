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
        $runId = $this->context->runId();
        if (!$runId) {
            return;
        }

        $taskKey = ScheduledRun::whereKey($runId)->value('task_key');
        $definition = ScheduledOperationDefinition::with('recipientRules')
            ->where('task_key', $taskKey)->first();

        // Legacy mode leaves every existing report exactly as it is today.
        if (!$definition || $definition->recipient_mode === 'legacy') {
            return;
        }

        $configured = $this->resolver->resolve($definition);
        $message = $event->message;
        $existing = $this->existingAddresses($message);
        $addresses = $definition->recipient_mode === 'managed'
            ? $configured
            : array_merge($existing, $configured);
        $addresses = $this->deduplicate($addresses);

        // Never send configured production addresses during local/test runs.
        if (!app()->environment('prod')) {
            $dev = config('mail.email_dev');
            $addresses = $dev ? [['type' => 'to', 'email' => $dev, 'name' => null]] : [];
        }

        if (!collect($addresses)->contains('type', 'to')) {
            throw new RuntimeException("Scheduled operation [$taskKey] has managed recipients but no valid To address.");
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
