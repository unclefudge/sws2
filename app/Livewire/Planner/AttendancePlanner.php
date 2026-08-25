<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AttendancePlanner extends Component
{
    /**
     * Single-site attendance view used by the secondary Roster page.
     * It reads the legacy controller data shape, then performs small roster writes
     * directly so expanding rows and ticking users remain fast in Livewire.
     */
    #[Locked]
    public string $date;

    #[Locked]
    public ?int $siteId = null;

    #[Locked]
    public ?string $supervisorId = null;

    #[Locked]
    public ?string $siteStart = null;

    #[Locked]
    public array $siteOptions = [];

    #[Locked]
    public array $rostered = [];

    #[Locked]
    public array $unrostered = [];

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

    public function mount(string $date, ?int $siteId = null, ?string $supervisorId = null, ?string $siteStart = null, bool $preview = false): void
    {
        $user = Auth::user();

        // Keep navigation context so links between planner screens return to the
        // same date/site instead of unexpectedly resetting the user's view.
        $this->date = $this->validDate($date);
        $this->siteId = $siteId;
        $this->supervisorId = $supervisorId;
        $this->siteStart = $siteStart;
        $this->preview = $preview;
        $this->userCompanyId = (int)$user->company_id;
        $this->canViewSitePlanner = (bool)$user->hasPermission2('view.site.planner');
        $this->canViewTradePlanner = (bool)$user->hasPermission2('view.trade.planner');
        $this->canViewPreconstructionPlanner = (bool)$user->hasPermission2('view.preconstruction.planner');
        $this->canViewWeeklyPlanner = (bool)$user->hasPermission2('view.weekly.planner');

        $this->loadPlanner();
    }

    public function changeSite(string $siteId): void
    {
        $selected = $siteId === '' ? null : (int)$siteId;
        abort_unless(array_key_exists((string)($selected ?? ''), $this->siteOptions), 422);

        $this->siteId = $selected;
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

    public function toggleRoster(int $userId): void
    {
        abort_unless($this->canManageToday() && $this->siteId, 403);

        // Resolve the user from the permission-filtered planner payload before any
        // database write; a raw user ID from the browser is not trusted.
        $user = $this->findRosterUser($userId);
        abort_unless($user !== null, 404);

        $roster = $this->findRoster($userId);
        $attended = $this->hasAttended($userId);

        if ($roster) {
            // Once attendance exists it is historical evidence, so the roster row
            // must no longer be removable from this screen.
            if (!$attended) {
                $roster->delete();
                $this->setRosterId($userId, 0);
            }
        } else {
            $roster = SiteRoster::create([
                'site_id' => $this->siteId,
                'user_id' => $userId,
                'date' => $this->date . ' 00:00:00',
            ]);
            $this->setRosterId($userId, (int)$roster->id);
        }
    }

    public function checkAll(string $entityKey, string $action): void
    {
        abort_unless($this->canManageToday() && $this->siteId, 403);
        abort_unless(in_array($action, ['add', 'delete'], true), 422);

        $entity = $this->findRosterEntity($entityKey);
        abort_unless($entity !== null, 404);

        // Apply the same attendance safeguard as a single tick to every visible
        // user in the selected company/trade row.
        foreach ($entity['attendance'] as $user) {
            $userId = (int)$user['user_id'];
            $roster = $this->findRoster($userId);
            $attended = $this->hasAttended($userId);

            if ($action === 'delete' && $roster && !$attended) {
                $roster->delete();
                $this->setRosterId($userId, 0);
            }

            if ($action === 'add' && !$roster) {
                $roster = SiteRoster::create([
                    'site_id' => $this->siteId,
                    'user_id' => $userId,
                    'date' => $this->date . ' 00:00:00',
                ]);
                $this->setRosterId($userId, (int)$roster->id);
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
        // These classes deliberately match the planner legend used throughout the
        // legacy application.
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

    public function isToday(): bool
    {
        return Carbon::createFromFormat('Y-m-d', $this->date)->isSameDay(Carbon::today());
    }

    public function canManageToday(): bool
    {
        return $this->userCompanyId === 3 && $this->isToday();
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
        // The controller remains the source of truth while the Vue pages and new
        // Livewire pages coexist; this adapter only reshapes its returned arrays.
        $planner = app(SitePlannerController::class)->getSiteAttendance($this->siteId ?: 'none', $this->date);

        $this->rostered = $planner[1] ?? [];
        $this->unrostered = $planner[2] ?? [];
        $this->permission = (string)($planner[4] ?? '');
        $this->siteOptions = [];

        foreach (($planner[3] ?? []) as $option) {
            if (array_key_exists('value', $option) && isset($option['text'])) {
                $this->siteOptions[(string)$option['value']] = (string)$option['text'];
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

    protected function findRosterEntity(string $entityKey): ?array
    {
        foreach ($this->rostered as $entity) {
            if ((string)$entity['key'] === $entityKey) {
                return $entity;
            }
        }

        return null;
    }

    protected function findRosterUser(int $userId): ?array
    {
        foreach ($this->rostered as $entity) {
            foreach ($entity['attendance'] as $user) {
                if ((int)$user['user_id'] === $userId) {
                    return $user;
                }
            }
        }

        return null;
    }

    protected function findRoster(int $userId): ?SiteRoster
    {
        return SiteRoster::where('site_id', $this->siteId)
            ->where('user_id', $userId)
            ->whereDate('date', $this->date)
            ->first();
    }

    protected function setRosterId(int $userId, int $rosterId): void
    {
        // Update the in-memory payload after a tick so Livewire can redraw without
        // repeating the relatively expensive complete attendance query.
        foreach ($this->rostered as &$entity) {
            foreach ($entity['attendance'] as &$user) {
                if ((int)$user['user_id'] === $userId) {
                    $user['roster_id'] = $rosterId;
                }
            }
            unset($user);
        }
        unset($entity);
    }

    protected function hasAttended(int $userId): bool
    {
        return SiteAttendance::where('site_id', $this->siteId)
            ->where('user_id', $userId)
            ->whereDate('date', $this->date)
            ->exists();
    }

    protected function allRosteredUsersOnsite(array $entity): bool
    {
        $rostered = false;

        // Blue is only valid when at least one rostered user attended and none of
        // the rostered users are still missing.
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
        foreach ($entity['attendance'] as $user) {
            if ($user['roster_id']) {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        return view('livewire.planner.attendance-planner');
    }
}
