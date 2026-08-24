<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use App\Livewire\Planner\Concerns\InteractsWithPlannerTasks;
use App\Models\Company\Company;
use App\Models\Site\Planner\Task;
use App\Services\Planner\PlannerDateService;
use App\Services\Planner\PlannerTaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TradePlanner extends Component
{
    use InteractsWithPlannerTasks;

    #[Locked]
    public string $date;

    #[Locked]
    public ?int $tradeId = null;

    #[Locked]
    public ?int $siteId = null;

    #[Locked]
    public ?string $supervisorId = null;

    #[Locked]
    public ?string $siteStart = null;

    #[Locked]
    public bool $preview = false;

    #[Locked]
    public string $tradeUrl;

    #[Locked]
    public string $tradeName = '';

    #[Locked]
    public array $tradeOptions = [];

    #[Locked]
    public array $days = [];

    #[Locked]
    public array $weekOptions = [];

    #[Locked]
    public array $rows = [];

    #[Locked]
    public array $plan = [];

    #[Locked]
    public array $sites = [];

    #[Locked]
    public array $upcoming = [];

    #[Locked]
    public array $publicHolidayDates = [];

    #[Locked]
    public array $permission = [];

    #[Locked]
    public bool $canEdit = false;

    #[Locked]
    public bool $isCc = false;

    #[Locked]
    public bool $canViewSitePlanner = false;

    #[Locked]
    public bool $canViewPreconstructionPlanner = false;

    #[Locked]
    public bool $canViewRoster = false;

    #[Locked]
    public bool $canViewWeeklyPlanner = false;

    #[Locked]
    public string $previousWeekUrl;

    #[Locked]
    public string $thisWeekUrl;

    #[Locked]
    public string $nextWeekUrl;

    public bool $showEditor = false;

    #[Locked]
    public string $editorEntityType = '';

    #[Locked]
    public int $editorEntityId = 0;

    #[Locked]
    public string $editorEntityName = '';

    #[Locked]
    public string $editorDate = '';

    #[Locked]
    public array $editorTasks = [];

    #[Locked]
    public array $editorSites = [];

    #[Locked]
    public array $availableTasks = [];

    #[Locked]
    public array $reassignTargets = [];

    #[Locked]
    public int $reassignTaskId = 0;

    public $newSiteId = '';

    public $newTaskId = '';

    public $reassignTarget = '';

    public string $reassignScope = '';

    public int $connectedMoveDays = 1;

    public function mount(string $date, ?int $tradeId = null, ?int $siteId = null, ?string $supervisorId = null, ?string $siteStart = null, bool $preview = false): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('trade.planner'), 404);

        $this->date = $this->validDate($date);
        $this->tradeId = $tradeId;
        $this->siteId = $siteId;
        $this->supervisorId = $supervisorId;
        $this->siteStart = $siteStart;
        $this->preview = $preview;
        $this->tradeUrl = $preview ? '/planner/trade-preview' : '/planner/trade';
        $this->publicHolidayDates = collect(app(PlannerDateService::class)->holidays())
            ->keys()
            ->map(fn ($holiday) => Carbon::createFromFormat('Y-m-d', $holiday)->format('d/m/Y'))
            ->values()
            ->all();

        $user = Auth::user();
        $this->isCc = (bool)$user->isCC();
        $this->canViewSitePlanner = (bool)$user->hasPermission2('view.site.planner');
        $this->canViewPreconstructionPlanner = (bool)$user->hasPermission2('view.preconstruction.planner');
        $this->canViewRoster = (bool)$user->hasPermission2('view.roster');
        $this->canViewWeeklyPlanner = (bool)$user->hasPermission2('view.weekly.planner');

        $this->loadTradeOptions();
        $this->buildDates();
        $this->loadPlanner();
    }

    public function openEditor(string $entityType, int $entityId, string $date): void
    {
        $row = collect($this->rows)->first(fn ($row) => $row['type'] === $entityType && (int)$row['id'] === $entityId);
        abort_unless($row && collect($this->days)->contains('date', $date), 404);

        $this->editorEntityType = $entityType;
        $this->editorEntityId = $entityId;
        $this->editorEntityName = (string)$row['name'];
        $this->editorDate = $date;
        $this->showEditor = true;
        $this->resetEditorInputs();
        $this->buildEditor();
    }

    public function openUpcomingTask(int $plannerTaskId): void
    {
        $task = collect($this->upcoming)->pluck('plans')->flatten(1)->firstWhere('id', $plannerTaskId);
        abort_unless($task, 404);

        $this->editorEntityType = (string)$task['entity_type'];
        $this->editorEntityId = (int)$task['entity_id'];
        $this->editorEntityName = (string)$task['entity_name'];
        $this->editorDate = (string)$task['from'];
        $this->showEditor = true;
        $this->resetEditorInputs();
        $this->buildEditor();
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->editorTasks = [];
        $this->editorSites = [];
        $this->resetEditorInputs();
    }

    public function addPlannerTask(): void
    {
        abort_unless($this->editorCanEdit(), 403);

        $siteId = (int)$this->newSiteId;
        $taskId = (int)$this->newTaskId;
        abort_unless(collect($this->sites)->contains(fn ($site) => (int)$site['id'] === $siteId), 404);
        abort_unless(collect($this->availableTasks)->contains(fn ($task) => (int)$task['id'] === $taskId), 404);

        $this->runPlannerAction(function (PlannerTaskService $service) use ($siteId, $taskId) {
            $service->create([
                'site_id' => $siteId,
                'entity_type' => $this->editorEntityType,
                'entity_id' => $this->editorEntityId,
                'task_id' => $taskId,
                'from' => $this->editorDate,
                'days' => 1,
            ]);

            $this->newSiteId = '';
            $this->newTaskId = '';
            $this->plannerMessage = 'Task added.';
        });
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
        $this->loadReassignTargets((int)$task['site_id'], (int)$task['trade_id']);
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
        abort_unless($this->editorCanEdit(), 403);
        abort_unless(in_array($this->reassignScope, ['task', 'all'], true), 422);

        $task = collect($this->editorTasks)->firstWhere('id', $this->reassignTaskId);
        abort_unless($task, 404);

        $siteId = (int)$task['site_id'];
        $tradeId = (int)$task['trade_id'];
        abort_unless(collect($this->reassignTargets)->contains(fn ($target) => (string)$target['value'] === (string)$this->reassignTarget), 404);

        $entityType = $this->reassignTarget === 'gen' ? 't' : 'c';
        $entityId = $this->reassignTarget === 'gen' ? $tradeId : (int)$this->reassignTarget;

        $this->runPlannerAction(function (PlannerTaskService $service) use ($siteId, $tradeId, $entityType, $entityId) {
            if ($this->reassignScope === 'task') {
                $service->reassignTask($this->reassignTaskId, $entityType, $entityId);
                $count = 1;
            } else {
                $count = $service->reassignFrom($siteId, $tradeId, $this->editorDate, $entityType, $entityId, 'all');
            }

            $this->plannerMessage = $count . ' task' . ($count === 1 ? '' : 's') . ' reassigned.';
            $this->cancelReassign();
        });
    }

    public function editorCanEdit(): bool
    {
        if (!$this->canEdit || !$this->editorDate) {
            return false;
        }

        return Carbon::createFromFormat('Y-m-d', $this->editorDate)->startOfDay()->gte(Carbon::today());
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
            'trade_id' => $this->tradeId,
            'supervisor_id' => $this->supervisorId,
            'site_id' => $this->siteId,
            'site_start' => $this->siteStart,
        ], $overrides);

        $params = array_filter($params, fn ($value) => $value !== null && $value !== '');

        return $path . ($params ? '?' . http_build_query($params) : '');
    }

    protected function canEditPlannerTasks(): bool
    {
        return $this->editorCanEdit();
    }

    protected function canAccessPlannerTask(int $plannerTaskId): bool
    {
        return $this->showEditor
            && collect($this->editorTasks)->contains(fn ($task) => (int)$task['id'] === $plannerTaskId);
    }

    protected function canAccessPlannerSite(int $siteId): bool
    {
        return $this->showEditor
            && collect($this->editorSites)->contains(fn ($site) => (int)$site['id'] === $siteId);
    }

    protected function canAccessPlannerTaskFrom(int $plannerTaskId, string $fromDate): bool
    {
        return $this->canAccessPlannerTask($plannerTaskId) && $fromDate === $this->editorDate;
    }

    protected function canAccessPlannerSiteFrom(int $siteId, string $fromDate): bool
    {
        return $this->canAccessPlannerSite($siteId) && $fromDate === $this->editorDate;
    }

    protected function canAccessPlannerEntity(int $siteId, string $entityType, int $entityId, string $fromDate): bool
    {
        return $this->canAccessPlannerSite($siteId)
            && $entityType === $this->editorEntityType
            && $entityId === $this->editorEntityId
            && $fromDate === $this->editorDate;
    }

    protected function refreshPlannerData(): void
    {
        $this->loadPlanner();

        if ($this->showEditor) {
            $this->buildEditor();
        }
    }

    protected function validDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date)->startOfWeek()->format('Y-m-d');
            } catch (\Throwable) {
                // Fall through to the current week.
            }
        }

        return Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    protected function loadTradeOptions(): void
    {
        $options = Auth::user()->company->tradeListPlannerSelect();

        foreach ($options as $id => $name) {
            $this->tradeOptions[] = ['id' => (int)$id, 'name' => (string)$name];
        }

        if (!$this->tradeId && $this->isCc) {
            $this->tradeId = 2;
        }

        if ($this->tradeId && !collect($this->tradeOptions)->contains('id', $this->tradeId)) {
            $this->tradeId = null;
        }

        $this->tradeName = (string)(collect($this->tradeOptions)->firstWhere('id', $this->tradeId)['name'] ?? 'Trade');
    }

    protected function buildDates(): void
    {
        $current = Carbon::createFromFormat('Y-m-d', $this->date);
        $thisWeek = Carbon::now()->startOfWeek();

        $this->days = [];
        $this->weekOptions = [];

        for ($day = 0; $day < 5; $day++) {
            $date = $current->copy()->addDays($day);
            $this->days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'label' => $date->format('d/m'),
                'is_today' => $date->isToday(),
                'holiday' => '',
            ];
        }

        foreach ([-14, -7, 0, 7, 14, 21, 28, 35, 42, 49, 56] as $offset) {
            $week = $current->copy()->addDays($offset);
            $this->weekOptions[] = [
                'date' => $week->format('Y-m-d'),
                'label' => $week->format('M d') . ' - ' . $week->copy()->addDays(4)->format('M d, Y'),
            ];
        }

        $this->previousWeekUrl = $this->plannerUrl($this->tradeUrl, ['date' => $current->copy()->subWeek()->format('Y-m-d')]);
        $this->thisWeekUrl = $this->plannerUrl($this->tradeUrl, ['date' => $thisWeek->format('Y-m-d')]);
        $this->nextWeekUrl = $this->plannerUrl($this->tradeUrl, ['date' => $current->copy()->addWeek()->format('Y-m-d')]);
    }

    protected function loadPlanner(): void
    {
        $controller = app(SitePlannerController::class);
        $planner = $controller->getWeeklyPlan(request(), $this->date, 'alltrade');

        $this->plan = $planner[0] ?? [];
        $conflicts = $planner[2] ?? [];
        $leave = $planner[3] ?? [];
        $this->permission = ['level' => (string)($planner[6] ?? '')];
        $this->canEdit = $this->permission['level'] === 'edit' && (bool)Auth::user()->hasPermission2('edit.trade.planner');

        foreach ($this->days as $index => $day) {
            $this->days[$index]['holiday'] = (string)(($planner[7] ?? [])[$day['date']] ?? '');
        }

        $this->sites = $controller->getSites();
        $this->buildRows($controller, $conflicts, $leave);
        $this->buildUpcoming($controller);
    }

    protected function buildRows(SitePlannerController $controller, array $conflicts, array $leave): void
    {
        $this->rows = [];

        if (!$this->tradeId) {
            return;
        }

        $entities = [[
            'entity' => 't.' . $this->tradeId,
            'type' => 't',
            'id' => $this->tradeId,
            'name' => $this->tradeName,
            'compliant' => 1,
        ]];

        $entities = array_merge($entities, $controller->getCompaniesWithTrade(request(), $this->tradeId));

        foreach ($entities as $entity) {
            $row = [
                'key' => (string)$entity['entity'],
                'type' => (string)$entity['type'],
                'id' => (int)$entity['id'],
                'name' => (string)$entity['name'],
                'compliant' => (int)($entity['compliant'] ?? 1),
                'leave_summary' => (string)($leave[(int)$entity['id']]['summary'] ?? ''),
                'days' => [],
            ];

            foreach ($this->days as $day) {
                $row['days'][] = $this->buildDayCell($row, $day['date'], $conflicts, $leave);
            }

            $this->rows[] = $row;
        }
    }

    protected function buildDayCell(array $entity, string $date, array $conflicts, array $leave): array
    {
        $sites = [];

        foreach ($this->plan as $task) {
            if ((string)$task['entity_type'] !== $entity['type'] || (int)$task['entity_id'] !== $entity['id']) {
                continue;
            }

            if ($date < substr((string)$task['from'], 0, 10) || $date > substr((string)$task['to'], 0, 10)) {
                continue;
            }

            $siteId = (int)$task['site_id'];

            if (!isset($sites[$siteId])) {
                $sites[$siteId] = [
                    'id' => $siteId,
                    'name' => (string)$task['site_name'],
                    'maintenance' => (bool)($task['maintenance'] ?? false),
                    'tasks' => [],
                ];
            }

            $sites[$siteId]['tasks'][] = [
                'id' => (int)$task['id'],
                'code' => (string)$task['task_code'],
                'highlight' => in_array((string)$task['task_code'], ['START', 'STARTCarp'], true),
            ];
        }

        uasort($sites, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        $conflict = $entity['type'] === 'c' ? (string)($conflicts[$entity['id']][$date] ?? '') : '';
        $onLeave = $entity['type'] === 'c' ? (string)($leave[$entity['id']][$date] ?? '') : '';

        return [
            'date' => $date,
            'sites' => array_values($sites),
            'conflict' => $conflict,
            'leave' => $onLeave,
            'editable' => $this->canEdit && Carbon::createFromFormat('Y-m-d', $date)->gte(Carbon::today()),
            'class' => $entity['type'] === 't' ? 'font-yellow-gold' : ($conflict !== '' ? 'font-green-jungle' : ''),
        ];
    }

    protected function buildUpcoming(SitePlannerController $controller): void
    {
        $this->upcoming = [];

        if (!$this->tradeId) {
            return;
        }

        $data = $controller->getUpcomingTasks($this->date);
        $plans = $data[1] ?? [];

        foreach (($data[0] ?? []) as $category) {
            if ((int)$category['trade_id'] !== $this->tradeId) {
                continue;
            }

            $categoryPlans = array_values(array_filter($plans, fn ($task) => (int)$task['task_id'] === (int)$category['id']));

            $this->upcoming[] = [
                'id' => (int)$category['id'],
                'name' => (string)$category['name'],
                'plans' => $categoryPlans,
            ];
        }
    }

    protected function buildEditor(): void
    {
        $this->editorTasks = [];
        $this->editorSites = [];
        $tasks = array_merge($this->plan, collect($this->upcoming)->pluck('plans')->flatten(1)->all());
        $dates = app(PlannerDateService::class);

        foreach ($tasks as $task) {
            if ((string)$task['entity_type'] !== $this->editorEntityType || (int)$task['entity_id'] !== $this->editorEntityId) {
                continue;
            }

            if ($this->editorDate < substr((string)$task['from'], 0, 10) || $this->editorDate > substr((string)$task['to'], 0, 10)) {
                continue;
            }

            $task = $this->logicalTaskFromRows($task, $tasks, $dates);
            $task['picker_disabled_dates'] = array_values(array_unique(array_merge(
                $this->publicHolidayDates,
                $this->matchingTaskOverlapDates($task, $tasks, $dates),
            )));
            $this->editorTasks[(int)$task['id']] = $task;
            $this->editorSites[(int)$task['site_id']] = [
                'id' => (int)$task['site_id'],
                'name' => (string)$task['site_name'],
            ];
        }

        $this->editorTasks = array_values($this->editorTasks);
        usort($this->editorTasks, fn ($a, $b) => [$a['site_name'], $a['task_name']] <=> [$b['site_name'], $b['task_name']]);
        $this->editorSites = array_values($this->editorSites);
        usort($this->editorSites, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $this->loadAvailableTasks();
    }

    protected function logicalTaskFromRows(array $selected, array $tasks, PlannerDateService $dates): array
    {
        if (empty($selected['task_id']) || in_array((int)$selected['task_id'], [11, 264], true)
            || in_array((string)($selected['task_code'] ?? ''), ['START', 'STARTCarp'], true)) {
            return $selected;
        }

        $rows = collect($tasks)->filter(fn ($task) =>
            (int)$task['site_id'] === (int)$selected['site_id']
            && (string)$task['entity_type'] === (string)$selected['entity_type']
            && (int)$task['entity_id'] === (int)$selected['entity_id']
            && (int)$task['task_id'] === (int)$selected['task_id']
            && (bool)($task['weekend'] ?? false) === (bool)($selected['weekend'] ?? false)
        )->sortBy(fn ($task) => substr((string)$task['from'], 0, 10) . '-' . str_pad((string)$task['id'], 12, '0', STR_PAD_LEFT));
        $group = collect();

        foreach ($rows as $task) {
            if ($group->isNotEmpty()) {
                $expected = $dates->shift((string)($group->last()['to']), 1)->format('Y-m-d');

                if ($expected !== substr((string)$task['from'], 0, 10)) {
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
            'from' => substr((string)$group->first()['from'], 0, 10),
            'to' => $dates->endDate((string)($group->first()['from']), $days)->format('Y-m-d'),
            'days' => $days,
            'logical_task_ids' => $group->pluck('id')->map(fn ($id) => (int)$id)->all(),
        ]);
    }

    protected function matchingTaskOverlapDates(array $task, array $tasks, PlannerDateService $dates): array
    {
        $blocked = [];
        $taskIds = array_map('intval', (array)($task['logical_task_ids'] ?? [$task['id']]));
        $movedDays = max(1, (int)$task['days']);

        collect($tasks)->filter(fn ($other) =>
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
                $blocked[] = $target->format('d/m/Y');
                $target = $dates->shift($target, 1);
                $guard++;
            }
        });

        return $blocked;
    }

    protected function loadAvailableTasks(): void
    {
        $tradeIds = [];

        if ($this->editorEntityType === 't') {
            $tradeIds = [$this->editorEntityId];
        } elseif ($this->editorEntityType === 'c') {
            $company = Company::find($this->editorEntityId);
            $tradeIds = $company ? $company->tradesSkilledIn->pluck('id')->map(fn ($id) => (int)$id)->all() : [];
        }

        $trades = collect($this->tradeOptions)->keyBy('id');
        $multipleTrades = count($tradeIds) > 1;

        $this->availableTasks = Task::where('status', 1)
            ->whereIn('trade_id', $tradeIds)
            ->orderBy('name')
            ->get()
            ->map(function (Task $task) use ($trades, $multipleTrades) {
                $tradeName = (string)($trades->get((int)$task->trade_id)['name'] ?? $task->trade?->name ?? '');

                return [
                    'id' => (int)$task->id,
                    'name' => $multipleTrades && $tradeName ? $tradeName . ': ' . $task->name : (string)$task->name,
                    'code' => (string)$task->code,
                    'trade_id' => (int)$task->trade_id,
                ];
            })
            ->all();
    }

    protected function loadReassignTargets(int $siteId, int $tradeId): void
    {
        $this->reassignTargets = [];
        $this->reassignTarget = '';
        $options = app(SitePlannerController::class)->getCompanies('match-trade', $tradeId, $siteId);

        foreach ($options as $option) {
            if ((string)$option['value'] === '') {
                continue;
            }

            $this->reassignTargets[] = [
                'value' => (string)$option['value'],
                'name' => trim(strip_tags((string)$option['text'])),
            ];
        }
    }

    protected function resetEditorInputs(): void
    {
        $this->newSiteId = '';
        $this->newTaskId = '';
        $this->reassignTaskId = 0;
        $this->reassignTarget = '';
        $this->reassignScope = '';
        $this->reassignTargets = [];
        $this->connectedMoveDays = 1;
        $this->plannerMessage = '';
        $this->plannerError = '';
    }

    public function render()
    {
        return view('livewire.planner.trade-planner');
    }
}
