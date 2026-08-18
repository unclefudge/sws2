@props([
    'show' => false,
    'title' => '',
    'closeAction' => 'closeModals',
    'maxWidth' => '560px',
    'footerAlign' => 'right',
])

@if ($show)
    @once
        <style>
            .sws-modal-backdrop {
                position: fixed;
                inset: 0;
                z-index: 10050;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                background: rgba(26, 34, 44, 0.58);
                overflow-y: auto;
            }

            .sws-modal-card {
                width: 100%;
                margin: auto;
                background: #fff;
                border: 1px solid rgba(30, 42, 54, 0.08);
                border-radius: 10px !important;
                box-shadow: 0 22px 70px rgba(20, 31, 43, 0.28);
                overflow: hidden;
            }

            .sws-modal-header {
                position: relative;
                padding: 22px 56px 18px 26px;
                border-bottom: 1px solid #edf0f2;
            }

            .sws-modal-title {
                margin: 0;
                color: #39424e;
                font-size: 21px;
                font-weight: 600;
                line-height: 1.3;
            }

            .sws-modal-close {
                position: absolute;
                top: 14px;
                right: 15px;
                width: 36px;
                height: 36px;
                padding: 0;
                border: 0;
                border-radius: 50%;
                background: transparent;
                color: #8a949e;
                font-size: 26px;
                font-weight: 300;
                line-height: 34px;
                text-align: center;
                cursor: pointer;
            }

            .sws-modal-close:hover,
            .sws-modal-close:focus {
                background: #f3f5f6;
                color: #39424e;
                outline: none;
            }

            .sws-modal-body {
                padding: 24px 26px 26px;
                color: #56616d;
            }

            .sws-modal-body .control-label {
                margin-bottom: 7px;
                color: #46515c;
                font-weight: 600;
            }

            .sws-modal-body .form-control {
                border-color: #d9dee3;
                border-radius: 5px;
                box-shadow: none;
            }

            .sws-modal-body .form-control:focus {
                border-color: #36c6d3;
                box-shadow: 0 0 0 3px rgba(54, 198, 211, 0.12);
            }

            .sws-modal-footer {
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                padding: 16px 26px 20px;
                border-top: 1px solid #edf0f2;
                background: #fafbfb;
            }

            .sws-modal-footer.sws-modal-footer-center {
                justify-content: center;
            }

            .sws-modal-footer.sws-modal-footer-left {
                justify-content: flex-start;
            }

            .sws-modal-btn {
                min-width: 96px;
                padding: 9px 18px;
                border: 1px solid transparent;
                border-radius: 5px;
                font-size: 14px;
                font-weight: 600;
                line-height: 1.2;
                cursor: pointer;
                transition: background .12s ease, border-color .12s ease, box-shadow .12s ease;
            }

            .sws-modal-btn:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(54, 198, 211, 0.15);
            }

            .sws-modal-btn[disabled] {
                cursor: default;
                opacity: .65;
            }

            .sws-modal-btn-secondary {
                border-color: #d2d8dd;
                background: #fff;
                color: #56616d;
            }

            .sws-modal-btn-secondary:hover {
                background: #f3f5f6;
                border-color: #c5ccd2;
            }

            .sws-modal-btn-primary {
                background: #36c6d3;
                border-color: #36c6d3;
                color: #fff;
            }

            .sws-modal-btn-primary:hover {
                background: #2bb5c2;
                border-color: #2bb5c2;
                color: #fff;
            }

            .sws-modal-btn-danger {
                background: #e7505a;
                border-color: #e7505a;
                color: #fff;
            }

            .sws-modal-btn-danger:hover {
                background: #d9434d;
                border-color: #d9434d;
                color: #fff;
            }

            .sws-confirm-content {
                text-align: center;
            }

            .sws-confirm-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 58px;
                height: 58px;
                margin-bottom: 15px;
                border-radius: 50%;
                background: #fff1f2;
                color: #e7505a;
                font-size: 24px;
            }

            .sws-confirm-text {
                margin: 0;
                color: #6b747d;
                font-size: 15px;
                line-height: 1.55;
            }

            .sws-confirm-item {
                display: inline-block;
                max-width: 100%;
                margin-top: 13px;
                padding: 7px 12px;
                border-radius: 5px;
                background: #f4f6f7;
                color: #39424e;
                font-weight: 600;
                overflow-wrap: anywhere;
            }

            @media (max-width: 600px) {
                .sws-modal-backdrop {
                    align-items: flex-start;
                    padding: 16px;
                }

                .sws-modal-card {
                    margin-top: 30px;
                }

                .sws-modal-header,
                .sws-modal-body {
                    padding-left: 20px;
                    padding-right: 20px;
                }

                .sws-modal-footer {
                    padding-left: 20px;
                    padding-right: 20px;
                }
            }
        </style>
    @endonce

    <div class="sws-modal-backdrop" role="presentation" wire:click.self="{{ $closeAction }}">
        <div {{ $attributes->merge(['class' => 'sws-modal-card']) }}role="dialog" aria-modal="true" aria-label="{{ $title ?: 'Dialog' }}" style="max-width: {{ $maxWidth }};">
            @if ($title)
                <div class="sws-modal-header">
                    <h3 class="sws-modal-title">{{ $title }}</h3>

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
