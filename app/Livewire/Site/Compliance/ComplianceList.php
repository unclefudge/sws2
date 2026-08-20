<?php

namespace App\Livewire\Site\Compliance;

use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SiteComplianceReason;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ComplianceList extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    protected $paginationTheme = 'bootstrap';

    public string $reason = '';
    public bool $status = false;
    public string $search = '';
    public string $sortKey = 'date';
    public string $sortDirection = 'desc';
    public int $perPage = 25;

    public bool $showEditModal = false;
    public bool $showSameCompanyModal = false;
    public string $message = '';

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public array $sameCompanyIds = [];

    public string $editUserName = '';
    public string $editCompanyName = '';
    public string $editDate = '';
    public string $editSiteName = '';
    public int $editSiteId = 0;
    public string $editReason = '';
    public int $editStatus = 0;
    public string $editResolvedAt = '';
    public string $editNotes = '';
    public int $editUserNc = 0;
    public array $editUserNcDates = [];

    public array $sameCompanyNames = [];
    public string $sameReasonName = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('compliance'), 404);

        $cachedPerPage = (int) Cache::get($this->perPageCacheKey(), 25);
        $this->perPage = in_array($cachedPerPage, self::PER_PAGE_OPTIONS, true) ? $cachedPerPage : 25;
    }

    public function updatedReason(): void
    {
        if ($this->reason === '' || $this->reason === '1') {
            $this->status = false;
        } elseif ((int) $this->reason > 1) {
            $this->status = true;
        }

        $this->resetPage('compliancePage');
    }

    public function updatedStatus(): void
    {
        $this->resetPage('compliancePage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('compliancePage');
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25;

        Cache::forever($this->perPageCacheKey(), $this->perPage);
        $this->resetPage('compliancePage');
    }

    public function sortBy(string $key): void
    {
        abort_unless(in_array($key, ['date', 'site_name', 'user_name', 'user_company', 'site_supers'], true), 404);

        if ($this->sortKey === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortKey = $key;
            $this->sortDirection = 'asc';
        }

        $this->resetPage('compliancePage');
    }

    public function openEdit(int $id): void
    {
        $record = SiteCompliance::with(['site', 'user.company'])->findOrFail($id);
        abort_unless(Auth::user()->allowed2('edit.compliance', $record), 404);

        $history = $this->nonCompliantHistory([$record->user_id]);
        $userHistory = $history[$record->user_id] ?? collect();

        $this->editingId = $record->id;
        $this->editUserName = $record->user?->full_name ?? '-';
        $this->editCompanyName = $record->user?->company?->name_alias ?? '-';
        $this->editDate = $record->date?->format('d/m/Y') ?? '';
        $this->editSiteName = $record->site?->name ?? '-';
        $this->editSiteId = (int) $record->site_id;
        $this->editReason = $record->reason === null ? '' : (string) $record->reason;
        $this->editStatus = (int) $record->status;
        $this->editResolvedAt = $record->resolved_at?->format('d/m/Y') ?? '';
        $this->editNotes = (string) ($record->notes ?? '');
        $this->editUserNc = $userHistory->count();
        $this->editUserNcDates = $userHistory->map(fn ($item) => $item->date?->format('d/m/Y'))->filter()->values()->toArray();

        $this->sameCompanyIds = [];
        $this->sameCompanyNames = [];
        $this->sameReasonName = '';
        $this->showSameCompanyModal = false;
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function updatedEditReason(): void
    {
        if (!$this->editingId) {
            return;
        }

        $record = SiteCompliance::find($this->editingId);

        if (!$record) {
            return;
        }

        $oldReason = $record->reason === null ? '' : (string) $record->reason;

        if ($this->editReason === $oldReason) {
            $this->editStatus = (int) $record->status;
            $this->editResolvedAt = $record->resolved_at?->format('d/m/Y') ?? '';
            return;
        }

        // Unassigned and Non-Compliant are unresolved until explicitly resolved.
        if ($this->editReason === '' || $this->editReason === '1') {
            $this->editStatus = 0;
            $this->editResolvedAt = '';
        } else {
            $this->editStatus = 1;
        }
    }

    public function save(): void
    {
        $record = $this->editingRecord();

        $this->validate([
            'editReason' => ['nullable'],
            'editNotes' => ['nullable', 'string'],
        ]);

        $oldReason = $record->reason === null ? '' : (string) $record->reason;
        $reasonChanged = $this->editReason !== $oldReason;
        [$newReason, $newStatus] = $this->normaliseReasonAndStatus($record, $reasonChanged);

        // Preserve the useful legacy behaviour: when an unassigned contractor
        // is assigned a reason, offer to apply it to other unassigned workers
        // from the same company on the same site/day.
        if ($oldReason === '' && $reasonChanged) {
            $sameCompany = $this->sameCompanyRecords($record);

            if ($sameCompany->count() > 1) {
                $this->sameCompanyIds = $sameCompany->pluck('id')->map(fn ($id) => (int) $id)->values()->toArray();
                $this->sameCompanyNames = $sameCompany->pluck('user_name')->filter()->values()->toArray();
                $this->sameReasonName = $this->reasonOptions()[$this->editReason] ?? 'selected reason';
                $this->showSameCompanyModal = true;

                return;
            }
        }

        $this->applyUpdate($record, $newReason, $newStatus, $this->editNotes);
        $this->finishUpdate('Updated compliance record.');
    }

    public function resolve(): void
    {
        $record = $this->editingRecord();

        $this->validate([
            'editNotes' => ['required', 'string'],
        ], [
            'editNotes.required' => 'Notes are required to resolve a non-compliance.',
        ]);

        $this->applyUpdate($record, 1, 1, $this->editNotes);
        $this->finishUpdate('Compliance record resolved.');
    }

    public function saveSameCompany(bool $all): void
    {
        $record = $this->editingRecord();
        $oldReason = $record->reason === null ? '' : (string) $record->reason;
        $reasonChanged = $this->editReason !== $oldReason;
        [$newReason, $newStatus] = $this->normaliseReasonAndStatus($record, $reasonChanged);

        if ($all) {
            foreach ($this->sameCompanyIds as $id) {
                $sameRecord = SiteCompliance::find($id);

                if (!$sameRecord || !Auth::user()->allowed2('edit.compliance', $sameRecord)) {
                    continue;
                }

                // Keep existing notes on the other contractors; only the record
                // the user actually edited receives the typed notes.
                $notes = $sameRecord->id === $record->id ? $this->editNotes : (string) ($sameRecord->notes ?? '');
                $this->applyUpdate($sameRecord, $newReason, $newStatus, $notes);
            }

            $message = 'Updated contractors from the same company.';
        } else {
            $this->applyUpdate($record, $newReason, $newStatus, $this->editNotes);
            $message = 'Updated compliance record.';
        }

        $this->finishUpdate($message);
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->showSameCompanyModal = false;
        $this->editingId = null;
        $this->sameCompanyIds = [];
        $this->sameCompanyNames = [];
        $this->resetValidation();
    }

    protected function editingRecord(): SiteCompliance
    {
        abort_unless($this->editingId, 404);

        $record = SiteCompliance::findOrFail($this->editingId);
        abort_unless(Auth::user()->allowed2('edit.compliance', $record), 404);

        return $record;
    }

    protected function normaliseReasonAndStatus(SiteCompliance $record, bool $reasonChanged): array
    {
        $newReason = $this->editReason === '' ? null : (int) $this->editReason;
        $newStatus = (int) $record->status;

        if ($reasonChanged) {
            $newStatus = ($newReason !== null && $newReason !== 1) ? 1 : 0;
        }

        return [$newReason, $newStatus];
    }

    protected function applyUpdate(SiteCompliance $record, ?int $reason, int $status, string $notes): void
    {
        $attributes = [
            'reason' => $reason,
            'status' => $status,
            'notes' => $notes,
        ];

        if ((int) $record->status !== $status) {
            $attributes['resolved_at'] = $status ? now() : null;
        }

        $record->update($attributes);
    }

    protected function finishUpdate(string $message): void
    {
        $this->message = $message;
        $this->showEditModal = false;
        $this->showSameCompanyModal = false;
        $this->editingId = null;
        $this->sameCompanyIds = [];
        $this->sameCompanyNames = [];
        $this->resetPage('compliancePage');
    }

    protected function sameCompanyRecords(SiteCompliance $record)
    {
        $companyId = $record->user?->company_id;

        if (!$companyId) {
            return collect();
        }

        return SiteCompliance::query()
            ->select('site_compliance.id', DB::raw("TRIM(CONCAT_WS(' ', users.firstname, users.lastname)) as user_name"))
            ->join('users', 'users.id', '=', 'site_compliance.user_id')
            ->whereNull('site_compliance.reason')
            ->where('site_compliance.archive', 0)
            ->where('site_compliance.site_id', $record->site_id)
            ->whereDate('site_compliance.date', $record->date->toDateString())
            ->where('users.company_id', $companyId)
            ->orderBy('users.firstname')
            ->orderBy('users.lastname')
            ->get();
    }

    protected function complianceQuery(): Builder
    {
        $siteIds = Auth::user()->authSites('view.compliance')->pluck('id')->toArray();

        $query = SiteCompliance::query()
            ->join('sites', 'sites.id', '=', 'site_compliance.site_id')
            ->join('users', 'users.id', '=', 'site_compliance.user_id')
            ->leftJoin('companys as user_company', 'user_company.id', '=', 'users.company_id')
            ->leftJoin('users as supervisor', 'supervisor.id', '=', 'sites.supervisor_id')
            ->whereIn('site_compliance.site_id', $siteIds)
            ->where('site_compliance.archive', 0);

        if ($this->reason === '') {
            $query->whereNull('site_compliance.reason');
        } else {
            $query->where('site_compliance.reason', (int) $this->reason);

            if ($this->reason === '1') {
                $query->where('site_compliance.status', (int) $this->status);
            }
        }

        $search = trim($this->search);

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('sites.name', 'like', $like)
                    ->orWhere('users.firstname', 'like', $like)
                    ->orWhere('users.lastname', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', users.firstname, users.lastname) LIKE ?", [$like])
                    ->orWhere('user_company.nickname', 'like', $like)
                    ->orWhere('user_company.name', 'like', $like)
                    ->orWhere('supervisor.firstname', 'like', $like)
                    ->orWhere('supervisor.lastname', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', supervisor.firstname, supervisor.lastname) LIKE ?", [$like])
                    ->orWhere('site_compliance.notes', 'like', $like)
                    ->orWhere('site_compliance.date', 'like', $like);
            });
        }

        return $this->applySort($query);
    }

    protected function applySort(Builder $query): Builder
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return match ($this->sortKey) {
            'site_name' => $query->orderBy('sites.name', $direction)->orderBy('site_compliance.date', 'desc'),
            'user_name' => $query->orderBy('users.firstname', $direction)->orderBy('users.lastname', $direction),
            'user_company' => $query
                ->orderByRaw("COALESCE(NULLIF(user_company.nickname, ''), user_company.name) {$direction}")
                ->orderBy('users.firstname'),
            'site_supers' => $query->orderBy('supervisor.firstname', $direction)->orderBy('supervisor.lastname', $direction),
            default => $query->orderBy('site_compliance.date', $direction)->orderBy('site_compliance.id', $direction),
        };
    }

    protected function nonCompliantHistory(array $userIds)
    {
        if (!$userIds) {
            return collect();
        }

        return SiteCompliance::query()
            ->whereIn('user_id', $userIds)
            ->where('reason', 1)
            ->where('archive', 0)
            ->whereDate('date', '>', Carbon::now()->subYear())
            ->orderByDesc('date')
            ->get(['user_id', 'date'])
            ->groupBy('user_id');
    }

    protected function reasonOptions(): array
    {
        return ['' => 'Unassigned Reason'] + SiteComplianceReason::query()
            ->where('status', 1)
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function perPageCacheKey(): string
    {
        return 'sws:user:' . Auth::id() . ':compliance:per_page';
    }

    public function render()
    {
        $records = $this->complianceQuery()->paginate(
            $this->perPage,
            [
                'site_compliance.*',
                'sites.name as site_name',
                DB::raw("TRIM(CONCAT_WS(' ', users.firstname, users.lastname)) as user_name"),
                DB::raw("COALESCE(NULLIF(user_company.nickname, ''), user_company.name, '-') as user_company"),
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', supervisor.firstname, supervisor.lastname)), ''), '-') as site_supers"),
            ],
            'compliancePage'
        );

        $userIds = collect($records->items())->pluck('user_id')->filter()->unique()->values()->toArray();
        $history = $this->nonCompliantHistory($userIds);

        foreach ($records as $record) {
            $userHistory = $history[$record->user_id] ?? collect();
            $record->setAttribute('user_nc', $userHistory->count());
            $record->setAttribute(
                'user_nc_dates',
                $userHistory->take(10)->map(fn ($item) => $item->date?->format('d/m/Y'))->filter()->values()->toArray()
            );
        }

        return view('livewire.site.compliance.compliance-list', [
            'records' => $records,
            'reasons' => $this->reasonOptions(),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'canEdit' => Auth::user()->hasPermission2('edit.compliance'),
        ]);
    }
}
