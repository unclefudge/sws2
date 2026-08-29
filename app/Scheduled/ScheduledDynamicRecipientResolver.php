<?php

namespace App\Scheduled;

use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\User;

/**
 * Safe, reusable resolvers for report-specific recipient roles.
 *
 * These methods return recipient context records rather than sending mail.
 * Report handlers remain responsible for deciding which records belong in
 * each email; this class consistently applies active-user and fallback rules.
 */
class ScheduledDynamicRecipientResolver
{
    public function siteSupervisor(string $key, string $label, ?Site $site, string $type = 'to', bool $required = true): array
    {
        $supervisor = $site && $site->supervisor_id ? User::find((int)$site->supervisor_id) : null;

        return $this->user($key, $label, $supervisor, $type, $required);
    }

    public function plannerCompanyContact(string $key, string $label, ?SitePlanner $planner, string $type = 'cc', bool $required = true): array
    {
        $company = $planner && $planner->entity_type === 'c' ? Company::find((int)$planner->entity_id) : null;

        return $this->companyContact($key, $label, $company, $type, $required);
    }

    public function user(string $key, string $label, ?User $user, string $type = 'to', bool $required = true): array
    {
        if ($this->validUser($user)) {
            return [$this->recipient($key, $label, $type, $user->email, $user->fullname, $required)];
        }

        return [$this->missing($key, $label, $type, $required, 'No active user with a valid email was found.')];
    }

    public function users(string $key, string $label, iterable $users, string $type = 'to', bool $required = true): array
    {
        $resolved = collect($users)
            ->filter(fn($user) => $user instanceof User && $this->validUser($user))
            ->unique(fn(User $user) => mb_strtolower($user->email))
            ->map(fn(User $user) => $this->recipient($key, $label, $type, $user->email, $user->fullname, $required))
            ->values()
            ->all();

        return $resolved ?: [
            $this->missing($key, $label, $type, $required, 'No assigned active user has a valid email.'),
        ];
    }

    /**
     * A ToDo retains assigned users, not whether they were originally chosen
     * individually, by company, or by role. Those users are therefore the most
     * reliable recipient source. If none remain valid, their companies provide
     * the primary/secondary/generic fallback chain.
     */
    public function todoAssignees(string $key, string $label, Todo $todo, string $type = 'cc', bool $required = true): array
    {
        $assigned = $todo->assignedTo();
        $direct = collect($this->users($key, $label, $assigned, $type, $required))->filter(fn(array $recipient) => !empty($recipient['email']))->values()->all();

        if ($direct) {
            return $direct;
        }

        $companies = $assigned->pluck('company_id')->filter()->unique()->map(fn($companyId) => Company::find((int)$companyId))->filter();

        $fallbacks = $companies->flatMap(fn(Company $company) => $this->companyContact($key, $label, $company, $type, $required))
            ->filter(fn(array $recipient) => !empty($recipient['email']))->unique(fn(array $recipient) => $recipient['email'])->values()->all();

        return $fallbacks ?: [$this->missing($key, $label, $type, $required, 'No assigned ToDo user or company contact has a valid email.'),];
    }

    /** Primary user, then secondary user, then the generic company mailbox. */
    public function companyContact(string $key, string $label, ?Company $company, string $type = 'cc', bool $required = true): array
    {
        if (!$company || (int)$company->status !== 1) {
            return [$this->missing($key, $label, $type, $required, 'The assigned company is missing or inactive.')];
        }

        foreach (array_filter([(int)$company->primary_user, (int)$company->secondary_user]) as $userId) {
            $user = User::query()->whereKey($userId)->where('company_id', $company->id)->where('status', 1)->first();

            if ($this->validUser($user)) {
                return [$this->recipient($key, $label, $type, $user->email, $user->fullname, $required)];
            }
        }

        if (filter_var($company->email, FILTER_VALIDATE_EMAIL)) {
            return [$this->recipient($key, $label, $type, $company->email, $company->name, $required)];
        }

        return [$this->missing($key, $label, $type, $required, "{$company->name} has no active primary/secondary contact or valid company email.")];
    }

    private function validUser(?User $user): bool
    {
        return $user && (int)$user->status === 1 && filter_var($user->email, FILTER_VALIDATE_EMAIL);
    }

    private function recipient(string $key, string $label, string $type, string $email, ?string $name, bool $required): array
    {
        return compact('key', 'label', 'type', 'email', 'name', 'required') + ['reason' => null];
    }

    private function missing(string $key, string $label, string $type, bool $required, string $reason): array
    {
        return compact('key', 'label', 'type', 'required', 'reason') + ['email' => null, 'name' => null];
    }
}
