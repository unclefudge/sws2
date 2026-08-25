<?php

namespace App\Livewire\Planner;

use App\Http\Controllers\Site\Planner\SitePlannerController;
use App\Models\Site\Site;
use App\Services\Planner\PlannerDateService;
use App\Services\Planner\PlannerJobService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class JobActions extends Component
{
    /**
     * Shared Job Start actions used by the Trade and Site planners.
     *
     * The component keeps these higher-risk bulk actions in one place so their
     * permissions, validation and linked-task rules cannot drift between pages.
     */
    #[Locked]
    public bool $showMenu = false;

    #[Locked]
    public bool $canEdit = false;

    #[Locked]
    public bool $canManage = false;

    #[Locked]
    public string $action = '';

    #[Locked]
    public array $siteOptions = [];

    #[Locked]
    public array $supervisorOptions = [];

    #[Locked]
    public array $publicHolidayDates = [];

    public bool $showModal = false;

    public bool $showMoveConfirmModal = false;

    #[Locked]
    public array $moveConfirmation = [];

    public $selectedSiteId = '';

    public $selectedSupervisorId = '';

    public string $jobDate = '';

    public string $moveScope = 'linked';

    public string $actionError = '';

    public string $noticeMessage = '';

    public function mount(bool $showMenu = false): void
    {
        $user = Auth::user();
        // General managers/admins can create and allocate. Other planner editors may
        // move an existing Job Start but cannot create a complete preset schedule.
        $this->showMenu = $showMenu;
        $this->canEdit = (bool)$user?->hasPermission2('edit.trade.planner');
        $this->canManage = $this->canEdit && (bool)$user?->hasAnyRole2('mgt-general-manager|web-admin');
        $this->publicHolidayDates = collect(app(PlannerDateService::class)->holidays())
            ->keys()
            ->map(fn ($holiday) => Carbon::createFromFormat('Y-m-d', $holiday)->format('d/m/Y'))
            ->values()
            ->all();
    }

    #[On('open-planner-job-action')]
    public function openAction(string $action, ?int $siteId = null, ?string $date = null): void
    {
        abort_unless(in_array($action, ['add', 'move', 'allocate'], true), 404);
        abort_unless($action === 'move' ? $this->canEdit : $this->canManage, 403);

        // Rebuild the option lists for every open. Site status and permissions may
        // have changed since a previous modal interaction.
        $this->resetValidation();
        $this->resetActionState();
        $this->action = $action;
        $this->loadOptions($siteId);

        if ($siteId && collect($this->siteOptions)->contains(fn ($site) => (int)$site['id'] === $siteId)) {
            $this->selectedSiteId = $siteId;
        }

        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->jobDate = $date;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showMoveConfirmModal = false;
        $this->moveConfirmation = [];
        $this->showModal = false;
        $this->resetActionState();
    }

    public function updatedSelectedSiteId(): void
    {
        if ($this->action !== 'add' || $this->jobDate !== '') {
            return;
        }

        // For a new schedule, pre-fill the site's estimate but still allow the user
        // to choose a different valid workday.
        $site = collect($this->siteOptions)->first(fn ($option) => (int)$option['id'] === (int)$this->selectedSiteId);
        $estimate = (string)($site['jobstart_estimate'] ?? '');

        if ($estimate !== '') {
            try {
                $this->jobDate = Carbon::createFromFormat('d/m/Y', $estimate)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }
    }

    public function saveAction(): void
    {
        // Moving a Job Start can shift the complete preset schedule. Pause before
        // that write so the user sees exactly which site and dates are involved.
        if ($this->action === 'move') {
            $this->prepareMoveConfirmation();

            return;
        }

        $this->performAction();
    }

    public function closeMoveConfirmModal(): void
    {
        $this->showMoveConfirmModal = false;
        $this->moveConfirmation = [];
    }

    public function confirmMoveJobStart(): void
    {
        abort_unless($this->showMoveConfirmModal && $this->action === 'move' && $this->canEdit, 403);

        $this->showMoveConfirmModal = false;
        $this->moveConfirmation = [];
        $this->performAction();
    }

    protected function prepareMoveConfirmation(): void
    {
        abort_unless($this->canEdit, 403);
        $this->actionError = '';

        try {
            $siteId = (int)$this->selectedSiteId;

            // Options are permission-filtered when the modal opens; checking the
            // selected ID against them prevents a tampered Livewire request.
            if (!$siteId || !collect($this->siteOptions)->contains(fn ($site) => (int)$site['id'] === $siteId)) {
                throw ValidationException::withMessages(['selectedSiteId' => 'Select an available site.']);
            }

            $this->validateDate();

            if (!in_array($this->moveScope, ['linked', 'only'], true)) {
                throw ValidationException::withMessages(['moveScope' => 'Choose which tasks to move.']);
            }

            $site = Site::findOrFail($siteId);
            $jobStart = $site->jobStartTask;

            if (!$jobStart) {
                throw new DomainException('This site does not have a Job Start to move.');
            }

            if ($jobStart->from->isSameDay(Carbon::createFromFormat('Y-m-d', $this->jobDate))) {
                throw new DomainException('Choose a different date for the Job Start.');
            }

            $this->moveConfirmation = [
                'site' => (string)$site->name,
                'from' => $jobStart->from->format('D d/m/Y'),
                'to' => Carbon::createFromFormat('Y-m-d', $this->jobDate)->format('D d/m/Y'),
                'scope' => $this->moveScope,
            ];
            $this->showMoveConfirmModal = true;
        } catch (ValidationException $exception) {
            $this->actionError = collect($exception->errors())->flatten()->first() ?: 'Check the highlighted fields and try again.';
        } catch (DomainException $exception) {
            $this->actionError = $exception->getMessage();
        } catch (\Throwable $exception) {
            report($exception);
            $this->actionError = 'The planner could not prepare that move. Please try again.';
        }
    }

    protected function performAction(): void
    {
        abort_unless($this->action === 'move' ? $this->canEdit : $this->canManage, 403);
        $this->actionError = '';

        try {
            $siteId = (int)$this->selectedSiteId;

            if (!$siteId || !collect($this->siteOptions)->contains(fn ($site) => (int)$site['id'] === $siteId)) {
                throw ValidationException::withMessages(['selectedSiteId' => 'Select an available site.']);
            }

            // PlannerJobService owns transactions and linked-task rules; this
            // component is responsible for permissions, input and user feedback.
            $service = app(PlannerJobService::class);

            if ($this->action === 'add') {
                $this->validateDate();
                $count = $service->addJobStart($siteId, $this->jobDate);
                $message = 'Job Start and ' . max(0, $count - 1) . ' linked tasks added.';
            } elseif ($this->action === 'move') {
                $this->validateDate();

                if (!in_array($this->moveScope, ['linked', 'only'], true)) {
                    throw ValidationException::withMessages(['moveScope' => 'Choose which tasks to move.']);
                }

                // Linked mode preserves preset offsets where possible and compresses
                // only the pre-start check when it would otherwise fall before today.
                $count = $service->moveJobStart($siteId, $this->jobDate, $this->moveScope);
                $message = $this->moveScope === 'only' ? 'Job Start marker moved.' : $count . ' linked planner tasks moved.';
            } else {
                $supervisorId = (int)$this->selectedSupervisorId;

                if (!$supervisorId || !collect($this->supervisorOptions)->contains(fn ($supervisor) => (int)$supervisor['id'] === $supervisorId)) {
                    throw ValidationException::withMessages(['selectedSupervisorId' => 'Select an available supervisor.']);
                }

                $service->allocateJob($siteId, $supervisorId);
                $message = 'Job allocated to the selected supervisor.';
            }

            $this->showModal = false;
            $this->noticeMessage = $message;
            $this->dispatch('planner-job-updated', message: $message);
            $this->resetActionState(false);
        } catch (ValidationException $exception) {
            $this->actionError = collect($exception->errors())->flatten()->first() ?: 'Check the highlighted fields and try again.';
        } catch (DomainException $exception) {
            $this->actionError = $exception->getMessage();
        } catch (\Throwable $exception) {
            report($exception);
            $this->actionError = 'The planner could not complete that action. Please try again.';
        }
    }

    public function dismissNotice(): void
    {
        $this->noticeMessage = '';
    }

    protected function validateDate(): void
    {
        $this->validate([
            'jobDate' => ['required', 'date_format:Y-m-d'],
        ], [
            'jobDate.required' => 'Choose a date.',
            'jobDate.date_format' => 'Choose a valid date.',
        ]);
    }

    protected function loadOptions(?int $selectedSiteId = null): void
    {
        $controller = app(SitePlannerController::class);

        // Reuse the legacy option queries so the Vue and Livewire actions expose the
        // same eligible site set during the migration.
        if ($this->action === 'add') {
            $this->siteOptions = $this->normaliseSites($controller->getJobStarts(request(), 'false'));
        } elseif ($this->action === 'move') {
            $this->siteOptions = $this->normaliseSites($controller->getJobStarts(request(), 'true'));
            $this->appendCurrentMoveSite($selectedSiteId);
        } else {
            $this->siteOptions = $this->normaliseSites($controller->getSitesWithoutSuper(request()));
            $this->loadSupervisors();
        }
    }

    protected function appendCurrentMoveSite(?int $siteId): void
    {
        // Site Planner can open this action for its current site even when that site
        // sits just outside the generic Move dropdown query.
        if (!$siteId || collect($this->siteOptions)->contains(fn ($site) => (int)$site['id'] === $siteId)) {
            return;
        }

        $allowedSiteIds = Auth::user()->company->reportsTo()->sites('1')->pluck('id');
        $site = Site::whereIn('id', $allowedSiteIds)->where('status', 1)->whereNull('special')->find($siteId);

        if ($site?->jobStartTask) {
            $this->siteOptions[] = [
                'id' => (int)$site->id,
                'name' => $site->name . ' - ' . $site->job_start->format('d/m/Y'),
                'jobstart_estimate' => $site->jobstart_estimate?->format('d/m/Y') ?? '',
            ];

            $this->siteOptions = collect($this->siteOptions)->sortBy('name')->values()->all();
        }
    }

    protected function normaliseSites(array $sites): array
    {
        return collect($sites)->filter(fn ($site) => !empty($site['value']))->map(fn ($site) => [
            'id' => (int)$site['value'],
            'name' => trim(strip_tags((string)($site['text'] ?? $site['name'] ?? 'Site'))),
            'jobstart_estimate' => (string)($site['jobstart_estimate'] ?? ''),
        ])->values()->all();
    }

    protected function loadSupervisors(): void
    {
        $user = Auth::user();
        $supervisors = [];

        // Supervisors see only themselves/their reporting tree; management users
        // receive the company's normal supervisor list.
        if ($user->company->addon('planner') && $user->isSupervisor()) {
            $supervisors = $user->isAreaSupervisor()
                ? [$user->id => $user->fullname] + $user->subSupervisorsSelect()
                : [$user->id => $user->fullname];
        } else {
            $supervisors = $user->company->supervisorsSelect();
        }

        $this->supervisorOptions = collect($supervisors)->map(fn ($name, $id) => [
            'id' => (int)$id,
            'name' => trim(strip_tags((string)$name)),
        ])->values()->all();
    }

    protected function resetActionState(bool $clearAction = true): void
    {
        if ($clearAction) {
            $this->action = '';
        }

        $this->selectedSiteId = '';
        $this->selectedSupervisorId = '';
        $this->jobDate = '';
        $this->moveScope = 'linked';
        $this->siteOptions = [];
        $this->supervisorOptions = [];
        $this->actionError = '';
        $this->showMoveConfirmModal = false;
        $this->moveConfirmation = [];
    }

    public function render()
    {
        return view('livewire.planner.job-actions');
    }
}
