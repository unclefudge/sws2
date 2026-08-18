@props([
    'show' => false,
    'title' => '',
    'closeAction' => 'closeModals',
    'confirmAction',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'loadingTarget' => null,
])

<x-ui.modal
        :show="$show"
        :title="$title"
        :close-action="$closeAction"
        max-width="500px"
        footer-align="center"
>
    <div class="sws-confirm-content">
        <div class="sws-confirm-text">
            {{ $slot }}
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="{{ $closeAction }}">
            {{ $cancelLabel }}
        </button>

        <button type="button" class="sws-modal-btn sws-modal-btn-danger" wire:click="{{ $confirmAction }}" wire:loading.attr="disabled" wire:target="{{ $loadingTarget ?: $confirmAction }}">
            {{ $confirmLabel }}
        </button>
    </x-slot>
</x-ui.modal>
