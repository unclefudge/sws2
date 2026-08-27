<?php

namespace App\Scheduled;

use App\Http\Controllers\Misc\CronController;
use App\Http\Controllers\Misc\CronReportController;
use App\Http\Controllers\Misc\CronTaskController;
use App\Jobs\Reports\EmailFocDefectiveInspections;
use App\Models\Scheduled\ScheduledOperationChangeLog;
use App\Models\Scheduled\ScheduledOperationDefinition;
use App\Models\Scheduled\ScheduledTaskSetting;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Executable handlers remain code-whitelisted, while operational configuration
 * is database-driven. This lets an administrator change timing and recipients
 * without allowing browser input to execute an arbitrary PHP class or method.
 */
class ScheduledOperationRegistry
{
    public function all(): array
    {
        if ($this->definitionsTableReady() && ScheduledOperationDefinition::query()->exists()) {
            return ScheduledOperationDefinition::with('recipientRules')
                ->orderBy('category')->orderBy('name')->get()
                ->map(fn(ScheduledOperationDefinition $definition) => $this->modelToDefinition($definition))
                ->all();
        }

        // Deploys remain safe between migrate and scheduled:sync. Until the
        // database has rows, use the exact v1 defaults and existing overrides.
        return $this->defaults();
    }

    public function defaults(): array
    {
        return array_merge($this->nightlyTasks(), $this->reports(), $this->hourlyTasks());
    }

    /**
     * Return the code-defined defaults for one operation.
     *
     * A converted ScheduledOperationHandler deliberately takes precedence over
     * the legacy CronController entry with the same key. This is the source used
     * by the dashboard's Restore defaults action; saved database settings are
     * never treated as defaults.
     */
    public function defaultFor(string $key): ?array
    {
        return collect($this->handlerDefinitions())->firstWhere('key', $key);
    }

    /**
     * Converted reports that may be exposed under Settings > Notifications.
     * Legacy controller entries are deliberately excluded.
     */
    public function clientReports(): array
    {
        return collect($this->allEffective())
            ->filter(function (array $definition) {
                $handler = $definition['handler'] ?? null;

                return ($definition['category'] ?? null) === 'report'
                    && ($definition['clientConfigurable'] ?? false)
                    && is_array($handler)
                    && isset($handler[0])
                    && is_subclass_of($handler[0], ScheduledOperationHandler::class);
            })
            ->values()
            ->all();
    }

    public function dynamicRecipientsFor(string $key): array
    {
        return $this->defaultFor($key)['dynamicRecipients'] ?? [];
    }

    public function find(string $key): ?array
    {
        foreach ($this->all() as $definition) {
            if ($definition['key'] === $key) {
                return $definition;
            }
        }

        return null;
    }

    public function effective(array $definition): array
    {
        if ($definition['_database'] ?? false) {
            return $definition;
        }

        $setting = ScheduledTaskSetting::where('task_key', $definition['key'])->first();
        $definition['enabled'] = $setting ? $setting->enabled : true;

        if ($setting?->schedule_override) {
            // Preserve the registry's schedule type and only replace fields the
            // settings screen intentionally supplied (usually weekday/time).
            $definition['schedule'] = array_replace($definition['schedule'], $setting->schedule_override);
        }

        return $definition;
    }

    public function allEffective(): array
    {
        return array_map(fn(array $definition) => $this->effective($definition), $this->all());
    }

    public function execute(string $key): mixed
    {
        $definition = $this->find($key);

        if (!$definition) {
            throw new \InvalidArgumentException("Unknown scheduled operation [$key].");
        }

        if (empty($definition['handler'])) {
            throw new \RuntimeException("The handler for scheduled operation [$key] is not available in this deployment.");
        }

        [$class, $method] = $definition['handler'];

        // Legacy cron methods are static. Job/service handlers are resolved from
        // Laravel's container so their dependencies still work normally.
        if ((new \ReflectionMethod($class, $method))->isStatic()) {
            return $class::$method();
        }

        return app($class)->{$method}();
    }

    /**
     * Insert newly discovered/default handlers without overwriting settings an
     * administrator already changed in the dashboard.
     */
    public function syncDefinitions(bool $updateMetadata = false, ?int $userId = null, bool $includeDiscovered = true): array
    {
        if (!$this->definitionsTableReady()) {
            throw new \RuntimeException('Run the scheduled-operation configuration migration first.');
        }

        $result = ['created' => 0, 'updated' => 0, 'preserved' => 0];

        foreach ($this->handlerDefinitions($includeDiscovered) as $handlerKey => $default) {
            $model = ScheduledOperationDefinition::where('task_key', $default['key'])->first();

            if (!$model) {
                $isLegacyDefault = collect($this->defaults())->contains('key', $default['key']);
                $legacy = ScheduledTaskSetting::where('task_key', $default['key'])->first();
                $schedule = $legacy?->schedule_override
                    ? array_replace($default['schedule'], $legacy->schedule_override)
                    : $default['schedule'];

                ScheduledOperationDefinition::create([
                    'task_key' => $default['key'],
                    'handler_key' => $handlerKey,
                    'name' => $default['name'],
                    'category' => $default['category'],
                    'description' => $default['description'],
                    'recipient_summary' => $default['recipients'],
                    // Existing v1 operations retain their enabled state. Newly
                    // discovered handlers start disabled until an administrator
                    // has reviewed their schedule and recipient rules.
                    'enabled' => $isLegacyDefault ? ($legacy ? $legacy->enabled : true) : false,
                    'schedule_type' => $schedule['type'],
                    'schedule_data' => $schedule,
                    'recipient_mode' => 'legacy',
                    'tries' => 3,
                    'timeout_seconds' => 240,
                    'client_configurable' => $default['clientConfigurable'] ?? false,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $result['created']++;
                continue;
            }

            if ($updateMetadata) {
                $model->update([
                    'handler_key' => $handlerKey,
                    'name' => $default['name'],
                    'category' => $default['category'],
                    'description' => $default['description'],
                    'recipient_summary' => $default['recipients'],
                    'client_configurable' => $default['clientConfigurable'] ?? false,
                    'updated_by' => $userId,
                ]);
                $result['updated']++;
            } else {
                $result['preserved']++;
            }
        }

        return $result;
    }

    /** Handlers visible to the Add operation dialog but not configured yet. */
    public function availableHandlers(): array
    {
        $configured = $this->definitionsTableReady()
            ? ScheduledOperationDefinition::pluck('handler_key')->all()
            : [];

        return collect($this->handlerDefinitions())
            ->reject(fn(array $definition, string $key) => in_array($key, $configured, true))
            ->values()->all();
    }

    public function installHandler(string $handlerKey, ?int $userId = null): ScheduledOperationDefinition
    {
        $definition = $this->handlerDefinitions()[$handlerKey] ?? null;
        if (!$definition) {
            throw new \InvalidArgumentException("Unknown scheduled handler [$handlerKey].");
        }

        $existing = ScheduledOperationDefinition::where('handler_key', $handlerKey)->first();
        if ($existing) {
            return $existing;
        }

        $model = ScheduledOperationDefinition::create([
            'task_key' => $definition['key'],
            'handler_key' => $handlerKey,
            'name' => $definition['name'],
            'category' => $definition['category'],
            'description' => $definition['description'],
            'recipient_summary' => $definition['recipients'],
            'enabled' => false,
            'schedule_type' => $definition['schedule']['type'],
            'schedule_data' => $definition['schedule'],
            'recipient_mode' => 'legacy',
            'tries' => 3,
            'timeout_seconds' => 240,
            'client_configurable' => $definition['clientConfigurable'] ?? false,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        ScheduledOperationChangeLog::create([
            'scheduled_operation_definition_id' => $model->id,
            'user_id' => $userId,
            'action' => 'installed',
            'after' => $model->toArray(),
        ]);

        return $model;
    }

    public function isDue(array $definition, Carbon $at): bool
    {
        $schedule = $definition['schedule'];

        if (!$this->timeMatches($schedule, $at)) {
            return false;
        }

        return match ($schedule['type']) {
            'hourly' => true,
            'daily' => true,
            'weekdays' => $at->isWeekday(),
            'weekly' => in_array($at->dayOfWeekIso, $schedule['weekdays'], true),
            'fortnightly' => $at->dayOfWeekIso === $schedule['weekday']
                && Carbon::parse($schedule['anchor'])->startOfDay()->diffInDays($at->copy()->startOfDay()) % 14 === 0,
            'monthly_nth_weekday' => $at->dayOfWeekIso === $schedule['weekday']
                && (int) ceil($at->day / 7) === $schedule['occurrence'],
            'monthly_last_weekday' => $at->dayOfWeekIso === $schedule['weekday']
                && $at->copy()->addWeek()->month !== $at->month,
            'monthly_day' => $at->day === $schedule['day'],
            'quarterly' => $at->day === $schedule['day'] && in_array($at->month, $schedule['months'], true),
            default => false,
        };
    }

    public function scheduleLabel(array $definition): string
    {
        $schedule = $definition['schedule'];
        $time = $schedule['time'] ?? '00:05';
        $timeLabel = $time === '00:05' ? '' : " at $time";
        $minute = (int) ($schedule['minute'] ?? 0);
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

        return match ($schedule['type']) {
            'hourly' => in_array($minute, [0, 1], true) ? 'Every hour' : sprintf('Every hour at :%02d', $minute),
            'daily' => 'Daily' . $timeLabel,
            'weekdays' => 'Weekdays' . $timeLabel,
            'weekly' => implode(', ', array_map(fn($day) => $days[$day], $schedule['weekdays'])) . $timeLabel,
            'fortnightly' => "Fortnightly {$days[$schedule['weekday']]}" . $timeLabel,
            'monthly_nth_weekday' => "Monthly ({$this->ordinal($schedule['occurrence'])} {$days[$schedule['weekday']]})" . $timeLabel,
            'monthly_last_weekday' => "Monthly (last {$days[$schedule['weekday']]})" . $timeLabel,
            'monthly_day' => "Monthly (day {$schedule['day']})" . $timeLabel,
            'quarterly' => "Quarterly (day {$schedule['day']})" . $timeLabel,
            default => 'Custom schedule',
        };
    }

    private function timeMatches(array $schedule, Carbon $at): bool
    {
        if ($schedule['type'] === 'hourly') {
            return $at->minute === (int) $schedule['minute'];
        }

        return $at->format('H:i') === $schedule['time'];
    }

    private function definition(string $key, string $name, string $category, array $handler, array $schedule, string $description, string $recipients = 'No email is sent by this operation', bool $clientConfigurable = false): array
    {
        return array_merge(compact('key', 'name', 'category', 'handler', 'schedule', 'description', 'recipients', 'clientConfigurable'), [
            'handler_key' => $key,
            'recipient_mode' => 'legacy',
            'tries' => 3,
            'timeout' => 240,
            '_database' => false,
        ]);
    }

    private function handlerDefinitions(bool $includeDiscovered = true): array
    {
        $definitions = collect($this->defaults())->keyBy('handler_key');

        if (!$includeDiscovered) {
            return $definitions->all();
        }

        foreach ($this->discoverHandlerClasses() as $class) {
            $metadata = $class::scheduledOperation();
            $key = $metadata['key'] ?? null;
            if (!$key) {
                continue;
            }

            $schedule = $metadata['schedule'] ?? ['type' => 'daily', 'time' => '00:05'];
            $definition = $this->definition(
                $key,
                $metadata['name'] ?? class_basename($class),
                $metadata['category'] ?? 'report',
                [$class, 'handle'],
                $schedule,
                $metadata['description'] ?? '',
                $metadata['recipients'] ?? 'Configure recipients in Scheduled Operations',
                // Client visibility is always an explicit handler decision.
                $metadata['clientConfigurable'] ?? false
            );
            $definition['dynamicRecipients'] = $this->normaliseDynamicRecipientDefinitions(
                $metadata['dynamicRecipients'] ?? []
            );
            $definitions->put($key, $definition);
        }

        return $definitions->all();
    }

    private function handlerMap(): array
    {
        return collect($this->handlerDefinitions())->mapWithKeys(
            fn(array $definition, string $key) => [$key => $definition['handler']]
        )->all();
    }

    private function modelToDefinition(ScheduledOperationDefinition $model): array
    {
        $schedule = array_replace($model->schedule_data ?: [], ['type' => $model->schedule_type]);
        $default = $this->handlerDefinitions()[$model->handler_key] ?? null;

        return [
            'definition_id' => $model->id,
            'key' => $model->task_key,
            'handler_key' => $model->handler_key,
            'name' => $model->name,
            'category' => $model->category,
            'handler' => $this->handlerMap()[$model->handler_key] ?? null,
            'schedule' => $schedule,
            'description' => $model->description ?: '',
            'recipients' => $model->recipient_summary ?: 'No recipient summary supplied',
            'recipient_mode' => $model->recipient_mode,
            'recipient_rules' => $model->recipientRules->toArray(),
            'dynamicRecipients' => $default['dynamicRecipients'] ?? [],
            'clientConfigurable' => $model->client_configurable,
            'enabled' => $model->enabled,
            'tries' => $model->tries,
            'timeout' => $model->timeout_seconds,
            '_database' => true,
        ];
    }

    private function normaliseDynamicRecipientDefinitions(array $definitions): array
    {
        return collect($definitions)
            ->filter(fn($definition) => is_array($definition) && !empty($definition['key']))
            ->map(fn(array $definition) => [
                'key' => (string) $definition['key'],
                'label' => (string) ($definition['label'] ?? $definition['key']),
                'delivery' => in_array(($definition['delivery'] ?? 'to'), ['to', 'cc'], true)
                    ? $definition['delivery']
                    : 'to',
                'description' => (string) ($definition['description'] ?? ''),
                'required' => (bool) ($definition['required'] ?? true),
            ])
            ->unique('key')
            ->values()
            ->all();
    }

    private function discoverHandlerClasses(): array
    {
        $classes = [];
        $locations = [
            app_path('Scheduled/Operations') => 'App\\Scheduled\\Operations\\',
            app_path('Scheduled/Reports') => 'App\\Scheduled\\Reports\\',
        ];

        // Operations contains background/maintenance work, while Reports
        // contains jobs whose main result is a generated or emailed report.
        // Reports is deliberately scanned last so a moved report takes
        // precedence while an old Operations copy is being removed during a
        // deployment.
        foreach ($locations as $directory => $namespace) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relative = str_replace(
                    [$directory . DIRECTORY_SEPARATOR, '.php', DIRECTORY_SEPARATOR],
                    ['', '', '\\'],
                    $file->getPathname()
                );
                $class = $namespace . $relative;

                if (class_exists($class) && is_subclass_of($class, ScheduledOperationHandler::class)) {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }

    private function definitionsTableReady(): bool
    {
        try {
            return function_exists('app') && app()->bound('db') && Schema::hasTable('scheduled_operation_definitions');
        } catch (Throwable) {
            return false;
        }
    }

    private function nightlyTasks(): array
    {
        $daily = ['type' => 'daily', 'time' => '00:05'];
        $weekdays = ['type' => 'weekdays', 'time' => '00:05'];

        return [
            $this->definition('nightly.blessing', 'Prayer of blessing log', 'maintenance', [CronController::class, 'blessing'], $daily, 'Writes the nightly prayer and active worker list to the operational log.'),
            // The legacy batch called this every night, but the method only did
            // work on Monday. Scheduling it on Monday makes the dashboard honest.
            $this->definition('nightly.support_hours', 'Reset support hours', 'maintenance', [CronController::class, 'supporthours'], ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], 'Resets the weekly support-hour counters.'),
            $this->definition('nightly.non_attendees', 'Create non-attendance records', 'attendance', [CronController::class, 'nonattendees'], $daily, 'Finds rostered workers who did not attend and creates compliance records.'),
            $this->definition('nightly.roster', 'Build today\'s roster', 'attendance', [CronController::class, 'roster'], $daily, 'Adds planned company workers to today\'s site roster.'),
            $this->definition('nightly.qa', 'Trigger QA inspections', 'maintenance', [CronController::class, 'qa'], $daily, 'Creates and activates QA inspections when planner tasks are completed.'),
            $this->definition('nightly.qa_on_hold_completed', 'Close completed on-hold QA', 'maintenance', [CronController::class, 'qaOnholdButCompleted'], $daily, 'Reconciles QA records that are on hold but already complete.'),
            $this->definition('nightly.company_doc_todos', 'Complete company document ToDos', 'maintenance', [CronController::class, 'completeToDoCompanyDoc'], $daily, 'Completes ToDos whose required company document has been supplied.'),
            $this->definition('nightly.qa_todos', 'Complete QA ToDos', 'maintenance', [CronController::class, 'completedToDoQA'], $daily, 'Completes ToDos for QA work that has finished.'),
            $this->definition('nightly.rogue_todos', 'Repair rogue ToDos', 'maintenance', [CronController::class, 'rogueToDo'], $daily, 'Finds invalid or orphaned ToDo records and repairs their state.'),
            $this->definition('nightly.expired_company_docs', 'Process expired company documents', 'documents', [CronController::class, 'expiredCompanyDoc'], $daily, 'Expires company documents and creates the required notifications.'),
            $this->definition('nightly.expired_swms', 'Process expired SWMS', 'documents', [CronController::class, 'expiredSWMS'], $daily, 'Expires SWMS records and creates the required notifications.'),
            $this->definition('nightly.archive_toolbox', 'Archive toolbox talks', 'maintenance', [CronController::class, 'archiveToolbox'], $daily, 'Archives old toolbox talk records.'),
            $this->definition('nightly.broken_qa_items', 'Repair broken QA items', 'maintenance', [CronController::class, 'brokenQaItem'], $daily, 'Repairs QA items whose relationships are no longer valid.'),
            $this->definition('nightly.planner_key_emails', 'Email planner key tasks', 'notifications', [CronController::class, 'emailPlannerKeyTasks'], $daily, 'Sends task-triggered planner emails.', 'Recipients are resolved from each planner key task and site'),
            $this->definition('nightly.planner_key_actions', 'Create planner key actions', 'maintenance', [CronController::class, 'actionPlannerKeyTasks'], $daily, 'Creates automatic actions linked to planner key tasks.'),
            $this->definition('nightly.site_extensions', 'Update site extensions', 'maintenance', [CronController::class, 'siteExtensions'], $daily, 'Updates contract extension records and related notifications.'),
            $this->definition('nightly.company_doc_reminders', 'Company document upload reminders', 'notifications', [CronController::class, 'uploadCompanyDocReminder'], $daily, 'Sends reminders for required company document uploads.', 'Recipients are resolved from the affected company records'),
            $this->definition('nightly.asbestos_notifications', 'Create asbestos notifications', 'notifications', [CronController::class, 'createAsbestosNotification'], $daily, 'Creates notifications for active asbestos records.', 'Recipients are resolved from each asbestos/site record'),
            $this->definition('nightly.overdue_todos', 'Process overdue ToDos', 'maintenance', [CronController::class, 'overdueToDo'], $weekdays, 'Processes overdue ToDos on working days.'),
            $this->definition('nightly.foc_defective', 'FOC defective inspections', 'report', [EmailFocDefectiveInspections::class, 'handle'], ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], 'Emails outstanding defective FOC inspection items.', 'Site Supervisor plus users configured for site.foc.defective', true),
            $this->definition('nightly.extension_task', 'Site extension Supervisor task', 'notifications', [CronController::class, 'siteExtensionsSupervisorTask'], ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], 'Creates the weekly Supervisor site-extension task.', 'Affected Site Supervisors'),
            $this->definition('nightly.extension_reminder', 'Site extension task reminder', 'notifications', [CronController::class, 'siteExtensionsSupervisorTaskReminder'], ['type' => 'weekly', 'weekdays' => [4], 'time' => '00:05'], 'Sends the first site-extension task reminder.', 'Affected Site Supervisors'),
            $this->definition('nightly.extension_final_reminder', 'Site extension final reminder', 'notifications', [CronController::class, 'siteExtensionsSupervisorTaskFinalReminder'], ['type' => 'weekly', 'weekdays' => [5], 'time' => '00:05'], 'Sends the final site-extension task reminder.', 'Affected Site Supervisors'),
        ];
    }

    private function reports(): array
    {
        $weekly = fn(int $day) => ['type' => 'weekly', 'weekdays' => [$day], 'time' => '00:05'];
        $report = fn(string $key, string $name, string $method, array $schedule, string $recipients) =>
            $this->definition("report.$key", $name, 'report', [CronReportController::class, $method], $schedule, "Generates and sends the $name report.", $recipients, true);

        return [
            $report('jobstart', 'Job Start', 'emailJobstart', $weekly(1), 'Users configured for the Job Start report'),
            $report('maintenance_appointment', 'Maintenance without appointment', 'emailMaintenanceAppointment', $weekly(1), 'Configured maintenance recipients and relevant Supervisors'),
            $report('maintenance_under_review', 'Maintenance under review', 'emailMaintenanceUnderReview', $weekly(1), 'Configured maintenance report recipients'),
            $report('missing_company_info_planner', 'Planned companies missing information', 'emailMissingCompanyInfoPlanner', $weekly(1), 'Users configured for missing company information reports'),
            $report('company_docs_pending', 'Pending company documents', 'emailCompanyDocsPending', $weekly(1), 'Users configured for pending company documents'),
            $report('active_asbestos', 'Active asbestos', 'emailActiveAsbestos', $weekly(1), 'Users configured for asbestos reports'),
            $report('supervisor_attendance', 'Supervisor attendance', 'emailSupervisorAttendance', $weekly(1), 'Each relevant Supervisor and configured management recipients'),
            $report('scaffold_overdue', 'Overdue scaffolds', 'emailScaffoldOverdue', $weekly(1), 'Recipients defined by the overdue scaffold report'),
            $report('outstanding_on_hold_qa', 'Outstanding and on-hold QA', 'emailOutstandingOnHoldQA', $weekly(1), 'Configured QA recipients and relevant Supervisors'),
            $report('equipment_transfers', 'Equipment transfers', 'emailEquipmentTransfers', $weekly(1), 'Users configured for equipment transfer reports'),
            $report('project_supply_overdue', 'Overdue project supplies', 'emailProjectSupplyOverdue', $weekly(1), 'Configured project-supply recipients'),
            $report('pending_electrical_plumbing', 'Pending electrical and plumbing', 'emailPendingElectricalPlumbing', $weekly(1), 'Configured inspection recipients'),
            $report('supervisor_site_export', 'Supervisor site export', 'emailSupervisorSiteExport', $weekly(1), 'Each active Supervisor receives their site export'),
            $report('upcoming_job_compliance', 'Upcoming job compliance', 'emailUpcomingJobCompilance', $weekly(2), 'Users configured for upcoming job compliance'),
            $report('maintenance_supervisor_no_action', 'Maintenance Supervisor no action', 'emailMaintenanceSupervisorNoAction', $weekly(2), 'Relevant Supervisors and configured maintenance recipients'),
            $report('prac_completion_supervisor_no_action', 'Practical completion Supervisor no action', 'emailPracCompletionSupervisorNoAction', $weekly(2), 'Relevant Supervisors and configured practical-completion recipients'),
            $report('no_works_planned', 'No works planned', 'emailNoWorksPlanned', $weekly(2), 'Relevant Site Supervisors'),
            $report('active_electrical_plumbing', 'Active electrical and plumbing inspections', 'emailActiveElectricalPlumbing', $weekly(4), 'Configured electrical and plumbing report recipients'),
            $report('equipment_restock', 'Equipment restock', 'emailEquipmentRestock', $weekly(5), 'Users configured for equipment restock reports'),
            $report('fortnightly', 'Fortnightly management reports', 'emailFortnightlyReports', ['type' => 'fortnightly', 'weekday' => 1, 'time' => '00:05', 'anchor' => '2020-10-26'], 'Users configured for each report contained in the fortnightly pack'),
            $report('old_users', 'Old users', 'emailOldUsers', ['type' => 'monthly_nth_weekday', 'weekday' => 2, 'occurrence' => 1, 'time' => '00:05'], 'Users configured for the old-users report'),
            $report('outstanding_aftercare', 'Outstanding aftercare', 'emailOutstandingAftercare', ['type' => 'monthly_last_weekday', 'weekday' => 5, 'time' => '00:05'], 'Users configured for aftercare reports'),
            $report('trades_attendance', 'Trades attendance', 'emailTradesAttendance', ['type' => 'monthly_day', 'day' => 1, 'time' => '00:05'], 'Users configured for trades attendance reports'),
            $report('maintenance_executive', 'Maintenance executive summary', 'emailMaintenanceExecutive', ['type' => 'quarterly', 'day' => 1, 'months' => [3, 6, 9, 12], 'time' => '00:05'], 'Executive maintenance report recipients'),
        ];
    }

    private function hourlyTasks(): array
    {
        return [
            $this->definition('hourly.client_enquiry_followup', 'Client enquiry follow-up', 'hourly', [CronTaskController::class, 'clientEnquiryFollowup'], ['type' => 'hourly', 'minute' => 1], 'Expires old enquiries and sends follow-up emails.', 'Valid enquiry address plus SafeWorksite support BCC'),
            $this->definition('hourly.sync_foc_stages', 'Synchronise FOC stages', 'hourly', [CronTaskController::class, 'syncFocStages'], ['type' => 'hourly', 'minute' => 1], 'Reconciles FOC stages with their current site data.'),
            // emailUpcomingJobs contains Monday/Thursday-specific subject and
            // recipient logic, so these two times intentionally remain fixed.
            $this->definition('hourly.upcoming_jobs_monday', 'Upcoming jobs email (Monday)', 'report', [CronTaskController::class, 'emailUpcomingJobs'], ['type' => 'weekly', 'weekdays' => [1], 'time' => '13:01'], 'Sends the Monday upcoming-jobs report.', 'Recipients resolved from Upcoming Jobs settings'),
            $this->definition('hourly.upcoming_jobs_thursday', 'Upcoming jobs email (Thursday)', 'report', [CronTaskController::class, 'emailUpcomingJobs'], ['type' => 'weekly', 'weekdays' => [4], 'time' => '10:01'], 'Sends the Thursday upcoming-jobs report.', 'Recipients resolved from Upcoming Jobs settings'),
            $this->definition('hourly.super_checklist_reminder', 'Supervisor checklist reminder', 'notifications', [CronTaskController::class, 'superChecklistsReminder'], ['type' => 'weekly', 'weekdays' => [1, 2, 3, 4, 5], 'time' => '14:01'], 'Processes outstanding Supervisor checklist reminders.', 'Affected Site Supervisors'),
        ];
    }

    private function ordinal(int $number): string
    {
        return $number . match ($number) {
            1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
        };
    }
}
