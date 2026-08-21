<?php

namespace App\Livewire\Planner\Concerns;

use App\Services\Planner\PlannerTaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

trait InteractsWithPlannerTasks
{
    public string $plannerMessage = '';
    public string $plannerError = '';

    abstract protected function canEditPlannerTasks(): bool;

    abstract protected function canAccessPlannerTask(int $plannerTaskId): bool;

    abstract protected function canAccessPlannerSite(int $siteId): bool;

    abstract protected function canAccessPlannerTaskFrom(int $plannerTaskId, string $fromDate): bool;

    abstract protected function canAccessPlannerSiteFrom(int $siteId, string $fromDate): bool;

    abstract protected function canAccessPlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate): bool;

    abstract protected function refreshPlannerData(): void;

    public function changePlannerTaskDays(int $plannerTaskId, int $change): void
    {
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

    protected function runPlannerAction(callable $action): void
    {
        $this->plannerMessage = '';
        $this->plannerError = '';

        if (!$this->canEditPlannerTasks()) {
            throw new AuthorizationException();
        }

        try {
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
