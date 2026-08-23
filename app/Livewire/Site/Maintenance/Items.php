<?php

namespace App\Livewire\Site\Maintenance;

use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Items extends Component
{
    #[Locked]
    public int $maintenanceId;

    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingItemId = null;
    public ?int $deletingItemId = null;

    public string $itemName = '';
    public $assignedTo = '';
    public $plannerTaskId = '';
    public string $plannerDate = '';
    public $itemStatus = '0';

    public string $message = '';
    public string $filter = 'all';

    public function mount(int $maintenanceId): void
    {
        $this->maintenanceId = $maintenanceId;
    }

    public function updatedFilter(string $filter): void
    {
        if (!in_array($filter, ['all', 'completed', 'outstanding'], true)) {
            $this->filter = 'all';
        }
    }

    protected function maintenance(): SiteMaintenance
    {
        return SiteMaintenance::findOrFail($this->maintenanceId);
    }

    protected function item(int $itemId): SiteMaintenanceItem
    {
        return SiteMaintenanceItem::where('main_id', $this->maintenanceId)
            ->findOrFail($itemId);
    }

    protected function canMutate(SiteMaintenance $main): bool
    {
        return Auth::user()->allowed2('edit.site.maintenance', $main)
            || Auth::id() == $main->super_id;
    }

    protected function canAdd(SiteMaintenance $main): bool
    {
        return (bool) $main->status
            && $this->canMutate($main)
            && Auth::user()->hasAnyRole2('con-administrator|web-admin|mgt-general-manager');
    }

    protected function canEdit(SiteMaintenance $main): bool
    {
        return !$main->supervisor_sign_by
            && Auth::user()->allowed2('edit.site.maintenance', $main);
    }

    protected function canDelete(SiteMaintenance $main): bool
    {
        return (bool) $main->status
            && Auth::user()->allowed2('del.site.maintenance', $main)
            && Auth::user()->hasAnyRole2('web-admin|mgt-general-manager');
    }

    protected function companyOptions(): array
    {
        return Auth::user()->company->reportsTo()
            ->companies('1')
            ->sortBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function validCompanyId($companyId): bool
    {
        if ($companyId === '' || $companyId === null) {
            return true;
        }

        return array_key_exists((int) $companyId, $this->companyOptions())
            || array_key_exists((string) $companyId, $this->companyOptions());
    }

    protected function plannerTaskOptions(): array
    {
        if (!$this->assignedTo || !$this->validCompanyId($this->assignedTo)) {
            return [];
        }

        $company = Company::find($this->assignedTo);

        if (!$company) {
            return [];
        }

        $options = [];
        $trades = $company->tradesSkilledIn;
        $tradeCount = $trades->count();

        foreach ($trades as $trade) {
            $tasks = Task::where('trade_id', $trade->id)
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            foreach ($tasks as $task) {
                $options[$task->id] = $tradeCount > 1
                    ? $trade->name . ':' . $task->name
                    : $task->name;
            }
        }

        return $options;
    }

    protected function resetForm(): void
    {
        $this->resetValidation();

        $this->editingItemId = null;
        $this->deletingItemId = null;
        $this->itemName = '';
        $this->assignedTo = '';
        $this->plannerTaskId = '';
        $this->plannerDate = '';
        $this->itemStatus = '0';
    }

    public function openAdd(): void
    {
        $main = $this->maintenance();
        abort_unless($this->canAdd($main), 404);

        $this->resetForm();
        $this->showAddModal = true;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function saveAdd(): void
    {
        $main = $this->maintenance();
        abort_unless($this->canAdd($main), 404);

        $this->validate([
            'itemName' => ['required', 'string'],
        ]);

        $item = SiteMaintenanceItem::create([
            'main_id' => $main->id,
            'name' => trim($this->itemName),
            'order' => $main->items()->count() + 1,
            'status' => 0,
        ]);

        // Preserve the existing Maintenance item behaviour: a new item creates
        // a Todo for the Maintenance Supervisor, including the special email CC.
        if ($main->super_id) {
            $item->createAssignSupervisorToDo($main->super_id);
        }

        $main->touch();

        $this->showAddModal = false;
        $this->resetForm();
        $this->message = 'Item added.';
        $this->dispatch('maintenance-items-updated');
    }

    public function openEdit(int $itemId): void
    {
        $main = $this->maintenance();
        abort_unless($this->canEdit($main), 404);

        $item = $this->item($itemId);
        $planner = $item->planner;

        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->itemName = $item->name;
        $this->assignedTo = $item->assigned_to ? (string) $item->assigned_to : '';
        $this->plannerTaskId = $planner?->task_id ? (string) $planner->task_id : '';
        $this->plannerDate = $planner?->from?->format('d/m/Y') ?? '';
        $this->itemStatus = (string) $item->status;

        $this->showAddModal = false;
        $this->showEditModal = true;
        $this->showDeleteModal = false;
    }

    public function updatedAssignedTo(): void
    {
        // A planner task belongs to the selected company's trade(s), so changing
        // company invalidates the previous task/date choice.
        $this->plannerTaskId = '';
        $this->plannerDate = '';
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $main = $this->maintenance();
        abort_unless($this->canEdit($main), 404);
        abort_unless($this->editingItemId, 404);

        $item = $this->item($this->editingItemId);

        $this->validate([
            'assignedTo' => ['nullable', 'integer'],
            'plannerTaskId' => ['nullable', 'integer'],
            'plannerDate' => [$this->plannerTaskId ? 'required' : 'nullable', 'date_format:d/m/Y'],
            'itemStatus' => ['required', 'in:0,1,2'],
        ]);

        if (!$this->validCompanyId($this->assignedTo)) {
            $this->addError('assignedTo', 'Please select a valid company.');
            return;
        }

        $plannerTasks = $this->plannerTaskOptions();

        if ($this->plannerTaskId && !array_key_exists((int) $this->plannerTaskId, $plannerTasks)) {
            $this->addError('plannerTaskId', 'Please select a valid planner task for this company.');
            return;
        }

        if ($this->plannerTaskId && !$this->assignedTo) {
            $this->addError('assignedTo', 'Please select a company before selecting a planner task.');
            return;
        }

        $assignedToOriginal = $item->assigned_to;
        $statusOriginal = (string) $item->status;

        // Replace the old planner record when the planner selection is saved.
        // This keeps SiteMaintenanceItem.planner_id pointing at a live record.
        if ($item->planner_id) {
            SitePlanner::whereKey($item->planner_id)->delete();
            $item->planner_id = null;
            $item->save();
        }

        if ($this->plannerTaskId) {
            $plannerDate = Carbon::createFromFormat('d/m/Y H:i', $this->plannerDate . ' 00:00');

            $planner = SitePlanner::create([
                'site_id' => $main->site_id,
                'from' => $plannerDate->toDateTimeString(),
                'to' => $plannerDate->toDateTimeString(),
                'days' => 1,
                'entity_type' => 'c',
                'entity_id' => (int) $this->assignedTo,
                'task_id' => (int) $this->plannerTaskId,
            ]);

            $item->planner_id = $planner->id;
            $item->save();
        }

        $itemRequest = [
            'assigned_to' => $this->assignedTo !== '' ? (int) $this->assignedTo : null,
            'status' => (int) $this->itemStatus,
        ];

        // Preserve the existing status semantics:
        // 0 = Incomplete, 1 = Completed, 2 = Owner Works.
        if ($this->itemStatus !== $statusOriginal) {
            if ($this->itemStatus === '0') {
                $itemRequest['done_by'] = null;
                $itemRequest['done_at'] = null;
                $itemRequest['sign_by'] = null;
                $itemRequest['sign_at'] = null;

                Action::create([
                    'action' => 'Maintenance Item has been mark as NOT completed',
                    'table' => 'site_maintenance',
                    'table_id' => $main->id,
                ]);
            } else {
                $itemRequest['done_by'] = Auth::id();
                $itemRequest['done_at'] = now();
                $itemRequest['sign_by'] = Auth::id();
                $itemRequest['sign_at'] = now();

                Action::create([
                    'action' => 'Maintenance Item has been completed',
                    'table' => 'site_maintenance',
                    'table_id' => $main->id,
                ]);
            }
        }

        $item->update($itemRequest);

        // Preserve the existing company-assignment notification behaviour.
        if ($this->assignedTo !== '' && (string) $this->assignedTo !== (string) $assignedToOriginal) {
            $company = Company::find($this->assignedTo);

            if ($company) {
                if ($company->primary_contact()) {
                    $item->emailAssigned($company->primary_contact());
                }

                Action::create([
                    'action' => "Company assigned to request updated to {$company->name}",
                    'table' => 'site_maintenance',
                    'table_id' => $main->id,
                ]);
            }

            if (!$main->assigned_at) {
                $main->assigned_at = now();
                $main->save();
            }

            $main->closeToDo();
            $item->closeToDo();
        }

        $main->touch();

        $this->showEditModal = false;
        $this->resetForm();
        $this->message = 'Item updated.';
        $this->dispatch('maintenance-items-updated');
    }

    public function confirmDelete(int $itemId): void
    {
        $main = $this->maintenance();
        abort_unless($this->canDelete($main), 404);

        $item = $this->item($itemId);

        $this->resetValidation();
        $this->deletingItemId = $item->id;
        $this->itemName = $item->name;

        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = true;
    }

    public function reorderItems(array $orderedIds): void
    {
        $main = $this->maintenance();
        abort_unless($this->canEdit($main), 404);

        $items = $main->items()->orderBy('order')->get();
        $existingIds = $items->pluck('id')->map(fn($id) => (int)$id)->all();
        $orderedIds = array_map('intval', $orderedIds);

        $existingSorted = $existingIds;
        $orderedSorted = $orderedIds;
        sort($existingSorted);
        sort($orderedSorted);
        abort_unless($existingSorted === $orderedSorted, 422);

        DB::transaction(function () use ($orderedIds, $items) {
            $itemsById = $items->keyBy('id');

            foreach ($orderedIds as $index => $itemId) {
                $itemsById->get($itemId)->update(['order' => $index + 1]);
            }
        });

        $main->touch();
    }

    public function deleteItem(): void
    {
        $main = $this->maintenance();
        abort_unless($this->canDelete($main), 404);
        abort_unless($this->deletingItemId, 404);

        $item = $this->item($this->deletingItemId);

        if ($item->planner_id) {
            SitePlanner::whereKey($item->planner_id)->delete();
        }

        $item->closeToDo();
        $item->delete();

        $order = 1;

        foreach ($main->items()->orderBy('order')->get() as $remainingItem) {
            if ((int) $remainingItem->order !== $order) {
                $remainingItem->order = $order;
                $remainingItem->save();
            }

            $order++;
        }

        $main->touch();

        $this->showDeleteModal = false;
        $this->resetForm();
        $this->message = 'Item deleted.';
        $this->dispatch('maintenance-items-updated');
    }

    public function closeModals(): void
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $main = $this->maintenance();

        $allItems = $main->items()
            ->with(['assigned', 'planner.task'])
            ->orderBy('order')
            ->get();

        $items = match ($this->filter) {
            'completed' => $allItems->whereIn('status', [1, 2])->values(),
            'outstanding' => $allItems->where('status', 0)->values(),
            default => $allItems,
        };

        $userIds = $items->pluck('done_by')->merge($items->pluck('sign_by'))->filter()->unique()->values();
        $users = $userIds->isEmpty()
            ? collect()
            : \App\User::whereIn('id', $userIds)->with('company')->get()->keyBy('id');

        $companyOptions = $this->companyOptions();
        $plannerTaskOptions = $this->plannerTaskOptions();

        $canAdd = $this->canAdd($main);
        $canEdit = $this->canEdit($main);
        $canDelete = $this->canDelete($main);

        $itemsTotal = $allItems->count();
        $itemsDone = $allItems->whereNotNull('done_by')->count();
        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;

        return view('livewire.site.maintenance.items', compact(
            'main',
            'items',
            'users',
            'companyOptions',
            'plannerTaskOptions',
            'canAdd',
            'canEdit',
            'canDelete',
            'allDone'
        ));
    }
}
