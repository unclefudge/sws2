<?php

namespace App\Livewire\Manage\ScheduledOperations;

use App\Models\Misc\SettingsNotificationCategory;
use App\Models\Scheduled\ScheduledDispatchHeartbeat;
use App\Models\Scheduled\ScheduledOperationChangeLog;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledOperationDispatcher;
use App\Scheduled\ScheduledOperationRegistry;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Dashboard extends Component
{
    public string $activeTab = 'runs';
    public string $statusFilter = '';
    public string $categoryFilter = '';
    public string $search = '';

    public ?int $selectedRunId = null;
    public ?string $pendingTaskKey = null;
    public ?int $pendingRetryRunId = null;
    public bool $showRunConfirm = false;
    public bool $showRetryConfirm = false;
    public bool $showSettings = false;
    public bool $showAddOperation = false;

    // Operation editor. Every value below is persisted to the new definition
    // tables; the registry only supplies a safe executable handler.
    public ?int $settingDefinitionId = null;
    public string $settingTaskKey = '';
    public string $settingName = '';
    public string $settingCategory = 'report';
    public string $settingDescription = '';
    public string $settingRecipientSummary = '';
    public bool $settingEnabled = true;
    public string $settingScheduleType = 'weekly';
    public string $settingTime = '00:05';
    public ?int $settingMinute = 5;
    public array $settingWeekdays = [1];
    public ?int $settingWeekday = 1;
    public ?int $settingOccurrence = 1;
    public ?int $settingDay = 1;
    public array $settingMonths = [3, 6, 9, 12];
    public string $settingAnchor = '';
    public ?int $settingTries = 3;
    public ?int $settingTimeout = 240;
    public string $settingRecipientMode = 'legacy';
    public array $recipientRules = [];

    public function mount(ScheduledOperationRegistry $registry): void
    {
        $this->authoriseAdmin();

        // The first dashboard visit after deployment imports the v1 catalogue.
        // Discovered custom handlers remain in Add operation until deliberately
        // installed (or imported disabled with scheduled:sync during deploy).
        if (Schema::hasTable('scheduled_operation_definitions')) {
            $registry->syncDefinitions(false, auth()->id(), false);
        }
    }

    public function showRun(int $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function closeModals(): void
    {
        $this->selectedRunId = null;
        $this->pendingTaskKey = null;
        $this->pendingRetryRunId = null;
        $this->showRunConfirm = false;
        $this->showRetryConfirm = false;
        $this->showSettings = false;
        $this->showAddOperation = false;
        $this->resetValidation();
    }

    public function requestRun(string $taskKey): void
    {
        $this->pendingTaskKey = $taskKey;
        $this->pendingRetryRunId = null;
        $this->selectedRunId = null;
        $this->showRetryConfirm = false;
        $this->showRunConfirm = true;
    }

    public function requestRunAgain(int $runId): void
    {
        $run = ScheduledRun::findOrFail($runId);

        // Re-running a completed operation is a fresh manual execution. It is
        // not linked to the previous successful run as a failure retry.
        $this->requestRun($run->task_key);
    }

    public function confirmRun(ScheduledOperationDispatcher $dispatcher): void
    {
        $this->authoriseAdmin();
        $run = $dispatcher->dispatchManual($this->pendingTaskKey, auth()->id());
        $this->closeModals();
        $this->selectedRunId = $run->id;
        session()->flash('scheduled-success', 'The operation was added to the queue.');
    }

    public function requestRetry(int $runId): void
    {
        $run = ScheduledRun::findOrFail($runId);

        // Only failed/missed executions have something to retry. This guard
        // also protects against stale browser markup calling the old action for
        // a successful run after this dashboard update is deployed.
        if (!in_array($run->status, ['failed', 'missed'], true)) {
            $this->requestRun($run->task_key);

            return;
        }

        $this->pendingRetryRunId = $run->id;
        $this->pendingTaskKey = $run->task_key;
        $this->showRunConfirm = false;
        $this->showRetryConfirm = true;
        $this->selectedRunId = null;
    }

    public function confirmRetry(ScheduledOperationDispatcher $dispatcher): void
    {
        $this->authoriseAdmin();
        $run = $dispatcher->dispatchManual($this->pendingTaskKey, auth()->id(), $this->pendingRetryRunId);
        $this->closeModals();
        $this->selectedRunId = $run->id;
        session()->flash('scheduled-success', 'The retry was added to the queue.');
    }

    public function openAddOperation(): void
    {
        $this->showAddOperation = true;
    }

    public function installHandler(string $handlerKey, ScheduledOperationRegistry $registry): void
    {
        $this->authoriseAdmin();
        $definition = $registry->installHandler($handlerKey, auth()->id());
        $this->showAddOperation = false;
        $this->editSettings($definition->task_key, $registry);
        session()->flash('scheduled-success', 'The operation was added disabled. Review its settings before enabling it.');
    }

    public function editSettings(string $taskKey, ScheduledOperationRegistry $registry): void
    {
        $definition = ScheduledOperationDefinition::with('recipientRules')->where('task_key', $taskKey)->first();
        if (!$definition) {
            // A missing legacy definition is safe to repair here, but do not
            // silently install newly discovered custom handlers from a click.
            $registry->syncDefinitions(false, auth()->id(), false);
            $definition = ScheduledOperationDefinition::with('recipientRules')->where('task_key', $taskKey)->firstOrFail();
        }

        $schedule = $definition->schedule_data ?: [];
        $this->settingDefinitionId = $definition->id;
        $this->settingTaskKey = $definition->task_key;
        $this->settingName = $definition->name;
        $this->settingCategory = $definition->category;
        $this->settingDescription = $definition->description ?: '';
        $this->settingRecipientSummary = $definition->recipient_summary ?: '';
        $this->settingEnabled = $definition->enabled;
        $this->settingScheduleType = $definition->schedule_type;
        $this->settingTime = $schedule['time'] ?? '00:05';
        $this->settingMinute = (int) ($schedule['minute'] ?? 5);
        $this->settingWeekdays = array_map('intval', $schedule['weekdays'] ?? [1]);
        $this->settingWeekday = (int) ($schedule['weekday'] ?? $this->settingWeekdays[0] ?? 1);
        $this->settingOccurrence = (int) ($schedule['occurrence'] ?? 1);
        $this->settingDay = (int) ($schedule['day'] ?? 1);
        $this->settingMonths = array_map('intval', $schedule['months'] ?? [3, 6, 9, 12]);
        $this->settingAnchor = $schedule['anchor'] ?? today()->format('Y-m-d');
        $this->settingTries = $definition->tries;
        $this->settingTimeout = $definition->timeout_seconds;
        $this->settingRecipientMode = $definition->recipient_mode;
        $this->recipientRules = $definition->recipientRules->map(fn($rule) => [
            'delivery_type' => $rule->delivery_type,
            'source_type' => $rule->source_type,
            'source_value' => (string) $rule->source_value,
            'label' => $rule->label ?: '',
            'enabled' => $rule->enabled,
        ])->values()->all();
        $this->showSettings = true;
    }

    public function addRecipientRule(): void
    {
        $this->recipientRules[] = [
            'delivery_type' => 'to',
            'source_type' => 'user',
            'source_value' => '',
            'label' => '',
            'enabled' => true,
        ];
    }

    public function removeRecipientRule(int $index): void
    {
        unset($this->recipientRules[$index]);
        $this->recipientRules = array_values($this->recipientRules);
    }

    public function saveSettings(): void
    {
        $this->authoriseAdmin();

        $rules = [
            'settingName' => ['required', 'string', 'max:255'],
            'settingCategory' => ['required', 'string', 'max:40'],
            'settingDescription' => ['nullable', 'string', 'max:5000'],
            'settingRecipientSummary' => ['nullable', 'string', 'max:2000'],
            'settingEnabled' => ['boolean'],
            'settingScheduleType' => ['required', Rule::in($this->scheduleTypes())],
            'settingTries' => ['required', 'integer', 'between:1,10'],
            // Forge currently runs queue:work with --timeout=300 and the
            // database retry_after is 360. Keep the editable timeout inside
            // that safe window so a long job cannot be claimed twice.
            'settingTimeout' => ['required', 'integer', 'between:30,300'],
            'settingRecipientMode' => ['required', Rule::in(['legacy', 'append', 'managed'])],
            'recipientRules.*.delivery_type' => ['required', Rule::in(['to', 'cc', 'bcc'])],
            'recipientRules.*.source_type' => ['required', Rule::in(['user', 'manual', 'notification_group'])],
            'recipientRules.*.source_value' => ['required'],
            'recipientRules.*.label' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->settingScheduleType === 'hourly') {
            $rules['settingMinute'] = ['required', 'integer', 'between:0,59'];
        } else {
            $rules['settingTime'] = ['required', 'date_format:H:i'];
        }
        if ($this->settingScheduleType === 'weekly') {
            $rules['settingWeekdays'] = ['required', 'array', 'min:1'];
            $rules['settingWeekdays.*'] = ['integer', 'between:1,7'];
        }
        if (in_array($this->settingScheduleType, ['fortnightly', 'monthly_nth_weekday', 'monthly_last_weekday'], true)) {
            $rules['settingWeekday'] = ['required', 'integer', 'between:1,7'];
        }
        if ($this->settingScheduleType === 'fortnightly') {
            $rules['settingAnchor'] = ['required', 'date_format:Y-m-d'];
        }
        if ($this->settingScheduleType === 'monthly_nth_weekday') {
            $rules['settingOccurrence'] = ['required', 'integer', 'between:1,5'];
        }
        if (in_array($this->settingScheduleType, ['monthly_day', 'quarterly'], true)) {
            $rules['settingDay'] = ['required', 'integer', 'between:1,28'];
        }
        if ($this->settingScheduleType === 'quarterly') {
            $rules['settingMonths'] = ['required', 'array', 'min:1'];
            $rules['settingMonths.*'] = ['integer', 'between:1,12'];
        }

        $this->validate($rules);

        foreach ($this->recipientRules as $index => $rule) {
            $value = trim((string) ($rule['source_value'] ?? ''));
            if ($rule['source_type'] === 'manual' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError("recipientRules.$index.source_value", 'Enter a valid email address.');
            } elseif ($rule['source_type'] === 'user' && !User::query()
                ->whereKey((int) $value)
                ->where('company_id', auth()->user()->company_id)
                ->whereNotNull('email')->exists()) {
                $this->addError("recipientRules.$index.source_value", 'Select a valid SafeWorksite user.');
            } elseif ($rule['source_type'] === 'notification_group' && !SettingsNotificationCategory::query()
                ->whereKey((int) $value)
                ->where('status', 1)
                ->where(fn($query) => $query->where('company_id', auth()->user()->company_id)->orWhereNull('company_id'))
                ->exists()) {
                $this->addError("recipientRules.$index.source_value", 'Select a valid notification group.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }
        if ($this->settingRecipientMode === 'managed' && !collect($this->recipientRules)->contains('delivery_type', 'to')) {
            $this->addError('recipientRules', 'Managed recipients require at least one To rule.');
            return;
        }

        $definition = ScheduledOperationDefinition::with('recipientRules')->findOrFail($this->settingDefinitionId);
        $before = $definition->toArray();
        $before['recipient_rules'] = $definition->recipientRules->toArray();

        DB::transaction(function () use ($definition, $before) {
            $definition->update([
                'name' => trim($this->settingName),
                'category' => trim($this->settingCategory),
                'description' => trim($this->settingDescription),
                'recipient_summary' => trim($this->settingRecipientSummary),
                'enabled' => $this->settingEnabled,
                'schedule_type' => $this->settingScheduleType,
                'schedule_data' => $this->buildSchedule(),
                'recipient_mode' => $this->settingRecipientMode,
                'tries' => $this->settingTries,
                'timeout_seconds' => $this->settingTimeout,
                'updated_by' => auth()->id(),
            ]);

            $definition->recipientRules()->delete();
            foreach (array_values($this->recipientRules) as $index => $rule) {
                $definition->recipientRules()->create([
                    'delivery_type' => $rule['delivery_type'],
                    'source_type' => $rule['source_type'],
                    'source_value' => trim((string) $rule['source_value']),
                    // Store the tenant used when the rule was configured so a
                    // tampered user/group id cannot resolve outside this company.
                    'source_meta' => in_array($rule['source_type'], ['user', 'notification_group'], true)
                        ? ['company_id' => auth()->user()->company_id]
                        : null,
                    'label' => trim((string) ($rule['label'] ?? '')) ?: null,
                    'enabled' => (bool) ($rule['enabled'] ?? true),
                    'sort_order' => $index,
                ]);
            }

            $definition->refresh()->load('recipientRules');
            $after = $definition->toArray();
            $after['recipient_rules'] = $definition->recipientRules->toArray();
            ScheduledOperationChangeLog::create([
                'scheduled_operation_definition_id' => $definition->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'before' => $before,
                'after' => $after,
            ]);
        });

        $this->closeModals();
        session()->flash('scheduled-success', 'Operation schedule and recipient rules updated.');
    }

    public function resetSettings(ScheduledOperationRegistry $registry): void
    {
        $this->authoriseAdmin();
        // Resolve the default through the registry so a converted handler's
        // scheduledOperation() metadata wins over its old CronController entry.
        $default = $registry->defaultFor($this->settingTaskKey);
        abort_unless($default, 422, 'This operation has no code defaults to restore.');
        $definition = ScheduledOperationDefinition::with('recipientRules')->findOrFail($this->settingDefinitionId);
        $before = $definition->toArray();

        DB::transaction(function () use ($definition, $default, $before) {
            $definition->update([
                'name' => $default['name'],
                'category' => $default['category'],
                'description' => $default['description'],
                'recipient_summary' => $default['recipients'],
                'enabled' => true,
                'schedule_type' => $default['schedule']['type'],
                'schedule_data' => $default['schedule'],
                'recipient_mode' => 'legacy',
                'tries' => 3,
                'timeout_seconds' => 240,
                'updated_by' => auth()->id(),
            ]);
            $definition->recipientRules()->delete();
            ScheduledOperationChangeLog::create([
                'scheduled_operation_definition_id' => $definition->id,
                'user_id' => auth()->id(),
                'action' => 'restored_defaults',
                'before' => $before,
                'after' => $definition->fresh()->toArray(),
            ]);
        });

        $this->closeModals();
        session()->flash('scheduled-success', 'The handler defaults and legacy recipient mode were restored.');
    }

    public function render(ScheduledOperationRegistry $registry)
    {
        $this->authoriseAdmin();

        $query = ScheduledRun::with(['messages.recipients', 'group'])->latest('scheduled_for')->latest('id');
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }
        if ($this->categoryFilter !== '') {
            $query->where('category', $this->categoryFilter);
        }
        if ($this->search !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(fn($sub) => $sub->where('task_name', 'like', $search)->orWhere('task_key', 'like', $search));
        }

        $runs = $query->limit(100)->get();
        $selectedRun = $this->selectedRunId
            ? ScheduledRun::with(['messages.recipients', 'group', 'retryOf'])->find($this->selectedRunId)
            : null;
        $definitions = collect($registry->allEffective())
            ->map(fn($definition) => array_merge($definition, ['schedule_label' => $registry->scheduleLabel($definition)]))
            ->groupBy('category');
        $today = ScheduledRun::whereDate('scheduled_for', today())->get();
        $mode = config('scheduled_operations.mode');
        $heartbeat = in_array($mode, ['shadow', 'live'], true)
            ? ScheduledDispatchHeartbeat::where('mode', $mode)->first()
            : null;

        return view('livewire.manage.scheduled-operations.dashboard', [
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'definitions' => $definitions,
            'availableHandlers' => $registry->availableHandlers(),
            'users' => User::query()->where('company_id', auth()->user()->company_id)
                ->whereNotNull('email')->orderBy('firstname')->orderBy('lastname')->get(),
            'notificationGroups' => SettingsNotificationCategory::query()
                ->where('status', 1)
                ->where(fn($query) => $query->where('company_id', auth()->user()->company_id)->orWhereNull('company_id'))
                ->orderBy('sort_order')->orderBy('name')->get(),
            'changeLogs' => $this->settingDefinitionId
                ? ScheduledOperationChangeLog::with('user')->where('scheduled_operation_definition_id', $this->settingDefinitionId)->latest()->limit(8)->get()
                : collect(),
            'mode' => $mode,
            'heartbeat' => $heartbeat,
            'stats' => [
                'total' => $today->count(),
                'successful' => $today->where('status', 'successful')->count(),
                'failed' => $today->whereIn('status', ['failed', 'missed'])->count(),
                'running' => $today->whereIn('status', ['queued', 'running'])->count(),
            ],
            'pendingDefinition' => $this->pendingTaskKey ? $registry->find($this->pendingTaskKey) : null,
            'settingsDefinition' => $this->settingTaskKey ? $registry->find($this->settingTaskKey) : null,
            'hasLegacyDefault' => $this->settingTaskKey
                ? collect($registry->defaults())->contains('key', $this->settingTaskKey)
                : false,
        ]);
    }

    private function buildSchedule(): array
    {
        $schedule = ['type' => $this->settingScheduleType];
        if ($this->settingScheduleType === 'hourly') {
            $schedule['minute'] = $this->settingMinute;
            return $schedule;
        }

        $schedule['time'] = $this->settingTime;
        if ($this->settingScheduleType === 'weekly') {
            $schedule['weekdays'] = collect($this->settingWeekdays)->map(fn($day) => (int) $day)->sort()->values()->all();
        } elseif (in_array($this->settingScheduleType, ['fortnightly', 'monthly_nth_weekday', 'monthly_last_weekday'], true)) {
            $schedule['weekday'] = $this->settingWeekday;
        }
        if ($this->settingScheduleType === 'fortnightly') {
            $schedule['anchor'] = $this->settingAnchor;
        } elseif ($this->settingScheduleType === 'monthly_nth_weekday') {
            $schedule['occurrence'] = $this->settingOccurrence;
        } elseif (in_array($this->settingScheduleType, ['monthly_day', 'quarterly'], true)) {
            $schedule['day'] = $this->settingDay;
        }
        if ($this->settingScheduleType === 'quarterly') {
            $schedule['months'] = collect($this->settingMonths)->map(fn($month) => (int) $month)->sort()->values()->all();
        }

        return $schedule;
    }

    private function scheduleTypes(): array
    {
        return ['hourly', 'daily', 'weekdays', 'weekly', 'fortnightly', 'monthly_nth_weekday', 'monthly_last_weekday', 'monthly_day', 'quarterly'];
    }

    private function authoriseAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole2('web-admin'), 403);
    }
}
