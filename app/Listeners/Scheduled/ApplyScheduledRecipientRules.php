<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledOperationRegistry;
use App\Scheduled\ScheduledRecipientRuleResolver;
use App\Scheduled\ScheduledRunContext;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Mime\Address;

class ApplyScheduledRecipientRules
{
    public function __construct(
        private ScheduledRunContext $context,
        private ScheduledRecipientRuleResolver $resolver,
        private ScheduledDynamicRecipientContext $dynamicContext,
        private ScheduledOperationRegistry $registry
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
        if (!$definition) {
            throw new RuntimeException("Scheduled operation [$taskKey] has no database definition.");
        }

        if (!$this->registry->sendsEmailFor($taskKey)) {
            throw new RuntimeException("Scheduled operation [$taskKey] is marked as a no-email operation but attempted to send mail.");
        }

        $configured = $this->resolver->resolve($definition);
        $dynamic = collect($this->dynamicContext->resolved())
            ->map(fn(array $recipient) => [
                'type' => $recipient['type'],
                'email' => $recipient['email'],
                'name' => $recipient['name'],
                'source' => 'dynamic',
            ])->all();

        $this->recordDynamicWarnings($taskKey);

        // Scheduled mail has one recipient policy: handler-declared automatic
        // recipients plus optional rules configured in the dashboard. Any
        // hidden to()/cc()/bcc() addresses left in old mailables are replaced.
        $addresses = $this->deduplicate(array_merge($dynamic, $configured));

        // A missing dynamic primary recipient must not silently lose a report.
        // Promote configured management CC recipients to To and record it.
        if (!collect($addresses)->contains('type', 'to')) {
            $promoted = false;
            foreach ($addresses as &$address) {
                if (($address['type'] ?? null) === 'cc') {
                    $address['type'] = 'to';
                    $promoted = true;
                }
            }
            unset($address);

            if ($promoted) {
                $warning = "Scheduled operation [$taskKey] promoted configured CC recipients to To because no valid primary recipient was resolved.";
                Log::warning($warning, ['scheduled_run_id' => $runId]);
                echo "Recipient warning: {$warning}\n";
            }
        }

        if (!collect($addresses)->contains('type', 'to')) {
            throw new RuntimeException("Scheduled operation [$taskKey] has no valid automatic or configured To address.");
        }

        $this->replaceAddresses($message, $addresses);
        $message->getHeaders()->addTextHeader('X-SWS-Recipient-Rules', 'automatic+configured');
        if ($dynamic) {
            $message->getHeaders()->addTextHeader(
                'X-SWS-Dynamic-Recipients',
                collect($this->dynamicContext->resolved())->pluck('key')->unique()->join(',')
            );
        }
    }

    private function recordDynamicWarnings(string $taskKey): void
    {
        $warnings = collect($this->dynamicContext->missing())
            ->filter(fn(array $recipient) => $recipient['required'])
            ->map(fn(array $recipient) => ($recipient['label'] ?? $recipient['key']).': '.($recipient['reason'] ?? 'No valid email was resolved.'));

        $warnings->unique()->each(function (string $warning) use ($taskKey) {
            Log::warning('Scheduled dynamic recipient unavailable', [
                'task_key' => $taskKey,
                'warning' => $warning,
            ]);
            echo "Recipient warning: {$warning}\n";
        });
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
