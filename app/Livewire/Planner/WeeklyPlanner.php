<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WeeklyPlanner extends Component
{
    /**
     * Read-only five-day overview grouped by site.
     * It deliberately reuses the established controller calculations for roster,
     * attendance, leave and maximum-job colours while Vue is being retired.
     */
    #[Locked]
    public string $date;

    #[Locked]
    public string $supervisorId;

    #[Locked]
    public ?int $siteId = null;

    #[Locked]
    public ?string $siteStart = null;

    #[Locked]
    public array $supervisors = [];

    #[Locked]
    public bool $preview = false;

    #[Locked]
    public string $weeklyUrl;

    #[Locked]
    public array $days = [];

    #[Locked]
    public array $weekOptions = [];

    #[Locked]
    public array $rows = [];

    #[Locked]
    public bool $isCc = false;

    #[Locked]
    public bool $showContact = false;

    #[Locked]
    public bool $showGuideWarning = false;

    #[Locked]
    public bool $canViewSitePlanner = false;

    #[Locked]
    public bool $canViewTradePlanner = false;

    #[Locked]
    public bool $canViewPreconstructionPlanner = false;

    #[Locked]
    public bool $canViewRoster = false;

    #[Locked]
    public bool $canViewNextWeek = false;

    #[Locked]
    public int $userCompanyId;

    #[Locked]
    public string $previousWeekUrl;

    #[Locked]
    public string $thisWeekUrl;

    #[Locked]
    public string $nextWeekUrl;

    public function mount(string $date, string $supervisorId = 'all', ?int $siteId = null, ?string $siteStart = null, array $supervisors = [], bool $preview = false): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('weekly.planner'), 404);

        // Normalise all URL state once so later date comparisons remain simple
        // ISO string comparisons (YYYY-MM-DD).
        $this->date = $this->validDate($date);
        $this->supervisorId = $supervisorId ?: 'all';
        $this->siteId = $siteId;
        $this->siteStart = $siteStart;
        $this->supervisors = $supervisors;
        $this->preview = $preview;
        $this->weeklyUrl = $preview ? '/planner/weekly-preview' : '/planner/weekly';

        $user = Auth::user();
        $this->userCompanyId = (int)$user->company_id;
        $this->isCc = (bool)$user->isCC();
        $this->showContact = (bool)$user->company->parent_company;
        $this->showGuideWarning = $this->showContact && (int)$user->company->reportsTo()->id === 3;
        $this->canViewSitePlanner = (bool)$user->hasPermission2('view.site.planner');
        $this->canViewTradePlanner = (bool)$user->hasPermission2('view.trade.planner');
        $this->canViewPreconstructionPlanner = (bool)$user->hasPermission2('view.preconstruction.planner');
        $this->canViewRoster = (bool)$user->hasPermission2('view.roster');

        $this->buildDates();
        $this->loadPlanner();
    }

    protected function validDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
            } catch (\Throwable) {
                // Fall through to the current week.
            }
        }

        return Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    protected function buildDates(): void
    {
        $current = Carbon::createFromFormat('Y-m-d', $this->date);
        $thisWeek = Carbon::now()->startOfWeek();

        // Planners are work-week based, so only Monday-Friday columns are built.
        for ($day = 0; $day < 5; $day++) {
            $date = $current->copy()->addDays($day);
            $this->days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'label' => $date->format('d/m'),
                'is_today' => $date->isToday(),
            ];
        }

        // Keep the legacy week picker range, including two previous weeks and the
        // upcoming weeks allowed to external companies.
        foreach ([-14, -7, 0, 7, 14, 21, 28, 35, 42, 49, 56] as $offset) {
            $week = $current->copy()->addDays($offset);
            $this->weekOptions[] = [
                'date' => $week->format('Y-m-d'),
                'label' => $this->weekLabel($week),
            ];
        }

        $this->previousWeekUrl = $this->plannerUrl($this->weeklyUrl, ['date' => $current->copy()->subWeek()->format('Y-m-d')]);
        $this->thisWeekUrl = $this->plannerUrl($this->weeklyUrl, ['date' => $thisWeek->format('Y-m-d')]);
        $this->nextWeekUrl = $this->plannerUrl($this->weeklyUrl, ['date' => $current->copy()->addWeek()->format('Y-m-d')]);
        $this->canViewNextWeek = $this->userCompanyId === 3 || $current->copy()->addWeek()->lt($thisWeek->copy()->addDays(62));
    }

    protected function weekLabel(Carbon $week): string
    {
        return $week->format('M d') . ' - ' . $week->copy()->addDays(4)->format('M d, Y');
    }

    protected function loadPlanner(): void
    {
        // The controller returns several keyed lookup tables. Keeping them separate
        // avoids repeating roster/leave/conflict queries for every visible cell.
        $controller = app(SitePlannerController::class);
        $sites = $controller->getSites();
        $planner = $controller->getWeeklyPlan(request(), $this->date, $this->supervisorId);

        $plan = $planner[0] ?? [];
        $nonRostered = $planner[1] ?? [];
        $maxJobs = $planner[2] ?? [];
        $leave = $planner[3] ?? [];
        $entityOnsite = $planner[4] ?? [];
        $permission = (string)($planner[6] ?? '');
        $holidays = $planner[7] ?? [];

        foreach ($this->days as $index => $day) {
            $this->days[$index]['holiday'] = $holidays[$day['date']] ?? '';
        }

        // Build one presentation row per visible site, then attach its five cells.
        foreach ($sites as $site) {
            if (!$this->showSite($site) || (string)($site['code'] ?? '') === '0007') {
                continue;
            }

            $row = $site;
            $row['name_short'] = mb_substr((string)$site['name'], 0, 20);
            $row['preconstruction_date'] = $this->preconstructionDate($site['start'] ?? '');
            $row['completion_formatted'] = $this->formattedDate($site['completion_date'] ?? '');
            $row['completion_soon'] = $this->completionWithinDays($site['completion_date'] ?? '', 10);
            $row['site_url'] = $this->plannerUrl('/planner/site', ['site_id' => $site['id']]);
            $row['search_name'] = mb_strtolower((string)$site['name']);
            $row['days'] = [];

            foreach ($this->days as $day) {
                $row['days'][] = $this->dayPlan(
                    (int)$site['id'],
                    $day['date'],
                    $plan,
                    $nonRostered,
                    $maxJobs,
                    $leave,
                    $entityOnsite,
                    $permission
                );
            }

            $this->rows[] = $row;
        }
    }

    protected function showSite(array $site): bool
    {
        if ($this->userCompanyId === 3 && $this->supervisorId === 'all') {
            return (int)$site['status'] === 1;
        }

        if ($this->userCompanyId !== 3 && $this->supervisorId === 'all') {
            return true;
        }

        if ($this->supervisorId === 'maint') {
            return (int)$site['status'] === 2;
        }

        if ($this->supervisorId === 'prac') {
            return (int)$site['order'] === 3;
        }

        return array_key_exists($this->supervisorId, $site['supervisors'] ?? []);
    }

    protected function dayPlan(int $siteId, string $date, array $plan, array $nonRostered, array $maxJobs, array $leave, array $entityOnsite, string $permission): array
    {
        $entities = [];

        // A multi-day database task is expanded into each matching day here. Tasks
        // from other sites are explicitly excluded before any colour calculation.
        foreach ($plan as $task) {
            if ((int)($task['site_id'] ?? 0) !== $siteId) {
                continue;
            }

            $from = substr((string)($task['from'] ?? ''), 0, 10);
            $to = substr((string)($task['to'] ?? ''), 0, 10);

            if ($date < $from || $date > $to) {
                continue;
            }

            $entityType = (string)$task['entity_type'];
            $entityId = (int)$task['entity_id'];
            $key = $entityType . '.' . $entityId;
            $taskCode = (string)($task['task_code'] ?? '');

            // Several tasks for the same company/trade share one cell heading.
            if (!isset($entities[$key])) {
                $conflict = $entityType === 'c' ? ($maxJobs[$entityId][$date] ?? '') : '';
                $onLeave = $entityType === 'c' ? ($leave[$entityId][$date] ?? '') : '';

                $entities[$key] = [
                    'key' => $key,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'entity_name' => mb_substr((string)($task['entity_name'] ?? ''), 0, 15),
                    'maintenance' => (bool)($task['maintenance'] ?? false),
                    'conflicts' => $conflict,
                    'leave' => $onLeave,
                    'tasks' => [],
                ];
            }

            $entities[$key]['tasks'][] = [
                'code' => $taskCode,
                'highlight' => in_array($taskCode, ['START', 'STARTCarp'], true),
            ];
        }

        krsort($entities, SORT_STRING);

        foreach ($entities as $key => $entity) {
            $entities[$key]['class'] = $this->entityClass($entity, $date, $siteId, $entityOnsite);
        }

        $past = Carbon::createFromFormat('Y-m-d', $date)->lt(Carbon::today()) && $permission === 'view';

        return [
            'date' => $date,
            'past' => $past,
            'entities' => array_values($entities),
            'non_rostered' => array_values($nonRostered[$siteId . '.' . $date] ?? []),
        ];
    }

    protected function entityClass(array $entity, string $date, int $siteId, array $entityOnsite): string
    {
        // Class precedence matches the legend: attendance state first, then future
        // conflict/leave indicators.
        if ($entity['entity_type'] === 't') {
            return 'font-yellow-gold';
        }

        $today = Carbon::today()->format('Y-m-d');
        $onsiteKey = $date . '.' . $siteId . '.' . $entity['entity_type'] . '.' . $entity['entity_id'];
        $onsite = (string)($entityOnsite[$onsiteKey] ?? '');

        if ($date === $today && $onsite === '1') {
            return 'font-blue';
        }

        if ($date < $today && $onsite === '0') {
            return 'font-red';
        }

        if ($date <= $today && $onsite === '-1') {
            return 'font-purple text-bold';
        }

        if ($entity['conflicts'] !== '' && $entity['leave'] === '') {
            return 'font-green-jungle';
        }

        if ($entity['leave'] !== '') {
            return 'label label-sm label-warning';
        }

        return '';
    }

    protected function preconstructionDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            $jobStart = Carbon::createFromFormat('Y-m-d', substr($date, 0, 10));
            $plannerDate = Carbon::createFromFormat('Y-m-d', $this->date);

            return $jobStart->gte($plannerDate) ? $jobStart->format('d/m/Y') : '';
        } catch (\Throwable) {
            return '';
        }
    }

    protected function formattedDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->format('d/m/Y');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function completionWithinDays(?string $date, int $days): bool
    {
        if (!$date) {
            return false;
        }

        try {
            $completion = Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->startOfDay();
            $today = Carbon::today();

            return $completion->gte($today) && $completion->lte($today->copy()->addDays($days));
        } catch (\Throwable) {
            return false;
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

    public function render()
    {
        return view('livewire.planner.weekly-planner');
    }
}
