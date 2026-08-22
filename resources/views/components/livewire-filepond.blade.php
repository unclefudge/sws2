@once
    <style>
        .sws-livewire-filepond .filepond--drop-label {
            min-height: 48px;
            background: #f1f0ef;
            border-radius: 0;
        }

        .sws-livewire-filepond .filepond--drop-label label {
            padding: 12px;
        }
    </style>
@endonce

@php
    $model = $attributes->whereStartsWith('wire:model')->first();
    $saveAction = $attributes->get('save-action');
@endphp

<div class="sws-livewire-filepond" wire:ignore x-data x-init="
    const pond = FilePond.create($refs.input, {
        allowMultiple: {{ $attributes->has('multiple') ? 'true' : 'false' }},
        maxParallelUploads: {{ $saveAction ? '1' : '2' }},
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                $wire.upload('{{ $model }}', file, async (filename) => {
                    if (saveAction) {
                        emitUploadState(true);

                        try {
                            await $wire.call(saveAction);
                        } catch (exception) {
                            error(exception?.message || 'Unable to save file');
                            return;
                        }
                    }

                    load(filename);
                }, error, progress);
            },
            revert: (filename, load) => {
                $wire.removeUpload('{{ $model }}', filename, load);
            },
        },
    });

    const saveAction = {{ json_encode($saveAction) }};

    // Tell the containing Livewire component whenever this FilePond is busy
    // loading/processing files. The event bubbles, so each parent component can
    // react to its own pond without global state.
    const emitUploadState = (uploading = pond.status === FilePond.Status.BUSY) => {
        $el.dispatchEvent(new CustomEvent('filepond-upload-state', {
            bubbles: true,
            detail: {
                model: '{{ $model }}',
                uploading: !!uploading,
            },
        }));
    };

    // Some FilePond completion events fire just before the instance status has
    // settled, so check again on the next event-loop tick.
    const syncUploadState = () => {
        setTimeout(() => emitUploadState(), 0);
    };

    pond.on('addfilestart', () => emitUploadState(true));
    pond.on('processfilestart', () => emitUploadState(true));
    pond.on('processfile', syncUploadState);
    pond.on('processfileabort', syncUploadState);
    pond.on('processfiles', syncUploadState);

    // FilePond's file rows are positioned inside the pond and can visually extend
    // below its reported height. Reserve only the actual overflow so surrounding
    // content (such as a modal footer) is pushed down correctly.
    const syncOverflowSpace = () => {
        $el.style.paddingBottom = '0px';

        requestAnimationFrame(() => {
            const root = $el.querySelector('.filepond--root');
            if (!root) return;

            const rootRect = root.getBoundingClientRect();
            let lowestBottom = rootRect.bottom;

            $el.querySelectorAll('.filepond--item').forEach((item) => {
                lowestBottom = Math.max(lowestBottom, item.getBoundingClientRect().bottom);
            });

            const overflow = Math.max(0, Math.ceil(lowestBottom - rootRect.bottom + 8));
            $el.style.paddingBottom = overflow ? overflow + 'px' : '0px';
        });
    };

    // FilePond animates its list, so measure once immediately and again after
    // the animation has settled.
    const syncLater = () => {
        syncOverflowSpace();
        setTimeout(syncOverflowSpace, 100);
        setTimeout(syncOverflowSpace, 300);
    };

    pond.on('updatefiles', () => {
        syncLater();
        syncUploadState();
    });
    pond.on('processfile', syncLater);
    pond.on('processfile', (processError, file) => {
        if (saveAction && !processError) {
            setTimeout(() => pond.removeFile(file.id, { revert: false }), 250);
        }
    });
    pond.on('removefile', () => {
        syncLater();
        syncUploadState();
    });

    $el.addEventListener('pondReset', () => {
        pond.removeFiles();
        syncLater();
        syncUploadState();
    });

    // If this FilePond is inside one of our shared Livewire modals, keep the
    // footer fixed below the scrollable body instead of allowing dynamic file
    // rows to overlap it when the modal approaches the viewport height.
    const modalCard = $el.closest('.sws-modal-card');
    if (modalCard) {
        modalCard.style.maxHeight = 'calc(100vh - 48px)';
        modalCard.style.display = 'flex';
        modalCard.style.flexDirection = 'column';

        const modalHeader = modalCard.querySelector('.sws-modal-header');
        const modalBody = modalCard.querySelector('.sws-modal-body');
        const modalFooter = modalCard.querySelector('.sws-modal-footer');

        if (modalHeader) modalHeader.style.flexShrink = '0';
        if (modalBody) {
            modalBody.style.minHeight = '0';
            modalBody.style.overflowY = 'auto';
        }
        if (modalFooter) modalFooter.style.flexShrink = '0';
    }

    syncLater();
    emitUploadState();
">
    <input type="file" x-ref="input" {{ $attributes->has('multiple') ? 'multiple' : '' }} {!! $attributes->has('accept') ? 'accept="' . e($attributes->get('accept')) . '"' : '' !!}>
</div>
