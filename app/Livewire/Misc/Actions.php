<?php

namespace App\Livewire\Misc;

use App\Livewire\Concerns\NotifiesWithToastr;
use App\Mail\Site\SiteMaintenanceNote;
use App\Models\Misc\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Actions extends Component
{
    use NotifiesWithToastr, WithPagination;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    protected $paginationTheme = 'bootstrap';

    #[Locked]
    public string $table;

    #[Locked]
    public int $tableId;

    #[Locked]
    public bool $allowAdd = true;

    public bool $showModal = false;
    public string $note = '';
    public int $perPage = 10;

    public function mount(string $table, int $tableId, bool $allowAdd = true): void
    {
        $this->table = $table;
        $this->tableId = $tableId;
        $this->allowAdd = $allowAdd;

        $cachedPerPage = (int) Cache::get($this->perPageCacheKey(), 10);
        $this->perPage = in_array($cachedPerPage, self::PER_PAGE_OPTIONS, true) ? $cachedPerPage : 10;
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;

        Cache::forever($this->perPageCacheKey(), $this->perPage);
        $this->resetPage($this->pageName());
    }

    protected function perPageCacheKey(): string
    {
        return 'sws:user:' . Auth::id() . ':actions:per_page';
    }

    protected function pageName(): string
    {
        return 'notesPage_' . preg_replace('/[^A-Za-z0-9_]/', '_', $this->table) . '_' . $this->tableId;
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
        $this->resetPage($this->pageName());
        $this->notify('Note added.');
    }

    public function render()
    {
        $pageName = $this->pageName();

        return view('livewire.misc.actions', [
            'actions' => Action::with('user')
                ->where('table', $this->table)
                ->where('table_id', $this->tableId)
                ->latest()
                ->paginate($this->perPage, ['*'], $pageName),
            'pageName' => $pageName,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}
