<?php

namespace App\Livewire\Comms;

use App\Models\Comms\SafetyTip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SafetyTips extends Component
{
    public bool $showTipModal = false;
    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $deletingId = null;

    public string $title = '';
    public string $body = '';
    public string $deletingTitle = '';
    public string $message = '';

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyPermissionType('safetytip'), 404);
    }

    protected function companyId(): int
    {
        return (int) Auth::user()->company->reportsTo()->id;
    }

    protected function tipForCompany(int $id): SafetyTip
    {
        return SafetyTip::query()
            ->whereKey($id)
            ->where('company_id', $this->companyId())
            ->firstOrFail();
    }

    public function openAdd(): void
    {
        abort_unless(Auth::user()->allowed2('add.safetytip'), 404);

        $this->resetValidation();
        $this->editingId = null;
        $this->title = '';
        $this->body = '';
        $this->showDeleteModal = false;
        $this->showTipModal = true;
    }

    public function openEdit(int $id): void
    {
        $tip = $this->tipForCompany($id);
        abort_unless(Auth::user()->allowed2('edit.safetytip', $tip), 404);

        $this->resetValidation();
        $this->editingId = $tip->id;
        $this->title = (string) $tip->title;
        $this->body = (string) $tip->body;
        $this->showDeleteModal = false;
        $this->showTipModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:250'],
            'body' => ['required', 'string'],
        ]);

        if ($this->editingId) {
            $tip = $this->tipForCompany($this->editingId);
            abort_unless(Auth::user()->allowed2('edit.safetytip', $tip), 404);

            $tip->update([
                'title' => trim($this->title),
                'body' => trim($this->body),
            ]);

            $this->message = 'Safety Tip saved.';
        } else {
            abort_unless(Auth::user()->allowed2('add.safetytip'), 404);

            SafetyTip::create([
                'title' => trim($this->title),
                'body' => trim($this->body),
                'last_published' => now(),
                'status' => 0,
                'company_id' => $this->companyId(),
            ]);

            $this->message = 'Safety Tip created.';
        }

        $this->closeModals();
    }

    public function setActive(int $id): void
    {
        $tip = $this->tipForCompany($id);

        // The old UI exposed the publish circle with del.safetytip, while the
        // old PATCH endpoint also required edit.safetytip. Preserve the
        // effective permission requirement here.
        abort_unless(
            Auth::user()->allowed2('del.safetytip', $tip)
            && Auth::user()->allowed2('edit.safetytip', $tip),
            404
        );

        DB::transaction(function () use ($tip) {
            SafetyTip::query()
                ->where('company_id', $this->companyId())
                ->where('status', 1)
                ->update([
                    'status' => 0,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $tip = SafetyTip::query()
                ->whereKey($tip->id)
                ->where('company_id', $this->companyId())
                ->lockForUpdate()
                ->firstOrFail();

            $tip->status = 1;
            $tip->last_published = now();
            $tip->save();
        });

        $this->message = 'Safety Tip published.';
    }

    public function confirmDelete(int $id): void
    {
        $tip = $this->tipForCompany($id);
        abort_unless(Auth::user()->allowed2('del.safetytip', $tip), 404);

        $this->deletingId = $tip->id;
        $this->deletingTitle = (string) $tip->title;
        $this->showTipModal = false;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        abort_unless($this->deletingId, 404);

        $tip = $this->tipForCompany($this->deletingId);
        abort_unless(Auth::user()->allowed2('del.safetytip', $tip), 404);

        $wasActive = (int) $tip->status === 1;
        $title = $tip->title;
        $tip->delete();

        $this->message = $wasActive
            ? 'Active Safety Tip deleted. There is currently no published Safety Tip.'
            : 'Safety Tip deleted.';

        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingTitle = '';
    }

    public function closeModals(): void
    {
        $this->showTipModal = false;
        $this->showDeleteModal = false;
        $this->editingId = null;
        $this->deletingId = null;
        $this->deletingTitle = '';
        $this->title = '';
        $this->body = '';
        $this->resetValidation();
    }

    public function render()
    {
        $tips = SafetyTip::query()
            ->where('company_id', $this->companyId())
            ->orderByDesc('last_published')
            ->orderByDesc('id')
            ->get();

        $canAdd = Auth::user()->allowed2('add.safetytip');

        return view('livewire.comms.safety-tips', compact('tips', 'canAdd'));
    }
}
