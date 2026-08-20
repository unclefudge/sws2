<div>
    @once
        <style>
            .bs-container { z-index:10080 !important; }

            .supervisor-child-wrap {
                background:#444d58;
                padding:5px 10px 1px;
            }

            .supervisor-child-table {
                margin:0 0 5px;
                max-width:420px;
                background:#fff;
            }

            .supervisor-child-table td {
                vertical-align:middle !important;
            }
        </style>
    @endonce

    @if ($message)
        <div class="alert alert-success" style="padding:8px 12px">{{ $message }}</div>
    @endif

    <div class="portlet light">
        <div class="portlet-title tabbable-line">
            <div class="caption font-dark">
                <i class="icon-layers"></i>
                <span class="caption-subject bold uppercase font-green-haze">Supervisor List</span>
            </div>

            @if ($canEdit)
                <div class="actions">
                    <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAdd">Add</button>
                </div>
            @endif
        </div>

        <div class="portlet-body">
            <div class="note note-warning">
                <p>An Area Supervisor (ie. senior supervisor of another) is granted access to the sites of all the supervisors under them.</p>

                @if ($isCC)
                    <p><br>In regards to Quality Assurance Reports they will be:</p>
                    <ul>
                        <li>granted ability to Sign Off as Site Manager</li>
                        <li>notified of overdue QA tasks associated with their sites</li>
                    </ul>
                @endif
            </div>

            @if ($areaSupervisors->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:45px"></th>
                            <th>
                                <a href="#" class="mytable-header-link" wire:click.prevent="sortByName">
                                    Name
                                    <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                </a>
                            </th>
                            @if ($canEdit)
                                <th style="width:90px">Actions</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($areaSupervisors as $supervisor)
                            @php
                                $children = $childrenByParent->get($supervisor->id, collect());
                                $hasChildren = $children->isNotEmpty();
                                $isOpen = in_array((int)$supervisor->id, $openAreaIds, true);
                            @endphp

                            <tr wire:key="area-supervisor-{{ $supervisor->id }}">
                                <td class="text-center">
                                    @if ($hasChildren)
                                        <button type="button" class="btn btn-link btn-xs" wire:click="toggleArea({{ $supervisor->id }})" style="padding:0">
                                            @if ($isOpen)
                                                <i class="fa fa-minus-circle" style="color:#e7505a"></i>
                                            @else
                                                <i class="fa fa-plus-circle" style="color:#32c5d2"></i>
                                            @endif
                                        </button>
                                    @endif
                                </td>

                                <td>
                                    {{ $supervisor->name }}

                                    @if ($canEdit)
                                        <button type="button" class="btn btn-link btn-xs" wire:click="openAdd({{ $supervisor->id }})" style="margin-left:8px">
                                            <i class="fa fa-plus"></i> Add under
                                        </button>
                                    @endif
                                </td>

                                @if ($canEdit)
                                    <td>
                                        <button type="button" class="btn btn-xs dark" wire:click="confirmDeleteArea({{ $supervisor->id }})" title="Delete Area Supervisor">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>

                            @if ($hasChildren && $isOpen)
                                <tr wire:key="area-supervisor-children-{{ $supervisor->id }}" class="nohover">
                                    <td colspan="{{ $canEdit ? 3 : 2 }}" style="padding:0">
                                        <div class="supervisor-child-wrap">
                                            <table class="table table-striped table-hover order-column supervisor-child-table">
                                                <tbody>
                                                @foreach ($children as $child)
                                                    <tr wire:key="child-supervisor-{{ $child->id }}">
                                                        <td>{{ $child->name }}</td>
                                                        @if ($canEdit)
                                                            <td style="width:55px" class="text-center">
                                                                <button type="button" class="btn btn-xs dark" wire:click="deleteChildSupervisor({{ $child->id }})" title="Delete Supervisor">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="font-grey-silver">No Supervisors.</div>
            @endif
        </div>
    </div>

    <x-ui.modal :show="$showAddModal" title="Add Supervisor" close-action="closeModals" max-width="520px">
        <div class="form-group {{ $errors->has('userId') ? 'has-error' : '' }}">
            <label class="control-label">Employee</label>
            <div wire:ignore wire:key="supervisor-user-select-{{ $modalNonce }}">
                <select class="form-control bs-select" data-width="100%" data-live-search="true" data-container="body" data-size="8"
                        x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                        x-on:change="$wire.set('userId', Number($el.value))">
                    <option value="0">Select employee to add as Supervisor</option>
                    @foreach ($staffOptions as $staffId => $staffName)
                        <option value="{{ $staffId }}" {{ (int)$userId === (int)$staffId ? 'selected' : '' }}>{{ $staffName }}</option>
                    @endforeach
                </select>
            </div>
            @error('userId')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group {{ $errors->has('parentId') ? 'has-error' : '' }}">
            <label class="control-label">Area Supervisor</label>
            <div wire:ignore wire:key="supervisor-parent-select-{{ $modalNonce }}">
                <select class="form-control bs-select" data-width="100%" data-live-search="true" data-container="body" data-size="8"
                        x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                        x-on:change="$wire.set('parentId', Number($el.value))">
                    <option value="0" {{ $parentId === 0 ? 'selected' : '' }}>No assigned Area Supervisor</option>
                    @foreach ($areaOptions as $areaId => $areaName)
                        <option value="{{ $areaId }}" {{ (int)$parentId === (int)$areaId ? 'selected' : '' }}>{{ $areaName }}</option>
                    @endforeach
                </select>
            </div>
            @error('parentId')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="addSupervisor" wire:loading.attr="disabled" wire:target="addSupervisor" @disabled(!$userId)>Save</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showDeleteModal" title="Delete Area Supervisor" close-action="closeModals" confirm-action="deleteAreaSupervisor" confirm-label="Yes, delete it">
        <div>
            This will also delete all supervisors under
            <div class="sws-confirm-item">{{ $deletingName }}</div>
        </div>
    </x-ui.confirm-modal>
</div>
