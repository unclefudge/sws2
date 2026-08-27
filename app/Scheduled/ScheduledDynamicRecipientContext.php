<?php

namespace App\Scheduled;

use Closure;

/**
 * Carries report-specific recipients while one scheduled email is built.
 *
 * A converted report supplies actual recipients for code-declared roles such
 * as "site supervisor" or "assigned company contact". The mail listener can
 * then preserve those recipients in managed mode while replacing old fixed
 * addresses with the rules maintained under Settings > Notifications.
 */
class ScheduledDynamicRecipientContext
{
    private array $recipients = [];

    public function run(array $recipients, Closure $callback): mixed
    {
        $previous = $this->recipients;
        $this->recipients = $this->normalise($recipients);

        try {
            return $callback();
        } finally {
            $this->recipients = $previous;
        }
    }

    public function all(): array
    {
        return $this->recipients;
    }

    public function resolved(): array
    {
        return array_values(array_filter(
            $this->recipients,
            fn(array $recipient) => !empty($recipient['email'])
                && filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)
        ));
    }

    public function missing(): array
    {
        return array_values(array_filter(
            $this->recipients,
            fn(array $recipient) => empty($recipient['email'])
        ));
    }

    private function normalise(array $recipients): array
    {
        return collect($recipients)
            ->filter(fn($recipient) => is_array($recipient) && !empty($recipient['key']))
            ->map(function (array $recipient) {
                $email = trim((string) ($recipient['email'] ?? ''));

                return [
                    'key' => (string) $recipient['key'],
                    'label' => (string) ($recipient['label'] ?? $recipient['key']),
                    'type' => in_array(($recipient['type'] ?? 'to'), ['to', 'cc'], true)
                        ? $recipient['type']
                        : 'to',
                    'email' => filter_var($email, FILTER_VALIDATE_EMAIL)
                        ? mb_strtolower($email)
                        : null,
                    'name' => trim((string) ($recipient['name'] ?? '')) ?: null,
                    'required' => (bool) ($recipient['required'] ?? true),
                    'reason' => trim((string) ($recipient['reason'] ?? '')) ?: null,
                ];
            })
            ->values()
            ->all();
    }
}
