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

    public $selectedSiteId = '';

    public $selectedSupervisorId = '';

    public string $jobDate = '';

    public string $moveScope = 'linked';

    public string $actionError = '';

    public string $noticeMessage = '';

    public function mount(bool $showMenu = false): void
    {
        $user = Auth::user();
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
        $this->showModal = false;
        $this->resetActionState();
    }

    public function updatedSelectedSiteId(): void
    {
        if ($this->action !== 'add' || $this->jobDate !== '') {
            return;
        }

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
        abort_unless($this->action === 'move' ? $this->canEdit : $this->canManage, 403);
        $this->actionError = '';

        try {
            $siteId = (int)$this->selectedSiteId;

            if (!$siteId || !collect($this->siteOptions)->contains(fn ($site) => (int)$site['id'] === $siteId)) {
                throw ValidationException::withMessages(['selectedSiteId' => 'Select an available site.']);
            }

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
    }

    public function render()
    {
        return view('livewire.planner.job-actions');
    }
}
