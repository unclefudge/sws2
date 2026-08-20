<?php

namespace App\Livewire\Site\Qa;

use App\Models\Misc\Action;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Workflow extends Component
{
    #[Locked]
    public int $qaId;

    public function mount(int $qaId): void
    {
        $this->qaId = $qaId;
    }

    protected function qa(): SiteQa
    {
        return SiteQa::findOrFail($this->qaId);
    }

    protected function editableQa(): SiteQa
    {
        $qa = $this->qa();
        abort_unless(Auth::user()->allowed2('edit.site.qa', $qa), 404);

        return $qa;
    }

    protected function itemCounts(SiteQa $qa): array
    {
        $itemsTotal = $qa->items()->count();
        $itemsDone = $qa->items()->where('status', '!=', 0)->count();

        return [$itemsTotal, $itemsDone];
    }

    protected function handoverBlocked(SiteQa $qa): bool
    {
        return !$qa->master && (int)$qa->master_id === 2581 && $qa->allDocs(1)->count() > 1;
    }

    #[On('qa-items-updated')]
    public function refreshWorkflow(): void
    {
        // Re-render sign-off/status controls whenever the Items component changes.
    }

    public function markNotRequired()
    {
        $qa = $this->editableQa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        abort_unless(!$qa->master && (int)$qa->status === 1 && $itemsDone === 0, 404);

        $qa->closeToDo(Auth::user());
        $qa->status = -1;
        $qa->save();

        return redirect()->to('/site/qa/' . $qa->id);
    }

    public function placeOnHold()
    {
        $qa = $this->editableQa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        abort_unless(!$qa->master && (int)$qa->status === 1 && $itemsTotal !== 0 && $itemsDone !== $itemsTotal, 404);

        $qa->moveToHold(Auth::user());

        return redirect()->to('/site/qa/' . $qa->id);
    }

    public function changeToOwnersWorks()
    {
        $qa = $this->editableQa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        abort_unless(!$qa->master && (int)$qa->status === 1 && $itemsTotal !== 0 && $itemsDone !== $itemsTotal, 404);

        $qa->moveToOwner(Auth::user());

        return redirect()->to('/site/qa/' . $qa->id);
    }

    public function makeActive()
    {
        $qa = $this->editableQa();

        abort_unless(!$qa->master && in_array((int)$qa->status, [4, 5, -1], true), 404);

        $qa->moveToActive(Auth::user());

        return redirect()->to('/site/qa/' . $qa->id);
    }

    public function signSupervisor()
    {
        $qa = $this->editableQa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        abort_unless(
            !$qa->master
            && !$this->handoverBlocked($qa)
            && $itemsTotal !== 0
            && $itemsDone === $itemsTotal
            && !$qa->supervisor_sign_by,
            404
        );

        // Preserve legacy updateReport(): supervisor sign-off re-activates the
        // report without creating a fresh Supervisor Todo.
        if ((int)$qa->status !== 1) {
            Action::create(['action' => 'Moved report to Active', 'table' => 'site_qa', 'table_id' => $qa->id]);
            $qa->status = 1;
            $qa->save();
        }

        $qa->closeToDo(Auth::user());

        if (!$qa->manager_sign_by) {
            $qa->createManagerSignOffToDo([108]);
        }

        $qa->supervisor_sign_by = Auth::id();
        $qa->supervisor_sign_at = now();
        $qa->save();

        $this->completeIfFullySigned($qa);

        return redirect()->to('/site/qa/' . $qa->id);
    }

    public function signManager()
    {
        $qa = $this->editableQa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        abort_unless(
            !$qa->master
            && !$this->handoverBlocked($qa)
            && $itemsTotal !== 0
            && $itemsDone === $itemsTotal
            && $qa->supervisor_sign_by
            && !$qa->manager_sign_by
            && Auth::user()->hasPermission2('sig.site.qa'),
            404
        );

        $qa->closeToDo(Auth::user());
        $qa->manager_sign_by = Auth::id();
        $qa->manager_sign_at = now();
        $qa->save();

        $this->completeIfFullySigned($qa);

        return redirect()->to('/site/qa/');
    }

    protected function completeIfFullySigned(SiteQa $qa): void
    {
        $qa->refresh();

        if (!$qa->supervisor_sign_by || !$qa->manager_sign_by) {
            return;
        }

        $qa->status = 0;
        $qa->save();

        if ((int)$qa->master_id === 2581 && $qa->owned_by->notificationsUsersType('site.qa.handover')) {
            Mail::to($qa->owned_by->notificationsUsersType('site.qa.handover'))->send(new \App\Mail\Site\SiteQaHandover($qa));
        }

        if ((int)$qa->master_id === 2752 && $qa->owned_by->notificationsUsersType('site.qa.super.photo')) {
            Mail::to($qa->owned_by->notificationsUsersType('site.qa.super.photo'))->send(new \App\Mail\Site\SiteQaSuperPhoto($qa));
        }
    }

    public function render()
    {
        $qa = $this->qa();
        [$itemsTotal, $itemsDone] = $this->itemCounts($qa);

        $allDone = $itemsTotal !== 0 && $itemsDone === $itemsTotal;
        $canEdit = !$qa->master && Auth::user()->allowed2('edit.site.qa', $qa);
        $handoverBlocked = $this->handoverBlocked($qa);
        $outstandingHandoverQas = $handoverBlocked ? $qa->allDocs(1)->where('id', '!=', $qa->id) : collect();

        $canMarkNotRequired = $canEdit && (int)$qa->status === 1 && $itemsDone === 0;
        $canSupervisorSign = $canEdit && !$handoverBlocked && $allDone && !$qa->supervisor_sign_by;
        $canManagerSign = $canEdit && !$handoverBlocked && $allDone && $qa->supervisor_sign_by && !$qa->manager_sign_by && Auth::user()->hasPermission2('sig.site.qa');
        $canPlaceOnHold = $canEdit && (int)$qa->status === 1 && $itemsTotal !== 0 && $itemsDone !== $itemsTotal;
        $canMakeActive = $canEdit && in_array((int)$qa->status, [4, 5, -1], true);

        return view('livewire.site.qa.workflow', compact(
            'qa',
            'itemsTotal',
            'itemsDone',
            'allDone',
            'canEdit',
            'handoverBlocked',
            'outstandingHandoverQas',
            'canMarkNotRequired',
            'canSupervisorSign',
            'canManagerSign',
            'canPlaceOnHold',
            'canMakeActive'
        ));
    }
}
