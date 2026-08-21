<?php

namespace App\Livewire\Manage\Report;

use App\Http\Controllers\Misc\ReportTasksController;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class TodoTasks extends Component
{
    use WithPagination;

    private const PER_PAGE_OPTIONS = [25, 50, 100];

    protected $paginationTheme = 'bootstrap';

    #[Locked]
    public bool $inactive = false;

    public string $assignedTasks = '1';
    public string $assignedCc = '1';
    public string $username = 'all';
    public string $activeRecord = '1';
    public string $taskType = 'all';
    public string $search = '';

    public string $sortKey = 'title';
    public string $sortDirection = 'asc';
    public int $perPage = 25;

    public array $expandedIds = [];
    public array $selectedIds = [];

    public bool $showReassignModal = false;
    public bool $showDeleteModal = false;
    public int $assignTo = 0;
    public int $modalNonce = 0;
    public string $message = '';

    public function mount(bool $inactive = false): void
    {
        $this->inactive = $inactive;

        if ($inactive) {
            $this->assignedTasks = 'all';
            $this->activeRecord = 'all';
        }

        $cachedPerPage = (int) Cache::get($this->perPageCacheKey(), 25);
        $this->perPage = in_array($cachedPerPage, self::PER_PAGE_OPTIONS, true) ? $cachedPerPage : 25;
    }

    public function updatedAssignedTasks(): void
    {
        $this->filtersChanged();
    }

    public function updatedAssignedCc(): void
    {
        $this->username = 'all';
        $this->filtersChanged();
    }

    public function updatedUsername(): void
    {
        $this->filtersChanged();
    }

    public function updatedActiveRecord(): void
    {
        $this->filtersChanged();
    }

    public function updatedTaskType(): void
    {
        $this->filtersChanged();
    }

    public function updatedSearch(): void
    {
        $this->filtersChanged(false);
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25;

        Cache::forever($this->perPageCacheKey(), $this->perPage);
        $this->resetPage('todoPage');
    }

    protected function filtersChanged(bool $clearSelected = true): void
    {
        if ($clearSelected) {
            $this->selectedIds = [];
        }

        $this->expandedIds = [];
        $this->resetPage('todoPage');
    }

    public function refreshReport(): void
    {
        Cache::forget($this->cacheKey());
        $this->message = 'Report refreshed.';
        $this->selectedIds = [];
        $this->expandedIds = [];
        $this->resetPage('todoPage');
    }

    public function sortBy(string $key): void
    {
        abort_unless(in_array($key, ['title', 'assigned_names', 'due_at', 'lastupdated'], true), 404);

        if ($this->sortKey === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortKey = $key;
            $this->sortDirection = 'asc';
        }

        $this->resetPage('todoPage');
    }

    public function toggleExpanded(int $id): void
    {
        if (in_array($id, $this->expandedIds, true)) {
            $this->expandedIds = array_values(array_diff($this->expandedIds, [$id]));
            return;
        }

        $this->expandedIds[] = $id;
    }

    public function openReassign(): void
    {
        abort_unless($this->inactive, 404);

        if (!$this->selectedIds) {
            $this->addError('selected', 'Please select at least one task.');
            return;
        }

        $this->resetValidation();
        $this->assignTo = 0;
        $this->modalNonce++;
        $this->showDeleteModal = false;
        $this->showReassignModal = true;
    }

    public function reassignSelected(): void
    {
        abort_unless($this->inactive, 404);

        $allowedUsers = $this->reassignUserOptions();
        abort_unless($this->assignTo && array_key_exists($this->assignTo, $allowedUsers), 404);

        $ids = $this->validatedSelectedIds();
        abort_unless($ids, 404);

        foreach (Todo::whereIn('id', $ids)->get() as $todo) {
            TodoUser::where('todo_id', $todo->id)->delete();
            $todo->assignUsers($this->assignTo);
        }

        Cache::forget($this->cacheKey());
        $this->selectedIds = [];
        $this->assignTo = 0;
        $this->showReassignModal = false;
        $this->message = 'Selected tasks reassigned.';
        $this->resetPage('todoPage');
    }

    public function openDelete(): void
    {
        abort_unless($this->inactive, 404);

        if (!$this->selectedIds) {
            $this->addError('selected', 'Please select at least one task.');
            return;
        }

        $this->resetValidation();
        $this->showReassignModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteSelected(): void
    {
        abort_unless($this->inactive, 404);

        $ids = $this->validatedSelectedIds();
        abort_unless($ids, 404);

        Todo::whereIn('id', $ids)->delete();

        Cache::forget($this->cacheKey());
        $this->selectedIds = [];
        $this->showDeleteModal = false;
        $this->message = 'Selected tasks deleted.';
        $this->resetPage('todoPage');
    }

    public function closeModals(): void
    {
        $this->showReassignModal = false;
        $this->showDeleteModal = false;
        $this->assignTo = 0;
        $this->resetValidation();
    }

    protected function validatedSelectedIds(): array
    {
        $allowed = collect($this->data()[0] ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_intersect(
            array_map('intval', $this->selectedIds),
            $allowed
        ));
    }

    protected function data(): array
    {
        return Cache::remember($this->cacheKey(), now()->addSeconds(30), function () {
            $controller = app(ReportTasksController::class);

            return $this->inactive
                ? $controller->todoTasksInactive()
                : $controller->todoTasks();
        });
    }

    protected function cacheKey(): string
    {
        return 'sws:user:' . Auth::id() . ':manage-report:' . ($this->inactive ? 'todo-inactive' : 'todo-active');
    }

    protected function perPageCacheKey(): string
    {
        return $this->cacheKey() . ':per_page';
    }

    protected function optionMap(array $rows): array
    {
        $options = [];

        foreach ($rows as $row) {
            $options[(string) $row['value']] = $row['text'];
        }

        return $options;
    }

    protected function userOptions(array $data): array
    {
        return match ($this->assignedCc) {
            '0' => $this->optionMap($data[6] ?? []),
            'all' => $this->optionMap($data[7] ?? []),
            default => $this->optionMap($data[5] ?? []),
        };
    }

    protected function reassignUserOptions(): array
    {
        if (!$this->inactive) {
            return [];
        }

        return Auth::user()->company->reportsTo()->usersSelect('', 1);
    }

    protected function filteredRows(array $data): Collection
    {
        $rows = collect($data[0] ?? []);
        $search = mb_strtolower(trim($this->search));

        if ($search !== '') {
            $rows = $rows->filter(function ($task) use ($search) {
                return str_contains(mb_strtolower((string) $task['title']), $search)
                    || str_contains(mb_strtolower((string) $task['assigned_names']), $search);
            });
        }

        if (!$this->inactive) {
            if ($this->assignedTasks === '0') {
                $rows = $rows->where('assigned_names', '-');
            } elseif ($this->assignedTasks === '1') {
                $rows = $rows->where('assigned_names', '!=', '-');
            }
        }

        if ($this->assignedCc === '0') {
            $rows = $rows->where('assigned_cc', 0);
        } elseif ($this->assignedCc === '1') {
            $rows = $rows->where('assigned_cc', 1);
        }

        if ($this->username !== 'all') {
            $username = $this->username;
            $rows = $rows->filter(fn ($task) => str_contains((string) $task['assigned_names'], $username));
        }

        if (!$this->inactive) {
            if ($this->taskType !== 'all') {
                $rows = $rows->where('type', $this->taskType);
            }

            $rows = $rows->where('active', (int) $this->activeRecord);
        }

        $sortKey = $this->sortKey;
        $descending = $this->sortDirection === 'desc';

        $rows = $rows->sortBy(
            fn ($task) => mb_strtolower((string) ($task[$sortKey] ?? '')),
            SORT_NATURAL,
            $descending
        );

        return $rows->values();
    }

    protected function paginate(Collection $rows): LengthAwarePaginator
    {
        $page = $this->getPage('todoPage');
        $items = $rows->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $this->perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'todoPage',
            ]
        );
    }

    public function render()
    {
        $data = $this->data();
        $rows = $this->filteredRows($data);
        $tasks = $this->paginate($rows);

        return view('livewire.manage.report.todo-tasks', [
            'tasks' => $tasks,
            'totalFiltered' => $rows->count(),
            'assignedTaskOptions' => $this->optionMap($data[1] ?? []),
            'assignedCcOptions' => $this->optionMap($data[2] ?? []),
            'taskTypeOptions' => $this->optionMap($data[3] ?? []),
            'activeRecordOptions' => $this->optionMap($data[4] ?? []),
            'userOptions' => $this->userOptions($data),
            'reassignUserOptions' => $this->reassignUserOptions(),
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }
}
