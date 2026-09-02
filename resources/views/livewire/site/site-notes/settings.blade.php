<div class="site-note-settings sws-settings" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    <div class="tabbable-line settings-tabs">
        <ul class="nav nav-tabs">
            <li class="{{ $tab === 'categories' ? 'active' : '' }}">
                <a href="/site/note/settings" class="{{ $tab === 'categories' ? 'font-green-haze' : '' }}">Categories</a>
            </li>
            <li class="{{ $tab === 'cost-centres' ? 'active' : '' }}">
                <a href="/site/note/settings/cost-centres" class="{{ $tab === 'cost-centres' ? 'font-green-haze' : '' }}">Cost Centres</a>
            </li>
        </ul>
    </div>
    <br>

    @if ($tab === 'categories')
        <h3 class="font-green-haze">
            Categories
            <span class="pull-right">
                <button type="button" class="btn btn-circle btn-outline btn-sm blue" wire:click="openAddOption('category')">
                    <i class="fa fa-plus"></i> Add option
                </button>
            </span>
        </h3>
        <p class="help-block">Drag categories into the order users should see them. Edit the fields, then save.</p>
        <hr class="field-hr">

        <div class="row hidden-sm hidden-xs">
            <div class="col-md-1">#</div>
            <div class="col-md-5"><strong>Name</strong></div>
            <div class="col-md-5"><strong>Users to notify</strong></div>
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
                if (items.indexOf(dragged) < items.indexOf(target)) {
                    target.after(dragged);
                } else {
                    target.before(dragged);
                }
             "
             x-on:drop.prevent="
                const items = [...$root.querySelectorAll('[data-sort-id]')];
                const ids = items.map(item => Number(item.dataset.sortId));
                items.forEach((item, index) => {
                    const number = item.querySelector('[data-sort-number]');
                    if (number) number.textContent = index + 1;
                });
                draggingId = null;
                $wire.reorderOptions('category', ids);
             "
             x-on:dragend="draggingId = null">
            @forelse ($categoryRows as $rowKey => $row)
                <div class="sortable-item"
                     data-sort-id="{{ $row['id'] }}"
                     wire:key="site-note-category-{{ $row['id'] }}"
                     x-bind:style="String(draggingId) === '{{ $row['id'] }}' ? 'opacity: .45;' : ''">
                    <div class="row sortable-option-row">
                        <div class="col-md-1">
                            <span class="btn btn-link btn-xs font-grey-salsa sortable-handle" draggable="true" role="button" tabindex="0" title="Drag to reorder" aria-label="Drag {{ $row['name'] }}"
                                  x-on:dragstart.stop="draggingId = {{ $row['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $row['id'] }}')">
                                <i class="fa fa-bars"></i>
                            </span>
                            <span data-sort-number>{{ $loop->iteration }}</span>
                        </div>

                        <div class="col-md-5">
                            <x-form.input name="categoryRows.{{ $rowKey }}.name" :value="$row['name']" wire:model="categoryRows.{{ $rowKey }}.name" aria-label="Category name"/>
                        </div>

                        <div class="col-md-5">
                            <div wire:ignore>
                                <x-form.select name="categoryRows.{{ $rowKey }}.notify_users[]" id="site-note-notify-users-{{ $row['id'] }}" :options="$staffOptions" :value="$row['notify_users']" plugin="select2" placeholder="Select one or more users" multiple style="width:100%" aria-label="Users to notify for {{ $row['name'] }}" x-init="$($el).select2({width: '100%', placeholder: 'Select one or more users'}).on('change', function () { $wire.set('categoryRows.{{ $rowKey }}.notify_users', $(this).val() || [], false); })"/>
                            </div>
                        </div>

                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-link {{ $row['in_use'] ? 'font-red' : 'font-dark' }}" wire:click="requestRemove('category', {{ $row['id'] }})" title="{{ $row['in_use'] ? 'Archive category' : 'Delete category' }}"
                                    aria-label="{{ $row['in_use'] ? 'Archive' : 'Delete' }} {{ $row['name'] }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">No active categories.</div>
            @endforelse
        </div>

        <br>
        <div class="form">
            <div class="form-actions right">
                <a href="/site/all/notes" class="btn default">Back</a>
                <button type="button" class="btn green" wire:click="saveCategories" wire:loading.attr="disabled" wire:target="saveCategories">
                    <span wire:loading.remove wire:target="saveCategories">Save changes</span>
                    <span wire:loading wire:target="saveCategories">Saving...</span>
                </button>
            </div>
        </div>
    @else
        <h3 class="font-green-haze">
            Cost Centres
            <span class="pull-right">
                <button type="button" class="btn btn-circle btn-outline btn-sm blue" wire:click="openAddOption('cost-centre')">
                    <i class="fa fa-plus"></i> Add option
                </button>
            </span>
        </h3>
        <p class="help-block">Drag cost centres into the order users should see them. Edit the names, then save.</p>
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
                if (items.indexOf(dragged) < items.indexOf(target)) {
                    target.after(dragged);
                } else {
                    target.before(dragged);
                }
             "
             x-on:drop.prevent="
                const items = [...$root.querySelectorAll('[data-sort-id]')];
                const ids = items.map(item => Number(item.dataset.sortId));
                items.forEach((item, index) => {
                    const number = item.querySelector('[data-sort-number]');
                    if (number) number.textContent = index + 1;
                });
                draggingId = null;
                $wire.reorderOptions('cost-centre', ids);
             "
             x-on:dragend="draggingId = null">
            @forelse ($costCentreRows as $rowKey => $row)
                <div class="sortable-item"
                     data-sort-id="{{ $row['id'] }}"
                     wire:key="site-note-cost-centre-{{ $row['id'] }}"
                     x-bind:style="String(draggingId) === '{{ $row['id'] }}' ? 'opacity: .45;' : ''">
                    <div class="row sortable-option-row">
                        <div class="col-md-1">
                            <span class="btn btn-link btn-xs font-grey-salsa sortable-handle" draggable="true" role="button" tabindex="0" title="Drag to reorder" aria-label="Drag {{ $row['name'] }}"
                                  x-on:dragstart.stop="draggingId = {{ $row['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $row['id'] }}')">
                                <i class="fa fa-bars"></i>
                            </span>
                            <span data-sort-number>{{ $loop->iteration }}</span>
                        </div>

                        <div class="col-md-10">
                            <x-form.input name="costCentreRows.{{ $rowKey }}.name" :value="$row['name']" wire:model="costCentreRows.{{ $rowKey }}.name" aria-label="Cost centre name"/>
                        </div>

                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-link {{ $row['in_use'] ? 'font-red' : 'font-dark' }}" wire:click="requestRemove('cost-centre', {{ $row['id'] }})" title="{{ $row['in_use'] ? 'Archive cost centre' : 'Delete cost centre' }}"
                                    aria-label="{{ $row['in_use'] ? 'Archive' : 'Delete' }} {{ $row['name'] }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">No active cost centres.</div>
            @endforelse
        </div>

        <br>
        <div class="form">
            <div class="form-actions right">
                <a href="/site/all/notes" class="btn default">Back</a>
                <button type="button" class="btn green" wire:click="saveCostCentres" wire:loading.attr="disabled" wire:target="saveCostCentres">
                    <span wire:loading.remove wire:target="saveCostCentres">Save changes</span>
                    <span wire:loading wire:target="saveCostCentres">Saving...</span>
                </button>
            </div>
        </div>
    @endif

    <x-ui.modal :show="$showAddOption" :title="$newOptionType === 'category' ? 'Add category' : 'Add cost centre'" close-action="closeModals" max-width="620px">
        <x-form.input name="newOptionName" id="new-site-note-option-name" label="Name" wire:model="newOptionName"/>

        @if ($newOptionType === 'category')
            <div wire:ignore wire:key="new-site-note-notify-users-{{ $showAddOption ? 'open' : 'closed' }}">
                <x-form.select name="newNotifyUsers[]" id="new-site-note-notify-users" label="Users to notify" :options="$staffOptions" :value="$newNotifyUsers" plugin="select2" placeholder="Select one or more users" multiple style="width:100%" x-init="const parent = $($el).closest('.sws-modal-card'); $($el).select2({width: '100%', placeholder: 'Select one or more users', dropdownParent: parent.length ? parent : $(document.body)}).on('change', function () { $wire.set('newNotifyUsers', $(this).val() || [], false); })"/>
            </div>
        @endif

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="addOption" wire:loading.attr="disabled" wire:target="addOption">
                Add option
            </button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRemove" :title="$removeAction === 'delete' ? 'Delete option' : 'Archive option'" close-action="closeModals" confirm-action="removeOption" :confirm-label="$removeAction === 'delete' ? 'Delete option' : 'Archive option'">
        @if ($removeAction === 'delete')
            This option is not currently used by any site notes.<br>
            <div class="sws-confirm-item">
                {{ $removeName }}
            </div>
        @else
            It will no longer be available for new selections, but existing site-note records will be retained.<br>
            <div class="sws-confirm-item">
                {{ $removeName }}
            </div>
        @endif
    </x-ui.confirm-modal>

</div>
