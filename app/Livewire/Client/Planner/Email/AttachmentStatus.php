<?php

namespace App\Livewire\Client\Planner\Email;

use App\Models\Client\ClientPlannerEmail;
use App\Services\FileBank;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AttachmentStatus extends Component
{
    #[Locked]
    public int $emailId;

    public function mount(int $emailId): void
    {
        $this->emailId = $emailId;

        $email = ClientPlannerEmail::findOrFail($emailId);
        abort_unless(Auth::user()->allowed2('edit.client.planner.email', $email), 404);
    }

    public function refreshAttachments(): void
    {
        $email = ClientPlannerEmail::findOrFail($this->emailId);
        abort_unless(Auth::user()->allowed2('edit.client.planner.email', $email), 404);

        $ready = !$email->attachments()->where('status', '!=', 1)->exists();

        $this->dispatch('client-planner-attachments-status', ready: $ready);
    }

    public function render()
    {
        $email = ClientPlannerEmail::findOrFail($this->emailId);

        $attachments = $email->attachments()
            ->orderBy('id')
            ->get()
            ->map(function ($file) {
                $path = trim((string)$file->directory, '/') . '/' . $file->attachment;

                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => $file->attachment ? FileBank::url($path) : null,
                    'status' => (int)$file->status,
                ];
            });

        $hasPending = $attachments->contains(fn ($file) => $file['status'] === 2);
        $hasFailed = $attachments->contains(fn ($file) => $file['status'] === 0);
        $ready = $attachments->every(fn ($file) => $file['status'] === 1);

        return view('livewire.client.planner.email.attachment-status', compact(
            'attachments',
            'hasPending',
            'hasFailed',
            'ready'
        ));
    }
}
