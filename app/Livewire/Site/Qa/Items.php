<?php

namespace App\Livewire\Site\Qa;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Items extends Component
{
    use NotifiesWithToastr;

    #[Locked]
    public int $qaId;

    public bool $showCompanyModal = false;
    public ?int $editingItemId = null;
    public string $editingItemName = '';
    public $doneBy = '';
    public string $doneByOther = '';
    public $doneByAll = '1';

    public function mount(int $qaId): void
    {
        $this->qaId = $qaId;
    }

    protected function qa(): SiteQa
    {
        return SiteQa::findOrFail($this->qaId);
    }

    protected function item(int $itemId): SiteQaItem
    {
        return SiteQaItem::where('doc_id', $this->qaId)->findOrFail($itemId);
    }

    protected function effectiveDoneBy(SiteQa $qa, SiteQaItem $item)
    {
        if ($item->done_by) {
            return $item->done_by;
        }

        $plannedTask = SitePlanner::where('site_id', $qa->site_id)->where('task_id', $item->task_id)->first();

        return ($plannedTask && $plannedTask->entity_type === 'c' && !$item->super) ? $plannedTask->entity_id : null;
    }

    protected function canUpdateStatus(SiteQa $qa): bool
    {
        return !$qa->master && !$qa->isSigned() && Auth::user()->allowed2('edit.site.qa', $qa);
    }

    protected function canOpenCompany(SiteQa $qa, SiteQaItem $item): bool
    {
        if ($qa->master) {
            return false;
        }

        $effectiveDoneBy = $this->effectiveDoneBy($qa, $item);
        $status = (int)$item->status;

        if ($status === 0) {
            return Auth::user()->allowed2('edit.site.qa', $qa) && ($effectiveDoneBy || !$item->super);
        }

        return $status === 1
            && (int)$qa->status !== 0
            && $effectiveDoneBy
            && Auth::user()->hasPermission2('sig.site.qa');
    }

    protected function companyOptionsForTask(int $taskId): array
    {
        $task = Task::find($taskId);

        if (!$task) {
            return [];
        }

        $companyList = Auth::user()->company->companies('1')->pluck('id')->toArray();

        $companies = Company::select(['companys.id', 'companys.name', 'companys.licence_no'])
            ->join('company_trade', 'companys.id', '=', 'company_trade.company_id')
            ->where('companys.status', '1')
            ->where('company_trade.trade_id', $task->trade_id)
            ->whereIn('companys.id', $companyList)
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($companies as $company) {
            $options[$company->id] = $company->name_alias;
        }

        $options[1] = 'Other Company (specify)';

        return $options;
    }

    public function updateStatus(int $itemId, $status): void
    {
        $qa = $this->qa();
        abort_unless($this->canUpdateStatus($qa), 404);

        $item = $this->item($itemId);
        $status = (string)$status;

        if ($status === '') {
            return;
        }

        abort_unless(in_array($status, ['1', '-1'], true), 422);

        $effectiveDoneBy = $this->effectiveDoneBy($qa, $item);

        // Legacy QA only allowed "Sign Off" when a non-supervisor item had a
        // company assigned (including a company inferred from the Planner).
        if ($status === '1') {
            abort_unless($item->super || $effectiveDoneBy, 422);

            $item->sign_by = Auth::id();
            $item->sign_at = now();

            if ((int)$qa->status !== 1) {
                $qa->moveToActive(Auth::user());
            }
        }

        if ($effectiveDoneBy) {
            $item->done_by = $effectiveDoneBy;
        }

        $item->status = (int)$status;
        $item->save();
        $qa->touch();

        $this->dispatch('qa-items-updated');
        $this->notify(
            $status === '1' ? 'QA item signed off.' : 'QA item marked N/A.',
            $status === '1' ? 'success' : 'warning'
        );
    }

    public function resetStatus(int $itemId): void
    {
        $qa = $this->qa();
        $item = $this->item($itemId);

        abort_unless(
            !$qa->master
            && Auth::user()->allowed2('view.site.qa', $qa)
            && (int)$qa->status !== 0
            && !$qa->isSigned()
            && $item->sign_by,
            404
        );

        $item->status = 0;
        $item->sign_by = null;
        $item->sign_at = null;
        $item->save();
        $qa->touch();

        $this->dispatch('qa-items-updated');
        $this->notify('QA item reset.', 'info');
    }

    public function openCompany(int $itemId): void
    {
        $qa = $this->qa();
        $item = $this->item($itemId);

        abort_unless($this->canOpenCompany($qa, $item), 404);

        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->editingItemName = $item->name;
        $this->doneBy = $this->effectiveDoneBy($qa, $item) ?: '';
        $this->doneByOther = $item->done_by_other ?? '';
        $this->doneByAll = '1';
        $this->showCompanyModal = true;
    }

    public function closeCompanyModal(): void
    {
        $this->resetValidation();
        $this->showCompanyModal = false;
        $this->editingItemId = null;
        $this->editingItemName = '';
        $this->doneBy = '';
        $this->doneByOther = '';
        $this->doneByAll = '1';
    }

    public function saveCompany(): void
    {
        $qa = $this->qa();
        abort_unless($this->editingItemId !== null, 404);

        $item = $this->item($this->editingItemId);
        abort_unless($this->canOpenCompany($qa, $item), 404);

        $this->validate([
            'doneBy' => ['required'],
            'doneByOther' => [(string)$this->doneBy === '1' ? 'required' : 'nullable', 'string'],
            'doneByAll' => ['required', 'in:0,1'],
        ]);

        $options = $this->companyOptionsForTask((int)$item->task_id);
        abort_unless((string)$this->doneBy === '1' || array_key_exists((int)$this->doneBy, $options), 422);

        $item->done_by = (int)$this->doneBy;
        $item->done_by_other = (string)$this->doneBy === '1' ? trim($this->doneByOther) : null;
        $item->save();

        // Preserve the old "Assign to all unassigned" behaviour. Planner-derived
        // companies count as already assigned, just as they did in the Vue list.
        if ((string)$this->doneByAll === '1') {
            foreach ($qa->items()->where('status', 0)->get() as $qaItem) {
                if ($qaItem->id === $item->id || $this->effectiveDoneBy($qa, $qaItem)) {
                    continue;
                }

                $qaItem->done_by = (int)$this->doneBy;
                $qaItem->done_by_other = (string)$this->doneBy === '1' ? trim($this->doneByOther) : null;
                $qaItem->save();
            }
        }

        $qa->touch();
        $this->closeCompanyModal();
        $this->dispatch('qa-items-updated');
        $this->notify('Item company updated.');
    }

    protected function rows(SiteQa $qa)
    {
        $items = $qa->items()->orderBy('order')->get();
        $taskIds = $items->pluck('task_id')->filter()->unique()->values();
        $tasks = Task::whereIn('id', $taskIds)->get()->keyBy('id');

        $userIds = $items->pluck('sign_by')->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return $items->map(function (SiteQaItem $item) use ($qa, $tasks, $users) {
            $doneBy = $this->effectiveDoneBy($qa, $item);
            $doneByCompany = '';
            $doneByLicence = '';

            if ($doneBy) {
                if ((int)$doneBy === 1) {
                    $doneByCompany = $item->done_by_other ?? '';
                    $doneByLicence = '???????';
                } else {
                    $company = Company::find($doneBy);
                    $doneByCompany = $company?->name_alias ?? 'Unknown Company';
                    $doneByLicence = $company?->licence_no ?? '';
                }
            }

            return [
                'model' => $item,
                'id' => $item->id,
                'order' => $item->order,
                'name' => $item->name,
                'super' => (bool)$item->super,
                'task_code' => $tasks->get($item->task_id)?->code ?? '',
                'status' => (int)$item->status,
                'done_by' => $doneBy,
                'done_by_company' => $doneByCompany,
                'done_by_licence' => $doneByLicence,
                'sign_by' => $item->sign_by,
                'sign_by_name' => $item->sign_by ? ($users->get($item->sign_by)?->full_name ?? 'Unknown') : '',
                'sign_at' => $item->sign_at,
                'can_open_company' => $this->canOpenCompany($qa, $item),
            ];
        });
    }

    public function render()
    {
        $qa = $this->qa();
        $rows = $this->rows($qa);
        $canUpdateStatus = $this->canUpdateStatus($qa);
        $companyOptions = [];

        if ($this->showCompanyModal && $this->editingItemId) {
            $companyOptions = $this->companyOptionsForTask((int)$this->item($this->editingItemId)->task_id);
        }

        return view('livewire.site.qa.items', compact('qa', 'rows', 'canUpdateStatus', 'companyOptions'));
    }
}
