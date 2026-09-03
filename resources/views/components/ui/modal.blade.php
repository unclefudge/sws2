@props([
    'show' => false,
    'title' => '',
    'subtitle' => '',
    'closeAction' => 'closeModals',
    'maxWidth' => '560px',
    'footerAlign' => 'right',
])

<x-ui.modal-styles/>

@if ($show)
    <div class="sws-modal-backdrop" role="presentation" wire:click.self="{{ $closeAction }}">
        <div {{ $attributes->merge(['class' => 'sws-modal-card']) }} role="dialog" aria-modal="true" aria-label="{{ $title ?: 'Dialog' }}" style="max-width: {{ $maxWidth }};">
            @if ($title)
                <div class="sws-modal-header">
                    <div>
                        <h3 class="sws-modal-title">{{ $title }}</h3>

                        @if ($subtitle)
                            <div class="sws-modal-subtitle">{{ $subtitle }}</div>
                        @endif
                    </div>

                    <button type="button" class="sws-modal-close" wire:click="{{ $closeAction }}" aria-label="Close">&times;</button>
                </div>
            @endif

            <div class="sws-modal-body">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="sws-modal-footer {{ $footerAlign === 'center' ? 'sws-modal-footer-center' : ($footerAlign === 'left' ? 'sws-modal-footer-left' : '') }}">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
@endif
