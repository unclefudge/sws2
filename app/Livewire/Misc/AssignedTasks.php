<?php

namespace App\Livewire\Misc;

use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Role2;
use App\Services\FileBank;
use App\Support\TodoTypeRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssignedTasks extends Component
{
    use WithFileUploads;

    public string $context;
    public int $contextId;

    public bool $showAddModal = false;
    public string $info = '';
    public string $dueAt = '';
    public string $assignTo = '';
    public array $userList = [];
    public array $companyList = [];
    public array $roleList = [];
    public array $uploads = [];

    public function mount(string $context, int $contextId): void
    {
        $this->context = $context;
        $this->contextId = $contextId;

        abort_unless(TodoTypeRegistry::definition($context), 404);
        abort_unless($this->parentRecord(), 404);
    }

    public function add(): void
    {
        abort_unless($this->canAdd(), 403);
        $this->resetValidation();
        $this->resetTaskForm();
        $this->showAddModal = true;
    }

    public function close(): void
    {
        $this->resetValidation();
        $this->resetTaskForm();
        $this->showAddModal = false;
    }

    public function updatedAssignTo(): void
    {
        $this->userList = [];
        $this->companyList = [];
        $this->roleList = [];
        $this->resetValidation(['userList', 'companyList', 'roleList']);
    }

    public function save(): void
    {
        abort_unless($this->canAdd(), 403);

        $this->validate([
            'info' => ['required', 'string'],
            'dueAt' => ['nullable', 'date_format:d/m/Y'],
            'assignTo' => ['required', 'in:user,company,role'],
            'userList' => ['required_if:assignTo,user', 'array'],
            'companyList' => ['required_if:assignTo,company', 'array'],
            'roleList' => ['required_if:assignTo,role', 'array'],
            'uploads.*' => ['file', 'max:20480'],
        ], [
            'userList.required_if' => 'Select at least one user.',
            'companyList.required_if' => 'Select at least one company.',
            'roleList.required_if' => 'Select at least one role.',
            'dueAt.date_format' => 'Due Date must be dd/mm/yyyy.',
        ]);

        $assignedUserIds = $this->resolveAssignedUserIds();
        if (!$assignedUserIds) {
            $this->addError('assignTo', 'No active users were found for that selection.');
            return;
        }

        $parent = $this->parentRecord();
        $todo = Todo::create([
            'name' => TodoTypeRegistry::taskName($this->context, $parent),
            'info' => $this->info,
            'type' => TodoTypeRegistry::taskType($this->context),
            'type_id' => $this->contextId,
            'due_at' => $this->dueAt ? Carbon::createFromFormat('d/m/Y', $this->dueAt)->startOfDay() : null,
            'company_id' => Auth::user()->company_id,
        ]);

        $todo->assignUsers($assignedUserIds);
        $this->saveAttachments($todo);

        if ($table = TodoTypeRegistry::actionTable($this->context))
            Action::create(['action' => "Created task: {$todo->info}", 'table' => $table, 'table_id' => $this->contextId]);

        $parent->touch();
        $todo->emailToDo();

        $this->showAddModal = false;
        $this->resetTaskForm();
    }

    protected function resolveAssignedUserIds(): array
    {
        if ($this->assignTo === 'user') {
            $allowedUserIds = Auth::user()->company->users(1)->pluck('id')->map(fn($id) => (int)$id);

            if (in_array('all', $this->userList, true))
                return $allowedUserIds->all();

            return collect($this->userList)
                ->map(fn($id) => (int)$id)
                ->filter(fn($id) => $allowedUserIds->contains($id))
                ->unique()
                ->values()
                ->all();
        }

        if ($this->assignTo === 'company') {
            $allowedCompanyIds = Auth::user()->company->companies(1)->pluck('id')->map(fn($id) => (int)$id);
            $companyIds = $this->companyList;
            if (in_array('all', $companyIds, true))
                $companyIds = $allowedCompanyIds->all();
            else
                $companyIds = collect($companyIds)->map(fn($id) => (int)$id)->filter(fn($id) => $allowedCompanyIds->contains($id))->all();

            return collect($companyIds)
                ->map(fn($id) => Company::find((int)$id))
                ->filter()
                ->flatMap(fn(Company $company) => $company->staffStatus(1)->pluck('id'))
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values()
                ->all();
        }

        if ($this->assignTo === 'role') {
            $allowedRoleIds = Role2::where('company_id', Auth::user()->company_id)->pluck('id')->map(fn($id) => (int)$id);
            $roleIds = collect($this->roleList)->map(fn($id) => (int)$id)->filter(fn($id) => $allowedRoleIds->contains($id))->all();
            $allowedUserIds = Auth::user()->company->users(1)->pluck('id');

            return DB::table('role_user')
                ->whereIn('role_id', $roleIds)
                ->pluck('user_id')
                ->filter(fn($id) => $allowedUserIds->contains($id))
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    protected function saveAttachments(Todo $todo): void
    {
        foreach ($this->uploads as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $isImage = in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true);
            $directory = "todo/{$todo->id}";
            $filename = FileBank::storeUploadedFile($file, $directory, null, $isImage);

            Attachment::create([
                'table' => 'todo',
                'table_id' => $todo->id,
                'directory' => $directory,
                'attachment' => $filename,
                'name' => $file->getClientOriginalName(),
                'type' => $isImage ? 'image' : 'file',
            ]);
        }
    }

    protected function parentRecord()
    {
        return TodoTypeRegistry::record($this->context, $this->contextId);
    }

    protected function canAdd(): bool
    {
        $record = $this->parentRecord();
        return $record && Auth::check() && TodoTypeRegistry::canAddTask($this->context, $record, Auth::user());
    }

    protected function resetTaskForm(): void
    {
        $this->info = '';
        $this->dueAt = '';
        $this->assignTo = '';
        $this->userList = [];
        $this->companyList = [];
        $this->roleList = [];
        $this->uploads = [];
    }

    public function render()
    {
        $taskType = TodoTypeRegistry::taskType($this->context);

        return view('livewire.misc.assigned-tasks', [
            'tasks' => Todo::with(['createdBy', 'doneBy', 'users.user', 'attachments'])
                ->where('type', $taskType)
                ->where('type_id', $this->contextId)
                ->latest()
                ->get(),
            'canAdd' => $this->canAdd(),
            'userOptions' => Auth::user()->company->usersSelect('ALL'),
            'companyOptions' => Auth::user()->company->subscription ? Auth::user()->company->companiesSelect('ALL') : [],
            'roleOptions' => Auth::user()->company->subscription ? Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray() : [],
        ]);
    }
}
