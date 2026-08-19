<?php

namespace App\Livewire\Site\Maintenance;

use App\Models\Misc\Action;
use App\Models\Site\SiteMaintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Workflow extends Component
{
    #[Locked]
    public int $maintenanceId;

    public function mount(int $maintenanceId): void
    {
        $this->maintenanceId = $maintenanceId;
    }

    protected function maintenance(): SiteMaintenance
    {
        return SiteMaintenance::findOrFail($this->maintenanceId);
    }

    protected function editableMaintenance(): SiteMaintenance
    {
        $main = $this->maintenance();

        abort_unless(Auth::user()->allowed2('edit.site.maintenance', $main), 404);

        return $main;
    }

    #[On('maintenance-items-updated')]
    public function refreshWorkflow(): void
    {
        // The listener exists so the sign-off state immediately re-renders when
        // the separate Maintenance Items component changes an item.
    }

    public function signSupervisor()
    {
        $main = $this->editableMaintenance();

        $itemsTotal = $main->items()->count();
        $itemsDone = $main->items()->whereNotNull('done_by')->count();
        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;

        $isSupervisor =
            in_array(Auth::id(), $main->site->areaSupervisors()->pluck('id')->toArray())
            || Auth::id() == $main->super_id
            || Auth::user()->hasPermission2('sig.site.maintenance');

        abort_unless($allDone && !$main->supervisor_sign_by && $isSupervisor, 404);

        $mainRequest = [
            'supervisor_sign_by' => Auth::id(),
            'supervisor_sign_at' => now(),
        ];

        // Preserve the old updateReport() side effects.
        $main->closeToDo();

        if (!$main->manager_sign_by) {
            $main->createManagerSignOffToDo(
                array_merge(getUserIdsWithRoles('con-construction-manager'), [108])
            );
        }

        Action::create([
            'action' => 'Request has been signed off by Supervisor',
            'table' => 'site_maintenance',
            'table_id' => $main->id,
        ]);

        $main->update($mainRequest);
        $this->completeIfFullySigned($main);

        return redirect()->to('/site/maintenance/' . $main->id);
    }

    public function signManager()
    {
        $main = $this->editableMaintenance();

        $itemsTotal = $main->items()->count();
        $itemsDone = $main->items()->whereNotNull('done_by')->count();
        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;

        $canManagerSign =
            Auth::user()->allowed2('sig.site.maintenance', $main)
            || Auth::user()->hasPermission2('sig.site.maintenance');

        abort_unless(
            $allDone
            && $main->supervisor_sign_by
            && !$main->manager_sign_by
            && $canManagerSign,
            404
        );

        $mainRequest = [
            'manager_sign_by' => Auth::id(),
            'manager_sign_at' => now(),
        ];

        $main->closeToDo();

        // Preserve the old rule: if this is the last active Maintenance request,
        // return the Site to Completed.
        $active = SiteMaintenance::where('status', 1)
            ->where('site_id', $main->site_id)
            ->get();

        if ($active->count() < 2) {
            $main->site->status = 0;
            $main->site->save();
        }

        Action::create([
            'action' => 'Request has been signed off by construction Manager',
            'table' => 'site_maintenance',
            'table_id' => $main->id,
        ]);

        // Keep the existing notification behaviour.
        $emailList = [config('mail.email_dev')];

        if (app()->environment('prod')) {
            $emailList = $main->site->company
                ->notificationsUsersEmailType('site.maintenance.completed');
        }

        if ($emailList) {
            Mail::to($emailList)->send(
                new \App\Mail\Site\SiteMaintenanceCompleted($main)
            );
        }

        $main->update($mainRequest);
        $this->completeIfFullySigned($main);

        return redirect()->to('/site/maintenance/' . $main->id);
    }

    public function placeUnderReview()
    {
        $main = $this->editableMaintenance();

        $itemsTotal = $main->items()->count();
        $itemsDone = $main->items()->whereNotNull('done_by')->count();

        abort_unless(
            !$main->master
            && (int) $main->status === 1
            && $itemsTotal > 0
            && $itemsDone !== $itemsTotal,
            404
        );

        // IMPORTANT: this preserves the legacy Vue behaviour. The old button
        // was labelled "Place On Hold" but sent status 2, which is UNDER REVIEW.
        $main->status = 2;
        $main->save();

        return redirect()->to('/site/maintenance/' . $main->id);
    }

    public function makeActive()
    {
        $main = $this->editableMaintenance();

        abort_unless(
            !$main->master && in_array((int) $main->status, [2, -1], true),
            404
        );

        // Preserve the old bottom-button behaviour: updateReport() simply
        // changed status to 1 here; it did not run the normal form's
        // re-activation/signature-reset logic.
        $main->status = 1;
        $main->save();

        return redirect()->to('/site/maintenance/' . $main->id);
    }

    protected function completeIfFullySigned(SiteMaintenance $main): void
    {
        $main->refresh();

        if ($main->supervisor_sign_by && $main->manager_sign_by) {
            $main->status = 0;

            if (!$main->ac_form_required) {
                $main->ac_form_sent = '0001-01-01 01:01:01';
            }

            $main->save();
        }
    }

    public function render()
    {
        $main = $this->maintenance();

        $itemsTotal = $main->items()->count();
        $itemsDone = $main->items()->whereNotNull('done_by')->count();
        $allDone = $itemsTotal > 0 && $itemsDone === $itemsTotal;

        $isSupervisor =
            in_array(Auth::id(), $main->site->areaSupervisors()->pluck('id')->toArray())
            || Auth::id() == $main->super_id
            || Auth::user()->hasPermission2('sig.site.maintenance');

        $canEdit = Auth::user()->allowed2('edit.site.maintenance', $main);

        $canSupervisorSign =
            $canEdit
            && $allDone
            && !$main->supervisor_sign_by
            && $isSupervisor;

        $canManagerSign =
            $canEdit
            && $allDone
            && $main->supervisor_sign_by
            && !$main->manager_sign_by
            && (
                Auth::user()->allowed2('sig.site.maintenance', $main)
                || Auth::user()->hasPermission2('sig.site.maintenance')
            );

        $canPlaceUnderReview =
            $canEdit
            && !$main->master
            && (int) $main->status === 1
            && $itemsTotal > 0
            && $itemsDone !== $itemsTotal;

        $canMakeActive =
            $canEdit
            && !$main->master
            && in_array((int) $main->status, [2, -1], true);

        return view('livewire.site.maintenance.workflow', compact(
            'main',
            'itemsTotal',
            'itemsDone',
            'allDone',
            'canSupervisorSign',
            'canManagerSign',
            'canPlaceUnderReview',
            'canMakeActive'
        ));
    }
}
