<div x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @once
        <style>
            /* Bootstrap Select menus are rendered under <body> so the
               custom modal card cannot crop the dropdown. */
            .bs-container {
                z-index: 10080 !important;
            }
        </style>
    @endonce
    @if ($allDone)
        <div class="note note-warning">
            <p style="margin:0">
                All items have been completed and request requires
                <button type="button" class="btn btn-xs btn-outline dark disabled">Sign Off</button>
                at the bottom.
            </p>
        </div>
    @endif

    <h4 class="clearfix" style="margin-bottom: 5px">
        Maintenance Items

        <div class="pull-right">
            <span wire:ignore style="display:inline-block; margin-right:10px; vertical-align:middle">
                <select class="form-control bs-select" data-width="160px" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('filter', $el.value)">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="completed" {{ $filter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="outstanding" {{ $filter === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                </select>
            </span>

            @if ($canAdd)
                <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAdd">Add</button>
            @endif
        </div>
    </h4>
    <hr style="padding: 0; margin: 0 0 10px 0">

    @if ($items->isNotEmpty())
        <table class="table table-striped table-bordered table-nohover order-column">
            <thead>
            <tr class="mytable-header">
                @if ($canEdit && $filter === 'all')
                    <th style="width:5%; text-align:center">#</th>
                @endif
                <th>Maintenance Item</th>
                <th style="width:30%">Assigned Task</th>
                <th style="width:15%">Completed</th>
                @if ($canEdit || $canDelete)
                    <th style="width:10%">Action</th>
                @endif
            </tr>
            </thead>
            @if ($canEdit && $filter === 'all')
                <tbody x-data="{ draggingId: null }"
                    x-on:dragover.prevent="const target = $event.target.closest('tr[data-item-id]'); if (!target || String(target.dataset.itemId) === String(draggingId)) return; const dragged = $root.querySelector('tr[data-item-id=&quot;' + draggingId + '&quot;]'); if (!dragged) return; const rows = [...$root.querySelectorAll('tr[data-item-id]')]; rows.indexOf(dragged) < rows.indexOf(target) ? target.after(dragged) : target.before(dragged)"
                    x-on:drop.prevent="const ids = [...$root.querySelectorAll('tr[data-item-id]')].map(row => Number(row.dataset.itemId)); draggingId = null; $wire.reorderItems(ids)"
                    x-on:dragend="draggingId = null">
            @else
                <tbody>
            @endif
            @foreach ($items as $item)
                <tr wire:key="maintenance-item-{{ $item->id }}" data-item-id="{{ $item->id }}" @if ($canEdit && $filter === 'all') x-bind:style="String(draggingId) === '{{ $item->id }}' ? 'opacity:.45' : ''" @endif>
                    @if ($canEdit && $filter === 'all')
                        <td class="text-center" style="padding-top:15px">
                            <span draggable="true" title="Drag to reorder" style="display:inline-block; cursor:move; padding:2px 8px; color:#9aa0a6" x-on:dragstart.stop="draggingId = {{ $item->id }}; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', '{{ $item->id }}')"><i class="fa fa-bars" style="font-size:16px"></i></span>
                        </td>
                    @endif
                    <td style="padding-top:15px">{{ $item->name }}</td>
                    <td style="padding-top:15px">
                        {{ $item->assigned?->name ?? 'Unassigned' }}

                        @if ($item->planner && $item->planner->task && $item->planner->from)
                            <br><b>Task:</b> {{ $item->planner->task->name }} ({{ $item->planner->from->format('d/m/Y') }})
                        @endif
                    </td>
                    <td>
                        @if ($item->done_by)
                            {{ $item->done_at?->format('d/m/Y') ?? '-' }}<br>
                            {{ $users->get($item->done_by)?->full_name ?? 'Unknown' }}

                            @if ((int) $item->status === 2)
                                <br><span class="font-red">OWNER WORKS</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>

                    @if ($canEdit || $canDelete)
                        <td>
                            @if ($canEdit)
                                <button type="button" class="btn btn-xs btn-outline blue" wire:click="openEdit({{ $item->id }})">
                                    <i class="fa fa-pencil"></i> Edit
                                </button>
                            @endif

                            @if ($canDelete)
                                <button type="button" class="btn btn-xs dark" wire:click="confirmDelete({{ $item->id }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <div class="font-grey-silver">{{ $filter === 'all' ? 'No maintenance items.' : 'No ' . $filter . ' maintenance items.' }}</div>
    @endif

    {{-- Add Item --}}
    <x-ui.modal :show="$showAddModal" title="Add Maintenance Item" close-action="closeModals">
        <div class="form-group {{ $errors->has('itemName') ? 'has-error' : '' }}">
            <label class="control-label">Item</label>
            <textarea wire:model="itemName" rows="3" class="form-control" placeholder="Specific details of maintenance request item"></textarea>
            @error('itemName')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd">Save</button>
        </x-slot>
    </x-ui.modal>

    {{-- Edit Item --}}
    <x-ui.modal :show="$showEditModal" title="Edit Maintenance Item" close-action="closeModals">
        <div style="margin-bottom:18px">
            <b>Item</b><br>
            {{ $itemName }}
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group {{ $errors->has('assignedTo') ? 'has-error' : '' }}">
                    <label class="control-label">Assigned To</label>
                    <div wire:ignore wire:key="maintenance-assigned-to-{{ $editingItemId }}">
                        <select class="form-control bs-select"
                                data-width="100%"
                                data-live-search="true"
                                data-container="body"
                                data-size="7"
                                x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                x-on:change="$wire.set('assignedTo', $el.value)">
                            <option value="" {{ $assignedTo === '' ? 'selected' : '' }}>Select company</option>

                            @foreach ($companyOptions as $companyId => $companyName)
                                <option value="{{ $companyId }}" {{ (string) $assignedTo === (string) $companyId ? 'selected' : '' }}>
                                    {{ $companyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @error('assignedTo')<span class="help-block font-red">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('plannerTaskId') ? 'has-error' : '' }}">
                    <label class="control-label">Planner Task</label>

                    {{-- The available tasks change when Assigned To changes, so the
                         Livewire key deliberately includes assignedTo. This replaces
                         the wire:ignore/selectpicker block with fresh options. --}}
                    <div wire:ignore wire:key="maintenance-planner-task-{{ $editingItemId }}-{{ $assignedTo }}">
                        <select class="form-control bs-select"
                                data-width="100%"
                                data-live-search="true"
                                data-container="body"
                                data-size="8"
                                {{ !$assignedTo ? 'disabled' : '' }}
                                x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                x-on:change="$wire.set('plannerTaskId', $el.value)">
                            <option value="" {{ $plannerTaskId === '' ? 'selected' : '' }}>
                                {{ $assignedTo ? 'Select task' : 'Select company first' }}
                            </option>

                            @foreach ($plannerTaskOptions as $taskId => $taskName)
                                <option value="{{ $taskId }}" {{ (string) $plannerTaskId === (string) $taskId ? 'selected' : '' }}>
                                    {{ $taskName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @error('plannerTaskId')<span class="help-block font-red">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                @if ($plannerTaskId)
                    <x-form.datepicker name="maintenancePlannerDate" label="Task Date" :value="$plannerDate" format="dd/mm/yyyy" readonly wire:ignore
                                       x-init="
                                           const picker = $($el).closest('.date-picker');
                                           if (!picker.data('datepicker')) {
                                               picker.datepicker({
                                                   rtl: typeof App !== 'undefined' ? App.isRTL() : false,
                                                   orientation: 'left',
                                                   autoclose: true
                                               });
                                           }
                                           picker.on('changeDate clearDate', function () {
                                               $wire.set('plannerDate', $(this).find('input').val() || '');
                                           });
                                       "/>
                    @error('plannerDate')<span class="help-block font-red">{{ $message }}</span>@enderror
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('itemStatus') ? 'has-error' : '' }}">
                    <label class="control-label">Status</label>

                    <div wire:ignore wire:key="maintenance-item-status-{{ $editingItemId }}">
                        <select class="form-control bs-select"
                                data-width="100%"
                                data-container="body"
                                x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                x-on:change="$wire.set('itemStatus', $el.value)">
                            <option value="0" {{ (string) $itemStatus === '0' ? 'selected' : '' }}>Incomplete</option>
                            <option value="1" {{ (string) $itemStatus === '1' ? 'selected' : '' }}>Completed</option>
                            <option value="2" {{ (string) $itemStatus === '2' ? 'selected' : '' }}>Owner Works</option>
                        </select>
                    </div>

                    @error('itemStatus')<span class="help-block font-red">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit">Save</button>
        </x-slot>
    </x-ui.modal>

    {{-- Delete Item --}}
    <x-ui.confirm-modal :show="$showDeleteModal" close-action="closeModals" confirm-action="deleteItem" confirm-label="Yes, delete it">
        <div><h3><strong>Are you sure?</strong></h3></div>
        <div>This maintenance item will be permanently deleted.</div>
        <div class="sws-confirm-item">{{ $itemName }}</div>
    </x-ui.confirm-modal>
</div>
