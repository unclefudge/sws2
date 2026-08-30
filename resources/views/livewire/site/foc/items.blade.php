<div>
    <h4 class="clearfix" style="margin-bottom: 5px">
        FOC Completion Items

        <div class="pull-right">
            <span wire:ignore style="display: inline-block; margin-right: 10px; vertical-align: middle">
                <select class="form-control bs-select" data-width="160px" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('filter', $el.value)">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="completed" {{ $filter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="outstanding" {{ $filter === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                </select>
            </span>

            @if ($canAdd)
                <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAdd">Add</button>
                <button type="button" class="btn btn-circle green btn-outline btn-sm" style="margin-left: 5px" wire:click="openMultiple">Add Multiple</button>
            @endif
        </div>
    </h4>

    <hr style="padding: 0; margin: 0 0 10px 0">

    @if ($message)
        <div class="alert alert-success" style="padding: 8px 12px;">
            {{ $message }}
        </div>
    @endif

    @forelse ($categories as $category)
        @php
            $categoryItems = $items->where('category_id', $category->id);
        @endphp

        @if ($categoryItems->isNotEmpty())
            <table class="table table-striped table-bordered table-nohover order-column">
                <thead>
                <tr class="mytable-header">
                    @if ($canEdit && $filter === 'all')
                        <th style="width:5%; text-align: center">#</th>
                    @endif
                    <th style="width:5%"></th>
                    <th>{{ $category->name }}</th>
                    <th style="width:18%">Completed</th>

                    @if ($canEdit || $canDelete || $canComplete)
                        <th style="width:12%"></th>
                    @endif
                </tr>
                </thead>

                @if ($canEdit && $filter === 'all')
                    <tbody
                            x-data="{ draggingId: null }"
                            x-on:dragover.prevent="
                            const target = $event.target.closest('tr[data-item-id]');
                            if (!target || String(target.dataset.itemId) === String(draggingId)) return;

                            const dragged = $root.querySelector('tr[data-item-id=&quot;' + draggingId + '&quot;]');
                            if (!dragged) return;

                            const rows = [...$root.querySelectorAll('tr[data-item-id]')];
                            if (rows.indexOf(dragged) < rows.indexOf(target)) {
                                target.after(dragged);
                            } else {
                                target.before(dragged);
                            }
                        "
                            x-on:drop.prevent="
                            const ids = [...$root.querySelectorAll('tr[data-item-id]')].map(row => Number(row.dataset.itemId));
                            draggingId = null;
                            $wire.reorderItems({{ $category->id }}, ids);
                        "
                            x-on:dragend="draggingId = null"
                    >
                @else
                    <tbody>
                    @endif
                    @foreach ($categoryItems as $item)
                        <tr wire:key="foc-item-{{ $item->id }}" data-item-id="{{ $item->id }}" @if ($canEdit && $filter === 'all') x-bind:style="String(draggingId) === '{{ $item->id }}' ? 'opacity: .45;' : ''" @endif>
                            @if ($canEdit && $filter === 'all')
                                <td class="text-center" style="padding-top: 15px">
                                <span draggable="true" title="Drag to reorder" style="display: inline-block; cursor: move; padding: 2px 8px; color: #9aa0a6"
                                      x-on:dragstart.stop="draggingId = {{ $item->id }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item->id }}')">
                                    <i class="fa fa-bars" style="font-size: 16px"></i>
                                </span>
                                </td>
                            @endif

                            <td class="text-center" style="padding-top: 15px">
                                @if ((int) $item->status === \App\Models\Site\SiteFocItem::STATUS_COMPLETED)
                                    <i class="fa fa-check-square-o font-green" style="font-size: 20px; padding-top: 5px"></i>
                                @elseif ((int) $item->status === \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE)
                                    <i class="fa fa-exclamation-triangle font-red" style="font-size: 18px; padding-top: 5px"></i>
                                @else
                                    <i class="fa fa-square-o font-red" style="font-size: 20px; padding-top: 5px"></i>
                                @endif
                            </td>

                            <td style="padding-top: 15px;">
                                {{ $item->name }}

                                @if ($item->notes)
                                    <div style="margin-top: 8px">
                                        <label class="font-grey-silver" style="font-size: 11px; margin-bottom: 3px">Notes</label>

                                        @if ($canMutateItems && (bool) $foc->status)
                                            <textarea class="form-control input-sm" rows="2"
                                                      placeholder="Add notes for this item"
                                                      wire:change="saveNotes({{ $item->id }}, $event.target.value)">{{ $item->notes }}</textarea>
                                            @error("notes.{$item->id}")
                                            <span class="help-block font-red">{{ $message }}</span>
                                            @enderror
                                        @else
                                            <div style="white-space: pre-line">{{ $item->notes ?: '-' }}</div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if ((int) $item->status === \App\Models\Site\SiteFocItem::STATUS_COMPLETED)
                                    {{ $item->sign_at?->format('d/m/Y') ?? '-' }}<br>
                                    {{ $signers->get($item->sign_by)?->full_name ?? 'Unknown' }}
                                @else
                                    @if ((int) $item->status === \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE)
                                        <span class="font-red">Defective</span>
                                    @elseif (!$canComplete)
                                        <span class="font-grey-silver">Incomplete</span>
                                    @endif

                                    @if ($canComplete && (bool) $foc->status)
                                        <div style="margin-top: {{ (int) $item->status === \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE ? '6px' : '0' }}">
                                            <button type="button" class="btn green btn-xs btn-outline"
                                                    wire:click="setItemStatus({{ $item->id }}, {{ \App\Models\Site\SiteFocItem::STATUS_COMPLETED }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="setItemStatus({{ $item->id }}, {{ \App\Models\Site\SiteFocItem::STATUS_COMPLETED }})">
                                                Complete
                                            </button>

                                            @if ($item->isInspections() && (int) $item->status !== \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE)
                                                <button type="button" class="btn red btn-xs btn-outline"
                                                        wire:click="setItemStatus({{ $item->id }}, {{ \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="setItemStatus({{ $item->id }}, {{ \App\Models\Site\SiteFocItem::STATUS_DEFECTIVE }})">
                                                    Defective
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>

                            @if ($canEdit || $canDelete || $canComplete)
                                <td>
                                    @if ((int) $item->status === \App\Models\Site\SiteFocItem::STATUS_COMPLETED && (int) $foc->status !== 0 && $canComplete)
                                        <button type="button" class="btn btn-xs btn-outline red"
                                                wire:click="setItemStatus({{ $item->id }}, {{ \App\Models\Site\SiteFocItem::STATUS_OUTSTANDING }})">
                                            Re-open
                                        </button>
                                    @endif

                                    @if ((int) $item->status !== \App\Models\Site\SiteFocItem::STATUS_COMPLETED && $canEdit)
                                        <button type="button" class="btn btn-xs btn-outline blue" wire:click="openEdit({{ $item->id }})"><i class="fa fa-pencil"></i> Edit</button>
                                    @endif

                                    @if ($canDelete)
                                        <button type="button" class="btn btn-xs dark" wire:click="confirmDelete({{ $item->id }})"><i class="fa fa-trash"></i></button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
            </table>
        @endif
    @empty
        <div class="font-grey-silver">No FOC item categories are configured.</div>
    @endforelse

    @if ($items->isEmpty() && $categories->isNotEmpty())
        <div class="font-grey-silver">
            {{ $filter === 'all' ? 'No FOC completion items.' : 'No ' . $filter . ' FOC completion items.' }}
        </div>
    @endif


    {{-- Add Item Modal --}}
    <x-ui.modal :show="$showAddModal" title="Add FOC Item" close-action="closeModals">
        <div class="row" style="padding-bottom: 18px">
            <div class="col-md-6">
                <label class="control-label">Category</label>

                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('categoryId', $el.value)">
                        <option value="" {{ $categoryId === '' ? 'selected' : '' }}>Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) $categoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @error('categoryId')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <label class="control-label">Item Description</label>
                <textarea wire:model="itemName" rows="4" class="form-control" placeholder="Specific details of FOC item"></textarea>

                @error('itemName')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row" style="margin-top: 18px">
            <div class="col-md-12">
                <label class="control-label">Notes</label>
                <textarea wire:model="itemNotes" rows="3" class="form-control" placeholder="Optional notes for this item"></textarea>

                @error('itemNotes')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd">Add Item</button>
        </x-slot>
    </x-ui.modal>


    {{-- Add Multiple Items Modal --}}
    <x-ui.modal :show="$showMultipleModal" title="Add Multiple FOC Items" close-action="closeModals" max-width="900px">
        <div style="max-height: 60vh; overflow-y: auto; padding-right: 5px">
            @foreach ($multipleItems as $index => $row)
                <div class="row" wire:key="multiple-item-{{ $index }}" style="margin-bottom: 12px">
                    <div class="col-md-1" style="padding-top: 8px"><strong>{{ $index + 1 }}.</strong></div>

                    <div class="col-md-3">
                        <div wire:ignore>
                            <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('multipleItems.{{ $index }}.category_id', $el.value)">
                                <option value="" {{ ($row['category_id'] ?? '') === '' ? 'selected' : '' }}>Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) ($row['category_id'] ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error("multipleItems.$index.category_id")
                        <span class="help-block font-red">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <textarea wire:model="multipleItems.{{ $index }}.name" rows="2" class="form-control" placeholder="Specific details of FOC item {{ $index + 1 }}"></textarea>
                        @error("multipleItems.$index.name")
                        <span class="help-block font-red">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>

        @error('multipleItems')
        <div class="alert alert-danger" style="margin: 15px 0 0 0; padding: 8px 12px">{{ $message }}</div>
        @enderror

        <div style="margin-top: 15px">
            <button type="button" class="btn blue btn-outline" wire:click="moreItems"><i class="fa fa-plus"></i> More Items</button>
            <span class="font-grey-silver" style="margin-left: 8px">{{ count($multipleItems) }} item rows</span>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveMultiple" wire:loading.attr="disabled" wire:target="saveMultiple">Add Items</button>
        </x-slot>
    </x-ui.modal>

    {{-- Edit Item Modal --}}
    <x-ui.modal :show="$showEditModal" title="Edit FOC Item" close-action="closeModals">
        <div class="row" style="padding-bottom: 18px">
            <div class="col-md-6">
                <label class="control-label">Category</label>
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('categoryId', $el.value)">
                        <option value="" {{ $categoryId === '' ? 'selected' : '' }}>Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) $categoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @error('categoryId')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row" style="padding-bottom: 18px">
            <div class="col-md-12">
                <label class="control-label">Item Description</label>
                <textarea wire:model="itemName" rows="4" class="form-control"></textarea>

                @error('itemName')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row" style="margin-top: 18px">
            <div class="col-md-12">
                <label class="control-label">Notes</label>
                <textarea wire:model="itemNotes" rows="3" class="form-control" placeholder="Optional notes for this item"></textarea>

                @error('itemNotes')
                <span class="help-block font-red">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit">Save Changes</button>
        </x-slot>
    </x-ui.modal>

    {{-- Delete Item Modal --}}
    <x-ui.confirm-modal :show="$showDeleteModal" close-action="closeModals" confirm-action="deleteItem" confirm-label="Yes, delete it">
        <div><h3><strong>Are you sure?</strong></h3></div>
        <div>This item will be permanently deleted.</div>

        <div class="sws-confirm-item">
            {{ $itemName }}
        </div>
    </x-ui.confirm-modal>
</div>
