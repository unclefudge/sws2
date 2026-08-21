<div>
    @once
        <style>
            .bs-container {
                z-index: 10080 !important;
            }

            .report-filter-row {
                margin-bottom: 12px;
            }

            .report-table tbody tr > td {
                background: #fff !important;
            }

            .report-row {
                cursor: pointer;
            }

            .report-row:hover > td {
                background: #f8fafb !important;
            }

            .report-detail-row > td {
                background: #f7f9fb !important;
                padding: 12px 16px !important;
            }

            .report-detail {
                border-left: 3px solid #b8c1ca;
                padding-left: 14px;
            }

            .report-count {
                padding-top: 7px;
                font-weight: 600;
            }

            .report-actions {
                margin-bottom: 12px;
            }
        </style>
    @endonce

    @if ($message)
        <div class="alert alert-success" style="padding:8px 12px">{{ $message }}</div>
    @endif

    @error('selected')
    <div class="alert alert-warning" style="padding:8px 12px">{{ $message }}</div>
    @enderror

    @if ($inactive)
        <div class="row report-filter-row">
            <div class="col-md-3">
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('assignedCc', $el.value)">
                        @foreach ($assignedCcOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$assignedCc === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div wire:ignore wire:key="inactive-user-filter-{{ $assignedCc }}">
                    <select class="form-control bs-select" data-width="100%" data-live-search="true" data-size="8" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('username', $el.value)">
                        @foreach ($userOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$username === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" wire:model.live.debounce.350ms="search" placeholder="Search task or owner">
                </div>
            </div>
            
            <div class="col-md-1">
                <button type="button" class="btn default pull-right" wire:click="refreshReport"><i class="fa fa-refresh"></i> Refresh</button>
            </div>
        </div>

        <div class="clearfix report-actions">
            <div class="pull-left">
                <button type="button" class="btn blue btn-outline" wire:click="openReassign" @disabled(!$selectedIds)>Reassign selected</button>
                <button type="button" class="btn red btn-outline" wire:click="openDelete" @disabled(!$selectedIds)>Delete selected</button>
            </div>
        </div>
    @else
        <div class="row report-filter-row">
            <div class="col-md-3">
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('assignedTasks', $el.value)">
                        @foreach ($assignedTaskOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$assignedTasks === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('assignedCc', $el.value)">
                        @foreach ($assignedCcOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$assignedCc === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div wire:ignore wire:key="active-user-filter-{{ $assignedCc }}">
                    <select class="form-control bs-select" data-width="100%" data-live-search="true" data-size="8" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('username', $el.value)">
                        @foreach ($userOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$username === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('activeRecord', $el.value)">
                        @foreach ($activeRecordOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$activeRecord === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row report-filter-row">
            <div class="col-md-6">
                <div wire:ignore>
                    <select class="form-control bs-select" data-width="100%" data-live-search="true" data-size="8" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('taskType', $el.value)">
                        @foreach ($taskTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ (string)$taskType === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" wire:model.live.debounce.350ms="search" placeholder="Search task or owner">
                </div>
            </div>

            <div class="col-md-1">
                <button type="button" class="btn default pull-right" wire:click="refreshReport"><i class="fa fa-refresh"></i> Refresh</button>
            </div>
        </div>
    @endif

    <div class="clearfix" style="margin-bottom:10px">
        <div class="pull-left report-count">Tasks: {{ $totalFiltered }}</div>

        @if ($tasks->total() > 25)
            <div class="pull-right">
                <span wire:ignore>
                    <select class="form-control bs-select input-sm" data-width="110px" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('perPage', Number($el.value))">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>View {{ $option }}</option>
                        @endforeach
                    </select>
                </span>
            </div>
        @endif
    </div>

    @if ($tasks->count())
        <div class="table-responsive">
            <table class="table table-bordered table-nohover order-column report-table">
                <thead>
                <tr class="mytable-header">
                    @if ($inactive)
                        <th style="width:45px"></th>
                    @endif
                    <th>
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('title')">Task Name</a>
                        @if ($sortKey === 'title')
                            <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th style="width:25%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('assigned_names')">Task Owner(s)</a>
                        @if ($sortKey === 'assigned_names')
                            <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th style="width:10%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('due_at')">Due Date</a>
                        @if ($sortKey === 'due_at')
                            <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                    <th style="width:14%">
                        <a href="#" class="mytable-header-link" wire:click.prevent="sortBy('lastupdated')">Last updated</a>
                        @if ($sortKey === 'lastupdated')
                            <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </th>
                </tr>
                </thead>

                <tbody>
                @foreach ($tasks as $task)
                    @php
                        $taskId = (int)$task['id'];
                        $isExpanded = in_array($taskId, $expandedIds, true);
                        $overdue = !empty($task['due_at']) && \Carbon\Carbon::parse($task['due_at'])->startOfDay()->lt(today());
                    @endphp

                    <tr wire:key="todo-task-{{ $taskId }}" class="report-row">
                        @if ($inactive)
                            <td class="text-center" style="vertical-align:middle" x-on:click.stop>
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $taskId }}">
                            </td>
                        @endif
                        <td wire:click="toggleExpanded({{ $taskId }})">{{ $task['title'] }}</td>
                        <td wire:click="toggleExpanded({{ $taskId }})">{{ $task['assigned_names'] }}</td>
                        <td wire:click="toggleExpanded({{ $taskId }})" class="{{ $overdue ? 'font-red' : '' }}">
                            {{ !empty($task['due_at']) ? \Carbon\Carbon::parse($task['due_at'])->format('d/m/Y') : '' }}
                        </td>
                        <td wire:click="toggleExpanded({{ $taskId }})">{{ $task['lastupdated_human'] }}</td>
                    </tr>

                    @if ($isExpanded)
                        <tr wire:key="todo-task-detail-{{ $taskId }}" class="report-detail-row">
                            <td colspan="{{ $inactive ? 5 : 4 }}">
                                <div class="report-detail">
                                    <div class="font-grey-salsa" style="margin-bottom:8px">Task ID: {{ $taskId }}</div>
                                    {!! $task['info'] !!}
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            @php
                $currentPage = $tasks->currentPage();
                $lastPage = $tasks->lastPage();
                $pageFrom = max(1, $currentPage - 2);
                $pageTo = min($lastPage, $currentPage + 2);
            @endphp

            <div class="row" style="margin-top:10px; margin-bottom:20px">
                <div class="col-sm-6">
                    <div class="dataTables_info" style="padding-top:8px">Showing {{ $tasks->firstItem() }} to {{ $tasks->lastItem() }} of {{ $tasks->total() }} tasks</div>
                </div>

                <div class="col-sm-6 text-right">
                    <ul class="pagination" style="margin:0">
                        <li class="{{ $tasks->onFirstPage() ? 'disabled' : '' }}">
                            @if ($tasks->onFirstPage())
                                <span>&laquo;</span>
                            @else
                                <a href="#" wire:click.prevent="previousPage('todoPage')">&laquo;</a>
                            @endif
                        </li>

                        @if ($pageFrom > 1)
                            <li><a href="#" wire:click.prevent="gotoPage(1, 'todoPage')">1</a></li>
                            @if ($pageFrom > 2)
                                <li class="disabled"><span>…</span></li>
                            @endif
                        @endif

                        @for ($page = $pageFrom; $page <= $pageTo; $page++)
                            <li class="{{ $page === $currentPage ? 'active' : '' }}">
                                @if ($page === $currentPage)
                                    <span>{{ $page }}</span>
                                @else
                                    <a href="#" wire:click.prevent="gotoPage({{ $page }}, 'todoPage')">{{ $page }}</a>
                                @endif
                            </li>
                        @endfor

                        @if ($pageTo < $lastPage)
                            @if ($pageTo < $lastPage - 1)
                                <li class="disabled"><span>…</span></li>
                            @endif
                            <li><a href="#" wire:click.prevent="gotoPage({{ $lastPage }}, 'todoPage')">{{ $lastPage }}</a></li>
                        @endif

                        <li class="{{ $tasks->hasMorePages() ? '' : 'disabled' }}">
                            @if ($tasks->hasMorePages())
                                <a href="#" wire:click.prevent="nextPage('todoPage')">&raquo;</a>
                            @else
                                <span>&raquo;</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    @else
        <div class="font-grey-silver" style="padding:15px 0">No tasks found.</div>
    @endif

    @if ($inactive)
        <x-ui.modal :show="$showReassignModal" title="Reassign Tasks" close-action="closeModals" max-width="560px">
            <p>This will reassign the selected tasks to another active user.</p>

            <div class="form-group">
                <label class="control-label">Assign To</label>
                <div wire:ignore wire:key="todo-reassign-user-{{ $modalNonce }}">
                    <select class="form-control bs-select" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if (!$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('assignTo', Number($el.value))">
                        <option value="0">Select user</option>
                        @foreach ($reassignUserOptions as $userId => $userName)
                            <option value="{{ $userId }}">{{ $userName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
                <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="reassignSelected" wire:loading.attr="disabled" wire:target="reassignSelected">Save</button>
            </x-slot>
        </x-ui.modal>

        <x-ui.confirm-modal :show="$showDeleteModal" title="Delete Tasks" close-action="closeModals" confirm-action="deleteSelected" confirm-label="Yes, delete them">
            <div>
                These selected ToDo tasks will be permanently deleted.
                <div class="sws-confirm-item">{{ count($selectedIds) }} selected {{ \Illuminate\Support\Str::plural('task', count($selectedIds)) }}</div>
            </div>
        </x-ui.confirm-modal>
    @endif
</div>
