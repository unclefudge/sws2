<?php

namespace App\Livewire\Manage\ScheduledOperations;

use App\Models\Misc\SettingsNotificationCategory;
use App\Models\Scheduled\ScheduledDispatchHeartbeat;
use App\Models\Scheduled\ScheduledOperationCategory;
use App\Models\Scheduled\ScheduledOperationChangeLog;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledOperationDispatcher;
use App\Scheduled\ScheduledOperationRegistry;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $activeTab = 'runs';
    public string $statusFilter = '';
    public string $categoryFilter = 'except_hourly';
    public string $dateFilter = '';
    public string $search = '';
    public string $scheduleSearch = '';
    public bool $includeArchived = false;

    public ?int $selectedRunId = null;
    public ?string $pendingTaskKey = null;
    public ?int $pendingRetryRunId = null;
    public bool $showRunConfirm = false;
    public bool $showRetryConfirm = false;
    public bool $showSettings = false;
    public bool $showAddOperation = false;
    public bool $showAdvancedSettings = false;
    public bool $showCategoryManager = false;
    public bool $showArchiveConfirm = false;
    public bool $returnToSettingsAfterCategories = false;
    public ?int $pendingArchiveDefinitionId = null;
    public string $pendingArchiveName = '';
    public array $collapsedScheduleCategories = [];

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

    // Categories have stable slugs for operation records, while their friendly
    // names and display order can be maintained from the dashboard.
    public array $categoryRows = [];
    public string $newCategoryName = '';

    public function mount(ScheduledOperationRegistry $registry): void
    {
        $this->authoriseAdmin();
        $this->dateFilter = today()->format('Y-m-d');

        // The first dashboard visit after deployment imports the v1 catalogue.
        // Discovered custom handlers remain in Add operation until deliberately
        // installed (or imported disabled with scheduled:sync during deploy).
        if (Schema::hasTable('scheduled_operation_definitions')) {
            $registry->syncDefinitions(false, auth()->id(), false);
            $this->syncOperationCategories();
            if (Schema::hasTable('scheduled_operation_categories'))
                $this->collapsedScheduleCategories = ScheduledOperationCategory::orderBy('sort_order')->orderBy('name')->pluck('slug')->all();
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'categoryFilter', 'dateFilter'], true)) {
            $this->resetPage('runsPage');
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
        $this->showAdvancedSettings = false;
        $this->showCategoryManager = false;
        $this->showArchiveConfirm = false;
        $this->returnToSettingsAfterCategories = false;
        $this->pendingArchiveDefinitionId = null;
        $this->pendingArchiveName = '';
        $this->resetValidation();
    }

    public function requestRun(string $taskKey): void
    {
        if (!ScheduledOperationDefinition::query()->where('task_key', $taskKey)->whereNull('archived_at')->exists()) {
            session()->flash('scheduled-error', 'That operation is archived and cannot be run. Restore it first.');
            return;
        }

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

        if (!ScheduledOperationDefinition::query()->where('task_key', $run->task_key)->whereNull('archived_at')->exists()) {
            $this->closeModals();
            session()->flash('scheduled-error', 'That operation is archived and cannot be retried. Restore it first.');
            return;
        }

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
        $this->ensureOperationCategory($definition->category);
        $this->showAddOperation = false;
        $this->editSettings($definition->task_key, $registry);
        session()->flash('scheduled-success', 'The operation was added disabled. Review its settings before enabling it.');
    }

    public function requestArchive(): void
    {
        $this->authoriseAdmin();
        $definition = ScheduledOperationDefinition::query()->whereKey($this->settingDefinitionId)->whereNull('archived_at')->firstOrFail();
        $this->pendingArchiveDefinitionId = $definition->id;
        $this->pendingArchiveName = $definition->name;
        $this->showSettings = false;
        $this->showArchiveConfirm = true;
    }

    public function confirmArchive(): void
    {
        $this->authoriseAdmin();
        $definition = ScheduledOperationDefinition::with('recipientRules')->whereKey($this->pendingArchiveDefinitionId)->whereNull('archived_at')->firstOrFail();
        $before = $definition->toArray();
        $before['recipient_rules'] = $definition->recipientRules->toArray();

        DB::transaction(function () use ($definition, $before) {
            $definition->update(['enabled' => false, 'archived_at' => now(), 'archived_by' => auth()->id(), 'updated_by' => auth()->id()]);
            $fresh = $definition->fresh()->load('recipientRules');
            $after = $fresh->toArray();
            $after['recipient_rules'] = $fresh->recipientRules->toArray();
            ScheduledOperationChangeLog::create([
                'scheduled_operation_definition_id' => $definition->id,
                'user_id' => auth()->id(),
                'action' => 'archived',
                'before' => $before,
                'after' => $after,
            ]);
        });

        $name = $definition->name;
        $this->closeModals();
        session()->flash('scheduled-success', "{$name} was archived. Its settings and run history were preserved.");
    }

    public function restoreOperation(int $definitionId): void
    {
        $this->authoriseAdmin();
        $definition = ScheduledOperationDefinition::with('recipientRules')->whereKey($definitionId)->whereNotNull('archived_at')->firstOrFail();
        $before = $definition->toArray();
        $before['recipient_rules'] = $definition->recipientRules->toArray();

        DB::transaction(function () use ($definition, $before) {
            $definition->update(['enabled' => false, 'archived_at' => null, 'archived_by' => null, 'updated_by' => auth()->id()]);
            $fresh = $definition->fresh()->load('recipientRules');
            $after = $fresh->toArray();
            $after['recipient_rules'] = $fresh->recipientRules->toArray();
            ScheduledOperationChangeLog::create([
                'scheduled_operation_definition_id' => $definition->id,
                'user_id' => auth()->id(),
                'action' => 'restored_from_archive',
                'before' => $before,
                'after' => $after,
            ]);
        });

        session()->flash('scheduled-success', "{$definition->name} was restored in the disabled state. Review it before enabling.");
    }

    public function editSettings(string $taskKey, ScheduledOperationRegistry $registry): void
    {
        $definition = ScheduledOperationDefinition::with('recipientRules')->where('task_key', $taskKey)->whereNull('archived_at')->first();
        if (!$definition) {
            // A missing legacy definition is safe to repair here, but do not
            // silently install newly discovered custom handlers from a click.
            $registry->syncDefinitions(false, auth()->id(), false);
            $definition = ScheduledOperationDefinition::with('recipientRules')->where('task_key', $taskKey)->whereNull('archived_at')->firstOrFail();
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
        $this->settingWeekdays = collect($schedule['weekdays'] ?? [1])
            ->map(fn($day) => (int) $day)
            ->filter(fn(int $day) => $day >= 1 && $day <= 5)
            ->unique()
            ->values()
            ->all();
        $this->settingWeekday = (int) ($schedule['weekday'] ?? $this->settingWeekdays[0] ?? 1);
        $this->settingOccurrence = (int) ($schedule['occurrence'] ?? 1);
        $this->settingDay = (int) ($schedule['day'] ?? 1);
        $this->settingMonths = array_map('intval', $schedule['months'] ?? [3, 6, 9, 12]);
        $this->settingAnchor = $schedule['anchor'] ?? today()->format('Y-m-d');
        $this->settingTries = $definition->tries;
        $this->settingTimeout = $definition->timeout_seconds;
        $this->settingRecipientMode = $definition->recipient_mode;

        // Existing user rows were historically stored one user at a time.
        // Combine compatible rows for editing so migrated reports immediately
        // benefit from the new multi-user control without changing storage.
        $editableRules = [];
        foreach ($definition->recipientRules as $rule) {
            if ($rule->source_type === 'user') {
                $groupKey = implode('|', [
                    $rule->delivery_type,
                    $rule->label ?: '',
                    (int) $rule->enabled,
                ]);
                if (isset($editableRules[$groupKey])) {
                    $editableRules[$groupKey]['source_value'][] = (string) $rule->source_value;
                    continue;
                }
                $editableRules[$groupKey] = [
                    'delivery_type' => $rule->delivery_type,
                    'source_type' => 'user',
                    'source_value' => [(string) $rule->source_value],
                    'label' => $rule->label ?: '',
                    'enabled' => $rule->enabled,
                ];
                continue;
            }

            // A manual address or notification group stays as its own rule.
            $editableRules[] = [
                'delivery_type' => $rule->delivery_type,
                'source_type' => $rule->source_type,
                'source_value' => (string) $rule->source_value,
                'label' => $rule->label ?: '',
                'enabled' => $rule->enabled,
            ];
        }
        $this->recipientRules = array_values($editableRules);
        $this->showAdvancedSettings = false;
        $this->showSettings = true;
    }

    public function addRecipientRule(): void
    {
        $this->recipientRules[] = [
            'delivery_type' => 'to',
            'source_type' => 'user',
            'source_value' => [],
            'label' => '',
            'enabled' => true,
        ];
    }

    public function removeRecipientRule(int $index): void
    {
        unset($this->recipientRules[$index]);
        $this->recipientRules = array_values($this->recipientRules);
    }

    public function openCategoryManager(): void
    {
        $this->authoriseAdmin();
        $this->returnToSettingsAfterCategories = $this->showSettings;
        $this->showSettings = false;
        $this->showCategoryManager = true;
        $this->loadCategoryRows();
        $this->resetValidation();
    }

    public function closeCategoryManager(): void
    {
        $this->showCategoryManager = false;
        $this->newCategoryName = '';
        $this->resetValidation();

        // Return to the operation being edited instead of making the user find
        // it again after managing the category list.
        if ($this->returnToSettingsAfterCategories) {
            $this->showSettings = true;
        }
        $this->returnToSettingsAfterCategories = false;
    }

    public function addCategory(): void
    {
        $this->authoriseAdmin();
        $this->validate([
            'newCategoryName' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($this->newCategoryName);
        $slug = Str::slug($name, '_');
        if ($slug === '' || strlen($slug) > 40) {
            $this->addError('newCategoryName', 'Use a shorter category name containing letters or numbers.');
            return;
        }
        if (ScheduledOperationCategory::where('slug', $slug)->exists()) {
            $this->addError('newCategoryName', 'That category already exists.');
            return;
        }

        ScheduledOperationCategory::create([
            'slug' => $slug,
            'name' => $name,
            'sort_order' => ((int) ScheduledOperationCategory::max('sort_order')) + 10,
            'enabled' => true,
        ]);
        $this->newCategoryName = '';
        $this->loadCategoryRows();
    }

    public function moveCategory(int $index, int $direction): void
    {
        $rows = array_values($this->categoryRows);
        $target = $index + $direction;
        if (!isset($rows[$index], $rows[$target])) {
            return;
        }

        [$rows[$index], $rows[$target]] = [$rows[$target], $rows[$index]];
        $this->categoryRows = collect($rows)->mapWithKeys(fn(array $row) => ['category_'.$row['id'] => $row])->all();
    }

    public function reorderCategories(array $orderedIds): void
    {
        // Drag-and-drop only changes the in-memory editor order. The database
        // remains untouched until the user deliberately saves the modal.
        $rowsById = collect($this->categoryRows)->keyBy(fn(array $row) => (int) $row['id']);
        $ids = collect($orderedIds)
            ->filter(fn($id) => is_numeric($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->count() !== $rowsById->count() || $ids->contains(fn(int $id) => !$rowsById->has($id))) {
            return;
        }

        $this->categoryRows = $ids->mapWithKeys(fn(int $id) => ['category_'.$id => $rowsById->get($id)])->all();
    }

    public function toggleCategoryEnabled(string $rowKey): void
    {
        if (!isset($this->categoryRows[$rowKey])) {
            return;
        }

        $this->categoryRows[$rowKey]['enabled'] = !(bool) $this->categoryRows[$rowKey]['enabled'];
    }

    public function toggleScheduleCategory(string $category): void
    {
        // Keep collapse state in Livewire so it survives polling and operation
        // edits without changing any category or operation records.
        if (in_array($category, $this->collapsedScheduleCategories, true)) {
            $this->collapsedScheduleCategories = array_values(array_diff($this->collapsedScheduleCategories, [$category]));

            return;
        }

        $this->collapsedScheduleCategories[] = $category;
    }

    public function saveCategories(): void
    {
        $this->authoriseAdmin();
        $this->validate([
            'categoryRows' => ['required', 'array'],
            'categoryRows.*.id' => ['required', 'integer'],
            'categoryRows.*.name' => ['required', 'string', 'max:255'],
            'categoryRows.*.enabled' => ['boolean'],
        ]);

        DB::transaction(function () {
            foreach (array_values($this->categoryRows) as $index => $row) {
                ScheduledOperationCategory::whereKey($row['id'])->update([
                    'name' => trim($row['name']),
                    'sort_order' => ($index + 1) * 10,
                    'enabled' => (bool) $row['enabled'],
                ]);
            }
        });

        $this->closeCategoryManager();
        session()->flash('scheduled-success', 'Operation categories updated.');
    }

    public function updatedRecipientRules($value, string $key): void
    {
        if (!str_ends_with($key, '.source_type')) {
            return;
        }

        $index = (int) Str::before($key, '.');
        if (!isset($this->recipientRules[$index])) {
            return;
        }

        // Switching source type must also switch the bound value shape. A user
        // selector stores an array; email and notification-group rules store a
        // single value.
        $this->recipientRules[$index]['source_value'] = $value === 'user' ? [] : '';
        $this->resetValidation("recipientRules.$index.source_value");
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
            // The value is checked below because User is an array (multi-select)
            // while Email address and Notification group are scalar values.
            'recipientRules.*.source_value' => ['nullable'],
            'recipientRules.*.label' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->settingScheduleType === 'hourly') {
            $rules['settingMinute'] = ['required', 'integer', 'between:0,59'];
        } else {
            $rules['settingTime'] = ['required', 'date_format:H:i'];
        }
        if ($this->settingScheduleType === 'weekly') {
            $rules['settingWeekdays'] = ['required', 'array', 'min:1'];
            $rules['settingWeekdays.*'] = ['integer', 'between:1,5'];
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

        if (!ScheduledOperationCategory::where('slug', $this->settingCategory)->exists()) {
            $this->addError('settingCategory', 'Select a valid operation category.');
        }

        foreach ($this->recipientRules as $index => $rule) {
            $sourceType = $rule['source_type'] ?? '';
            $value = $rule['source_value'] ?? '';

            if ($sourceType === 'manual' && !filter_var(trim((string) $value), FILTER_VALIDATE_EMAIL)) {
                $this->addError("recipientRules.$index.source_value", 'Enter a valid email address.');
            } elseif ($sourceType === 'user') {
                $userIds = collect(is_array($value) ? $value : [$value])
                    ->filter(fn($id) => is_numeric($id) && (int) $id > 0)
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values();
                $validUsers = User::query()
                    ->whereIn('id', $userIds)
                    ->where('company_id', auth()->user()->company_id)
                    ->where('status', 1)
                    ->get()
                    ->filter(fn(User $user) => filter_var($user->email, FILTER_VALIDATE_EMAIL))
                    ->count();
                if ($userIds->isEmpty() || $validUsers !== $userIds->count()) {
                    $this->addError("recipientRules.$index.source_value", 'Select at least one active user with an email address.');
                }
            } elseif ($sourceType === 'notification_group' && !SettingsNotificationCategory::query()
                ->whereKey((int) trim((string) $value))
                ->where('status', 1)
                ->where(fn($query) => $query->where('company_id', auth()->user()->company_id)->orWhereNull('company_id'))
                ->exists()) {
                $this->addError("recipientRules.$index.source_value", 'Select a valid notification group.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }
        $hasManagedRecipient = collect($this->recipientRules)->contains(fn(array $rule) =>
            ($rule['enabled'] ?? true) && in_array($rule['delivery_type'] ?? '', ['to', 'cc'], true)
        );
        if ($this->settingRecipientMode === 'managed' && !$hasManagedRecipient) {
            $this->addError('recipientRules', 'Managed recipients require at least one enabled To or CC rule.');
            return;
        }

        $definition = ScheduledOperationDefinition::with('recipientRules')->whereNull('archived_at')->findOrFail($this->settingDefinitionId);
        $before = $definition->toArray();
        $before['recipient_rules'] = $definition->recipientRules->toArray();

        DB::transaction(function () use ($definition, $before) {
            $definition->update([
                'name' => trim($this->settingName),
                'category' => $this->settingCategory,
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
            $sortOrder = 0;
            foreach (array_values($this->recipientRules) as $rule) {
                $values = $rule['source_type'] === 'user'
                    ? collect($rule['source_value'])->map(fn($id) => (string) (int) $id)->unique()->values()->all()
                    : [trim((string) $rule['source_value'])];

                foreach ($values as $value) {
                    $definition->recipientRules()->create([
                        'delivery_type' => $rule['delivery_type'],
                        'source_type' => $rule['source_type'],
                        'source_value' => $value,
                        // Store the tenant used when the rule was configured so a
                        // tampered user/group id cannot resolve outside this company.
                        'source_meta' => in_array($rule['source_type'], ['user', 'notification_group'], true)
                            ? ['company_id' => auth()->user()->company_id]
                            : null,
                        'label' => trim((string) ($rule['label'] ?? '')) ?: null,
                        'enabled' => (bool) ($rule['enabled'] ?? true),
                        'sort_order' => $sortOrder++,
                    ]);
                }
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
        $definition = ScheduledOperationDefinition::with('recipientRules')->whereNull('archived_at')->findOrFail($this->settingDefinitionId);
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

        $this->ensureOperationCategory($default['category']);

        $this->closeModals();
        session()->flash('scheduled-success', 'The handler defaults and legacy recipient mode were restored.');
    }

    public function render(ScheduledOperationRegistry $registry)
    {
        $this->authoriseAdmin();

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateFilter)) {
            $this->dateFilter = today()->format('Y-m-d');
        }

        $query = ScheduledRun::with(['messages.recipients', 'group'])
            ->whereDate('scheduled_for', $this->dateFilter)
            ->latest('scheduled_for')->latest('id');
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }
        if ($this->categoryFilter === 'except_hourly') {
            $query->where('category', '!=', 'hourly');
        } elseif ($this->categoryFilter !== '') {
            $query->where('category', $this->categoryFilter);
        }
        if ($this->search !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(fn($sub) => $sub->where('task_name', 'like', $search)->orWhere('task_key', 'like', $search));
        }

        $runs = $query->paginate(25, ['*'], 'runsPage');
        $selectedRun = $this->selectedRunId
            ? ScheduledRun::with(['messages.recipients', 'group', 'retryOf'])->find($this->selectedRunId)
            : null;
        $categories = ScheduledOperationCategory::orderBy('sort_order')->orderBy('name')->get();
        $categoryOrder = $categories->pluck('sort_order', 'slug');
        $definitions = collect($registry->allEffective(true))
            ->map(fn($definition) => array_merge(
                $definition,
                ['schedule_label' => $registry->scheduleLabel($definition)],
                ['schedule_sort' => $this->scheduleSortKey($definition)],
                $this->handlerDetails($definition)
            ))
            ->when(!$this->includeArchived, fn($items) => $items->reject(fn($definition) => $definition['archived'] ?? false))
            ->when(trim($this->scheduleSearch) !== '', function ($items) {
                $search = mb_strtolower(trim($this->scheduleSearch));
                return $items->filter(function ($definition) use ($search) {
                    $haystack = implode(' ', [
                        $definition['name'] ?? '',
                        $definition['description'] ?? '',
                        $definition['category'] ?? '',
                        $definition['key'] ?? '',
                        $definition['handler_label'] ?? '',
                        $definition['schedule_label'] ?? '',
                        $definition['recipients'] ?? '',
                        json_encode($definition['recipient_rules'] ?? []),
                    ]);
                    return str_contains(mb_strtolower($haystack), $search);
                });
            })
            ->sortBy(fn($definition) => sprintf(
                '%06d-%s-%s',
                $categoryOrder[$definition['category']] ?? 999999,
                $definition['schedule_sort'],
                mb_strtolower($definition['name'])
            ))
            ->groupBy('category');
        $dateRuns = ScheduledRun::whereDate('scheduled_for', $this->dateFilter)->get();
        $mode = config('scheduled_operations.mode');
        $heartbeat = in_array($mode, ['shadow', 'live'], true)
            ? ScheduledDispatchHeartbeat::where('mode', $mode)->first()
            : null;

        return view('livewire.manage.scheduled-operations.dashboard', [
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'definitions' => $definitions,
            'availableHandlers' => $registry->availableHandlers(),
            'categories' => $categories,
            'categoryLabels' => $categories->pluck('name', 'slug'),
            'categoryOperationCounts' => ScheduledOperationDefinition::query()
                ->whereNull('archived_at')
                ->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category'),
            'users' => User::query()->with('company')->where('company_id', auth()->user()->company_id)
                ->where('status', 1)->orderBy('firstname')->orderBy('lastname')->get()
                ->filter(fn(User $user) => filter_var($user->email, FILTER_VALIDATE_EMAIL))->values(),
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
                'date_label' => \Carbon\Carbon::parse($this->dateFilter)->format('d/m/Y'),
                'total' => $dateRuns->count(),
                'successful' => $dateRuns->where('status', 'successful')->count(),
                'failed' => $dateRuns->whereIn('status', ['failed', 'missed'])->count(),
                'running' => $dateRuns->whereIn('status', ['queued', 'running'])->count(),
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
            $schedule['weekdays'] = collect($this->settingWeekdays)
                ->map(fn($day) => (int) $day)
                ->filter(fn(int $day) => $day >= 1 && $day <= 5)
                ->unique()
                ->sort()
                ->values()
                ->all();
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

    /** Identify the exact code that the v2 dispatcher will execute. */
    private function handlerDetails(array $definition): array
    {
        $handler = $definition['handler'] ?? null;

        if (!is_array($handler) || count($handler) !== 2) {
            return [
                'handler_type' => 'missing',
                'handler_type_label' => 'Handler missing',
                'handler_label' => 'No executable handler is available',
            ];
        }

        [$class, $method] = $handler;
        $isNewHandler = is_subclass_of($class, ScheduledOperationHandler::class);

        return [
            'handler_type' => $isNewHandler ? 'scheduled' : 'legacy',
            'handler_type_label' => $isNewHandler ? 'New handler' : 'Legacy controller',
            'handler_label' => class_basename($class) . '::' . $method,
        ];
    }

    /** Sort weekly work Monday-Sunday, followed by special schedules and hourly work. */
    private function scheduleSortKey(array $definition): string
    {
        $schedule = $definition['schedule'];

        return match ($schedule['type']) {
            'weekly' => sprintf('1-%02d', min(array_map('intval', $schedule['weekdays'] ?? [7]))),
            'daily' => '2-00',
            'weekdays' => '2-01',
            'fortnightly' => sprintf('3-%02d', (int) ($schedule['weekday'] ?? 7)),
            'monthly_nth_weekday' => sprintf('4-01-%02d-%02d', (int) ($schedule['weekday'] ?? 7), (int) ($schedule['occurrence'] ?? 1)),
            'monthly_last_weekday' => sprintf('4-02-%02d', (int) ($schedule['weekday'] ?? 7)),
            'monthly_day' => sprintf('4-03-%02d', (int) ($schedule['day'] ?? 1)),
            'quarterly' => sprintf('5-%02d', (int) ($schedule['day'] ?? 1)),
            'hourly' => sprintf('9-%02d', (int) ($schedule['minute'] ?? 0)),
            default => '8-00',
        };
    }

    private function scheduleTypes(): array
    {
        return ['hourly', 'daily', 'weekdays', 'weekly', 'fortnightly', 'monthly_nth_weekday', 'monthly_last_weekday', 'monthly_day', 'quarterly'];
    }

    private function syncOperationCategories(): void
    {
        if (!Schema::hasTable('scheduled_operation_categories')) {
            return;
        }

        ScheduledOperationDefinition::query()->distinct()->pluck('category')
            ->filter()->each(fn(string $slug) => $this->ensureOperationCategory($slug));
    }

    private function ensureOperationCategory(string $slug): void
    {
        if (!Schema::hasTable('scheduled_operation_categories')) {
            return;
        }

        ScheduledOperationCategory::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => Str::headline($slug),
                'sort_order' => ((int) ScheduledOperationCategory::max('sort_order')) + 10,
                'enabled' => true,
            ]
        );
    }

    private function loadCategoryRows(): void
    {
        $this->categoryRows = ScheduledOperationCategory::orderBy('sort_order')->orderBy('name')->get()
            ->mapWithKeys(fn(ScheduledOperationCategory $category) => ['category_'.$category->id => [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
                'enabled' => $category->enabled,
            ]])->all();
    }

    private function authoriseAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasRole2('web-admin'), 403);
    }
}
