<?php

namespace App\Services\Planner;

use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Site;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlannerTaskService
{
    public function __construct(protected PlannerDateService $dates)
    {
    }

    public function create(array $attributes): SitePlanner
    {
        return $this->createTask($attributes, false);
    }

    public function createForSitePlanner(array $attributes): SitePlanner
    {
        return $this->createTask($attributes, true);
    }

    protected function createTask(array $attributes, bool $allowSiteOnlyTasks): SitePlanner
    {
        $data = validator($attributes, [
            'site_id' => ['required', 'integer'],
            'entity_type' => ['required', 'in:c,t'],
            'entity_id' => ['required', 'integer'],
            'task_id' => ['required', 'integer'],
            'from' => ['required', 'date_format:Y-m-d'],
            'days' => ['nullable', 'integer', 'min:1'],
            'weekend' => ['nullable', 'boolean'],
        ])->validate();

        $site = Site::findOrFail($data['site_id']);
        $task = Task::where('status', 1)->findOrFail($data['task_id']);
        $this->ensureEntityExists($data['entity_type'], $data['entity_id']);

        if (!$this->dates->isWorkDay($data['from']) && empty($data['weekend'])) {
            throw ValidationException::withMessages(['from' => 'Tasks must start on a working day.']);
        }

        if ($task->code === 'START') {
            throw ValidationException::withMessages(['task_id' => 'Start Job must be added using the Job Start action.']);
        }

        if (!$allowSiteOnlyTasks && ($task->code === 'STARTCarp' || (int)$task->id === 5)) {
            throw ValidationException::withMessages(['task_id' => 'This task can only be added from the Site Planner.']);
        }

        if ($allowSiteOnlyTasks && ($task->code === 'STARTCarp' || (int)$task->id === 5)
            && SitePlanner::where('site_id', $site->id)->where('task_id', $task->id)->exists()) {
            throw ValidationException::withMessages(['task_id' => $task->name . ' already exists on this site planner.']);
        }

        $days = max(1, (int)($data['days'] ?? 1));
        $from = $this->dates->parse($data['from']);

        return SitePlanner::create([
            'site_id' => $site->id,
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'task_id' => $task->id,
            'from' => $from->format('Y-m-d'),
            'to' => $this->dates->endDate($from, $days)->format('Y-m-d'),
            'days' => $days,
            'weekend' => (bool)($data['weekend'] ?? false),
        ]);
    }

    public function changeDuration(int $plannerTaskId, int $change): SitePlanner
    {
        return DB::transaction(function () use ($plannerTaskId, $change) {
            $task = $this->lockedTask($plannerTaskId);

            if ($change > 0 && in_array((string)$task->task?->code, ['START', 'STARTCarp'], true)) {
                throw new DomainException("{$task->task->name} cannot exceed one day.");
            }

            $task->days = max(1, min(365, (int)$task->days + $change));
            $task->to = $this->dates->endDate($task->from, $task->days)->format('Y-m-d');
            $task->save();

            return $task->fresh();
        });
    }

    public function setDuration(int $plannerTaskId, int $days): SitePlanner
    {
        return DB::transaction(function () use ($plannerTaskId, $days) {
            $task = $this->lockedTask($plannerTaskId);
            $days = max(1, min(365, $days));

            if ($days > 1 && in_array((string)$task->task?->code, ['START', 'STARTCarp'], true)) {
                throw new DomainException("{$task->task->name} cannot exceed one day.");
            }

            $task->days = $days;
            $task->to = $this->dates->endDate($task->from, $days)->format('Y-m-d');
            $task->save();

            return $task->fresh();
        });
    }

    public function move(int $plannerTaskId, string $fromDate, int $workDays): SitePlanner
    {
        if ($workDays === 0) {
            return SitePlanner::findOrFail($plannerTaskId);
        }

        return DB::transaction(function () use ($plannerTaskId, $fromDate, $workDays) {
            $task = $this->lockedTask($plannerTaskId);

            return $this->moveLockedTask($task, $fromDate, $workDays);
        });
    }

    public function moveTo(int $plannerTaskId, string $date): SitePlanner
    {
        return DB::transaction(function () use ($plannerTaskId, $date) {
            $task = $this->lockedTask($plannerTaskId);
            $target = $this->dates->parse($date);
            $this->ensureCanMoveTo($task, $target);

            $task->from = $target->format('Y-m-d');
            $task->to = $this->dates->endDate($target, (int)$task->days)->format('Y-m-d');
            $task->save();

            return $task->fresh();
        });
    }

    public function previewSegmentMove(int $plannerTaskId, string $fromDate, string $date): array
    {
        $task = SitePlanner::with(['site', 'task'])->findOrFail($plannerTaskId);

        return $this->segmentMovePreview($task, $fromDate, $date);
    }

    public function moveSegmentTo(int $plannerTaskId, string $fromDate, string $date): array
    {
        return DB::transaction(function () use ($plannerTaskId, $fromDate, $date) {
            $task = $this->lockedTask($plannerTaskId);
            $preview = $this->segmentMovePreview($task, $fromDate, $date);
            $before = $this->taskSnapshot($task);
            $originalId = (int)$task->id;
            $createdId = null;

            if ($preview['split']) {
                $task = $this->splitLockedTask($task, $this->dates->parse($fromDate));
                $createdId = (int)$task->id;
            }

            $target = $this->dates->parse($date);
            $task->from = $target->format('Y-m-d');
            $task->to = $this->dates->endDate($target, (int)$task->days)->format('Y-m-d');
            $task->save();

            $original = SitePlanner::findOrFail($originalId);
            $moved = $task->fresh();

            return [
                'preview' => $preview,
                'task' => $moved,
                'undo' => [
                    'site_id' => (int)$task->site_id,
                    'original_id' => $originalId,
                    'created_id' => $createdId,
                    'before' => $before,
                    'after_original' => $this->taskSnapshot($original),
                    'after_moved' => $this->taskSnapshot($moved),
                ],
            ];
        });
    }

    public function undoSegmentMove(array $undo): void
    {
        DB::transaction(function () use ($undo) {
            $originalId = (int)($undo['original_id'] ?? 0);
            $createdId = isset($undo['created_id']) ? (int)$undo['created_id'] : null;
            $before = (array)($undo['before'] ?? []);
            $expectedOriginal = (array)($undo['after_original'] ?? []);
            $expectedMoved = (array)($undo['after_moved'] ?? []);
            $original = SitePlanner::lockForUpdate()->findOrFail($originalId);

            if (!$this->snapshotMatches($original, $expectedOriginal)) {
                throw new DomainException('This move cannot be undone because the task has since been changed.');
            }

            if ($createdId) {
                $moved = SitePlanner::lockForUpdate()->findOrFail($createdId);

                if (!$this->snapshotMatches($moved, $expectedMoved)) {
                    throw new DomainException('This move cannot be undone because the moved task has since been changed.');
                }

                $moved->delete();
            } elseif (!$this->snapshotMatches($original, $expectedMoved)) {
                throw new DomainException('This move cannot be undone because the task has since been changed.');
            }

            $original->from = $before['from'];
            $original->to = $before['to'];
            $original->days = (int)$before['days'];
            $original->save();
        });
    }

    public function moveToEntity(int $plannerTaskId, string $date, string $entityType, int $entityId): SitePlanner
    {
        $this->ensureEntityExists($entityType, $entityId);

        return DB::transaction(function () use ($plannerTaskId, $date, $entityType, $entityId) {
            $task = $this->lockedTask($plannerTaskId);
            $target = $this->dates->parse($date);

            if ((string)$task->task?->code === 'START') {
                throw new DomainException('Use Move Job to move the Start Job task and the rest of the site together.');
            }

            $this->ensureEntityCanPerformTask($task, $entityType, $entityId);
            $this->ensureCanMoveTo($task, $target);

            $task->entity_type = $entityType;
            $task->entity_id = $entityId;
            $task->from = $target->format('Y-m-d');
            $task->to = $this->dates->endDate($target, (int)$task->days)->format('Y-m-d');
            $task->save();

            return $task->fresh();
        });
    }

    public function moveSiteFrom(int $siteId, string $fromDate, int $workDays): int
    {
        return DB::transaction(function () use ($siteId, $fromDate, $workDays) {
            $tasks = $this->tasksFromDate($fromDate, fn ($query) => $query->where('site_id', $siteId));

            foreach ($tasks as $task) {
                $this->moveLockedTask($task, $fromDate, $workDays);
            }

            return $tasks->count();
        });
    }

    public function moveEntityFrom(int $siteId, string $entityType, int $entityId, string $fromDate, int $workDays): int
    {
        return DB::transaction(function () use ($siteId, $entityType, $entityId, $fromDate, $workDays) {
            $tasks = $this->tasksFromDate($fromDate, fn ($query) => $query
                ->where('site_id', $siteId)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId));

            foreach ($tasks as $task) {
                $this->moveLockedTask($task, $fromDate, $workDays);
            }

            return $tasks->count();
        });
    }

    public function reassignFrom(int $siteId, int $tradeId, string $fromDate, string $entityType, int $entityId, string $scope): int
    {
        $this->ensureEntityExists($entityType, $entityId);

        return DB::transaction(function () use ($siteId, $tradeId, $fromDate, $entityType, $entityId, $scope) {
            $date = $this->dates->parse($fromDate);
            $tasks = SitePlanner::where('site_id', $siteId)
                ->whereHas('task', fn ($query) => $query->where('trade_id', $tradeId))
                ->where(function ($query) use ($date) {
                    $query->whereDate('from', '>=', $date->format('Y-m-d'))
                        ->orWhereDate('to', '>=', $date->format('Y-m-d'));
                })
                ->orderBy('from')
                ->lockForUpdate()
                ->get();

            $changed = 0;

            foreach ($tasks as $task) {
                if ($scope === 'day' && !$date->betweenIncluded($task->from, $task->to)) {
                    continue;
                }

                if ($date->gt($task->from) && $date->lte($task->to)) {
                    $task = $this->splitLockedTask($task, $date);
                }

                $task->entity_type = $entityType;
                $task->entity_id = $entityId;
                $task->save();
                $changed++;
            }

            return $changed;
        });
    }

    public function reassignTask(int $plannerTaskId, string $entityType, int $entityId): SitePlanner
    {
        $this->ensureEntityExists($entityType, $entityId);

        return DB::transaction(function () use ($plannerTaskId, $entityType, $entityId) {
            $task = $this->lockedTask($plannerTaskId);
            $task->entity_type = $entityType;
            $task->entity_id = $entityId;
            $task->save();

            return $task->fresh();
        });
    }

    public function delete(int $plannerTaskId): void
    {
        DB::transaction(function () use ($plannerTaskId) {
            $task = $this->lockedTask($plannerTaskId);

            if (in_array((int)$task->task_id, [11, 264], true)) {
                throw new DomainException('This protected planner task cannot be deleted here.');
            }

            $today = Carbon::today();

            if ($today->gt($task->from) && $today->lte($task->to)) {
                $end = $this->dates->isWorkDay($today) ? $today : $this->dates->shift($today, -1);
                $task->to = $end->format('Y-m-d');
                $task->days = $this->dates->workDaysBetween($task->from, $end);
                $task->save();

                return;
            }

            $task->delete();
        });
    }

    public function deleteEntityFrom(int $siteId, string $entityType, int $entityId, string $fromDate): int
    {
        return DB::transaction(function () use ($siteId, $entityType, $entityId, $fromDate) {
            $date = $this->dates->parse($fromDate);
            $tasks = $this->tasksFromDate($fromDate, fn ($query) => $query
                ->where('site_id', $siteId)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId));

            foreach ($tasks as $task) {
                if (in_array((int)$task->task_id, [11, 264], true)) {
                    throw new DomainException('A protected planner task cannot be deleted here.');
                }

                if ($date->gt($task->from) && $date->lte($task->to)) {
                    $task->to = $this->dates->shift($date, -1)->format('Y-m-d');
                    $task->days = $this->dates->workDaysBetween($task->from, $task->to);
                    $task->save();
                } else {
                    $task->delete();
                }
            }

            return $tasks->count();
        });
    }

    public function deleteSiteFrom(int $siteId, string $fromDate): int
    {
        return DB::transaction(function () use ($siteId, $fromDate) {
            $date = $this->dates->parse($fromDate);
            $tasks = $this->tasksFromDate($fromDate, fn ($query) => $query->where('site_id', $siteId));

            foreach ($tasks as $task) {
                if ($date->gt($task->from) && $date->lte($task->to)) {
                    $task->to = $this->dates->shift($date, -1)->format('Y-m-d');
                    $task->days = $this->dates->workDaysBetween($task->from, $task->to);
                    $task->save();
                } else {
                    $task->delete();
                }
            }

            return $tasks->count();
        });
    }

    public function deleteMany(array $plannerTaskIds): int
    {
        return DB::transaction(function () use ($plannerTaskIds) {
            $tasks = SitePlanner::whereIn('id', array_map('intval', $plannerTaskIds))->lockForUpdate()->get();

            foreach ($tasks as $task) {
                if (in_array((int)$task->task_id, [11, 264], true)) {
                    throw new DomainException('A protected planner task cannot be deleted here.');
                }
            }

            foreach ($tasks as $task) {
                $task->delete();
            }

            return $tasks->count();
        });
    }

    protected function moveLockedTask(SitePlanner $task, string $fromDate, int $workDays): SitePlanner
    {
        $moveFrom = $this->dates->parse($fromDate);

        if ($moveFrom->gt($task->from) && $moveFrom->lte($task->to)) {
            $task = $this->splitLockedTask($task, $moveFrom);
        }

        $target = $this->dates->shift($task->from, $workDays);
        $this->ensureCanMoveTo($task, $target);

        $task->from = $target->format('Y-m-d');
        $task->to = $this->dates->endDate($target, (int)$task->days)->format('Y-m-d');
        $task->save();

        return $task->fresh();
    }

    protected function splitLockedTask(SitePlanner $task, Carbon $splitDate): SitePlanner
    {
        $originalDays = (int)$task->days;
        $firstTo = $this->dates->shift($splitDate, -1);
        $firstDays = $this->dates->workDaysBetween($task->from, $firstTo);
        $secondDays = $originalDays - $firstDays;

        if ($firstDays < 1 || $secondDays < 1) {
            return $task;
        }

        $second = $task->replicate();
        $second->from = $splitDate->format('Y-m-d');
        $second->days = $secondDays;
        $second->to = $this->dates->endDate($splitDate, $secondDays)->format('Y-m-d');

        $task->days = $firstDays;
        $task->to = $firstTo->format('Y-m-d');
        $task->save();
        $second->save();

        return $second;
    }

    protected function segmentMovePreview(SitePlanner $task, string $fromDate, string $date): array
    {
        $source = $this->dates->parse($fromDate);
        $target = $this->dates->parse($date);
        $taskFrom = $this->dates->parse($task->from);
        $taskTo = $this->dates->parse($task->to);

        if (!$source->betweenIncluded($taskFrom, $taskTo)) {
            throw ValidationException::withMessages(['from' => 'The selected day is not part of this task.']);
        }

        if ($source->lt(Carbon::today())) {
            throw new DomainException('Completed task days cannot be moved.');
        }

        if (!$this->dates->isWorkDay($source) && !$task->weekend) {
            throw ValidationException::withMessages(['from' => 'The selected task day is not a working day.']);
        }

        if ((string)$task->task?->code === 'START') {
            throw new DomainException('Use Move Job to move the Start Job task and the rest of the site together.');
        }

        if ($target->lte(Carbon::today())) {
            throw new DomainException('Choose a future working day for the moved task.');
        }

        $this->ensureCanMoveTo($task, $target);

        if ($source->equalTo($target)) {
            throw ValidationException::withMessages(['date' => 'Choose a different date for the task.']);
        }

        $keptDays = $source->gt($taskFrom)
            ? $this->dates->workDaysBetween($taskFrom, $this->dates->shift($source, -1))
            : 0;
        $movedDays = max(1, (int)$task->days - $keptDays);
        $split = $keptDays > 0;

        if ($split) {
            $keptTo = $this->dates->shift($source, -1);
            $targetEnd = $this->dates->endDate($target, $movedDays);

            if ($target->lte($keptTo) && $targetEnd->gte($taskFrom)) {
                throw ValidationException::withMessages(['date' => 'The moved section cannot overlap the earlier days being kept.']);
            }
        }

        return [
            'task_id' => (int)$task->id,
            'task_name' => (string)($task->task?->name ?? 'Task'),
            'entity_name' => $task->entity_type === 'c'
                ? (string)(Company::find($task->entity_id)?->name ?? '')
                : (string)(Trade::find($task->entity_id)?->name ?? ''),
            'source' => $source->format('Y-m-d'),
            'target' => $target->format('Y-m-d'),
            'target_end' => $this->dates->endDate($target, $movedDays)->format('Y-m-d'),
            'total_days' => (int)$task->days,
            'kept_days' => $keptDays,
            'moved_days' => $movedDays,
            'split' => $split,
        ];
    }

    protected function taskSnapshot(SitePlanner $task): array
    {
        return [
            'id' => (int)$task->id,
            'from' => Carbon::parse($task->from)->format('Y-m-d'),
            'to' => Carbon::parse($task->to)->format('Y-m-d'),
            'days' => (int)$task->days,
            'updated_at' => $task->updated_at?->format('Y-m-d H:i:s.u'),
        ];
    }

    protected function snapshotMatches(SitePlanner $task, array $snapshot): bool
    {
        return $snapshot !== []
            && (int)$task->id === (int)($snapshot['id'] ?? 0)
            && Carbon::parse($task->from)->format('Y-m-d') === (string)($snapshot['from'] ?? '')
            && Carbon::parse($task->to)->format('Y-m-d') === (string)($snapshot['to'] ?? '')
            && (int)$task->days === (int)($snapshot['days'] ?? 0)
            && $task->updated_at?->format('Y-m-d H:i:s.u') === ($snapshot['updated_at'] ?? null);
    }

    protected function tasksFromDate(string $fromDate, callable $scope): Collection
    {
        $date = $this->dates->parse($fromDate)->format('Y-m-d');
        $query = SitePlanner::where(function ($query) use ($date) {
            $query->whereDate('from', '>=', $date)->orWhereDate('to', '>=', $date);
        });

        $scope($query);

        return $query->orderBy('from')->lockForUpdate()->get();
    }

    protected function lockedTask(int $plannerTaskId): SitePlanner
    {
        return SitePlanner::with(['site', 'task'])->lockForUpdate()->findOrFail($plannerTaskId);
    }

    protected function ensureCanMoveTo(SitePlanner $task, Carbon $target): void
    {
        $site = $task->relationLoaded('site') ? $task->site : Site::findOrFail($task->site_id);

        if ((int)$site->status > 0 && $target->lte(Carbon::today())) {
            throw new DomainException('Active and maintenance tasks cannot be moved to today or into the past.');
        }

        if (!$this->dates->isWorkDay($target) && !$task->weekend) {
            throw new DomainException('Tasks must be moved to a working day.');
        }
    }

    protected function ensureEntityExists(string $entityType, int $entityId): void
    {
        match ($entityType) {
            'c' => Company::findOrFail($entityId),
            't' => Trade::findOrFail($entityId),
            default => throw ValidationException::withMessages(['entity_type' => 'The planner entity type is invalid.']),
        };
    }

    protected function ensureEntityCanPerformTask(SitePlanner $plannerTask, string $entityType, int $entityId): void
    {
        $tradeId = (int)$plannerTask->task?->trade_id;

        if ($entityType === 't' && $entityId !== $tradeId) {
            throw ValidationException::withMessages(['entity_id' => 'That generic trade cannot perform this task.']);
        }

        if ($entityType === 'c' && !DB::table('company_trade')->where('company_id', $entityId)->where('trade_id', $tradeId)->exists()) {
            throw ValidationException::withMessages(['entity_id' => 'That company is not assigned to the task trade.']);
        }
    }
}
