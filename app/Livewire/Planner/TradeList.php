<?php

namespace App\Livewire\Planner;

use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TradeList extends Component
{
    /**
     * Management screen for the trade/task catalogue used by every planner.
     * Deleting is intentionally stricter than disabling: anything referenced by
     * planner history must be retained so old schedules still make sense.
     */
    public bool $showDisabled = false;
    public string $sortDirection = 'asc';
    public array $openTradeIds = [];
    public string $message = '';

    public bool $showTradeModal = false;
    public bool $showTaskModal = false;
    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $editingTradeId = null;

    #[Locked]
    public ?int $editingTaskId = null;

    #[Locked]
    public ?int $taskTradeId = null;

    #[Locked]
    public ?int $deletingId = null;

    public string $deletingType = '';
    public string $deletingName = '';

    public string $tradeName = '';
    public string $taskName = '';
    public string $taskCode = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('trade'), 404);
    }

    protected function companyId(): int
    {
        return (int) Auth::user()->company_id;
    }

    protected function canAddTrade(): bool
    {
        // Trade definitions are shared more broadly than company tasks, so legacy
        // behaviour restricts structural trade changes to the primary admin user.
        return Auth::id() === 2 && Auth::user()->hasPermission2('add.trade');
    }

    protected function canEditTrade(): bool
    {
        return Auth::id() === 2 && Auth::user()->hasPermission2('edit.trade');
    }

    protected function canToggleTrade(): bool
    {
        return Auth::id() === 2 && Auth::user()->hasPermission2('del.trade');
    }

    protected function canAddTask(): bool
    {
        return Auth::user()->hasPermission2('add.trade');
    }

    protected function canEditTask(): bool
    {
        return Auth::user()->hasPermission2('edit.trade');
    }

    protected function canToggleTask(): bool
    {
        return Auth::user()->hasPermission2('del.trade');
    }

    protected function canDeleteTrade(): bool
    {
        return $this->canToggleTrade();
    }

    protected function canDeleteTask(): bool
    {
        return $this->canToggleTask();
    }

    public function toggleShowDisabled(): void
    {
        $this->showDisabled = !$this->showDisabled;
    }

    public function sortByName(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function toggleTradeOpen(int $tradeId): void
    {
        abort_unless(Trade::whereKey($tradeId)->exists(), 404);

        if (in_array($tradeId, $this->openTradeIds, true)) {
            $this->openTradeIds = array_values(array_diff($this->openTradeIds, [$tradeId]));
            return;
        }

        $this->openTradeIds[] = $tradeId;
    }

    public function openAddTrade(): void
    {
        abort_unless($this->canAddTrade(), 404);

        $this->resetValidation();
        $this->editingTradeId = null;
        $this->tradeName = '';
        $this->showTaskModal = false;
        $this->showTradeModal = true;
    }

    public function openEditTrade(int $tradeId): void
    {
        abort_unless($this->canEditTrade(), 404);

        $trade = Trade::findOrFail($tradeId);

        $this->resetValidation();
        $this->editingTradeId = $trade->id;
        $this->tradeName = $trade->name;
        $this->showTaskModal = false;
        $this->showTradeModal = true;
    }

    public function saveTrade(): void
    {
        $this->validate([
            'tradeName' => ['required', 'string'],
        ]);

        if ($this->editingTradeId) {
            abort_unless($this->canEditTrade(), 404);

            $trade = Trade::findOrFail($this->editingTradeId);
            $trade->update(['name' => trim($this->tradeName)]);
            $this->message = 'Saved ' . $trade->name . '.';
        } else {
            abort_unless($this->canAddTrade(), 404);

            $trade = Trade::create([
                'name' => trim($this->tradeName),
                'company_id' => $this->companyId(),
                'status' => 1,
            ]);

            $this->message = 'Created trade ' . $trade->name . '.';
        }

        $this->closeModals();
    }

    public function toggleTradeStatus(int $tradeId): void
    {
        abort_unless($this->canToggleTrade(), 404);

        $trade = Trade::findOrFail($tradeId);
        $trade->status = (int) !$trade->status;
        $trade->save();

        if (!$trade->status && !$this->showDisabled) {
            $this->openTradeIds = array_values(array_diff($this->openTradeIds, [$trade->id]));
        }

        $this->message = ($trade->status ? 'Enabled ' : 'Disabled ') . $trade->name . '.';
    }

    public function openAddTask(int $tradeId): void
    {
        abort_unless($this->canAddTask(), 404);
        abort_unless(Trade::whereKey($tradeId)->exists(), 404);

        $this->resetValidation();
        $this->editingTaskId = null;
        $this->taskTradeId = $tradeId;
        $this->taskName = '';
        $this->taskCode = '';
        $this->showTradeModal = false;
        $this->showTaskModal = true;
    }

    public function openEditTask(int $taskId): void
    {
        abort_unless($this->canEditTask(), 404);

        $task = $this->taskForCurrentCompany($taskId);

        $this->resetValidation();
        $this->editingTaskId = $task->id;
        $this->taskTradeId = $task->trade_id;
        $this->taskName = $task->name;
        $this->taskCode = $task->code;
        $this->showTradeModal = false;
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        $this->validate([
            'taskName' => ['required', 'string'],
            'taskCode' => ['required', 'string'],
            'taskTradeId' => ['required', 'integer'],
        ]);

        // Do not trust the trade ID returned by the modal even though it came from
        // a select list; Livewire requests can still be manually altered.
        abort_unless(Trade::whereKey($this->taskTradeId)->exists(), 404);

        if ($this->editingTaskId) {
            abort_unless($this->canEditTask(), 404);

            $task = $this->taskForCurrentCompany($this->editingTaskId);
            $task->update([
                'name' => trim($this->taskName),
                'code' => trim($this->taskCode),
            ]);

            $this->message = 'Saved ' . $task->name . '.';
        } else {
            abort_unless($this->canAddTask(), 404);

            $task = Task::create([
                'name' => trim($this->taskName),
                'code' => trim($this->taskCode),
                'trade_id' => $this->taskTradeId,
                'company_id' => $this->companyId(),
                'status' => 1,
                'upcoming' => 0,
            ]);

            $this->message = 'Created task ' . $task->name . '.';

            if (!in_array((int)$task->trade_id, $this->openTradeIds, true)) {
                $this->openTradeIds[] = (int)$task->trade_id;
            }
        }

        $this->closeModals();
    }

    public function toggleTaskStatus(int $taskId): void
    {
        abort_unless($this->canToggleTask(), 404);

        $task = $this->taskForCurrentCompany($taskId);
        $task->status = (int) !$task->status;
        $task->save();

        $this->message = ($task->status ? 'Enabled ' : 'Disabled ') . $task->name . '.';
    }

    public function toggleTaskUpcoming(int $taskId): void
    {
        abort_unless($this->canEditTask(), 404);

        $task = $this->taskForCurrentCompany($taskId);
        $task->upcoming = (int) !$task->upcoming;
        $task->save();
    }

    public function confirmDeleteTask(int $taskId): void
    {
        abort_unless($this->canDeleteTask(), 404);

        $task = $this->taskForCurrentCompany($taskId);

        // Used tasks are historical planner records. They may be disabled, but a
        // hard delete would make those past rows point to missing catalogue data.
        abort_if(
            SitePlanner::where('task_id', $task->id)->exists(),
            409,
            'This task has been used on the Planner and cannot be deleted.'
        );

        $this->deletingType = 'task';
        $this->deletingId = $task->id;
        $this->deletingName = $task->name;
        $this->showTradeModal = false;
        $this->showTaskModal = false;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteTrade(int $tradeId): void
    {
        abort_unless($this->canDeleteTrade(), 404);

        $trade = Trade::findOrFail($tradeId);
        $taskIds = Task::where('trade_id', $trade->id)->pluck('id');

        abort_if(
            $taskIds->isNotEmpty() && SitePlanner::whereIn('task_id', $taskIds)->exists(),
            409,
            'This trade has tasks that have been used on the Planner and cannot be deleted.'
        );

        $this->deletingType = 'trade';
        $this->deletingId = $trade->id;
        $this->deletingName = $trade->name;
        $this->showTradeModal = false;
        $this->showTaskModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        abort_unless($this->deletingId && in_array($this->deletingType, ['trade', 'task'], true), 404);

        if ($this->deletingType === 'task') {
            $this->deleteTask($this->deletingId);
        } else {
            $this->deleteTrade($this->deletingId);
        }

        $this->showDeleteModal = false;
        $this->deletingType = '';
        $this->deletingId = null;
        $this->deletingName = '';
    }

    protected function deleteTask(int $taskId): void
    {
        abort_unless($this->canDeleteTask(), 404);

        DB::transaction(function () use ($taskId) {
            // Repeat the usage check under a row lock: another request could have
            // scheduled the task after the confirmation modal was opened.
            $task = Task::query()
                ->whereKey($taskId)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                SitePlanner::where('task_id', $task->id)->exists(),
                409,
                'This task has been used on the Planner and cannot be deleted.'
            );

            $name = $task->name;
            $tradeId = (int) $task->trade_id;
            $task->delete();

            $this->message = 'Deleted unused task ' . $name . '.';

            if (!in_array($tradeId, $this->openTradeIds, true)) {
                $this->openTradeIds[] = $tradeId;
            }
        });
    }

    protected function deleteTrade(int $tradeId): void
    {
        abort_unless($this->canDeleteTrade(), 404);

        DB::transaction(function () use ($tradeId) {
            // Lock both the trade and its tasks so the cascade decision is atomic.
            $trade = Trade::query()->whereKey($tradeId)->lockForUpdate()->firstOrFail();

            $tasks = Task::query()
                ->where('trade_id', $trade->id)
                ->lockForUpdate()
                ->get(['id']);

            $taskIds = $tasks->pluck('id');

            abort_if(
                $taskIds->isNotEmpty() && SitePlanner::whereIn('task_id', $taskIds)->exists(),
                409,
                'This trade has tasks that have been used on the Planner and cannot be deleted.'
            );

            $name = $trade->name;

            // trades.trade_task and company_trade foreign keys both cascade
            // on Trade deletion. This is safe here because we explicitly block
            // deletion when any Task has ever been used by site_planner.
            $trade->delete();

            $this->openTradeIds = array_values(array_diff($this->openTradeIds, [$trade->id]));
            $this->message = 'Deleted unused trade ' . $name . '.';
        });
    }

    public function closeModals(): void
    {
        $this->showTradeModal = false;
        $this->showTaskModal = false;
        $this->showDeleteModal = false;
        $this->editingTradeId = null;
        $this->editingTaskId = null;
        $this->taskTradeId = null;
        $this->deletingId = null;
        $this->deletingType = '';
        $this->deletingName = '';
        $this->tradeName = '';
        $this->taskName = '';
        $this->taskCode = '';
        $this->resetValidation();
    }

    protected function taskForCurrentCompany(int $taskId): Task
    {
        return Task::query()
            ->whereKey($taskId)
            ->where('company_id', $this->companyId())
            ->firstOrFail();
    }

    protected function trades()
    {
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Trade::query()
            ->when(!$this->showDisabled, fn ($query) => $query->where('trades.status', 1))
            ->withCount([
                'tasks as visible_task_count' => function ($query) {
                    $query->where('company_id', $this->companyId());

                    if (!$this->showDisabled) {
                        $query->where('status', 1);
                    }
                },
            ])
            ->orderBy('name', $direction)
            ->get(['id', 'name', 'company_id', 'status']);
    }

    protected function tasksByTrade()
    {
        // Only query tasks for expanded rows; a large catalogue stays inexpensive
        // until the user actually opens a trade.
        $openTradeIds = array_values(array_unique(array_map('intval', $this->openTradeIds)));

        if (!$openTradeIds) {
            return collect();
        }

        return Task::query()
            ->whereIn('trade_id', $openTradeIds)
            ->where('company_id', $this->companyId())
            ->when(!$this->showDisabled, fn ($query) => $query->where('status', 1))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'upcoming', 'status', 'trade_id'])
            ->groupBy('trade_id');
    }

    public function render()
    {
        $trades = $this->trades();
        $tasksByTrade = $this->tasksByTrade();

        $visibleTaskIds = $tasksByTrade
            ->flatten(1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        // Usage maps let Blade hide delete controls without issuing a query from
        // every rendered task/trade row.
        $usedTaskIds = $visibleTaskIds->isEmpty()
            ? collect()
            : SitePlanner::whereIn('task_id', $visibleTaskIds)
                ->distinct()
                ->pluck('task_id')
                ->map(fn ($id) => (int) $id)
                ->flip();

        $tradeIds = $trades->pluck('id')->map(fn ($id) => (int) $id)->values();

        $usedTradeIds = $tradeIds->isEmpty()
            ? collect()
            : DB::table('trade_task')
                ->join('site_planner', 'site_planner.task_id', '=', 'trade_task.id')
                ->whereIn('trade_task.trade_id', $tradeIds)
                ->distinct()
                ->pluck('trade_task.trade_id')
                ->map(fn ($id) => (int) $id)
                ->flip();

        return view('livewire.planner.trade-list', [
            'trades' => $trades,
            'tasksByTrade' => $tasksByTrade,
            'usedTaskIds' => $usedTaskIds,
            'usedTradeIds' => $usedTradeIds,
            'canAddTrade' => $this->canAddTrade(),
            'canEditTrade' => $this->canEditTrade(),
            'canToggleTrade' => $this->canToggleTrade(),
            'canDeleteTrade' => $this->canDeleteTrade(),
            'canAddTask' => $this->canAddTask(),
            'canEditTask' => $this->canEditTask(),
            'canToggleTask' => $this->canToggleTask(),
            'canDeleteTask' => $this->canDeleteTask(),
        ]);
    }
}
