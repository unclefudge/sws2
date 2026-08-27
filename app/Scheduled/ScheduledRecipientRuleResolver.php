<?php

namespace App\Scheduled;

use App\Models\Misc\SettingsNotification;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\User;

class ScheduledRecipientRuleResolver
{
    /**
     * Resolve database rules to simple channel/address records. Existing
     * handler-controlled recipients are handled separately by the mail event.
     */
    public function resolve(ScheduledOperationDefinition $definition): array
    {
        $resolved = [];

        foreach ($definition->recipientRules()->where('enabled', true)->get() as $rule) {
            $addresses = match ($rule->source_type) {
                'manual' => $this->manual($rule->source_value, $rule->label),
                'user' => $this->user($rule->source_value, $rule->source_meta),
                'notification_group' => $this->notificationGroup($rule->source_value, $rule->source_meta),
                default => [],
            };

            foreach ($addresses as $address) {
                $resolved[] = array_merge($address, [
                    'type' => $rule->delivery_type,
                    'source' => $rule->source_type,
                ]);
            }
        }

        return $this->deduplicate($resolved);
    }

    private function manual(?string $email, ?string $name): array
    {
        return $email && filter_var($email, FILTER_VALIDATE_EMAIL)
            ? [['email' => mb_strtolower(trim($email)), 'name' => $name ?: null]]
            : [];
    }

    private function user(?string $id, ?array $meta): array
    {
        $companyId = (int) ($meta['company_id'] ?? 0);
        if (!$companyId) {
            return [];
        }

        $user = User::query()
            ->whereKey((int) $id)
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->first();

        return $user && filter_var($user->email, FILTER_VALIDATE_EMAIL)
            ? [['email' => mb_strtolower($user->email), 'name' => $user->fullname ?: null]]
            : [];
    }

    private function notificationGroup(?string $categoryId, ?array $meta): array
    {
        $companyId = (int) ($meta['company_id'] ?? 0);
        if (!$companyId || !(int) $categoryId) {
            return [];
        }

        $userIds = SettingsNotification::query()
            ->where('company_id', $companyId)
            ->where('type', (int) $categoryId)
            ->pluck('user_id');

        return User::whereIn('id', $userIds)
            ->get()
            ->filter(fn(User $user) => filter_var($user->email, FILTER_VALIDATE_EMAIL))
            ->map(fn(User $user) => [
                'email' => mb_strtolower($user->email),
                'name' => $user->fullname ?: null,
            ])->values()->all();
    }

    /**
     * An address belongs to only one envelope channel. To wins over CC, and CC
     * wins over BCC, which avoids duplicate deliveries when rules overlap.
     */
    private function deduplicate(array $addresses): array
    {
        $priority = ['to' => 1, 'cc' => 2, 'bcc' => 3];
        usort($addresses, fn($a, $b) => ($priority[$a['type']] ?? 9) <=> ($priority[$b['type']] ?? 9));

        $seen = [];
        return array_values(array_filter($addresses, function (array $address) use (&$seen) {
            $key = mb_strtolower($address['email']);
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            return true;
        }));
    }
}
