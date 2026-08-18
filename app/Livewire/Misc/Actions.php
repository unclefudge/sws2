<?php

namespace App\Livewire\Misc;

use App\Models\Misc\Action;
use Livewire\Component;

class Actions extends Component
{
    public string $table;
    public int $tableId;

    public bool $showModal = false;
    public string $note = '';

    public function mount(string $table, int $tableId): void
    {
        $this->table = $table;
        $this->tableId = $tableId;
    }

    public function add(): void
    {
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
        $this->validate(['note' => ['required', 'string'],]);

        $action = Action::create(['table' => $this->table, 'table_id' => $this->tableId, 'action' => $this->note,]);

        // The Action model resolves its parent record through its
        // polymorphic "record" relationship.
        if ($record = $action->record) {
            $record->touch();

            if (method_exists($record, 'emailAction')) {
                $record->emailAction($action);
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
