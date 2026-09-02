<div class="extension-settings sws-settings" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">

    @if ($warningMessage)
        <div class="note note-warning" role="alert">{{ $warningMessage }}</div>
    @endif

    <h3 class="font-green-haze">
        Extend Reasons
        <span class="pull-right">
            <button type="button" class="btn btn-circle btn-outline btn-sm blue" wire:click="openAdd">
                <i class="fa fa-plus"></i> Add option
            </button>
        </span>
    </h3>
    <hr class="field-hr">

    <div class="row hidden-sm hidden-xs">
        <div class="col-md-1">#</div>
        <div class="col-md-10"><strong>Name</strong></div>
        <div class="col-md-1"></div>
    </div>

    <div class="sortable-list"
         x-data="{ draggingId: null }"
         x-on:dragover.prevent="
            const target = $event.target.closest('[data-sort-id]');
            if (!target || String(target.dataset.sortId) === String(draggingId)) return;

            const dragged = $root.querySelector('[data-sort-id=&quot;' + draggingId + '&quot;]');
            if (!dragged) return;

            const items = [...$root.querySelectorAll('[data-sort-id]')];
            if (items.indexOf(dragged) < items.indexOf(target)) target.after(dragged);
            else target.before(dragged);
         "
         x-on:drop.prevent="
            const items = [...$root.querySelectorAll('[data-sort-id]')];
            const ids = items.map(item => Number(item.dataset.sortId));
            items.forEach((item, index) => {
                const number = item.querySelector('[data-sort-number]');
                if (number) number.textContent = index + 1;
            });
            draggingId = null;
            $wire.reorderReasons(ids);
         "
         x-on:dragend="draggingId = null">
        @forelse ($rows as $rowKey => $row)
            <div class="sortable-item" data-sort-id="{{ $row['id'] }}" wire:key="extension-reason-{{ $row['id'] }}" x-bind:style="String(draggingId) === '{{ $row['id'] }}' ? 'opacity: .45;' : ''">
                <div class="row sortable-option-row">
                    <div class="col-md-1">
                        <span class="btn btn-link btn-xs font-grey-salsa sortable-handle" draggable="true" role="button" tabindex="0" title="Drag to reorder" aria-label="Drag {{ $row['name'] }}"
                              x-on:dragstart.stop="draggingId = {{ $row['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $row['id'] }}')">
                            <i class="fa fa-bars"></i>
                        </span>
                        <span data-sort-number>{{ $loop->iteration }}</span>
                    </div>
                    <div class="col-md-10">
                        <x-form.input name="rows.{{ $rowKey }}.name" :value="$row['name']" wire:model="rows.{{ $rowKey }}.name" aria-label="Extension reason name" :readonly="$row['locked']"/>
                    </div>
                    <div class="col-md-1 text-center">
                        @if ($row['locked'])
                            <span class="btn btn-link btn-xs font-grey-salsa" title="Required system reason" aria-label="Required system reason">
                                <i class="fa fa-lock"></i>
                            </span>
                        @else
                            <button type="button" class="btn btn-link btn-xs {{ $row['in_use'] ? 'font-red' : 'font-dark' }}" wire:click="requestRemove({{ $row['id'] }})" title="{{ $row['in_use'] ? 'Archive reason' : 'Delete reason' }}"
                                    aria-label="{{ $row['in_use'] ? 'Archive' : 'Delete' }} {{ $row['name'] }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted">No active extension reasons.</div>
        @endforelse
    </div>

    <br>
    <div class="form">
        <div class="form-actions right">
            <a href="/site/extension" class="btn default">Back</a>
            <button type="button" class="btn green" wire:click="saveReasons" wire:loading.attr="disabled" wire:target="saveReasons">
                <span wire:loading.remove wire:target="saveReasons">Save changes</span>
                <span wire:loading wire:target="saveReasons">Saving...</span>
            </button>
        </div>
    </div>

    <x-ui.modal :show="$showAdd" title="Add extension reason" close-action="closeModals" max-width="520px">
        <x-form.input name="newName" id="new-extension-reason" label="Name" wire:model="newName"/>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="addReason" wire:loading.attr="disabled" wire:target="addReason">Add reason</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRemove" :title="$removeAction === 'archive' ? 'Archive extension reason?' : 'Delete extension reason?'" close-action="closeModals"
                        confirm-action="removeReason" :confirm-label="$removeAction === 'archive' ? 'Archive reason' : 'Delete reason'" loading-target="removeReason">
        <p>
            {{ $removeAction === 'archive'
                ? 'This reason is already in use. It will be hidden from new extensions while existing records keep their saved reason.'
                : 'This unused reason will be permanently deleted.' }}
        </p>
        <span class="sws-confirm-item">{{ $removeName }}</span>
    </x-ui.confirm-modal>
</div>
