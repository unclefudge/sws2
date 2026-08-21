<div>
    @once
        <style>
            .bs-container { z-index:10080 !important; }

            .supervisor-info {
                background:#f7f9fb;
                border-left:3px solid #36c6d3;
                padding:14px 16px;
                margin-bottom:18px;
                color:#59636e;
            }

            .supervisor-info p {
                margin:0 0 6px;
            }

            .supervisor-info ul {
                margin:6px 0 0 20px;
                padding:0;
            }

            .supervisor-table {
                margin-bottom:0;
            }

            .supervisor-table > tbody > tr > td {
                background:#fff !important;
            }

            .supervisor-table > tbody > tr.supervisor-child-row:hover > td {
                background:#f8fafb !important;
            }

            .supervisor-expand {
                width:24px;
                height:24px;
                border:1px solid #cdd4da;
                border-radius:50%;
                background:#fff;
                color:#5b6770;
                padding:0;
                line-height:22px;
                text-align:center;
            }

            .supervisor-expand:hover,
            .supervisor-expand:focus {
                border-color:#36c6d3;
                color:#36c6d3;
                outline:none;
            }

            .supervisor-name {
                font-weight:600;
                color:#4b555f;
            }

            .supervisor-count {
                margin-left:8px;
                color:#9aa4ad;
                font-size:12px;
            }

            .supervisor-child-name {
                padding-left:34px !important;
                color:#65717b;
                position:relative;
            }

            .supervisor-child-name:before {
                content:"";
                position:absolute;
                left:18px;
                top:0;
                bottom:50%;
                width:8px;
                border-left:1px solid #d7dde2;
                border-bottom:1px solid #d7dde2;
            }

            .supervisor-add-under {
                margin-left:20px !important;
                font-weight:400;
            }

            .supervisor-actions {
                white-space:nowrap;
                text-align:center;
            }

            @media (max-width:767px) {
                .supervisor-child-name {
                    padding-left:24px !important;
                }

                .supervisor-child-name:before {
                    left:10px;
                }
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
            <div class="supervisor-info">
                <p><i class="fa fa-info-circle" style="margin-right:6px"></i><b>Area Supervisors</b> are granted access to the sites of all supervisors assigned under them.</p>

                @if ($isCC)
                    <ul>
                        <li>They can sign Quality Assurance Reports as Site Manager.</li>
                        <li>They are notified of overdue QA tasks associated with those sites.</li>
                    </ul>
                @endif
            </div>

            @if ($areaSupervisors->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-nohover order-column supervisor-table">
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
                                <td class="text-center" style="vertical-align:middle">
                                    @if ($hasChildren)
                                        <button type="button" class="supervisor-expand" wire:click="toggleArea({{ $supervisor->id }})" title="{{ $isOpen ? 'Collapse' : 'Expand' }}">
                                            <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                        </button>
                                    @endif
                                </td>

                                <td style="vertical-align:middle">
                                    <span class="supervisor-name">{{ $supervisor->name }}</span>

                                    @if ($hasChildren)
                                        <span class="supervisor-count">{{ $children->count() }} {{ \Illuminate\Support\Str::plural('supervisor', $children->count()) }}</span>
                                    @endif

                                    @if ($canEdit)
                                        <button type="button" class="btn blue btn-xs btn-outline supervisor-add-under" wire:click="openAdd({{ $supervisor->id }})">
                                            <i class="fa fa-plus"></i> Add under
                                        </button>
                                    @endif
                                </td>

                                @if ($canEdit)
                                    <td class="supervisor-actions">
                                        <button type="button" class="btn btn-xs dark" wire:click="confirmDeleteArea({{ $supervisor->id }})" title="Delete Area Supervisor">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>

                            @if ($hasChildren && $isOpen)
                                @foreach ($children as $child)
                                    <tr wire:key="child-supervisor-{{ $child->id }}" class="supervisor-child-row">
                                        <td></td>
                                        <td class="supervisor-child-name">{{ $child->name }}</td>
                                        @if ($canEdit)
                                            <td class="supervisor-actions">
                                                <button type="button" class="btn btn-xs dark" wire:click="deleteChildSupervisor({{ $child->id }})" title="Delete Supervisor">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
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
