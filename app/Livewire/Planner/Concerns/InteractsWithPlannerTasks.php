<?php

namespace App\Livewire\Planner\Concerns;

use App\Services\Planner\PlannerTaskService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait InteractsWithPlannerTasks
{
    /**
     * Shared mutation layer for Trade and Site planners.
     * Host components decide which records are visible/editable; this trait applies
     * the common service call, confirmation state and friendly error handling.
     */
    public string $plannerMessage = '';
    public string $plannerError = '';

    public bool $showPlannerDeleteModal = false;

    #[Locked]
    public string $plannerDeleteType = '';

    #[Locked]
    public array $plannerDeletePayload = [];

    #[Locked]
    public string $plannerDeleteTitle = '';

    #[Locked]
    public string $plannerDeleteMessage = '';

    #[Locked]
    public string $plannerDeleteItem = '';

    #[Locked]
    public string $plannerDeleteConfirmLabel = 'Yes, delete';

    abstract protected function canEditPlannerTasks(): bool;

    abstract protected function canAccessPlannerTask(int $plannerTaskId): bool;

    abstract protected function canAccessPlannerSite(int $siteId): bool;

    abstract protected function canAccessPlannerTaskFrom(int $plannerTaskId, string $fromDate): bool;

    abstract protected function canAccessPlannerSiteFrom(int $siteId, string $fromDate): bool;

    abstract protected function canAccessPlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate): bool;

    abstract protected function refreshPlannerData(): void;

    public function changePlannerTaskDays(int $plannerTaskId, int $change): void
    {
        // Access checks use the host component's already filtered planner context,
        // preventing a valid but unrelated planner task ID from being modified.
        abort_unless($this->canAccessPlannerTask($plannerTaskId), 404);
        abort_unless(in_array($change, [-1, 1], true), 422);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($plannerTaskId, $change) {
            $service->changeDuration($plannerTaskId, $change);
            $this->plannerMessage = 'Task duration updated.';
        });
    }

    public function setPlannerTaskDays(int $plannerTaskId, int $days): void
    {
        abort_unless($this->canAccessPlannerTask($plannerTaskId), 404);
        abort_unless($days >= 1 && $days <= 365, 422);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($plannerTaskId, $days) {
            $service->setDuration($plannerTaskId, $days);
            $this->plannerMessage = 'Task duration updated.';
        });
    }

    public function movePlannerTask(int $plannerTaskId, string $fromDate, int $workDays): void
    {
        abort_unless($this->canAccessPlannerTaskFrom($plannerTaskId, $fromDate), 404);
        abort_unless($workDays !== 0 && abs($workDays) <= 10, 422);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($plannerTaskId, $fromDate, $workDays) {
            $service->move($plannerTaskId, $fromDate, $workDays);
            $this->plannerMessage = 'Task moved.';
        });
    }

    public function movePlannerTaskTo(int $plannerTaskId, string $date): void
    {
        abort_unless($this->canAccessPlannerTask($plannerTaskId), 404);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($plannerTaskId, $date) {
            $service->moveTo($plannerTaskId, $date);
            $this->plannerMessage = 'Task moved.';
        });
    }

    public function movePlannerSite(int $siteId, string $fromDate, int $workDays): void
    {
        // "From" is significant: tasks before the chosen point remain untouched,
        // which is how the UI can split and move only a remaining schedule segment.
        abort_unless($this->canAccessPlannerSiteFrom($siteId, $fromDate), 404);
        abort_unless($workDays !== 0 && abs($workDays) <= 10, 422);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($siteId, $fromDate, $workDays) {
            $count = $service->moveSiteFrom($siteId, $fromDate, $workDays);
            $this->plannerMessage = $count . ' site task' . ($count === 1 ? '' : 's') . ' moved.';
        });
    }

    public function movePlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate, int $workDays): void
    {
        abort_unless($this->canAccessPlannerEntity($siteId, $entityType, $entityId, $fromDate), 404);
        abort_unless($workDays !== 0 && abs($workDays) <= 10, 422);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($siteId, $entityType, $entityId, $fromDate, $workDays) {
            $count = $service->moveEntityFrom($siteId, $entityType, $entityId, $fromDate, $workDays);
            $this->plannerMessage = $count . ' connected task' . ($count === 1 ? '' : 's') . ' moved.';
        });
    }

    public function deletePlannerTask(int $plannerTaskId): void
    {
        abort_unless($this->canAccessPlannerTask($plannerTaskId), 404);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($plannerTaskId) {
            $service->delete($plannerTaskId);
            $this->plannerMessage = 'Task deleted.';
        });
    }

    public function deletePlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate): void
    {
        abort_unless($this->canAccessPlannerEntity($siteId, $entityType, $entityId, $fromDate), 404);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($siteId, $entityType, $entityId, $fromDate) {
            $count = $service->deleteEntityFrom($siteId, $entityType, $entityId, $fromDate);
            $this->plannerMessage = $count . ' connected task' . ($count === 1 ? '' : 's') . ' removed.';
        });
    }

    public function confirmPlannerTaskDeletion(int $plannerTaskId): void
    {
        abort_unless($this->canAccessPlannerTask($plannerTaskId), 404);

        // Build confirmation text from the locked editor payload rather than taking
        // names or day counts from browser parameters.
        $task = collect($this->editorTasks ?? [])->firstWhere('id', $plannerTaskId);
        abort_unless($task && !in_array((int)$task['task_id'], [11, 264], true), 404);

        $days = max(1, (int)$task['days']);
        $entityName = (string)($task['site_name'] ?? $task['entity_name'] ?? '');

        $this->plannerDeleteType = 'task';
        $this->plannerDeletePayload = ['planner_task_id' => $plannerTaskId];
        $this->plannerDeleteTitle = 'Delete task?';
        $this->plannerDeleteMessage = 'This will permanently delete ' . $days . ' scheduled task day' . ($days === 1 ? '' : 's') . '.';
        $this->plannerDeleteItem = collect([(string)$task['task_name'], $entityName])->filter()->join(' — ');
        $this->plannerDeleteConfirmLabel = 'Yes, delete task';
        $this->showPlannerDeleteModal = true;
    }

    public function confirmPlannerEntityDeletion(int $siteId, string $entityType, int $entityId, string $fromDate): void
    {
        abort_unless($this->canAccessPlannerEntity($siteId, $entityType, $entityId, $fromDate), 404);

        // Entity deletion means the uninterrupted connected schedule from the
        // selected date onward, not every historical task for that company/trade.
        $tasks = collect($this->editorTasks ?? [])->where('site_id', $siteId);
        $editorSite = collect($this->editorSites ?? [])->firstWhere('id', $siteId);
        $siteName = property_exists($this, 'siteName')
            ? (string)$this->siteName
            : (string)($editorSite['name'] ?? '');
        $taskNames = $tasks->pluck('task_name')->filter()->unique()->join(', ');

        $this->plannerDeleteType = 'entity';
        $this->plannerDeletePayload = [
            'site_id' => $siteId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'from_date' => $fromDate,
        ];
        $this->plannerDeleteTitle = 'Remove connected tasks?';
        $this->plannerDeleteMessage = 'This will permanently remove the connected schedule from ' . $this->plannerConfirmationDate($fromDate) . ' onwards.';
        $this->plannerDeleteItem = collect([$siteName, $taskNames])->filter()->join(' — ');
        $this->plannerDeleteConfirmLabel = 'Yes, remove tasks';
        $this->showPlannerDeleteModal = true;
    }

    public function closePlannerDeleteModal(): void
    {
        $this->showPlannerDeleteModal = false;
        $this->plannerDeleteType = '';
        $this->plannerDeletePayload = [];
        $this->plannerDeleteTitle = '';
        $this->plannerDeleteMessage = '';
        $this->plannerDeleteItem = '';
        $this->plannerDeleteConfirmLabel = 'Yes, delete';
    }

    public function deleteConfirmedPlannerAction(): void
    {
        abort_unless($this->showPlannerDeleteModal && in_array($this->plannerDeleteType, ['task', 'entity'], true), 404);

        // Copy locked confirmation state before closing the modal, because closing
        // intentionally clears the stored payload.
        $type = $this->plannerDeleteType;
        $payload = $this->plannerDeletePayload;
        $this->closePlannerDeleteModal();

        if ($type === 'task') {
            $this->deletePlannerTask((int)($payload['planner_task_id'] ?? 0));

            return;
        }

        $this->deletePlannerEntity(
            (int)($payload['site_id'] ?? 0),
            (string)($payload['entity_type'] ?? ''),
            (int)($payload['entity_id'] ?? 0),
            (string)($payload['from_date'] ?? '')
        );
    }

    protected function plannerConfirmationDate(string $date): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->format('D d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    protected function runPlannerAction(callable $action): void
    {
        $this->plannerMessage = '';
        $this->plannerError = '';

        // Every mutation, including calls introduced by future UI buttons, passes
        // through this final authorisation gate.
        if (!$this->canEditPlannerTasks()) {
            throw new AuthorizationException();
        }

        try {
            // Refresh after a successful write so split/rejoined tasks and conflict
            // colours are always rebuilt from database truth.
            $action(app(PlannerTaskService::class));
            $this->refreshPlannerData();
        } catch (ValidationException $exception) {
            $this->plannerError = collect($exception->errors())->flatten()->first() ?: 'The planner change was not valid.';
        } catch (\DomainException $exception) {
            $this->plannerError = $exception->getMessage();
        } catch (\Throwable $exception) {
            report($exception);
            $this->plannerError = 'The planner could not be updated. Please refresh and try again.';
        }
    }
}
