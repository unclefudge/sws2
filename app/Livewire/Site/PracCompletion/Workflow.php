<?php

namespace App\Livewire\Site\PracCompletion;

use App\Models\Misc\Action;
use App\Models\Site\SitePracCompletion;
use App\Services\Zoho\ZohoConnectService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use nilsenj\Toastr\Facades\Toastr;

class Workflow extends Component
{
    #[Locked]
    public int $pracId;

    public function mount(int $pracId): void
    {
        $this->pracId = $pracId;
    }

    protected function prac(): SitePracCompletion
    {
        return SitePracCompletion::findOrFail($this->pracId);
    }

    protected function editablePrac(): SitePracCompletion
    {
        $prac = $this->prac();
        abort_unless(Auth::user()->allowed2('edit.prac.completion', $prac), 404);

        return $prac;
    }

    protected function itemCounts(SitePracCompletion $prac): array
    {
        $itemsTotal = $prac->items()->count();
        $itemsDone = $prac->items()->whereNotNull('sign_by')->count();

        return [$itemsTotal, $itemsDone];
    }

    #[On('prac-items-updated')]
    public function refreshWorkflow(): void
    {
        // Re-render sign-off/status controls after the sibling Items component changes.
    }

    public function signSupervisor()
    {
        $prac = $this->editablePrac();
        [$itemsTotal, $itemsDone] = $this->itemCounts($prac);

        $isSupervisor = in_array(Auth::id(), $prac->site->areaSupervisors()->pluck('id')->toArray())
            || Auth::id() == $prac->super_id
            || Auth::user()->hasPermission2('sig.prac.completion');

        abort_unless($itemsTotal > 0 && $itemsDone === $itemsTotal && !$prac->supervisor_sign_by && $isSupervisor, 404);

        $prac->closeToDo();

        if (!$prac->manager_sign_by) {
            $prac->createManagerSignOffToDo([108]);
        }

        Action::create([
            'action' => 'Report has been signed off by Supervisor',
            'table' => 'site_prac_completion',
            'table_id' => $prac->id,
        ]);

        $prac->supervisor_sign_by = Auth::id();
        $prac->supervisor_sign_at = now();
        $prac->save();

        $this->completeIfFullySigned($prac);

        Toastr::success('Updated Report');

        return redirect()->to('/site/prac-completion/' . $prac->id);
    }

    public function signManager()
    {
        $prac = $this->editablePrac();
        [$itemsTotal, $itemsDone] = $this->itemCounts($prac);

        $canManagerSign = Auth::user()->allowed2('sig.prac.completion', $prac) || Auth::user()->hasPermission2('sig.prac.completion');

        abort_unless(
            $itemsTotal > 0
            && $itemsDone === $itemsTotal
            && $prac->supervisor_sign_by
            && !$prac->manager_sign_by
            && $canManagerSign,
            404
        );

        $prac->closeToDo();

        Action::create([
            'action' => 'Report has been signed off by Manager',
            'table' => 'site_prac_completion',
            'table_id' => $prac->id,
        ]);

        // Preserve the existing completed-email behaviour and timing.
        $emailList = [config('mail.email_dev')];

        if (app()->environment('prod')) {
            $emailList = $prac->site->company->notificationsUsersEmailType('prac.completion.completed');
        }

        if ($emailList) {
            Mail::to($emailList)->send(new \App\Mail\Site\SitePracCompletionCompleted($prac));
        }

        // Preserve the existing Zoho Connect side effect, including the current
        // behaviour of attempting this outside production as well.
        if (app()->environment('prod') || true) {
            $zoho = app(ZohoConnectService::class);
            $cardTitle = $prac->site->name;
            $statusCompleted = '185487000002355093';
            $constructionBoardId = '185487000002355019';

            try {
                $zoho->updateTaskStatusByTitle($constructionBoardId, $cardTitle, $statusCompleted);
            } catch (\RuntimeException $e) {
                if (!str_starts_with($e->getMessage(), 'Task not found:')) {
                    throw $e;
                }

                Log::warning('Zoho task not found, continuing function', [
                    'board_id' => $constructionBoardId,
                    'card_title' => $cardTitle,
                    'status_id' => $statusCompleted,
                    'error' => $e->getMessage(),
                ]);

                Toastr::error('Failed to update task status in Zoho. The task may not exist in Zoho.');
            }
        }

        $prac->manager_sign_by = Auth::id();
        $prac->manager_sign_at = now();
        $prac->save();

        $this->completeIfFullySigned($prac);

        Toastr::success('Updated Report');

        return redirect()->to('/site/prac-completion/' . $prac->id);
    }

    public function placeOnHold()
    {
        $prac = $this->editablePrac();
        [$itemsTotal, $itemsDone] = $this->itemCounts($prac);

        abort_unless(!$prac->master && (int)$prac->status === 1 && $itemsTotal > 0 && $itemsDone !== $itemsTotal, 404);

        $prac->moveToHold(Auth::user());

        Toastr::success('Updated Report');

        return redirect()->to('/site/prac-completion/' . $prac->id);
    }

    public function makeActive()
    {
        $prac = $this->editablePrac();

        abort_unless(!$prac->master && in_array((int)$prac->status, [4, -1], true), 404);

        $prac->moveToActive(Auth::user());

        Toastr::success('Updated Report');

        return redirect()->to('/site/prac-completion/' . $prac->id);
    }

    protected function completeIfFullySigned(SitePracCompletion $prac): void
    {
        $prac->refresh();

        if ($prac->supervisor_sign_by && $prac->manager_sign_by) {
            $prac->status = 0;
            $prac->save();
        }
    }

    public function render()
    {
        $prac = $this->prac();
        [$itemsTotal, $itemsDone] = $this->itemCounts($prac);

        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;
        $canEdit = Auth::user()->allowed2('edit.prac.completion', $prac);

        $isSupervisor = in_array(Auth::id(), $prac->site->areaSupervisors()->pluck('id')->toArray())
            || Auth::id() == $prac->super_id
            || Auth::user()->hasPermission2('sig.prac.completion');

        $canManager = Auth::user()->allowed2('sig.prac.completion', $prac) || Auth::user()->hasPermission2('sig.prac.completion');

        $canSupervisorSign = $canEdit && $allDone && !$prac->supervisor_sign_by && $isSupervisor;
        $canManagerSign = $canEdit && $allDone && $prac->supervisor_sign_by && !$prac->manager_sign_by && $canManager;
        $canClearSignoff = $prac->supervisor_sign_by && $canManager;
        $canPlaceOnHold = $canEdit && !$prac->master && (int)$prac->status === 1 && $itemsTotal > 0 && $itemsDone !== $itemsTotal;
        $canMakeActive = $canEdit && !$prac->master && in_array((int)$prac->status, [4, -1], true);

        return view('livewire.site.prac-completion.workflow', compact(
            'prac',
            'allDone',
            'canSupervisorSign',
            'canManagerSign',
            'canClearSignoff',
            'canPlaceOnHold',
            'canMakeActive'
        ));
    }
}
