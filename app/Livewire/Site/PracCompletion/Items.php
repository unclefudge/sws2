<?php

namespace App\Livewire\Site\PracCompletion;

use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SitePracCompletionItem;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Items extends Component
{
    #[Locked]
    public int $pracId;

    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingItemId = null;
    public ?int $deletingItemId = null;

    public string $itemName = '';
    public $assignedTo = '';
    public $plannerTaskId = '';
    public string $plannerDate = '';
    public $itemStatus = '1';

    public string $message = '';
    public string $filter = 'all';

    public function mount(int $pracId): void
    {
        $this->pracId = $pracId;
    }

    protected function prac(): SitePracCompletion
    {
        return SitePracCompletion::findOrFail($this->pracId);
    }

    protected function item(int $itemId): SitePracCompletionItem
    {
        return SitePracCompletionItem::where('prac_id', $this->pracId)->findOrFail($itemId);
    }

    protected function canMutate(SitePracCompletion $prac): bool
    {
        return Auth::user()->allowed2('edit.prac.completion', $prac) || Auth::id() == $prac->super_id;
    }

    protected function canAdd(SitePracCompletion $prac): bool
    {
        return (bool)$prac->status && !$prac->supervisor_sign_by && Auth::user()->allowed2('edit.prac.completion', $prac);
    }

    protected function canEdit(SitePracCompletion $prac): bool
    {
        return !$prac->supervisor_sign_by && $this->canMutate($prac);
    }

    protected function canDelete(SitePracCompletion $prac): bool
    {
        return (bool)$prac->status
            && Auth::user()->allowed2('del.prac.completion', $prac)
            && Auth::user()->hasAnyRole2('web-admin|mgt-general-manager');
    }

    protected function companyOptions(): array
    {
        return Auth::user()->company->reportsTo()->companies('1')->sortBy('name')->pluck('name', 'id')->toArray();
    }

    protected function validCompanyId($companyId): bool
    {
        if ($companyId === '' || $companyId === null) {
            return true;
        }

        $options = $this->companyOptions();

        return array_key_exists((int)$companyId, $options) || array_key_exists((string)$companyId, $options);
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
            $tasks = Task::where('trade_id', $trade->id)->where('status', 1)->orderBy('name')->get();

            foreach ($tasks as $task) {
                $options[$task->id] = $tradeCount > 1 ? $trade->name . ':' . $task->name : $task->name;
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
        $this->itemStatus = '1';
    }

    public function openAdd(): void
    {
        $prac = $this->prac();
        abort_unless($this->canAdd($prac), 404);

        $this->resetForm();
        $this->showAddModal = true;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function saveAdd(): void
    {
        $prac = $this->prac();
        abort_unless($this->canAdd($prac), 404);

        $this->validate(['itemName' => ['required', 'string']]);

        $item = SitePracCompletionItem::create([
            'prac_id' => $prac->id,
            'name' => trim($this->itemName),
            'order' => $prac->items()->count() + 1,
            'status' => 1,
        ]);

        // Preserve the legacy Prac behaviour. This currently creates the item
        // Todo record; the model itself intentionally has assignment/email off.
        if ($prac->super_id) {
            $item->createAssignSupervisorToDo($prac->super_id);
        }

        $prac->touch();

        $this->showAddModal = false;
        $this->resetForm();
        $this->message = 'Item added.';
        $this->dispatch('prac-items-updated');
    }

    public function openEdit(int $itemId): void
    {
        $prac = $this->prac();
        abort_unless($this->canEdit($prac), 404);

        $item = $this->item($itemId);
        $planner = $item->planner;

        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->itemName = $item->name;
        $this->assignedTo = $item->assigned_to ? (string)$item->assigned_to : '';
        $this->plannerTaskId = $planner?->task_id ? (string)$planner->task_id : '';
        $this->plannerDate = $planner?->from?->format('d/m/Y') ?? '';
        $this->itemStatus = (string)$item->status;

        $this->showAddModal = false;
        $this->showEditModal = true;
        $this->showDeleteModal = false;
    }

    public function updatedAssignedTo(): void
    {
        $this->plannerTaskId = '';
        $this->plannerDate = '';
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $prac = $this->prac();
        abort_unless($this->canEdit($prac), 404);
        abort_unless($this->editingItemId, 404);

        $item = $this->item($this->editingItemId);

        $this->validate([
            'assignedTo' => ['nullable', 'integer'],
            'plannerTaskId' => ['nullable', 'integer'],
            'plannerDate' => [$this->plannerTaskId ? 'required' : 'nullable', 'date_format:d/m/Y'],
            'itemStatus' => ['required', 'in:0,1'],
        ]);

        if (!$this->validCompanyId($this->assignedTo)) {
            $this->addError('assignedTo', 'Please select a valid company.');
            return;
        }

        if ($this->plannerTaskId && !$this->assignedTo) {
            $this->addError('assignedTo', 'Please select a company before selecting a planner task.');
            return;
        }

        $plannerTasks = $this->plannerTaskOptions();

        if ($this->plannerTaskId && !array_key_exists((int)$this->plannerTaskId, $plannerTasks)) {
            $this->addError('plannerTaskId', 'Please select a valid planner task for this company.');
            return;
        }

        $assignedToOriginal = $item->assigned_to;
        $statusOriginal = (string)$item->status;

        // The old AJAX save recreated the planner row whenever planner details
        // were saved. Keep that effective behaviour while ensuring planner_id
        // always points to the new live row.
        if ($item->planner_id) {
            SitePlanner::whereKey($item->planner_id)->delete();
            $item->planner_id = null;
            $item->save();
        }

        if ($this->plannerTaskId) {
            $plannerDate = Carbon::createFromFormat('d/m/Y', $this->plannerDate)->startOfDay();

            $planner = SitePlanner::create([
                'site_id' => $prac->site_id,
                'from' => $plannerDate->toDateTimeString(),
                'to' => $plannerDate->toDateTimeString(),
                'days' => 1,
                'entity_type' => 'c',
                'entity_id' => (int)$this->assignedTo,
                'task_id' => (int)$this->plannerTaskId,
            ]);

            $item->planner_id = $planner->id;
            $item->save();
        }

        $itemRequest = [
            'assigned_to' => $this->assignedTo !== '' ? (int)$this->assignedTo : null,
            'status' => (int)$this->itemStatus,
        ];

        // Prac item status is intentionally the reverse of Maintenance:
        // 1 = Incomplete, 0 = Completed.
        if ($this->itemStatus !== $statusOriginal) {
            if ($this->itemStatus === '1') {
                $itemRequest['sign_by'] = null;
                $itemRequest['sign_at'] = null;

                Action::create([
                    'action' => 'Prac Item has been mark as NOT completed',
                    'table' => 'site_prac_completion',
                    'table_id' => $prac->id,
                ]);
            } else {
                $itemRequest['sign_by'] = Auth::id();
                $itemRequest['sign_at'] = now();

                Action::create([
                    'action' => 'Prac Item has been completed',
                    'table' => 'site_prac_completion',
                    'table_id' => $prac->id,
                ]);
            }
        }

        $item->update($itemRequest);

        // Preserve the existing company-assignment notification/action/Todo close.
        if ($this->assignedTo !== '' && (string)$this->assignedTo !== (string)$assignedToOriginal) {
            $company = Company::find($this->assignedTo);

            if ($company) {
                if ($company->primary_contact()) {
                    $item->emailAssigned($company->primary_contact());
                }

                Action::create([
                    'action' => "Company assigned to request updated to {$company->name}",
                    'table' => 'site_prac_completion',
                    'table_id' => $prac->id,
                ]);
            }

            $prac->closeToDo();
            $item->closeToDo();
        }

        $prac->touch();

        $this->showEditModal = false;
        $this->resetForm();
        $this->message = 'Item updated.';
        $this->dispatch('prac-items-updated');
    }

    public function confirmDelete(int $itemId): void
    {
        $prac = $this->prac();
        abort_unless($this->canDelete($prac), 404);

        $item = $this->item($itemId);

        $this->resetValidation();
        $this->deletingItemId = $item->id;
        $this->itemName = $item->name;
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        $prac = $this->prac();
        abort_unless($this->canDelete($prac), 404);
        abort_unless($this->deletingItemId, 404);

        $item = $this->item($this->deletingItemId);

        if ($item->planner_id) {
            SitePlanner::whereKey($item->planner_id)->delete();
        }

        $item->closeToDo();
        $item->delete();

        $order = 1;
        foreach ($prac->items()->orderBy('order')->get() as $remainingItem) {
            $remainingItem->order = $order++;
            $remainingItem->save();
        }

        $prac->touch();

        $this->showDeleteModal = false;
        $this->resetForm();
        $this->message = 'Item deleted.';
        $this->dispatch('prac-items-updated');
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
        $prac = $this->prac();

        $allItems = $prac->items()->with(['assigned', 'planner.task'])->orderBy('order')->get();
        $items = match ($this->filter) {
            'completed' => $allItems->where('status', 0)->values(),
            'outstanding' => $allItems->where('status', 1)->values(),
            default => $allItems,
        };

        $userIds = $items->pluck('sign_by')->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $itemsTotal = $allItems->count();
        $itemsDone = $allItems->where('status', 0)->count();
        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;

        return view('livewire.site.prac-completion.items', [
            'prac' => $prac,
            'items' => $items,
            'users' => $users,
            'allDone' => $allDone,
            'canAdd' => $this->canAdd($prac),
            'canEdit' => $this->canEdit($prac),
            'canDelete' => $this->canDelete($prac),
            'companyOptions' => $this->companyOptions(),
            'plannerTaskOptions' => $this->plannerTaskOptions(),
        ]);
    }
}
