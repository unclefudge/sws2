@props([
    'id',
    'title' => '',
    'maxWidth' => '560px',
    'footerAlign' => 'right',
])

<x-ui.modal-styles/>

<div
    id="{{ $id }}"
    class="modal sws-bootstrap-modal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    aria-labelledby="{{ $id }}-title"
    data-backdrop="false"
    onclick="if (event.target === this) $('#{{ $id }}').modal('hide')"
>
    <div class="modal-dialog" role="document" style="max-width: {{ $maxWidth }};">
        <div {{ $attributes->merge(['class' => 'modal-content sws-modal-card']) }}>
            @if ($title)
                <div class="sws-modal-header">
                    <h3 class="sws-modal-title" id="{{ $id }}-title">{{ $title }}</h3>
                    <button type="button" class="sws-modal-close" data-dismiss="modal" aria-label="Close">&times;</button>
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
</div>
