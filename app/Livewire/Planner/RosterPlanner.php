<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RosterPlanner extends Component
{
    /**
     * Daily roster overview across all authorised sites.
     * This is intentionally parallel to AttendancePlanner, but includes site IDs
     * in every lookup because the same user can appear on more than one site row.
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
    public array $sites = [];

    #[Locked]
    public string $permission = '';

    #[Locked]
    public int $userCompanyId;

    #[Locked]
    public bool $preview = false;

    #[Locked]
    public bool $canViewSitePlanner = false;

    #[Locked]
    public bool $canViewTradePlanner = false;

    #[Locked]
    public bool $canViewPreconstructionPlanner = false;

    #[Locked]
    public bool $canViewWeeklyPlanner = false;

    public array $openEntities = [];

    public function mount(string $date, string $supervisorId = 'all', ?int $siteId = null, ?string $siteStart = null, array $supervisors = [], bool $preview = false): void
    {
        $user = Auth::user();

        // Preserve the surrounding planner filters for toolbar links and previews.
        $this->date = $this->validDate($date);
        $this->supervisorId = $supervisorId ?: 'all';
        $this->siteId = $siteId;
        $this->siteStart = $siteStart;
        $this->supervisors = $supervisors;
        $this->preview = $preview;
        $this->userCompanyId = (int)$user->company_id;
        $this->canViewSitePlanner = (bool)$user->hasPermission2('view.site.planner');
        $this->canViewTradePlanner = (bool)$user->hasPermission2('view.trade.planner');
        $this->canViewPreconstructionPlanner = (bool)$user->hasPermission2('view.preconstruction.planner');
        $this->canViewWeeklyPlanner = (bool)$user->hasPermission2('view.weekly.planner');

        $this->loadPlanner();
    }

    public function changeSupervisor(string $supervisorId): void
    {
        abort_unless(array_key_exists($supervisorId, $this->supervisors), 422);

        $this->supervisorId = $supervisorId;
        $this->openEntities = [];
        $this->loadPlanner();
    }

    public function changeDay(string $direction): void
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->date);

        if ($direction === 'previous') {
            $date->subDay();
        } elseif ($direction === 'next') {
            $date->addDay();
        } elseif ($direction === 'today') {
            $date = Carbon::today();
        } else {
            abort(422);
        }

        $this->date = $date->format('Y-m-d');
        $this->openEntities = [];
        $this->loadPlanner();
    }

    public function toggleEntity(string $key): void
    {
        if (in_array($key, $this->openEntities, true)) {
            $this->openEntities = array_values(array_diff($this->openEntities, [$key]));

            return;
        }

        $this->openEntities[] = $key;
    }

    public function toggleRoster(int $siteId, int $userId): void
    {
        abort_unless($this->canManageToday(), 403);

        // Only users present in the authorised controller payload can be changed.
        $user = $this->findRosterUser($siteId, $userId);
        abort_unless($user !== null, 404);

        $roster = $this->findRoster($siteId, $userId);
        $attended = $this->hasAttended($siteId, $userId);

        if ($roster) {
            // Attendance records must remain auditable; do not remove a roster row
            // after that person has actually checked in.
            if (!$attended) {
                $roster->delete();
                $this->setRosterId($siteId, $userId, 0);
            }
        } else {
            $roster = SiteRoster::create([
                'site_id' => $siteId,
                'user_id' => $userId,
                'date' => $this->date . ' 00:00:00',
            ]);
            $this->setRosterId($siteId, $userId, (int)$roster->id);
        }
    }

    public function checkAll(int $siteId, string $entityKey, string $action): void
    {
        abort_unless($this->canManageToday(), 403);
        abort_unless(in_array($action, ['add', 'delete'], true), 422);

        $entity = $this->findRosterEntity($siteId, $entityKey);
        abort_unless($entity !== null, 404);

        // Bulk ticking follows the exact same attendance protection as one user.
        foreach ($entity['attendance'] as $user) {
            $userId = (int)$user['user_id'];
            $roster = $this->findRoster($siteId, $userId);
            $attended = $this->hasAttended($siteId, $userId);

            if ($action === 'delete' && $roster && !$attended) {
                $roster->delete();
                $this->setRosterId($siteId, $userId, 0);
            }

            if ($action === 'add' && !$roster) {
                $roster = SiteRoster::create([
                    'site_id' => $siteId,
                    'user_id' => $userId,
                    'date' => $this->date . ' 00:00:00',
                ]);
                $this->setRosterId($siteId, $userId, (int)$roster->id);
            }
        }
    }

    public function isOpen(string $key): bool
    {
        return in_array($key, $this->openEntities, true);
    }

    public function canSeeEntity(array $entity): bool
    {
        return $this->userCompanyId === 3
            || ((int)$entity['entity_id'] === $this->userCompanyId && $entity['entity_type'] === 'c');
    }

    public function entityClass(array $entity): string
    {
        // Return the existing legend colour rather than introducing Livewire-only
        // colours that would make the old and new planners disagree.
        if ($entity['entity_type'] === 't') {
            return 'font-yellow-gold';
        }

        if ($this->allRosteredUsersOnsite($entity)) {
            return 'font-blue';
        }

        if ($this->plannedButNotRostered($entity)) {
            return 'font-purple';
        }

        return '';
    }

    public function dateLabel(): string
    {
        return Carbon::createFromFormat('Y-m-d', $this->date)->format('l jS F Y');
    }

    public function formatTime(?string $time, bool $meridiem = false): string
    {
        if (!$time) {
            return '';
        }

        try {
            return Carbon::createFromFormat('H:i:s', $time)->format($meridiem ? 'g:i a' : 'g:i');
        } catch (\Throwable) {
            return $time;
        }
    }

    public function isFuture(): bool
    {
        return Carbon::createFromFormat('Y-m-d', $this->date)->startOfDay()->gt(Carbon::today());
    }

    public function canManageToday(): bool
    {
        return $this->userCompanyId === 3 && $this->date === Carbon::today()->format('Y-m-d');
    }

    public function plannerUrl(string $path): string
    {
        $params = array_filter([
            'date' => $this->date,
            'supervisor_id' => $this->supervisorId,
            'site_id' => $this->siteId,
            'site_start' => $this->siteStart,
        ], fn ($value) => $value !== null && $value !== '');

        return $path . ($params ? '?' . http_build_query($params) : '');
    }

    protected function loadPlanner(): void
    {
        // Reuse the established controller query while migration is gradual; this
        // component owns interaction/state, not the underlying reporting rules.
        $planner = app(SitePlannerController::class)->getSiteRoster($this->date, $this->supervisorId);

        $this->sites = $planner[0] ?? [];
        $this->permission = (string)($planner[1] ?? '');

        foreach (($planner[2] ?? []) as $option) {
            if (isset($option['value'], $option['text'])) {
                $this->supervisors[(string)$option['value']] = (string)$option['text'];
            }
        }
    }

    protected function validDate(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
            } catch (\Throwable) {
                // Fall through to today.
            }
        }

        return Carbon::today()->format('Y-m-d');
    }

    protected function findRosterEntity(int $siteId, string $entityKey): ?array
    {
        foreach ($this->sites as $site) {
            if ((int)$site['id'] !== $siteId) {
                continue;
            }

            foreach ($site['roster'] as $entity) {
                if ((string)$entity['key'] === $entityKey) {
                    return $entity;
                }
            }
        }

        return null;
    }

    protected function findRosterUser(int $siteId, int $userId): ?array
    {
        foreach ($this->sites as $site) {
            if ((int)$site['id'] !== $siteId) {
                continue;
            }

            foreach ($site['roster'] as $entity) {
                foreach ($entity['attendance'] as $user) {
                    if ((int)$user['user_id'] === $userId) {
                        return $user;
                    }
                }
            }
        }

        return null;
    }

    protected function findRoster(int $siteId, int $userId): ?SiteRoster
    {
        return SiteRoster::where('site_id', $siteId)
            ->where('user_id', $userId)
            ->whereDate('date', $this->date)
            ->first();
    }

    protected function setRosterId(int $siteId, int $userId, int $rosterId): void
    {
        // Patch only the affected nested row so Livewire does not need a full reload
        // after every user tick.
        foreach ($this->sites as &$site) {
            if ((int)$site['id'] !== $siteId) {
                continue;
            }

            foreach ($site['roster'] as &$entity) {
                foreach ($entity['attendance'] as &$user) {
                    if ((int)$user['user_id'] === $userId) {
                        $user['roster_id'] = $rosterId;
                    }
                }
                unset($user);
            }
            unset($entity);
        }
        unset($site);
    }

    protected function hasAttended(int $siteId, int $userId): bool
    {
        return SiteAttendance::where('site_id', $siteId)
            ->where('user_id', $userId)
            ->whereDate('date', $this->date)
            ->exists();
    }

    protected function allRosteredUsersOnsite(array $entity): bool
    {
        $rostered = false;

        foreach ($entity['attendance'] as $user) {
            if ($user['roster_id'] && !$user['attended']) {
                return false;
            }

            if ($user['roster_id'] && $user['attended']) {
                $rostered = true;
            }
        }

        return $rostered;
    }

    protected function plannedButNotRostered(array $entity): bool
    {
        // An explicitly "Unrostered" row already has its own status and should not
        // be painted as a company that simply forgot to complete its roster.
        if ($entity['tasks'] === 'Unrostered') {
            return false;
        }

        if (count($entity['attendance']) === 0) {
            return true;
        }

        foreach ($entity['attendance'] as $user) {
            if ($user['roster_id'] && !$user['attended']) {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        return view('livewire.planner.roster-planner');
    }
}
