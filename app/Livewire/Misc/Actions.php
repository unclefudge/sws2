<?php

namespace App\Livewire\Misc;

use App\Mail\Site\SiteMaintenanceNote;
use App\Models\Misc\Action;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Actions extends Component
{
    #[Locked]
    public string $table;

    #[Locked]
    public int $tableId;

    #[Locked]
    public bool $allowAdd = true;

    public bool $showModal = false;
    public string $note = '';

    public function mount(string $table, int $tableId, bool $allowAdd = true): void
    {
        $this->table = $table;
        $this->tableId = $tableId;
        $this->allowAdd = $allowAdd;
    }

    public function add(): void
    {
        abort_unless($this->allowAdd, 403);
        $this->resetValidation();
        $this->note = '';
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->resetValidation();
        $this->note = '';
        $this->showModal = false;
    }

    public function save(): void
    {
        abort_unless($this->allowAdd, 403);

        $this->validate(['note' => ['required', 'string'],]);

        $action = Action::create(['table' => $this->table, 'table_id' => $this->tableId, 'action' => $this->note,]);

        // The Action model resolves its parent record through its
        // polymorphic "record" relationship.
        if ($record = $action->record) {
            $record->touch();

            if (method_exists($record, 'emailAction')) {
                $record->emailAction($action);
            }

            // Preserve the legacy Maintenance Note notification.
            if ($this->table === 'site_maintenance' && $record->super_id) {
                $emailTo = [config('mail.email_dev')];

                if (app()->environment('prod')) {
                    $emailTo = ['kirstie@capecod.com.au'];
                }

                Mail::to($emailTo)->send(new SiteMaintenanceNote($record, $action));
            }
        }

        $this->note = '';
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.misc.actions', [
            'actions' => Action::with('user')
                ->where('table', $this->table)
                ->where('table_id', $this->tableId)
                ->latest()
                ->get(),
        ]);
    }
}
