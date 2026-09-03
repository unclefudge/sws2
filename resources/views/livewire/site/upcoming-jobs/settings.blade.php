<div class="upcoming-settings sws-settings" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    {{-- Only behaviours not supplied by Metronic/Bootstrap are defined here. --}}
    <style>
        .upcoming-settings .colour-option {
            width: 29px;
            height: 29px;
            padding: 4px;
            border: 0;
            background: transparent;
            opacity: .3;
        }

        .upcoming-settings .colour-option-selected {
            opacity: 1;
        }

        .upcoming-settings .colour-option img {
            display: block;
            width: 21px;
            height: 21px;
        }
    </style>

    <div class="tabbable-line settings-tabs">
        <ul class="nav nav-tabs">
            <li class="{{ $tab === 'stages' ? 'active' : '' }}">
                <a href="/site/upcoming/compliance/settings/stages" class="{{ $tab === 'stages' ? 'font-green-haze' : '' }}">Stage Options</a>
            </li>
            <li class="{{ $tab === 'steel' ? 'active' : '' }}">
                <a href="/site/upcoming/compliance/settings/steel" class="{{ $tab === 'steel' ? 'font-green-haze' : '' }}">STEEL Options</a>
            </li>
            <li class="{{ $tab === 'sites' ? 'active' : '' }}">
                <a href="/site/upcoming/compliance/settings/sites" class="{{ $tab === 'sites' ? 'font-green-haze' : '' }}">Additional Sites</a>
            </li>
        </ul>
    </div>
    <br>

    @if ($warningMessage)
        <div class="note note-warning" role="alert">{{ $warningMessage }}</div>
    @endif

    @if ($tab === 'stages')
        @foreach (['opt' => 'Standard Stage Options', 'cfest' => 'CF-EST Stage Options', 'cfadm' => 'CF-ADM Stage Options'] as $field => $title)
            <div wire:key="stage-section-{{ $field }}">
                <h3 class="font-green-haze">
                    {{ $title }}
                    <span class="pull-right">
                        <button type="button" class="btn btn-circle btn-outline btn-sm green" wire:click="openAddStage('{{ $field }}')">
                            <i class="fa fa-plus"></i> Add option
                        </button>
                    </span>
                </h3>
                <hr class="field-hr">

                <div class="row hidden-sm hidden-xs">
                    <div class="col-md-1">#</div>
                    <div class="col-md-3"><strong>Name</strong></div>
                    <div class="col-md-3"><strong>Default text</strong></div>
                    <div class="col-md-4"><strong>Colour</strong></div>
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
                        $wire.reorderStage('{{ $field }}', ids);
                     "
                     x-on:dragend="draggingId = null">
                    @forelse ($stageRows[$field] as $rowKey => $row)
                        <div class="sortable-item" data-sort-id="{{ $row['id'] }}" wire:key="stage-{{ $row['id'] }}" x-bind:style="String(draggingId) === '{{ $row['id'] }}' ? 'opacity: .45;' : ''">
                            <div class="row sortable-option-row">
                                <div class="col-md-1">
                                    <span class="btn btn-link btn-xs font-grey-salsa sortable-handle" draggable="true" role="button" tabindex="0" title="Drag to reorder" aria-label="Drag {{ $row['name'] }}"
                                          x-on:dragstart.stop="draggingId = {{ $row['id'] }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $row['id'] }}')">
                                        <i class="fa fa-bars"></i>
                                    </span>
                                    <span data-sort-number>{{ $loop->iteration }}</span>
                                </div>
                                <div class="col-md-3">
                                    <x-form.input name="stageRows.{{ $field }}.{{ $rowKey }}.name" :value="$row['name']" wire:model="stageRows.{{ $field }}.{{ $rowKey }}.name" aria-label="Stage name"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.input name="stageRows.{{ $field }}.{{ $rowKey }}.text" :value="$row['text']" wire:model="stageRows.{{ $field }}.{{ $rowKey }}.text" aria-label="Default text"/>
                                </div>
                                <div class="col-md-4 text-nowrap">
                                    @foreach ($colours as $colour)
                                        <button type="button" class="colour-option {{ $row['colour'] === $colour ? 'colour-option-selected' : '' }}" wire:click="selectStageColour('{{ $field }}', '{{ $rowKey }}', '{{ $colour }}')" title="Select {{ str_replace(['col-', '-'], ['', ' '], $colour) }}">
                                            <img src="/img/{{ $colour }}.png" alt="">
                                        </button>
                                    @endforeach
                                    @if ($row['colour'])
                                        <button type="button" class="btn btn-link btn-xs font-grey-salsa" wire:click="clearStageColour('{{ $field }}', '{{ $rowKey }}')">Clear</button>
                                    @endif
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-link {{ $row['in_use'] ? 'font-red' : 'font-dark' }}" wire:click="requestRemove('stage', {{ $row['id'] }})"
                                            title="{{ $row['in_use'] ? 'Archive option' : 'Delete option' }}" aria-label="{{ $row['in_use'] ? 'Archive' : 'Delete' }} {{ $row['name'] }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No active options.</div>
                    @endforelse
                </div>
            </div>
        @endforeach

        <br>
        <div class="form">
            <div class="form-actions right">
                <a href="/site/upcoming/compliance" class="btn default">Back</a>
                <button type="button" class="btn green" wire:click="saveStages" wire:loading.attr="disabled" wire:target="saveStages">
                    <span wire:loading.remove wire:target="saveStages">Save changes</span>
                    <span wire:loading wire:target="saveStages">Saving...</span>
                </button>
            </div>
        </div>
    @elseif ($tab === 'steel')
        <h3 class="font-green-haze">
            STEEL Options
            <span class="pull-right">
                <button type="button" class="btn btn-circle btn-outline btn-sm green" wire:click="openAddSteel">
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
                $wire.reorderSteel(ids);
             "
             x-on:dragend="draggingId = null">
            @forelse ($steelRows as $rowKey => $row)
                <div class="sortable-item"
                     data-sort-id="{{ $row['id'] }}"
                     wire:key="steel-{{ $row['id'] }}"
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
                            <x-form.input name="steelRows.{{ $rowKey }}.name" :value="$row['name']" wire:model="steelRows.{{ $rowKey }}.name" aria-label="STEEL option name"/>
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-link {{ $row['in_use'] ? 'font-red' : 'font-dark' }}" wire:click="requestRemove('steel', {{ $row['id'] }})"
                                    title="{{ $row['in_use'] ? 'Archive option' : 'Delete option' }}" aria-label="{{ $row['in_use'] ? 'Archive' : 'Delete' }} {{ $row['name'] }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">No active STEEL options.</div>
            @endforelse
        </div>

        <br>
        <div class="form">
            <div class="form-actions right">
                <a href="/site/upcoming/compliance" class="btn default">Back</a>
                <button type="button" class="btn green" wire:click="saveSteel" wire:loading.attr="disabled" wire:target="saveSteel">
                    <span wire:loading.remove wire:target="saveSteel">Save changes</span>
                    <span wire:loading wire:target="saveSteel">Saving...</span>
                </button>
            </div>
        </div>

    @else
        <h3 class="font-green-haze">Additional Sites</h3>
        <p class="help-block">Choose sites that should be included manually in Upcoming Jobs.</p>
        <hr class="field-hr">

        <div wire:ignore wire:key="upcoming-special-sites-field">
            <x-form.select name="specialSiteIds[]" id="upcoming-special-sites" label="Sites" :options="$siteOptions" :value="$specialSiteIds" plugin="select2" placeholder="Select one or more sites" multiple style="width:100%"
                           x-init="$($el).select2({placeholder: 'Select one or more sites', width: '100%'}).on('change', function () { $wire.set('specialSiteIds', $(this).val() || [], false); })"/>
        </div>

        <br>
        <div class="form">
            <div class="form-actions right">
                <a href="/site/upcoming/compliance" class="btn default">Back</a>
                <button type="button" class="btn green" wire:click="saveSites" wire:loading.attr="disabled" wire:target="saveSites">
                    <span wire:loading.remove wire:target="saveSites">Save changes</span>
                    <span wire:loading wire:target="saveSites">Saving...</span>
                </button>
            </div>
        </div>

    @endif

    <x-ui.modal :show="$showAddStage" title="Add stage option" close-action="closeModals" max-width="620px">
        <x-form.input name="newStageName" id="new-stage-name" label="Name" wire:model="newStageName"/>
        <x-form.input name="newStageText" id="new-stage-text" label="Default text" wire:model="newStageText"/>
        <div class="form-group">
            <label class="control-label">Colour</label>
            <div>
                @foreach ($colours as $colour)
                    <button type="button" class="colour-option {{ $newStageColour === $colour ? 'colour-option-selected' : '' }}" wire:click="$set('newStageColour', '{{ $colour }}')">
                        <img src="/img/{{ $colour }}.png" alt="">
                    </button>
                @endforeach
                @if ($newStageColour)
                    <button type="button" class="btn btn-link btn-xs font-grey-salsa" wire:click="$set('newStageColour', '')">Clear</button>
                @endif
            </div>
            @error('newStageColour') <span class="help-block font-red">{{ $message }}</span> @enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="addStage" wire:loading.attr="disabled" wire:target="addStage">Add option</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showAddSteel" title="Add STEEL option" close-action="closeModals" max-width="520px">
        <x-form.input name="newSteelName" id="new-steel-name" label="Name" wire:model="newSteelName"/>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="addSteel" wire:loading.attr="disabled" wire:target="addSteel">Add option</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showRemove" :title="$removeAction === 'delete' ? 'Delete option' : 'Archive option'" close-action="closeModals" confirm-action="removeOption" :confirm-label="$removeAction === 'delete' ? 'Delete option' : 'Archive option'">
        @if ($removeAction === 'delete')
            This option is not currently used by any sites.<br>
            <div class="sws-confirm-item">{{ $removeName }}</div>
        @else
            It will no longer be available for new selections, but its stored identifier will be retained.<br>
            <div class="sws-confirm-item">{{ $removeName }}</div>
        @endif
    </x-ui.confirm-modal>
</div>
