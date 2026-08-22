<div class="sws-attachments" x-data="{ uploading: false }" x-on:filepond-upload-state="if ($event.detail.model === 'upload') uploading = $event.detail.uploading">
    @once
        <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css">
        <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
        <style>
            .sws-attachments {
                margin-bottom: 25px;
            }

            .sws-attachments__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 15px;
            }

            .sws-attachments__header h4 {
                margin: 0;
            }

            .sws-attachments__rule {
                padding: 0;
                margin: 10px 0;
            }

            .sws-attachments__upload {
                padding: 15px;
                margin-bottom: 15px;
                background: #f7f7f7;
                border: 1px solid #e5e5e5;
            }

            .sws-attachments__images {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .sws-attachments__content {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
            }

            .sws-attachments__content--split {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 30px;
            }

            .sws-attachments__section-title {
                margin: 0 0 10px;
            }

            .sws-attachment-image {
                position: relative;
                width: 112px;
            }

            .sws-attachment-image__open {
                display: block;
                width: 112px;
                height: 90px;
                padding: 0;
                overflow: hidden;
                background: #f4f4f4;
                border: 1px solid #ddd;
                cursor: pointer;
            }

            .sws-attachment-image__open img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .sws-attachment-image__missing {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 112px;
                height: 90px;
                padding: 8px;
                color: #999;
                background: #f4f4f4;
                border: 1px solid #ddd;
                text-align: center;
            }

            .sws-attachment-image__name {
                display: block;
                margin-top: 5px;
                overflow: hidden;
                color: #666;
                font-size: 12px;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .sws-attachment-delete {
                position: absolute;
                top: 3px;
                right: 3px;
                width: 22px;
                height: 22px;
                padding: 0;
                color: #e7505a;
                background: rgba(255, 255, 255, .9);
                border: 0;
                border-radius: 2px;
                font-size: 13px;
                z-index: 2;
            }

            .sws-attachment-delete:hover, .sws-attachment-delete:focus {
                color: #c9302c;
                background: #fff;
            }

            .sws-attachment-file {
                display: flex;
                align-items: center;
                gap: 8px;
                min-height: 28px;
                padding: 3px 0;
            }

            .sws-attachment-file a {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .sws-attachment-file-delete {
                padding: 2px 4px;
                color: #e7505a;
                background: transparent;
                border: 0;
                flex: 0 0 auto;
            }

            .sws-attachment-file-delete:hover, .sws-attachment-file-delete:focus {
                color: #c9302c;
            }

            .sws-image-viewer {
                position: fixed;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 45px 70px;
                background: rgba(0, 0, 0, .9);
                z-index: 10060;
            }

            .sws-image-viewer__figure {
                max-width: 100%;
                max-height: 100%;
                margin: 0;
                text-align: center;
            }

            .sws-image-viewer__image {
                display: block;
                max-width: 100%;
                max-height: calc(100vh - 130px);
                margin: auto;
                object-fit: contain;
            }

            .sws-image-viewer__caption {
                margin-top: 12px;
                color: #fff;
            }

            .sws-image-viewer__control {
                position: absolute;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                background: rgba(0, 0, 0, .35);
                border: 1px solid rgba(255, 255, 255, .45);
                border-radius: 50%;
            }

            .sws-image-viewer__control:hover, .sws-image-viewer__control:focus {
                color: #fff;
                background: rgba(0, 0, 0, .7);
            }

            .sws-image-viewer__close {
                top: 18px;
                right: 18px;
                width: 42px;
                height: 42px;
                font-size: 22px;
            }

            .sws-image-viewer__previous, .sws-image-viewer__next {
                top: 50%;
                width: 48px;
                height: 48px;
                margin-top: -24px;
                font-size: 24px;
            }

            .sws-image-viewer__previous {
                left: 15px;
            }

            .sws-image-viewer__next {
                right: 15px;
            }

            @media (max-width: 767px) {
                .sws-attachments__content--split {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 20px;
                }

                .sws-image-viewer {
                    padding: 60px 15px;
                }

                .sws-image-viewer__previous, .sws-image-viewer__next {
                    top: auto;
                    bottom: 12px;
                }
            }
        </style>
    @endonce

    <div class="sws-attachments__header">
        <h4>Attachments <small>({{ $attachments->count() }})</small></h4>
        @if ($canUpload)
            <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="toggleUploader" wire:loading.attr="disabled" wire:target="toggleUploader" :disabled="uploading">{{ $showUploader ? 'Done' : 'Add' }}</button>
        @endif
    </div>
    <hr class="sws-attachments__rule">

    @if ($message)
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    @if ($showUploader)
        <div class="sws-attachments__upload">
            <x-livewire-filepond wire:model="upload" save-action="storeUpload" multiple/>
            <div x-show="uploading" x-cloak class="text-muted"><i class="fa fa-spinner fa-spin"></i> Uploading and saving attachments…</div>
            @error('upload')
            <div class="font-red">{{ $message }}</div> @enderror
            <small class="text-muted">Files save automatically when each upload completes. Maximum 20 MB per file.</small>
        </div>
    @endif

    @if ($attachments->isEmpty())
        <div class="text-muted">No attachments</div>
    @else
        <div class="sws-attachments__content {{ $images->isNotEmpty() && $files->isNotEmpty() ? 'sws-attachments__content--split' : '' }}">
            @if ($images->isNotEmpty())
                <div>
                    @if ($files->isNotEmpty())
                        <h5 class="sws-attachments__section-title"><b>Images</b></h5>
                    @endif
                    <div class="sws-attachments__images">
                        @foreach ($images as $attachment)
                            <div class="sws-attachment-image" wire:key="attachment-image-{{ $attachment->id }}">
                                @if ($canDelete)
                                    <button type="button" class="sws-attachment-delete" wire:click="confirmDelete({{ $attachment->id }})" wire:loading.attr="disabled" wire:target="confirmDelete({{ $attachment->id }})"
                                            title="Delete {{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}"><i class="fa fa-times"></i></button>
                                @endif
                                @if ($attachmentAvailable[$attachment->id] !== false)
                                    <button type="button" class="sws-attachment-image__open" wire:click="openViewer({{ $attachment->id }})" title="View {{ $attachment->name }}">
                                        <img src="{{ $attachmentUrls[$attachment->id] }}" alt="{{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}">
                                    </button>
                                @else
                                    <div class="sws-attachment-image__missing"><span><i class="fa fa-warning"></i><br>File unavailable</span></div>
                                @endif
                                {{--}}<span class="sws-attachment-image__name" title="{{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}">{{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}</span>--}}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($files->isNotEmpty())
                <div>
                    <h5 class="sws-attachments__section-title"><b>Documents</b></h5>
                    @foreach ($files as $attachment)
                        <div class="sws-attachment-file" wire:key="attachment-file-{{ $attachment->id }}">
                            <i class="fa fa-file-text-o"></i>
                            @if ($attachmentAvailable[$attachment->id] !== false)
                                <a href="{{ $attachmentUrls[$attachment->id] }}" target="_blank" rel="noopener">{{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}</a>
                            @else
                                <span class="text-muted">{{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }} — file unavailable</span>
                            @endif
                            @if ($canDelete)
                                <button type="button" class="sws-attachment-file-delete" wire:click="confirmDelete({{ $attachment->id }})" wire:loading.attr="disabled" wire:target="confirmDelete({{ $attachment->id }})"
                                        title="Delete {{ $attachment->name ?: $attachment->attachment ?: 'Attachment #' . $attachment->id }}"><i class="fa fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if ($showViewer && $viewerImage)
        <div class="sws-image-viewer" wire:click.self="closeViewer" x-data x-on:keydown.escape.window="$wire.closeViewer()">
            <button type="button" class="sws-image-viewer__control sws-image-viewer__close" wire:click="closeViewer" aria-label="Close image viewer"><i class="fa fa-times"></i></button>
            @if ($images->count() > 1)
                <button type="button" class="sws-image-viewer__control sws-image-viewer__previous" wire:click="previousImage" aria-label="Previous image"><i class="fa fa-chevron-left"></i></button>
                <button type="button" class="sws-image-viewer__control sws-image-viewer__next" wire:click="nextImage" aria-label="Next image"><i class="fa fa-chevron-right"></i></button>
            @endif
            <figure class="sws-image-viewer__figure">
                <img class="sws-image-viewer__image" src="{{ $attachmentUrls[$viewerImage->id] }}" alt="{{ $viewerImage->name }}">
                <figcaption class="sws-image-viewer__caption">{{ $viewerImage->name }}</figcaption>
            </figure>
        </div>
    @endif

    <x-ui.confirm-modal :show="$showDeleteModal" title="Delete attachment?" confirm-label="Delete" confirm-action="deleteAttachment" close-action="closeDeleteModal">
        <div>This will permanently delete</div>
        <div class="sws-confirm-item">
            {{ $deleteAttachmentName }}
        </div>
    </x-ui.confirm-modal>
</div>
