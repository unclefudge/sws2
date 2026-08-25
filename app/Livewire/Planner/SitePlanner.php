<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use App\Livewire\Planner\Concerns\InteractsWithPlannerTasks;
use App\Models\Site\Site;
use App\Services\Planner\PlannerDateService;
use App\Services\Planner\PlannerTaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SitePlanner extends Component
{
    use InteractsWithPlannerTasks;

    /**
     * Interactive Site Planner for one site across multiple weeks.
     *
     * Display data is locked and rebuilt from the database. Only the small form
     * values and modal flags that users interact with are browser-writable.
     */

    #[Locked]
    public ?string $date = null;

    #[Locked]
    public ?int $siteId = null;

    #[Locked]
    public ?string $supervisorId = null;

    #[Locked]
    public string $siteStart = 'week';

    #[Locked]
    public bool $preview = false;

    #[Locked]
    public string $plannerMode = 'site';

    #[Locked]
    public string $plannerTitle = 'Site Planner';

    #[Locked]
    public string $siteUrl = '/planner/site';

    #[Locked]
    public array $siteOptions = [];

    #[Locked]
    public array $plan = [];

    #[Locked]
    public array $weeks = [];

    #[Locked]
    public array $plannerVars = [];

    #[Locked]
    public array $holidays = [];

    #[Locked]
    public array $publicHolidayDates = [];

    #[Locked]
    public string $siteName = '';

    #[Locked]
    public int $siteStatus = 0;

    #[Locked]
    public bool $canEdit = false;

    #[Locked]
    public bool $isCc = false;

    #[Locked]
    public bool $canViewTradePlanner = false;

    #[Locked]
    public bool $canViewSitePlanner = false;

    #[Locked]
    public bool $canViewPreconstructionPlanner = false;

    #[Locked]
    public bool $canViewRoster = false;

    #[Locked]
    public bool $canViewWeeklyPlanner = false;

    #[Locked]
    public bool $canMoveToPreconstruction = false;

    #[Locked]
    public bool $canMoveJobStart = false;

    #[Locked]
    public bool $canManagePreconstruction = false;

    #[Locked]
    public array $tradeOptions = [];

    #[Locked]
    public int $noticeVersion = 0;

    public bool $showMoveConfirm = false;

    #[Locked]
    public array $pendingMove = [];

    #[Locked]
    public ?string $undoToken = null;

    public bool $showEditor = false;

    public bool $showClearSiteModal = false;

    public bool $showPreconstructionModal = false;

    public bool $showCancelPreconstructionSiteModal = false;

    #[Locked]
    public string $editorDate = '';

    #[Locked]
    public string $editorEntityType = '';

    #[Locked]
    public int $editorEntityId = 0;

    #[Locked]
    public string $editorEntityName = '';

    #[Locked]
    public array $editorTasks = [];

    #[Locked]
    public array $connectedTasks = [];

    #[Locked]
    public array $addTargets = [];

    #[Locked]
    public array $addTaskOptions = [];

    #[Locked]
    public array $reassignTargets = [];

    #[Locked]
    public int $reassignTaskId = 0;

    public $newTradeId = '';

    public $newTarget = '';

    public $newTaskId = '';

    public $reassignTarget = '';

    public string $reassignScope = '';

    public int $connectedMoveDays = 1;

    public int $siteMoveDays = 1;

    public function mount(?string $date = null, ?int $siteId = null, ?string $supervisorId = null, string $siteStart = 'week', bool $preview = false, string $plannerMode = 'site'): void
    {
        $this->plannerMode = $plannerMode === 'preconstruction' ? 'preconstruction' : 'site';
        abort_unless(Auth::user()->hasAnyPermissionType($this->plannerMode === 'preconstruction' ? 'preconstruction.planner' : 'site.planner'), 404);

        // Preserve the current date/site context in toolbar URLs while allowing a
        // separate preview route to coexist with the normal planner.
        $this->date = $this->validDate($date);
        $this->siteId = $siteId;
        $this->supervisorId = $supervisorId;
        $this->siteStart = in_array($siteStart, ['week', 'first', 'start'], true) ? $siteStart : 'week';
        $this->preview = $preview;
        $this->plannerTitle = $this->plannerMode === 'preconstruction' ? 'Pre-construction Planner' : 'Site Planner';
        $this->siteUrl = $this->plannerMode === 'preconstruction'
            ? '/planner/preconstruction'
            : ($preview ? '/planner/site-preview' : '/planner/site');

        $user = Auth::user();
        $this->isCc = (bool)$user->isCC();
        $this->canViewTradePlanner = (bool)$user->hasPermission2('view.trade.planner');
        $this->canViewSitePlanner = (bool)$user->hasPermission2('view.site.planner');
        $this->canViewPreconstructionPlanner = (bool)$user->hasPermission2('view.preconstruction.planner');
        $this->canViewRoster = (bool)$user->hasPermission2('view.roster');
        $this->canViewWeeklyPlanner = (bool)$user->hasPermission2('view.weekly.planner');
        $this->canMoveJobStart = (bool)$user->hasPermission2('edit.trade.planner');

        $this->loadSiteOptions();
        $this->loadTradeOptions();
        $this->loadPlanner();
    }

    public function activatePreconstructionSite()
    {
        abort_unless($this->canManagePreconstruction && $this->siteId && $this->siteStatus === -1, 403);

        // Keep the established controller workflow because it also sends the Job
        // Start email and creates the related project/todo records.
        return redirect("/planner/site/{$this->siteId}/status/1");
    }

    public function confirmCancelPreconstructionSite(): void
    {
        abort_unless($this->canManagePreconstruction && $this->siteId && $this->siteStatus === -1, 403);

        $this->showCancelPreconstructionSiteModal = true;
    }

    public function closeCancelPreconstructionSiteModal(): void
    {
        $this->showCancelPreconstructionSiteModal = false;
    }

    public function cancelPreconstructionSite()
    {
        abort_unless($this->showCancelPreconstructionSiteModal && $this->canManagePreconstruction && $this->siteId && $this->siteStatus === -1, 403);

        $this->showCancelPreconstructionSiteModal = false;

        // The existing status action performs the destructive cleanup after this
        // Livewire confirmation has made the consequences explicit.
        return redirect("/planner/site/{$this->siteId}/status/-2");
    }

    public function openEditor(string $entityType, int $entityId, string $date): void
    {
        // Only entities and dates present in the locked schedule can open an editor.
        abort_unless(in_array($entityType, ['c', 't'], true) && $this->validScheduleDate($date), 404);
        $name = $this->entityName($entityType, $entityId);
        abort_unless($name !== '', 404);

        $this->editorDate = $date;
        $this->editorEntityType = $entityType;
        $this->editorEntityId = $entityId;
        $this->editorEntityName = $name;
        $this->showEditor = true;
        $this->resetEditorInputs();
        $this->buildEditor();
        $this->loadContextAddTaskOptions();
    }

    public function openDayEditor(string $date): void
    {
        // A blank entity type represents every task on this site/date and powers
        // the site-wide connected and clear-schedule controls.
        abort_unless($this->validScheduleDate($date), 404);

        $this->editorDate = $date;
        $this->editorEntityType = '';
        $this->editorEntityId = 0;
        $this->editorEntityName = 'All site tasks';
        $this->showEditor = true;
        $this->resetEditorInputs();
        $this->buildEditor();
    }

    public function closeEditor(): void
    {
        $this->closePlannerDeleteModal();
        $this->closeClearSiteModal();
        $this->showEditor = false;
        $this->editorDate = '';
        $this->editorEntityType = '';
        $this->editorEntityId = 0;
        $this->editorEntityName = '';
        $this->editorTasks = [];
        $this->connectedTasks = [];
        $this->resetEditorInputs();
    }

    public function openMoveJobStart(?string $date = null): void
    {
        abort_unless($this->canMoveJobStart && $this->siteId, 403);

        $site = Site::findOrFail($this->siteId);
        abort_unless($site->jobStartTask, 404);

        $this->closeEditor();
        $this->dispatch('open-planner-job-action', action: 'move', siteId: $this->siteId, date: $date)->to(JobActions::class);
    }

    public function confirmMoveToPreconstruction(): void
    {
        // This locked flag is only true for an eligible active site with a future
        // start date, so a forged browser event cannot expose the destructive step.
        abort_unless($this->canMoveToPreconstruction && $this->siteId, 403);

        $this->showPreconstructionModal = true;
    }

    public function closePreconstructionModal(): void
    {
        $this->showPreconstructionModal = false;
    }

    public function moveToPreconstruction()
    {
        // Re-check the database immediately before handing off to the existing
        // status route, which performs the task and Project Supply cleanup.
        abort_unless($this->canMoveToPreconstruction && $this->siteId, 403);

        $site = Site::findOrFail($this->siteId);
        abort_unless((int)$site->status === 1, 403);

        $this->showPreconstructionModal = false;

        return redirect("/planner/site/{$this->siteId}/status/0");
    }

    #[On('planner-job-updated')]
    public function refreshAfterJobAction(string $message = ''): void
    {
        $this->closeEditor();
        $this->loadPlanner();
        $this->plannerMessage = $message;
        $this->noticeVersion++;
    }

    public function updatedNewTradeId(): void
    {
        $this->newTarget = '';
        $this->newTaskId = '';
        $this->addTargets = [];
        $this->addTaskOptions = [];

        if (!$this->newTradeId || !$this->siteId || !collect($this->tradeOptions)->contains('id', (int)$this->newTradeId)) {
            return;
        }

        // A trade can target its generic row or one of its assigned companies.
        $options = app(SitePlannerController::class)->getCompanies('match-trade', (int)$this->newTradeId, $this->siteId);
        $this->addTargets = $this->normaliseSelectOptions($options);
    }

    public function updatedNewTarget(): void
    {
        $this->newTaskId = '';
        $this->addTaskOptions = [];

        if (!$this->newTradeId || !$this->newTarget) {
            return;
        }

        $controller = app(SitePlannerController::class);
        $options = $this->newTarget === 'gen'
            ? $controller->getTradeTasks(request(), (int)$this->newTradeId)
            : $controller->getCompanyTasks(request(), (int)$this->newTarget, (int)$this->newTradeId);

        $this->addTaskOptions = $this->normaliseTaskOptions($options);
    }

    public function addPlannerTask(): void
    {
        abort_unless($this->editorCanEdit() && $this->siteId, 403);

        $taskId = (int)$this->newTaskId;
        $taskOption = collect($this->addTaskOptions)->firstWhere('id', $taskId);
        abort_unless($taskOption, 404);

        if ($this->editorEntityType !== '') {
            $tradeId = (int)($taskOption['trade_id'] ?? 0);
            $entityType = $this->editorEntityType;
            $entityId = $this->editorEntityId;
            abort_unless($tradeId && in_array($entityType, ['c', 't'], true), 404);
            abort_unless($entityType !== 't' || $entityId === $tradeId, 404);
        } else {
            $tradeId = (int)$this->newTradeId;
            abort_unless(collect($this->tradeOptions)->contains('id', $tradeId), 404);
            abort_unless(collect($this->addTargets)->contains(fn ($target) => (string)$target['value'] === (string)$this->newTarget), 404);
            abort_unless((int)($taskOption['trade_id'] ?? 0) === $tradeId, 404);

            // "gen" stores against the trade; a numeric target stores against the
            // selected company while retaining the task's trade relationship.
            $entityType = $this->newTarget === 'gen' ? 't' : 'c';
            $entityId = $this->newTarget === 'gen' ? $tradeId : (int)$this->newTarget;
        }

        $this->runPlannerAction(function (PlannerTaskService $service) use ($entityType, $entityId, $taskId) {
            $service->createForSitePlanner([
                'site_id' => $this->siteId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'task_id' => $taskId,
                'from' => $this->editorDate,
                'days' => 1,
            ]);

            $this->newTaskId = '';
            $this->plannerMessage = 'Task added.';
        });
    }

    public function dropPlannerTask(int $plannerTaskId, string $fromDate, string $date): void
    {
        abort_unless($this->validScheduleDate($date), 404);
        $this->preparePlannerTaskMove($plannerTaskId, $fromDate, $date);
    }

    public function preparePlannerTaskMove(int $plannerTaskId, string $fromDate, string $date): void
    {
        abort_unless($this->canEdit && $this->validScheduleDate($fromDate) && $this->dateCanEdit($fromDate), 403);
        abort_unless($this->dateCanDrop($date), 403);

        $task = collect($this->plan)->firstWhere('id', $plannerTaskId);
        abort_unless($task
            && (int)$task['site_id'] === $this->siteId
            && $fromDate >= (string)$task['from']
            && $fromDate <= (string)$task['to'], 404);

        if ((string)$task['task_code'] === 'START') {
            if ($this->canMoveJobStart) {
                $this->openMoveJobStart($date);
            } else {
                $this->plannerError = 'You do not have permission to move the Job Start schedule.';
            }

            return;
        }

        $this->plannerMessage = '';
        $this->plannerError = '';
        $this->pendingMove = [];
        $this->showMoveConfirm = false;

        try {
            // The service rejoins adjacent split rows for its calculation and works
            // out how many earlier days stay put when dragging from mid-task.
            $this->pendingMove = app(PlannerTaskService::class)->previewSegmentMove($plannerTaskId, $fromDate, $date);
            $this->showMoveConfirm = true;
        } catch (ValidationException $exception) {
            $this->plannerError = collect($exception->errors())->flatten()->first() ?: 'The planner move was not valid.';
        } catch (\DomainException $exception) {
            $this->plannerError = $exception->getMessage();
        }
    }

    public function cancelPlannerTaskMove(): void
    {
        $this->showMoveConfirm = false;
        $this->pendingMove = [];
    }

    public function confirmPlannerTaskMove(): void
    {
        abort_unless($this->showMoveConfirm && $this->pendingMove, 404);
        $pending = $this->pendingMove;
        $this->noticeVersion++;

        // The service repeats overlap/workday validation and returns a short-lived
        // undo snapshot only after the database transaction succeeds.
        $this->runPlannerAction(function (PlannerTaskService $service) use ($pending) {
            $result = $service->moveSegmentTo((int)$pending['task_id'], (string)$pending['source'], (string)$pending['target']);
            $this->storeMoveUndo((array)$result['undo']);
            $movedDays = (int)$result['preview']['moved_days'];
            $this->plannerMessage = $movedDays . ' task day' . ($movedDays === 1 ? '' : 's') . ' moved to ' . $this->formatDate($result['preview']['target'], 'D d/m/Y') . '.';
        });

        if (!$this->plannerError) {
            $this->cancelPlannerTaskMove();
        }
    }

    public function undoLastPlannerMove(): void
    {
        abort_unless($this->undoToken, 404);
        $token = $this->undoToken;
        // Undo data stays server-side so a database snapshot is never exposed in the
        // Livewire payload. Tokens expire quickly and are cleared after this attempt.
        $stored = session()->get($this->undoSessionKey($token));

        $this->runPlannerAction(function (PlannerTaskService $service) use ($stored) {
            if (!$stored || empty($stored['expires_at']) || (int)($stored['site_id'] ?? 0) !== $this->siteId || now()->greaterThan(Carbon::parse($stored['expires_at']))) {
                throw new \DomainException('The Undo time has expired.');
            }

            $service->undoSegmentMove((array)$stored['undo']);
            $this->plannerMessage = 'Task move undone.';
        });

        session()->forget($this->undoSessionKey($token));
        $this->undoToken = null;
        $this->noticeVersion++;
    }

    public function startReassign(int $plannerTaskId): void
    {
        abort_unless($this->editorCanEdit(), 403);
        $task = collect($this->editorTasks)->firstWhere('id', $plannerTaskId);
        abort_unless($task, 404);

        $this->reassignTaskId = $plannerTaskId;
        $this->reassignTarget = '';
        $this->reassignScope = '';
        $this->plannerMessage = '';
        $this->plannerError = '';
        $this->reassignTargets = $this->normaliseSelectOptions(
            app(SitePlannerController::class)->getCompanies('match-trade', (int)$task['trade_id'], $this->siteId)
        );
    }

    public function cancelReassign(): void
    {
        $this->reassignTaskId = 0;
        $this->reassignTarget = '';
        $this->reassignScope = '';
        $this->reassignTargets = [];
    }

    public function reassignPlannerTasks(): void
    {
        abort_unless($this->editorCanEdit() && $this->siteId, 403);
        abort_unless(in_array($this->reassignScope, ['task', 'all'], true), 422);

        $task = collect($this->editorTasks)->firstWhere('id', $this->reassignTaskId);
        abort_unless($task, 404);
        abort_unless(collect($this->reassignTargets)->contains(fn ($target) => (string)$target['value'] === (string)$this->reassignTarget), 404);

        $entityType = $this->reassignTarget === 'gen' ? 't' : 'c';
        $entityId = $this->reassignTarget === 'gen' ? (int)$task['trade_id'] : (int)$this->reassignTarget;

        $this->runPlannerAction(function (PlannerTaskService $service) use ($task, $entityType, $entityId) {
            if ($this->reassignScope === 'task') {
                $service->reassignTask($this->reassignTaskId, $entityType, $entityId);
                $count = 1;
            } else {
                $count = $service->reassignFrom($this->siteId, (int)$task['trade_id'], $this->editorDate, $entityType, $entityId, 'all');
            }

            $this->plannerMessage = $count . ' task' . ($count === 1 ? '' : 's') . ' reassigned.';
            $this->cancelReassign();
        });
    }

    public function confirmClearPlannerSite(): void
    {
        abort_unless($this->editorCanEdit() && $this->siteId, 403);

        $this->showClearSiteModal = true;
    }

    public function closeClearSiteModal(): void
    {
        $this->showClearSiteModal = false;
    }

    public function clearConfirmedPlannerSite(): void
    {
        abort_unless($this->showClearSiteModal && $this->editorCanEdit() && $this->siteId, 403);

        $this->closeClearSiteModal();

        // Clear from the selected date onwards; historical site tasks are retained.
        $this->runPlannerAction(function (PlannerTaskService $service) {
            $count = $service->deleteSiteFrom($this->siteId, $this->editorDate);
            $this->plannerMessage = $count . ' site task' . ($count === 1 ? '' : 's') . ' cleared.';
        });
    }

    public function editorCanEdit(): bool
    {
        return $this->canEdit && $this->editorDate !== '' && $this->dateCanEdit($this->editorDate);
    }

    public function formatDate(string $date, string $format = 'd/m/Y'): string
    {
        try {
            return Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->format($format);
        } catch (\Throwable) {
            return $date;
        }
    }

    public function plannerUrl(string $path, array $overrides = []): string
    {
        $params = array_merge([
            'date' => $this->date,
            'supervisor_id' => $this->supervisorId,
            'site_id' => $this->siteId,
            'site_start' => $this->siteStart,
        ], $overrides);

        $params = array_filter($params, fn ($value) => $value !== null && $value !== '');

        return $path . ($params ? '?' . http_build_query($params) : '');
    }

    protected function canEditPlannerTasks(): bool
    {
        return $this->canEdit && (!$this->showEditor || $this->editorCanEdit());
    }

    protected function canAccessPlannerTask(int $plannerTaskId): bool
    {
        $task = collect($this->plan)->firstWhere('id', $plannerTaskId);

        if (!$task || (int)$task['site_id'] !== $this->siteId) {
            return false;
        }

        return $this->showEditor
            ? $this->editorCanEdit() && $this->editorDate >= (string)$task['from'] && $this->editorDate <= (string)$task['to']
            : (string)$task['to'] >= Carbon::today()->format('Y-m-d');
    }

    protected function canAccessPlannerSite(int $siteId): bool
    {
        return $this->siteId === $siteId;
    }

    protected function canAccessPlannerTaskFrom(int $plannerTaskId, string $fromDate): bool
    {
        return $this->canAccessPlannerTask($plannerTaskId) && $fromDate === $this->editorDate;
    }

    protected function canAccessPlannerSiteFrom(int $siteId, string $fromDate): bool
    {
        return $this->canAccessPlannerSite($siteId) && $fromDate === $this->editorDate && $this->editorCanEdit();
    }

    protected function canAccessPlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate): bool
    {
        return $this->canAccessPlannerSiteFrom($siteId, $fromDate)
            && $entityType === $this->editorEntityType
            && $entityId === $this->editorEntityId;
    }

    protected function refreshPlannerData(): void
    {
        $this->loadPlanner();

        if ($this->showEditor) {
            $this->buildEditor();
        }
    }

    protected function loadSiteOptions(): void
    {
        $this->siteOptions = ['preconstruction' => [], 'active' => [], 'maintenance' => [], 'other' => []];

        if ($this->plannerMode === 'preconstruction') {
            // Match the original planner: only authorised sites with status -1
            // appear in this selector.
            foreach (Auth::user()->authSitesSelect('view.site.planner', '-1', 'prompt') as $id => $name) {
                if (is_numeric($id)) {
                    $this->siteOptions['preconstruction'][] = ['id' => (int)$id, 'name' => (string)$name];
                }
            }

            return;
        }

        foreach (['active' => [1, 'started'], 'maintenance' => [2, null]] as $group => [$status, $mode]) {
            $options = $mode
                ? Auth::user()->authSitesSelect('view.site.planner', $status, 'prompt', $mode)
                : Auth::user()->authSitesSelect('view.site.planner', $status, 'prompt');

            foreach ($options as $id => $name) {
                if (!is_numeric($id)) {
                    continue;
                }

                $this->siteOptions[$group][] = ['id' => (int)$id, 'name' => (string)$name];
            }
        }
    }

    protected function loadTradeOptions(): void
    {
        $this->tradeOptions = collect(app(SitePlannerController::class)->getTrades(request()))
            ->filter(fn ($option) => !empty($option['value']))
            ->map(fn ($option) => ['id' => (int)$option['value'], 'name' => (string)$option['text']])
            ->values()->all();
    }

    protected function loadPlanner(): void
    {
        // Reset all derived state first so switching sites cannot retain stale rows,
        // holiday dates or permissions from the previous selection.
        $this->plan = [];
        $this->weeks = [];
        $this->plannerVars = [];
        $this->siteName = '';
        $this->siteStatus = 0;
        $this->canEdit = false;
        $this->canManagePreconstruction = false;
        $this->canMoveToPreconstruction = false;
        $this->holidays = app(PlannerDateService::class)->holidays();
        $this->publicHolidayDates = collect($this->holidays)->keys()
            ->map(fn ($holiday) => Carbon::createFromFormat('Y-m-d', $holiday)->format('d/m/Y'))
            ->values()->all();

        if (!$this->siteId) {
            return;
        }

        $site = Site::findOrFail($this->siteId);
        abort_if($this->plannerMode === 'preconstruction' && (int)$site->status !== -1, 404);
        $this->siteName = (string)$site->name;
        $this->siteStatus = (int)$site->status;

        $siteIsAvailable = collect($this->siteOptions)->flatten(1)
            ->contains(fn ($option) => (int)$option['id'] === $this->siteId);

        // Pre-construction must honour the same authorised-site list as its
        // selector, including when somebody types a site ID into the URL.
        abort_if($this->plannerMode === 'preconstruction' && !$siteIsAvailable, 404);

        if (!$siteIsAvailable) {
            $this->siteOptions['other'][] = ['id' => $site->id, 'name' => $site->name];
        }

        // During migration this controller payload remains canonical for the plan,
        // leave/conflict lookups and the edit permission.
        $data = app(SitePlannerController::class)->getSitePlan(request(), $this->siteId);
        $this->plannerVars = $data[0] ?? [];
        $this->plan = $data[1] ?? [];
        $this->canEdit = $this->plannerMode === 'preconstruction'
            ? (bool)Auth::user()->hasPermission2('edit.preconstruction.planner')
            : (string)($data[4] ?? '') === 'edit';
        $this->holidays = $data[5] ?? $this->holidays;
        $this->publicHolidayDates = collect($this->holidays)->keys()
            ->map(fn ($holiday) => Carbon::createFromFormat('Y-m-d', $holiday)->format('d/m/Y'))
            ->values()->all();
        $this->canManagePreconstruction = $this->plannerMode === 'preconstruction'
            && $this->siteStatus === -1
            && (bool)Auth::user()->hasPermission2('edit.preconstruction.planner');
        $this->canMoveToPreconstruction = $this->plannerMode === 'site'
            && $this->canViewPreconstructionPlanner
            && $this->siteStatus === 1
            && !empty($this->plannerVars['start_date'])
            && (string)$this->plannerVars['start_date'] > Carbon::today()->format('Y-m-d');

        $this->buildSchedule($data[2] ?? [], $data[3] ?? []);
    }

    protected function buildSchedule(array $conflicts, array $leave): void
    {
        $dates = app(PlannerDateService::class);
        $thisMonday = Carbon::today()->startOfWeek();
        $first = $this->parsePlannerDate($this->plannerVars['first_date'] ?? null, $thisMonday);
        $start = $this->parsePlannerDate($this->plannerVars['start_date'] ?? null, $first);
        $final = $this->parsePlannerDate($this->plannerVars['final_date'] ?? null, $thisMonday);

        // The selector can anchor the long planner at today, the earliest task or
        // Job Start without changing which database records are loaded.
        $viewStart = (match ($this->siteStart) {
            'first' => $first,
            'start' => $start,
            default => $thisMonday,
        })->copy()->startOfWeek();

        $lastRelevant = $final->gt($thisMonday) ? $final : $thisMonday;
        $viewEnd = $lastRelevant->copy()->startOfWeek()->addWeeks(10);

        if ($viewEnd->lt($viewStart)) {
            $viewEnd = $viewStart->copy()->addWeeks(10);
        }

        $this->weeks = [];
        $week = $viewStart->copy();
        $guard = 0;

        // The guard prevents corrupt dates from generating an unbounded calendar.
        while ($week->lte($viewEnd) && $guard < 260) {
            $days = [];
            for ($offset = 0; $offset < 5; $offset++) {
                $day = $week->copy()->addDays($offset);
                $date = $day->format('Y-m-d');
                $days[] = [
                    'date' => $date,
                    'day' => $day->format('D'),
                    'label' => $day->format($day->year === Carbon::today()->year ? 'd/m' : 'd/m/y'),
                    'holiday' => (string)($this->holidays[$date] ?? ''),
                    'is_today' => $day->isToday(),
                    'past' => $day->lt(Carbon::today()),
                    'editable' => $this->canEdit && $date >= Carbon::today()->format('Y-m-d') && $dates->isWorkDay($date),
                    'droppable' => $this->canEdit && $date >= Carbon::today()->format('Y-m-d') && $dates->isWorkDay($date),
                ];
            }

            $this->weeks[] = [
                'key' => $week->format('Y-m-d'),
                'number' => $this->weekNumber($week, $start),
                'days' => $days,
                'rows' => $this->buildWeekRows($days, $conflicts, $leave, $dates),
            ];

            $week->addWeek();
            $guard++;
        }
    }

    protected function buildWeekRows(array $days, array $conflicts, array $leave, PlannerDateService $dates): array
    {
        $monday = $days[0]['date'];
        $friday = $days[4]['date'];
        $entities = [];

        // Only entities whose task touches this week receive a row. Date headings
        // remain visible even when the whole week has no tasks.
        foreach ($this->plan as $task) {
            if ((string)$task['from'] > $friday || (string)$task['to'] < $monday) {
                continue;
            }

            $key = (string)$task['entity_type'] . '.' . (int)$task['entity_id'];
            $entities[$key] ??= [
                'key' => $key,
                'type' => (string)$task['entity_type'],
                'id' => (int)$task['entity_id'],
                'name' => (string)($task['entity_name'] ?? 'Unassigned'),
            ];
        }

        uasort($entities, fn ($a, $b) => [$a['type'] === 't' ? 0 : 1, mb_strtolower($a['name'])] <=> [$b['type'] === 't' ? 0 : 1, mb_strtolower($b['name'])]);

        foreach ($entities as $key => $entity) {
            $entities[$key]['days'] = [];
            foreach ($days as $day) {
                $tasks = collect($this->plan)->filter(fn ($task) =>
                    (string)$task['entity_type'] === $entity['type']
                    && (int)$task['entity_id'] === $entity['id']
                    && $day['date'] >= (string)$task['from']
                    && $day['date'] <= (string)$task['to']
                )->map(fn ($task) => array_merge($task, [
                    'draggable' => $day['editable'] && (string)$task['task_code'] !== 'START',
                    'blocked_move_dates' => $this->blockedSegmentMoveDates($task, $day['date'], $dates),
                ]))->values()->all();

                $conflict = $entity['type'] === 'c' ? (string)($conflicts[$entity['id']][$day['date']] ?? '') : '';
                $onLeave = $entity['type'] === 'c' ? (string)($leave[$entity['id']][$day['date']] ?? '') : '';
                $entities[$key]['days'][] = array_merge($day, [
                    'tasks' => $tasks,
                    'conflict' => trim(strip_tags($conflict)),
                    'leave' => $onLeave,
                    'class' => $entity['type'] === 't' ? 'site-generic' : ($conflict !== '' ? 'site-conflict' : ''),
                ]);
            }
        }

        return array_values($entities);
    }

    protected function buildEditor(): void
    {
        $dates = app(PlannerDateService::class);
        // Collapse adjacent split database rows to the logical run users see, then
        // build the same overlap exclusions for drag/drop and the date picker.
        $this->editorTasks = collect($this->plan)->filter(function ($task) {
            if ($this->editorDate < (string)$task['from'] || $this->editorDate > (string)$task['to']) {
                return false;
            }

            return $this->editorEntityType === '' || (
                (string)$task['entity_type'] === $this->editorEntityType
                && (int)$task['entity_id'] === $this->editorEntityId
            );
        })->map(function ($task) use ($dates) {
            $task = $this->logicalTaskFromPlan($task, $dates);
            $blockedDates = $this->blockedSegmentMoveDates($task, $this->editorDate, $dates, false);

            return array_merge($task, [
                'blocked_move_dates' => $blockedDates,
                'picker_disabled_dates' => array_values(array_unique(array_merge(
                    $this->publicHolidayDates,
                    array_map(fn ($date) => $this->formatDate($date), $blockedDates),
                ))),
            ]);
        })->sortBy(fn ($task) => [(string)($task['entity_name'] ?? ''), (string)$task['task_name']])->values()->all();

        $this->connectedTasks = $this->editorEntityType === '' ? [] : $this->findConnectedTasks();
    }

    protected function findConnectedTasks(): array
    {
        $connected = [];
        $current = $this->editorDate;
        $dates = app(PlannerDateService::class);
        $guard = 0;

        // Walk one workday at a time until the entity has a gap. This uninterrupted
        // segment is what the connected move/remove controls operate on.
        while ($guard <= count($this->plan) + 5) {
            $found = collect($this->plan)->filter(fn ($task) =>
                (string)$task['entity_type'] === $this->editorEntityType
                && (int)$task['entity_id'] === $this->editorEntityId
                && $current >= (string)$task['from']
                && $current <= (string)$task['to']
            );

            if ($found->isEmpty()) {
                break;
            }

            foreach ($found as $task) {
                $connected[(int)$task['id']] = $task;
            }

            $current = $dates->shift($current, 1)->format('Y-m-d');
            $guard++;
        }

        return array_values($connected);
    }

    protected function dateCanEdit(string $date): bool
    {
        return $date >= Carbon::today()->format('Y-m-d') && app(PlannerDateService::class)->isWorkDay($date);
    }

    protected function dateCanDrop(string $date): bool
    {
        return $date >= Carbon::today()->format('Y-m-d') && app(PlannerDateService::class)->isWorkDay($date);
    }

    protected function blockedSegmentMoveDates(array $task, string $sourceDate, PlannerDateService $dates, bool $resolveLogicalTask = true): array
    {
        // Block targets where the moved portion would overlap days left behind or
        // another identical task for the same site and company/trade.
        if ($resolveLogicalTask) {
            $task = $this->logicalTaskFromPlan($task, $dates);
        }

        $source = $dates->parse($sourceDate);
        $taskFrom = $dates->parse((string)$task['from']);
        $taskTo = $dates->parse((string)$task['to']);
        $blocked = [$source->format('Y-m-d')];

        if (!$source->betweenIncluded($taskFrom, $taskTo)) {
            return $blocked;
        }

        $keptTo = $source->gt($taskFrom) ? $dates->shift($source, -1) : null;
        $keptDays = $keptTo ? $dates->workDaysBetween($taskFrom, $keptTo) : 0;
        $movedDays = max(1, (int)$task['days'] - $keptDays);

        if ($keptTo) {
            $target = $dates->shift($taskFrom, -($movedDays - 1));
            $guard = 0;

            while ($target->lte($keptTo) && $guard < 370) {
                $blocked[] = $target->format('Y-m-d');
                $target = $dates->shift($target, 1);
                $guard++;
            }
        }

        $taskIds = array_map('intval', (array)($task['logical_task_ids'] ?? [$task['id']]));
        collect($this->plan)->filter(fn ($other) =>
            !in_array((int)$other['id'], $taskIds, true)
            && (int)$other['site_id'] === (int)$task['site_id']
            && (string)$other['entity_type'] === (string)$task['entity_type']
            && (int)$other['entity_id'] === (int)$task['entity_id']
            && (int)$other['task_id'] === (int)$task['task_id']
            && (bool)($other['weekend'] ?? false) === (bool)($task['weekend'] ?? false)
        )->each(function ($other) use (&$blocked, $dates, $movedDays) {
            $target = $dates->shift((string)$other['from'], -($movedDays - 1));
            $lastTarget = $dates->parse((string)$other['to']);
            $guard = 0;

            while ($target->lte($lastTarget) && $guard < 370) {
                $blocked[] = $target->format('Y-m-d');
                $target = $dates->shift($target, 1);
                $guard++;
            }
        });

        return array_values(array_unique($blocked));
    }

    protected function logicalTaskFromPlan(array $selected, PlannerDateService $dates): array
    {
        // Job Start and the supervisor pre-check remain protected standalone rows;
        // ordinary adjacent identical rows are silently presented as one task.
        if (empty($selected['task_id']) || in_array((int)$selected['task_id'], [11, 264], true)
            || in_array((string)($selected['task_code'] ?? ''), ['START', 'STARTCarp'], true)) {
            return $selected;
        }

        $rows = collect($this->plan)->filter(fn ($task) =>
            (int)$task['site_id'] === (int)$selected['site_id']
            && (string)$task['entity_type'] === (string)$selected['entity_type']
            && (int)$task['entity_id'] === (int)$selected['entity_id']
            && (int)$task['task_id'] === (int)$selected['task_id']
            && (bool)($task['weekend'] ?? false) === (bool)($selected['weekend'] ?? false)
        )->sortBy(fn ($task) => (string)$task['from'] . '-' . str_pad((string)$task['id'], 12, '0', STR_PAD_LEFT));
        $group = collect();

        foreach ($rows as $task) {
            if ($group->isNotEmpty()) {
                $expected = $dates->shift((string)($group->last()['to']), 1)->format('Y-m-d');

                if ($expected !== (string)$task['from']) {
                    if ($group->contains(fn ($row) => (int)$row['id'] === (int)$selected['id'])) {
                        break;
                    }

                    $group = collect();
                }
            }

            $group->push($task);
        }

        if (!$group->contains(fn ($task) => (int)$task['id'] === (int)$selected['id']) || $group->count() < 2) {
            return array_merge($selected, ['logical_task_ids' => [(int)$selected['id']]]);
        }

        $days = $group->sum(fn ($task) => (int)$task['days']);

        return array_merge($selected, [
            'from' => (string)$group->first()['from'],
            'to' => $dates->endDate((string)($group->first()['from']), $days)->format('Y-m-d'),
            'days' => $days,
            'logical_task_ids' => $group->pluck('id')->map(fn ($id) => (int)$id)->all(),
        ]);
    }

    protected function validScheduleDate(string $date): bool
    {
        foreach ($this->weeks as $week) {
            if (collect($week['days'])->contains('date', $date)) {
                return true;
            }
        }

        return false;
    }

    protected function entityName(string $entityType, int $entityId): string
    {
        $task = collect($this->plan)->first(fn ($task) => (string)$task['entity_type'] === $entityType && (int)$task['entity_id'] === $entityId);

        return (string)($task['entity_name'] ?? '');
    }

    protected function normaliseSelectOptions(array $options): array
    {
        return collect($options)->filter(fn ($option) => (string)($option['value'] ?? '') !== '')
            ->map(fn ($option) => [
                'value' => (string)$option['value'],
                'name' => trim(strip_tags((string)($option['text'] ?? $option['name'] ?? 'Option'))),
            ])->values()->all();
    }

    protected function loadContextAddTaskOptions(): void
    {
        // Clicking an existing company/trade row already supplies its context, so
        // only valid task choices for that row are needed in the Add panel.
        if (!in_array($this->editorEntityType, ['c', 't'], true)) {
            return;
        }

        $controller = app(SitePlannerController::class);
        $options = $this->editorEntityType === 'c'
            ? $controller->getCompanyTasks(request(), $this->editorEntityId, 'all')
            : $controller->getTradeTasks(request(), $this->editorEntityId);

        $this->addTaskOptions = $this->normaliseTaskOptions($options);
    }

    protected function normaliseTaskOptions(array $options): array
    {
        return collect($options)
            ->filter(fn ($option) => !empty($option['value']) && (string)($option['code'] ?? '') !== 'START')
            ->map(function ($option) {
                $name = trim(strip_tags((string)($option['text'] ?? $option['name'] ?? 'Task')));
                $tradeName = trim(strip_tags((string)($option['trade_name'] ?? '')));

                if ($tradeName !== '' && str_starts_with($name, $tradeName . ':')) {
                    $name = $tradeName . ': ' . ltrim(substr($name, strlen($tradeName) + 1));
                }

                return [
                    'id' => (int)$option['value'],
                    'name' => $name,
                    'code' => (string)($option['code'] ?? ''),
                    'trade_id' => (int)($option['trade_id'] ?? 0),
                    'trade_name' => $tradeName,
                ];
            })->values()->all();
    }

    protected function resetEditorInputs(): void
    {
        $this->newTradeId = '';
        $this->newTarget = '';
        $this->newTaskId = '';
        $this->addTargets = [];
        $this->addTaskOptions = [];
        $this->reassignTaskId = 0;
        $this->reassignTarget = '';
        $this->reassignScope = '';
        $this->reassignTargets = [];
        $this->connectedMoveDays = 1;
        $this->siteMoveDays = 1;
        $this->plannerMessage = '';
        $this->plannerError = '';
    }

    protected function storeMoveUndo(array $undo): void
    {
        // Only the latest move is undoable. Keep its snapshot in the session rather
        // than sending database state to the browser.
        if ($this->undoToken) {
            session()->forget($this->undoSessionKey($this->undoToken));
        }

        $this->undoToken = Str::random(40);
        session()->put($this->undoSessionKey($this->undoToken), [
            'site_id' => $this->siteId,
            'expires_at' => now()->addSeconds(15)->toIso8601String(),
            'undo' => $undo,
        ]);
    }

    protected function undoSessionKey(string $token): string
    {
        return 'planner.site.undo.' . Auth::id() . '.' . $token;
    }

    protected function validDate(?string $date): string
    {
        try {
            return $date ? Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->startOfWeek()->format('Y-m-d') : Carbon::today()->startOfWeek()->format('Y-m-d');
        } catch (\Throwable) {
            return Carbon::today()->startOfWeek()->format('Y-m-d');
        }
    }

    protected function parsePlannerDate(?string $date, Carbon $fallback): Carbon
    {
        try {
            return $date ? Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->startOfDay() : $fallback->copy();
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    protected function weekNumber(Carbon $week, Carbon $start): string
    {
        $startMonday = new \DateTimeImmutable($start->copy()->startOfWeek()->format('Y-m-d'));
        $weekMonday = new \DateTimeImmutable($week->format('Y-m-d'));
        $days = (int)$startMonday->diff($weekMonday)->format('%r%a');
        $number = intdiv($days, 7);

        return (string)($number >= 0 ? $number + 1 : $number);
    }

    public function render()
    {
        return view('livewire.planner.site-planner');
    }
}
