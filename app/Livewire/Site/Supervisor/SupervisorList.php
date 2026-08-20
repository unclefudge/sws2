<?php

namespace App\Livewire\Site\Supervisor;

use App\Models\Company\Company;
use App\Models\Company\CompanySupervisor;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SupervisorList extends Component
{
    public bool $showAddModal = false;
    public bool $showDeleteModal = false;

    public int $modalNonce = 0;
    public int $userId = 0;
    public int $parentId = 0;

    public array $openAreaIds = [];
    public string $sortDirection = 'asc';
    public string $message = '';

    #[Locked]
    public ?int $deletingId = null;

    public string $deletingName = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('area.super'), 404);
    }

    protected function companyId(): int
    {
        return (int) Auth::user()->company_id;
    }

    protected function canEdit(): bool
    {
        return Auth::user()->hasPermission2('edit.area.super');
    }

    public function sortByName(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function toggleArea(int $id): void
    {
        if (in_array($id, $this->openAreaIds, true)) {
            $this->openAreaIds = array_values(array_diff($this->openAreaIds, [$id]));
            return;
        }

        $this->openAreaIds[] = $id;
    }

    public function openAdd(?int $parentId = null): void
    {
        abort_unless($this->canEdit(), 404);

        $this->resetValidation();
        $this->message = '';
        $this->userId = 0;
        $this->parentId = $parentId ?: 0;
        $this->modalNonce++;
        $this->showDeleteModal = false;
        $this->showAddModal = true;
    }

    public function closeModals(): void
    {
        $this->showAddModal = false;
        $this->showDeleteModal = false;
        $this->userId = 0;
        $this->parentId = 0;
        $this->deletingId = null;
        $this->deletingName = '';
        $this->resetValidation();
    }

    public function addSupervisor(): void
    {
        abort_unless($this->canEdit(), 404);

        $this->validate([
            'userId' => ['required', 'integer', 'min:1'],
            'parentId' => ['required', 'integer', 'min:0'],
        ]);

        $companyId = $this->companyId();

        $user = User::query()
            ->whereKey($this->userId)
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->first();

        if (!$user) {
            $this->addError('userId', 'Please select an active employee from this company.');
            return;
        }

        $parent = null;

        if ($this->parentId) {
            $parent = CompanySupervisor::query()
                ->whereKey($this->parentId)
                ->where('company_id', $companyId)
                ->where('parent_id', 0)
                ->first();

            if (!$parent) {
                $this->addError('parentId', 'Please select a valid Area Supervisor.');
                return;
            }
        }

        $duplicate = CompanySupervisor::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('parent_id', $this->parentId)
            ->exists();

        if ($duplicate) {
            $this->addError('userId', $user->full_name . ' already exists in this supervisor group.');
            return;
        }

        if ($this->parentId) {
            $ownAreaRecord = CompanySupervisor::query()
                ->where('company_id', $companyId)
                ->where('user_id', $user->id)
                ->where('parent_id', 0)
                ->whereKey($this->parentId)
                ->exists();

            if ($ownAreaRecord) {
                $this->addError('parentId', $user->full_name . " can't be their own Area Supervisor.");
                return;
            }
        }

        DB::transaction(function () use ($user, $companyId) {
            CompanySupervisor::create([
                'user_id' => $user->id,
                'parent_id' => $this->parentId,
                'company_id' => $companyId,
            ]);

            if ($companyId === 3) {
                $this->ensureCapeCodSupervisorSetup($user);
            }
        });

        if ($this->parentId && !in_array($this->parentId, $this->openAreaIds, true)) {
            $this->openAreaIds[] = $this->parentId;
        }

        $this->showAddModal = false;
        $this->message = 'Added supervisor ' . $user->full_name . '.';
        $this->userId = 0;
        $this->parentId = 0;
    }

    public function confirmDeleteArea(int $id): void
    {
        abort_unless($this->canEdit(), 404);

        $record = CompanySupervisor::query()
            ->join('users', 'users.id', '=', 'company_supervisors.user_id')
            ->where('company_supervisors.id', $id)
            ->where('company_supervisors.company_id', $this->companyId())
            ->where('company_supervisors.parent_id', 0)
            ->first([
                'company_supervisors.id',
                'company_supervisors.user_id',
                DB::raw("TRIM(CONCAT_WS(' ', users.firstname, users.lastname)) as name"),
            ]);

        abort_unless($record, 404);

        $user = User::findOrFail($record->user_id);
        abort_unless(Auth::user()->allowed2('edit.area.super', $user), 404);

        $this->deletingId = (int) $record->id;
        $this->deletingName = $record->name;
        $this->showAddModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteAreaSupervisor(): void
    {
        abort_unless($this->deletingId, 404);

        $this->deleteSupervisorRecord($this->deletingId, true);
    }

    public function deleteChildSupervisor(int $id): void
    {
        $this->deleteSupervisorRecord($id, false);
    }

    protected function deleteSupervisorRecord(int $id, bool $mustBeArea): void
    {
        $record = CompanySupervisor::query()
            ->whereKey($id)
            ->where('company_id', $this->companyId())
            ->firstOrFail();

        if ($mustBeArea) {
            abort_unless((int) $record->parent_id === 0, 404);
        }

        $user = User::findOrFail($record->user_id);
        abort_unless(Auth::user()->allowed2('edit.area.super', $user), 404);

        $name = $user->full_name;
        $companyId = $this->companyId();

        DB::transaction(function () use ($record, $user, $companyId) {
            // Preserve the old controller behaviour: deleting an Area Supervisor
            // also removes every supervisor directly under that Area Supervisor.
            CompanySupervisor::query()
                ->where('id', $record->id)
                ->orWhere('parent_id', $record->id)
                ->delete();

            // Preserve the existing Cape Cod "Cc-firstname lastname" company
            // activation/deactivation behaviour for the supervisor being deleted.
            if ((int) $user->company_id === 3) {
                $stillSupervisor = CompanySupervisor::query()
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$stillSupervisor) {
                    $this->setCapeCodSupervisorCompanyStatus($user, 0);
                }
            }
        });

        $this->openAreaIds = array_values(array_diff($this->openAreaIds, [$record->id]));
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = '';
        $this->message = 'Deleted supervisor ' . $name . '.';
    }

    protected function ensureCapeCodSupervisorSetup(User $user): void
    {
        $now = Carbon::now()->toDateTimeString();

        $exists = DB::table('company_supervisors_list')
            ->where('user_id', $user->id)
            ->where('company_id', 3)
            ->exists();

        if (!$exists) {
            DB::table('company_supervisors_list')->insert([
                'user_id' => $user->id,
                'company_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $companyName = $this->capeCodSupervisorCompanyName($user);
        $company = Company::where('name', $companyName)->first();

        if ($company) {
            if ((int) $company->status === 0) {
                $company->status = 1;
                $company->save();
            }

            return;
        }

        $sourceCompany = Company::findOrFail(3);

        $company = Company::create([
            'name' => $companyName,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $sourceCompany->address,
            'suburb' => $sourceCompany->suburb,
            'state' => $sourceCompany->state,
            'postcode' => $sourceCompany->postcode,
            'abn' => $sourceCompany->abn,
            'maxjobs' => 50,
            'gst' => 0,
            'payroll_tax' => 0,
            'category' => 0,
            'approved_by' => 1,
            'approved_at' => $now,
            'notes' => 'Working under Cape Cods Safe Work Method Statement',
            'parent_company' => 3,
            'status' => 1,
        ]);

        $company->tradesSkilledIn()->sync([31]);
    }

    protected function setCapeCodSupervisorCompanyStatus(User $user, int $status): void
    {
        $company = Company::where('name', $this->capeCodSupervisorCompanyName($user))->first();

        if ($company && (int) $company->status !== $status) {
            $company->status = $status;
            $company->save();
        }
    }

    protected function capeCodSupervisorCompanyName(User $user): string
    {
        $first = trim((string) $user->firstname);
        $last = trim((string) $user->lastname);

        return trim('Cc-' . strtolower($first) . ($last !== '' ? ' ' . $last : ''));
    }

    protected function supervisorRows()
    {
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return CompanySupervisor::query()
            ->join('users', 'users.id', '=', 'company_supervisors.user_id')
            ->where('company_supervisors.company_id', $this->companyId())
            ->orderBy('users.firstname', $direction)
            ->orderBy('users.lastname', $direction)
            ->get([
                'company_supervisors.id',
                'company_supervisors.user_id',
                'company_supervisors.parent_id',
                DB::raw("TRIM(CONCAT_WS(' ', users.firstname, users.lastname)) as name"),
            ]);
    }

    protected function staffOptions(): array
    {
        return User::query()
            ->where('company_id', $this->companyId())
            ->where('status', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname'])
            ->mapWithKeys(fn ($user) => [$user->id => trim($user->firstname . ' ' . $user->lastname)])
            ->toArray();
    }

    public function render()
    {
        $rows = $this->supervisorRows();

        $areaSupervisors = $rows->where('parent_id', 0)->values();
        $childrenByParent = $rows->where('parent_id', '!=', 0)->groupBy('parent_id');

        $areaOptions = $areaSupervisors
            ->mapWithKeys(fn ($supervisor) => [(int) $supervisor->id => $supervisor->name])
            ->toArray();

        return view('livewire.site.supervisor.supervisor-list', [
            'areaSupervisors' => $areaSupervisors,
            'childrenByParent' => $childrenByParent,
            'staffOptions' => $this->staffOptions(),
            'areaOptions' => $areaOptions,
            'canEdit' => $this->canEdit(),
            'isCC' => Auth::user()->isCC(),
        ]);
    }
}
