<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Scheduled\ScheduledOperationChangeLog;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledOperationDispatcher;
use App\Scheduled\ScheduledOperationRegistry;
use App\Scheduled\ScheduledRecipientRuleResolver;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ScheduledReports extends Component
{
    use NotifiesWithToastr;

    public string $reportSearch = '';
    public string $reportSort = 'name';
    public bool $showRecipientWarning = false;
    public string $recipientWarning = '';
    public bool $showEditor = false;
    public ?int $definitionId = null;
    public string $definitionUpdatedAt = '';
    public string $reportName = '';
    public bool $enabled = false;
    public string $scheduleType = 'weekly';
    public array $weekdays = [1];
    public ?int $weekday = 1;
    public ?int $occurrence = 1;
    public ?int $day = 1;
    public array $recipientRules = [];
    public array $dynamicRecipients = [];
    public ?int $logDefinitionId = null;
    public ?int $logRunId = null;
    public ?int $logMessageId = null;
    public bool $showRunConfirm = false;
    public ?int $pendingRunDefinitionId = null;
    public string $pendingRunName = '';
    public string $pendingRunRecipients = '';

    public function mount(): void
    {
        $this->authoriseClientReports();
    }

    public function toggleReportSort(): void
    {
        $this->reportSort = $this->reportSort === 'name' ? 'day' : 'name';
    }

    public function openReportLog(int $definitionId, ScheduledOperationRegistry $registry): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition($definitionId, $registry);
        $this->logDefinitionId = $definition->id;
        $this->logRunId = null;
        $this->logMessageId = null;
    }

    public function closeReportLog(): void
    {
        $this->logDefinitionId = null;
        $this->logRunId = null;
        $this->logMessageId = null;
    }

    public function showReportLogRun(int $runId, ScheduledOperationRegistry $registry): void
    {
        $definition = $this->clientDefinition((int) $this->logDefinitionId, $registry);
        abort_unless(ScheduledRun::whereKey($runId)->where('task_key', $definition->task_key)->exists(), 404);
        $this->logRunId = $runId;
        $this->logMessageId = null;
    }

    public function showReportLogMessage(int $messageId, ScheduledOperationRegistry $registry): void
    {
        $definition = $this->clientDefinition((int) $this->logDefinitionId, $registry);
        abort_unless(ScheduledReportMessage::whereKey($messageId)->where('scheduled_run_id', $this->logRunId)->whereHas('run', fn($query) => $query->where('task_key', $definition->task_key))->exists(), 404);
        $this->logMessageId = $messageId;
    }

    public function backToReportLogRun(): void
    {
        $this->logMessageId = null;
    }

    public function backToReportLogList(): void
    {
        $this->logRunId = null;
        $this->logMessageId = null;
    }

    public function requestReportRun(int $definitionId, ScheduledOperationRegistry $registry, ScheduledRecipientRuleResolver $recipientResolver): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition($definitionId, $registry);
        $configured = collect($recipientResolver->resolve($definition));
        $dynamic = $registry->dynamicRecipientsFor($definition->task_key);
        $hasAutomaticTo = collect($dynamic)->contains(fn(array $recipient) => ($recipient['delivery'] ?? 'to') === 'to');
        $hasRecipients = $hasAutomaticTo
            || $configured->contains(fn(array $recipient) => in_array($recipient['type'] ?? '', ['to', 'cc'], true));

        if (!$hasRecipients) {
            $this->recipientWarning = 'This report has no automatic To recipient. Configure at least one valid To or CC recipient before running it.';
            $this->showRecipientWarning = true;
            return;
        }

        $this->pendingRunDefinitionId = $definition->id;
        $this->pendingRunName = $definition->name;
        $this->pendingRunRecipients = $this->recipientLabel($definition, $dynamic, $this->capeCodUsers()->keyBy('id'));
        $this->showRunConfirm = true;
    }

    public function closeRunConfirm(): void
    {
        $this->showRunConfirm = false;
        $this->pendingRunDefinitionId = null;
        $this->pendingRunName = '';
        $this->pendingRunRecipients = '';
    }

    public function confirmReportRun(ScheduledOperationRegistry $registry, ScheduledRecipientRuleResolver $recipientResolver, ScheduledOperationDispatcher $dispatcher): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition((int) $this->pendingRunDefinitionId, $registry);
        $configured = collect($recipientResolver->resolve($definition));
        $dynamic = $registry->dynamicRecipientsFor($definition->task_key);
        $hasAutomaticTo = collect($dynamic)->contains(fn(array $recipient) => ($recipient['delivery'] ?? 'to') === 'to');
        $hasRecipients = $hasAutomaticTo
            || $configured->contains(fn(array $recipient) => in_array($recipient['type'] ?? '', ['to', 'cc'], true));
        if (!$hasRecipients) {
            $this->closeRunConfirm();
            $this->recipientWarning = 'The recipients changed after the confirmation opened. Configure a valid recipient before running this report.';
            $this->showRecipientWarning = true;
            return;
        }

        $run = $dispatcher->dispatchManual($definition->task_key, auth()->id());
        $this->closeRunConfirm();
        $this->logDefinitionId = $definition->id;
        $this->logRunId = $run->id;
        $this->notify('The report was added to the queue.');
    }

    public function toggleReportEnabled(int $definitionId, ScheduledOperationRegistry $registry, ScheduledRecipientRuleResolver $recipientResolver): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition($definitionId, $registry);

        if (!$definition->enabled) {
            $configured = collect($recipientResolver->resolve($definition));
            $hasAutomaticTo = collect($registry->dynamicRecipientsFor($definition->task_key))
                ->contains(fn(array $recipient) => ($recipient['delivery'] ?? 'to') === 'to');
            $hasRequiredRecipients = $hasAutomaticTo
                || $configured->contains(fn(array $recipient) => in_array($recipient['type'] ?? '', ['to', 'cc'], true));

            if (!$hasRequiredRecipients) {
                $this->recipientWarning = 'This report has no automatic To recipient. Configure at least one valid To or CC recipient before enabling it.';
                $this->showRecipientWarning = true;
                return;
            }
        }

        $before = $definition->toArray();
        $definition->update(['enabled' => !$definition->enabled, 'updated_by' => auth()->id()]);

        ScheduledOperationChangeLog::create([
            'scheduled_operation_definition_id' => $definition->id,
            'user_id' => auth()->id(),
            'action' => $definition->enabled ? 'client_enabled' : 'client_disabled',
            'before' => $before,
            'after' => $definition->fresh()->toArray(),
        ]);
    }

    public function closeRecipientWarning(): void
    {
        $this->showRecipientWarning = false;
        $this->recipientWarning = '';
    }

    public function editReport(int $definitionId, ScheduledOperationRegistry $registry): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition($definitionId, $registry);
        $schedule = $definition->schedule_data ?: [];

        $this->definitionId = $definition->id;
        $this->definitionUpdatedAt = $definition->updated_at?->format('Y-m-d H:i:s.u') ?? '';
        $this->reportName = $definition->name;
        $this->enabled = (bool) $definition->enabled;
        $this->scheduleType = $definition->schedule_type;
        $this->weekdays = collect($schedule['weekdays'] ?? [1])
            ->map(fn($day) => (int) $day)
            ->filter(fn(int $day) => $day >= 1 && $day <= 5)
            ->unique()
            ->values()
            ->all();
        $this->weekday = (int) ($schedule['weekday'] ?? $this->weekdays[0] ?? 1);
        $this->occurrence = (int) ($schedule['occurrence'] ?? 1);
        $this->day = (int) ($schedule['day'] ?? 1);
        $this->dynamicRecipients = $registry->dynamicRecipientsFor($definition->task_key);
        $this->recipientRules = $this->editableRecipientRules($definition);

        $this->resetValidation();
        $this->showEditor = true;
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->definitionId = null;
        $this->definitionUpdatedAt = '';
        $this->reportName = '';
        $this->recipientRules = [];
        $this->dynamicRecipients = [];
        $this->resetValidation();
    }

    public function addRecipientRule(): void
    {
        $this->recipientRules[] = [
            'delivery_type' => 'to',
            'source_type' => 'user',
            'source_value' => [],
            'label' => '',
        ];
    }

    public function removeRecipientRule(int $index): void
    {
        unset($this->recipientRules[$index]);
        $this->recipientRules = array_values($this->recipientRules);
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        if (preg_match('/^recipientRules\.(\d+)\.source_type$/', $property, $matches)) {
            $index = (int) $matches[1];
            if (isset($this->recipientRules[$index])) {
                $this->recipientRules[$index]['source_value'] =
                    $this->recipientRules[$index]['source_type'] === 'user' ? [] : '';
            }
        }
    }

    public function saveReport(ScheduledOperationRegistry $registry): void
    {
        $this->authoriseClientReports();
        $definition = $this->clientDefinition((int) $this->definitionId, $registry);

        // Dynamic roles are executable handler metadata, not browser input.
        // Reload them on every save so a crafted Livewire request cannot hide
        // a required Supervisor/company fallback or invent a new role.
        $this->dynamicRecipients = $registry->dynamicRecipientsFor($definition->task_key);

        $rules = [
            'enabled' => ['boolean'],
            'scheduleType' => ['required', Rule::in($this->clientScheduleTypes())],
            'recipientRules' => ['array'],
            'recipientRules.*.delivery_type' => ['required', Rule::in(['to', 'cc'])],
            'recipientRules.*.source_type' => ['required', Rule::in(['user', 'manual'])],
            'recipientRules.*.source_value' => ['nullable'],
            'recipientRules.*.label' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->scheduleType === 'weekly') {
            $rules['weekdays'] = ['required', 'array', 'min:1'];
            $rules['weekdays.*'] = ['integer', 'between:1,5'];
        }
        if (in_array($this->scheduleType, ['fortnightly', 'monthly_nth_weekday', 'monthly_last_weekday'], true)) {
            $rules['weekday'] = ['required', 'integer', 'between:1,7'];
        }
        if ($this->scheduleType === 'monthly_nth_weekday') {
            $rules['occurrence'] = ['required', 'integer', 'between:1,5'];
        }
        if (in_array($this->scheduleType, ['monthly_day', 'quarterly'], true)) {
            $rules['day'] = ['required', 'integer', 'between:1,28'];
        }

        $this->validate($rules);

        $availableUsers = $this->capeCodUsers()->keyBy('id');
        $normalisedRules = [];

        foreach ($this->recipientRules as $index => $rule) {
            $delivery = $rule['delivery_type'] ?? '';
            $source = $rule['source_type'] ?? '';
            $label = trim((string) ($rule['label'] ?? '')) ?: null;

            if ($source === 'manual') {
                $email = mb_strtolower(trim((string) ($rule['source_value'] ?? '')));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->addError("recipientRules.$index.source_value", 'Enter a valid email address.');
                    continue;
                }

                $normalisedRules[] = compact('delivery', 'source', 'label') + ['value' => $email];
                continue;
            }

            $userIds = collect(is_array($rule['source_value'] ?? null) ? $rule['source_value'] : [])
                ->filter(fn($id) => is_numeric($id))
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            if ($userIds->isEmpty() || $userIds->contains(fn(int $id) => !$availableUsers->has($id))) {
                $this->addError("recipientRules.$index.source_value", 'Select at least one active Cape Cod user with a valid email.');
                continue;
            }

            foreach ($userIds as $userId) {
                $normalisedRules[] = compact('delivery', 'source', 'label') + ['value' => (string) $userId];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $hasConfiguredRecipient = collect($normalisedRules)->contains(
            fn(array $rule) => in_array($rule['delivery'], ['to', 'cc'], true)
        );
        $hasAutomaticTo = collect($this->dynamicRecipients)
            ->contains(fn(array $recipient) => ($recipient['delivery'] ?? 'to') === 'to');

        if ($this->enabled && !$hasAutomaticTo && !$hasConfiguredRecipient) {
            $this->addError('recipientRules', 'An enabled report without an automatic To recipient requires at least one configured To or CC recipient.');
            return;
        }

        $currentStamp = $definition->updated_at?->format('Y-m-d H:i:s.u') ?? '';
        if ($currentStamp !== $this->definitionUpdatedAt) {
            $this->addError('report', 'This report was changed in Scheduled Operations after you opened it. Close and reopen it before saving.');
            return;
        }

        $before = $definition->toArray();
        $before['recipient_rules'] = $definition->recipientRules->toArray();
        $schedule = $this->buildSchedule($definition->schedule_data ?: []);
        $summary = $this->savedRecipientSummary($normalisedRules);

        DB::transaction(function () use ($definition, $normalisedRules, $schedule, $summary, $before) {
            $definition->update([
                'enabled' => $this->enabled,
                'schedule_type' => $this->scheduleType,
                'schedule_data' => $schedule,
                'recipient_summary' => $summary,
                'updated_by' => auth()->id(),
            ]);

            // Cape Cod controls To and CC. Preserve any administrator-only BCC
            // rules that may have been configured in Scheduled Operations.
            $definition->recipientRules()->whereIn('delivery_type', ['to', 'cc'])->delete();

            $sortOrder = 0;
            foreach ($normalisedRules as $rule) {
                $definition->recipientRules()->create([
                    'delivery_type' => $rule['delivery'],
                    'source_type' => $rule['source'],
                    'source_value' => $rule['value'],
                    'source_meta' => $rule['source'] === 'user' ? ['company_id' => 3] : null,
                    'label' => $rule['label'],
                    'enabled' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }

            $definition->refresh()->load('recipientRules');
            $after = $definition->toArray();
            $after['recipient_rules'] = $definition->recipientRules->toArray();

            ScheduledOperationChangeLog::create([
                'scheduled_operation_definition_id' => $definition->id,
                'user_id' => auth()->id(),
                'action' => 'client_updated',
                'before' => $before,
                'after' => $after,
            ]);
        });

        $this->closeEditor();
        $this->notify('Scheduled report settings updated.');
    }

    public function render(ScheduledOperationRegistry $registry)
    {
        $this->authoriseClientReports();
        $definitions = collect($registry->clientReports());
        $users = $this->capeCodUsers();
        $usersById = $users->keyBy('id');
        $models = ScheduledOperationDefinition::with('recipientRules')->whereIn('id', $definitions->pluck('definition_id')->filter())->get()->keyBy('id');

        $reports = $definitions->map(function (array $definition) use ($registry, $usersById, $models) {
            $model = $models->get($definition['definition_id'] ?? null);
            if (!$model) {
                return null;
            }

            $schedule = $model->schedule_data ?: [];
            $recipients = $this->recipientLabel($model, $registry->dynamicRecipientsFor($model->task_key), $usersById);

            return [
                'id' => $model->id,
                'name' => $model->name,
                'description' => $model->description,
                'enabled' => $model->enabled,
                'schedule' => $this->scheduleLabel($model->schedule_type, $schedule),
                'schedule_sort' => $this->scheduleSortKey($model->schedule_type, $schedule),
                'recipients' => $recipients,
            ];
        })->filter()
            ->when(trim($this->reportSearch) !== '', function ($reports) {
                $search = mb_strtolower(trim($this->reportSearch));

                return $reports->filter(fn(array $report) => str_contains(mb_strtolower(implode(' ', [
                    $report['name'],
                    $report['description'] ?? '',
                    $report['schedule'],
                    $report['recipients'],
                ])), $search));
            })
            ->sortBy(function (array $report) {
                $name = mb_strtolower($report['name']);
                return $this->reportSort === 'day' ? $report['schedule_sort'] . '-' . $name : $name;
            })->values();

        $logDefinition = $this->logDefinitionId ? $this->clientDefinition($this->logDefinitionId, $registry) : null;
        $logRuns = $logDefinition
            ? ScheduledRun::withCount(['messages as sent_messages_count' => fn($query) => $query->where('status', 'sent')])
                ->where('task_key', $logDefinition->task_key)->latest('scheduled_for')->latest('id')->limit(20)->get()
            : collect();
        $logRun = $this->logRunId && $logDefinition
            ? ScheduledRun::with(['messages.recipients', 'messages.archivedAttachments'])->where('task_key', $logDefinition->task_key)->find($this->logRunId)
            : null;
        $logMessage = $this->logMessageId && $logRun
            ? ScheduledReportMessage::with(['recipients', 'archivedAttachments'])->where('scheduled_run_id', $logRun->id)->find($this->logMessageId)
            : null;

        return view('livewire.settings.scheduled-reports', compact('reports', 'users', 'logDefinition', 'logRuns', 'logRun', 'logMessage'));
    }

    private function clientDefinition(int $definitionId, ScheduledOperationRegistry $registry): ScheduledOperationDefinition
    {
        $keys = collect($registry->clientReports())->pluck('key')->all();

        return ScheduledOperationDefinition::with('recipientRules')
            ->whereKey($definitionId)
            ->whereNull('archived_at')
            ->where('category', 'report')
            ->where('client_configurable', true)
            ->whereIn('task_key', $keys)
            ->firstOrFail();
    }

    private function editableRecipientRules(ScheduledOperationDefinition $definition): array
    {
        $rules = [];

        foreach (['to', 'cc'] as $delivery) {
            $userIds = $definition->recipientRules
                ->where('delivery_type', $delivery)
                ->where('source_type', 'user')
                ->where('enabled', true)
                ->pluck('source_value')
                ->map(fn($id) => (string) $id)
                ->unique()
                ->values()
                ->all();

            if ($userIds) {
                $rules[] = [
                    'delivery_type' => $delivery,
                    'source_type' => 'user',
                    'source_value' => $userIds,
                    'label' => '',
                ];
            }

            foreach ($definition->recipientRules
                ->where('delivery_type', $delivery)
                ->where('source_type', 'manual')
                ->where('enabled', true) as $rule) {
                $rules[] = [
                    'delivery_type' => $delivery,
                    'source_type' => 'manual',
                    'source_value' => $rule->source_value,
                    'label' => $rule->label ?: '',
                ];
            }
        }

        return $rules;
    }

    private function capeCodUsers()
    {
        return User::query()
            ->with('company')
            ->where('company_id', 3)
            ->where('status', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->filter(fn(User $user) => filter_var($user->email, FILTER_VALIDATE_EMAIL))
            ->values();
    }

    private function buildSchedule(array $existing): array
    {
        $time = $existing['time'] ?? '00:05';

        return match ($this->scheduleType) {
            'daily', 'weekdays' => ['type' => $this->scheduleType, 'time' => $time],
            'weekly' => [
                'type' => 'weekly',
                'weekdays' => collect($this->weekdays)
                    ->map(fn($day) => (int) $day)
                    ->filter(fn(int $day) => $day >= 1 && $day <= 5)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
                'time' => $time,
            ],
            'fortnightly' => [
                'type' => 'fortnightly',
                'weekday' => (int) $this->weekday,
                'anchor' => $this->fortnightlyAnchor($existing),
                'time' => $time,
            ],
            'monthly_nth_weekday' => [
                'type' => 'monthly_nth_weekday',
                'weekday' => (int) $this->weekday,
                'occurrence' => (int) $this->occurrence,
                'time' => $time,
            ],
            'monthly_last_weekday' => [
                'type' => 'monthly_last_weekday',
                'weekday' => (int) $this->weekday,
                'time' => $time,
            ],
            'monthly_day' => ['type' => 'monthly_day', 'day' => (int) $this->day, 'time' => $time],
            'quarterly' => [
                'type' => 'quarterly',
                'day' => (int) $this->day,
                'months' => $existing['months'] ?? [3, 6, 9, 12],
                'time' => $time,
            ],
        };
    }

    private function fortnightlyAnchor(array $existing): string
    {
        if (($existing['type'] ?? null) === 'fortnightly'
            && (int) ($existing['weekday'] ?? 0) === (int) $this->weekday
            && !empty($existing['anchor'])) {
            return $existing['anchor'];
        }

        $anchor = today();
        while ($anchor->dayOfWeekIso !== (int) $this->weekday) {
            $anchor->addDay();
        }

        return $anchor->format('Y-m-d');
    }

    private function scheduleLabel(string $type, array $schedule): string
    {
        $days = $this->days();
        $shortDays = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $weeklyDays = collect($schedule['weekdays'] ?? [1])->map(fn($day) => (int) $day)->filter(fn(int $day) => isset($days[$day]))->unique()->sort()->values();
        if ($weeklyDays->isEmpty()) $weeklyDays = collect([1]);
        $weeklyLabel = $weeklyDays->count() === 1 ? $days[$weeklyDays->first()] : $weeklyDays->map(fn(int $day) => $shortDays[$day])->join(', ');
        $time = $schedule['time'] ?? '00:05';
        $timeLabel = $time === '00:05' ? '' : ' at '.$this->displayTime($time);

        return match ($type) {
            'daily' => 'Daily'.$timeLabel,
            'weekdays' => 'Weekdays'.$timeLabel,
            'weekly' => $weeklyLabel.$timeLabel,
            'fortnightly' => 'Fortnightly — '.($days[(int) ($schedule['weekday'] ?? 1)] ?? 'Monday').$timeLabel,
            'monthly_nth_weekday' => 'Monthly — '.$this->ordinal((int) ($schedule['occurrence'] ?? 1)).' '.($days[(int) ($schedule['weekday'] ?? 1)] ?? 'Monday').$timeLabel,
            'monthly_last_weekday' => 'Monthly — last '.($days[(int) ($schedule['weekday'] ?? 1)] ?? 'Monday').$timeLabel,
            'monthly_day' => 'Monthly — '.$this->ordinal((int) ($schedule['day'] ?? 1)).$timeLabel,
            'quarterly' => 'Quarterly — '.$this->ordinal((int) ($schedule['day'] ?? 1)).$timeLabel,
            default => 'Configured by SafeWorksite',
        };
    }

    private function displayTime(string $time): string
    {
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $parts)) return $time;
        $hour = (int) $parts[1];
        return (($hour % 12) ?: 12).($parts[2] === '00' ? '' : ':'.$parts[2]).($hour >= 12 ? 'pm' : 'am');
    }

    /** Match the day-based order used on the Scheduled Operations dashboard. */
    private function scheduleSortKey(string $type, array $schedule): string
    {
        return match ($type) {
            'daily' => '0-00',
            'weekdays' => '0-01',
            'weekly' => sprintf('1-%02d', min(array_map('intval', $schedule['weekdays'] ?? [7]))),
            'fortnightly' => sprintf('3-%02d', (int) ($schedule['weekday'] ?? 7)),
            'monthly_nth_weekday' => sprintf('4-01-%02d-%02d', (int) ($schedule['weekday'] ?? 7), (int) ($schedule['occurrence'] ?? 1)),
            'monthly_last_weekday' => sprintf('4-02-%02d', (int) ($schedule['weekday'] ?? 7)),
            'monthly_day' => sprintf('4-03-%02d', (int) ($schedule['day'] ?? 1)),
            'quarterly' => sprintf('5-%02d', (int) ($schedule['day'] ?? 1)),
            default => '8-00',
        };
    }

    private function recipientLabel(ScheduledOperationDefinition $definition, array $dynamic, $usersById): string
    {
        $parts = collect($dynamic)->map(fn(array $recipient) => strtoupper($recipient['delivery']).': '.$recipient['label']);

        foreach (['to', 'cc'] as $delivery) {
            $labels = $definition->recipientRules
                ->where('delivery_type', $delivery)
                ->where('enabled', true)
                ->map(function ($rule) use ($usersById) {
                    if ($rule->source_type === 'user') {
                        return $usersById->get((int) $rule->source_value)?->fullname;
                    }
                    if ($rule->source_type === 'manual') {
                        return $rule->label ?: $rule->source_value;
                    }
                    return null;
                })->filter()->unique()->values();

            if ($labels->isNotEmpty()) {
                $parts->push(strtoupper($delivery).': '.$labels->join(', '));
            }
        }

        return $parts->join(' · ') ?: 'No recipients configured';
    }

    private function savedRecipientSummary(array $rules): string
    {
        $to = collect($rules)->where('delivery', 'to')->count();
        $cc = collect($rules)->where('delivery', 'cc')->count();
        $dynamic = collect($this->dynamicRecipients)->pluck('label')->filter()->join(', ');
        $parts = array_filter([
            $dynamic ? 'Dynamic: '.$dynamic : null,
            $to ? "$to configured To" : null,
            $cc ? "$cc configured CC" : null,
        ]);

        return implode('; ', $parts) ?: 'No recipients configured';
    }

    private function clientScheduleTypes(): array
    {
        return [
            'daily', 'weekdays', 'weekly', 'fortnightly',
            'monthly_nth_weekday', 'monthly_last_weekday', 'monthly_day', 'quarterly',
        ];
    }

    private function days(): array
    {
        return [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
    }

    private function ordinal(int $number): string
    {
        $suffix = in_array($number % 100, [11, 12, 13], true) ? 'th' : match ($number % 10) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
        };

        return $number.$suffix;
    }

    private function authoriseClientReports(): void
    {
        $user = auth()->user();
        $mode = config('scheduled_operations.report_settings_mode', 'legacy');
        $mode = in_array($mode, ['legacy', 'preview', 'live'], true) ? $mode : 'legacy';

        $allowed = $user && $user->isCC() && match ($mode) {
            'preview' => $user->hasRole2('web-admin'),
            'live' => $user->hasRole2('web-admin')
                || $user->hasAnyPermissionType('settings'),
            default => false,
        };

        abort_unless($allowed, 403);
    }
}
