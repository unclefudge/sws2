<?php

namespace App\Livewire\Concerns;

trait NotifiesWithToastr
{
    protected function notify(string $message, string $type = 'success'): void
    {
        abort_unless(in_array($type, ['success', 'info', 'warning', 'error'], true), 500);

        $this->dispatch('sws-toastr', message: $message, type: $type)->self();
    }
}
