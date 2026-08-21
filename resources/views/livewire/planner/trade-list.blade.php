<div>
    @once
        <style>
            .trade-list-table {
                margin-bottom: 0;
            }

            .trade-list-table > tbody > tr > td {
                background: #fff !important;
            }

            .trade-expand {
                width: 24px;
                height: 24px;
                border: 1px solid #cdd4da;
                border-radius: 50%;
                background: #fff;
                color: #5b6770;
                padding: 0;
                line-height: 22px;
                text-align: center;
            }

            .trade-expand:hover,
            .trade-expand:focus {
                border-color: #36c6d3;
                color: #36c6d3;
                outline: none;
            }

            .trade-name {
                font-weight: 600;
                color: #4b555f;
            }

            .trade-count {
                margin-left: 8px;
                color: #9aa4ad;
                font-size: 12px;
            }

            .trade-disabled {
                color: #e7505a !important;
                text-decoration: line-through;
            }

            .task-upcoming {
                color: #3598dc;
                font-weight: bold;
            }

            .trade-task-child-row > td {
                background: #fff !important;
            }

            .trade-task-child-row.task-upcoming-row > td {
                background: #FFFCF3 !important;
                font-weight: bold;
            }

            .trade-task-child-name {
                padding-left: 34px !important;
                color: #65717b;
                position: relative;
            }

            .trade-task-child-name:before {
                content: "";
                position: absolute;
                left: 18px;
                top: 0;
                bottom: 50%;
                width: 8px;
                border-left: 1px solid #d7dde2;
                border-bottom: 1px solid #d7dde2;
            }

            .trade-task-code {
                display: inline-block;
                min-width: 88px;
                margin-right: 12px;
                color: #65717b;
            }

            .trade-add-task {
                margin-left: 20px !important;
                font-weight: 400;
            }

            @media (max-width: 767px) {
                .trade-task-child-name {
                    padding-left: 24px !important;
                }

                .trade-task-child-name:before {
                    left: 10px;
                }

                .trade-task-code {
                    min-width: 58px;
                    margin-right: 8px;
                }
            }

            .trade-actions,
            .task-actions {
                white-space: nowrap;
                text-align: center;
            }

            .task-upcoming-toggle {
                border: 0;
                background: transparent;
                padding: 2px 5px;
                color: #69757f;
            }

            .task-upcoming-toggle:hover,
            .task-upcoming-toggle:focus {
                color: #36c6d3;
                outline: none;
            }

            .custom-badge {
                margin-left: 7px;
                vertical-align: middle;
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
                <span class="caption-subject bold uppercase font-green-haze">Trades List</span>
            </div>

            <div class="actions">
                <button type="button" class="btn grey btn-sm" wire:click="toggleShowDisabled">{{ $showDisabled ? 'Hide Disabled' : 'Show Disabled' }}</button>

                @if ($canAddTrade)
                    <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="openAddTrade">Add</button>
                @endif
            </div>
        </div>

        <div class="portlet-body">
            @if ($trades->isNotEmpty())
                <div class="table-responsive">
                    @php
                        $showActions = $canEditTrade || $canToggleTrade || $canEditTask || $canToggleTask;
                    @endphp

                    <table class="table table-bordered table-nohover order-column trade-list-table">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:45px"></th>
                            <th>
                                <a href="#" class="mytable-header-link" wire:click.prevent="sortByName">
                                    Name
                                    <i class="fa fa-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                </a>
                            </th>
                            <th style="width:120px" class="text-center"><span class="hidden-xs">Upcoming</span><span class="visible-xs">Up</span></th>
                            @if ($showActions)
                                <th style="width:165px">Actions</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($trades as $trade)
                            @php
                                $isOpen = in_array((int)$trade->id, $openTradeIds, true);
                                $tasks = $tasksByTrade->get($trade->id, collect());
                            @endphp

                            <tr wire:key="trade-{{ $trade->id }}">
                                <td class="text-center" style="vertical-align:middle">
                                    <button type="button" class="trade-expand" wire:click="toggleTradeOpen({{ $trade->id }})" title="{{ $isOpen ? 'Collapse' : 'Expand' }}">
                                        <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                    </button>
                                </td>

                                <td style="vertical-align:middle">
                                    <span class="trade-name {{ !$trade->status ? 'trade-disabled' : '' }}">{{ $trade->name }}</span>

                                    @if ((int)$trade->company_id !== 1)
                                        <span class="badge badge-info badge-roundless custom-badge">Custom</span>
                                    @endif

                                    <span class="trade-count">{{ $trade->visible_task_count }} {{ \Illuminate\Support\Str::plural('task', $trade->visible_task_count) }}</span>

                                    @if ($canAddTask)
                                        <button type="button" class="btn blue btn-xs btn-outline trade-add-task" wire:click="openAddTask({{ $trade->id }})">
                                            <i class="fa fa-plus"></i> Add task
                                        </button>
                                    @endif
                                </td>

                                <td></td>

                                @if ($showActions)
                                    <td class="trade-actions">
                                        @if ($canEditTrade)
                                            <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="openEditTrade({{ $trade->id }})">
                                                <i class="fa fa-pencil"></i> Edit
                                            </button>
                                        @endif

                                        @if ($canDeleteTrade && !$usedTradeIds->has((int)$trade->id))
                                            <button type="button" class="btn btn-xs dark margin-bottom" wire:click="confirmDeleteTrade({{ $trade->id }})" title="Delete unused Trade">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @elseif ($canToggleTrade)
                                            <button type="button" class="btn {{ $trade->status ? 'green' : 'red' }} btn-xs btn-outline sbold uppercase margin-bottom" wire:click="toggleTradeStatus({{ $trade->id }})" title="{{ $trade->status ? 'Disable Trade' : 'Enable Trade' }}">
                                                <i class="fa fa-{{ $trade->status ? 'eye' : 'eye-slash' }}"></i>
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                            @if ($isOpen)
                                @forelse ($tasks as $task)
                                    <tr wire:key="task-{{ $task->id }}" class="trade-task-child-row {{ $task->upcoming ? 'task-upcoming-row' : '' }}">
                                        <td></td>

                                        <td class="trade-task-child-name">
                                            <span class="trade-task-code {{ $task->upcoming ? 'task-upcoming' : '' }} {{ !$task->status ? 'trade-disabled' : '' }}">{{ $task->code }}</span>
                                            <span class="{{ !$task->status ? 'trade-disabled' : '' }}">{{ $task->name }}</span>
                                        </td>

                                        <td class="text-center" style="vertical-align:middle">
                                            @if ($canEditTask)
                                                <button type="button" class="task-upcoming-toggle" wire:click="toggleTaskUpcoming({{ $task->id }})" title="{{ $task->upcoming ? 'Unset Upcoming' : 'Set Upcoming' }}">
                                                    <i class="fa fa-lg fa-{{ $task->upcoming ? 'check-square-o' : 'square-o' }}"></i>
                                                </button>
                                            @endif
                                        </td>

                                        @if ($showActions)
                                            <td class="task-actions">
                                                @if ($canEditTask)
                                                    <button type="button" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom" wire:click="openEditTask({{ $task->id }})">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                @endif

                                                @if ($canDeleteTask && !$usedTaskIds->has((int)$task->id))
                                                    <button type="button" class="btn btn-xs dark margin-bottom" wire:click="confirmDeleteTask({{ $task->id }})" title="Delete unused Task">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                @elseif ($canToggleTask)
                                                    <button type="button" class="btn {{ $task->status ? 'green' : 'red' }} btn-xs btn-outline sbold uppercase margin-bottom" wire:click="toggleTaskStatus({{ $task->id }})" title="{{ $task->status ? 'Disable Task' : 'Enable Task' }}">
                                                        <i class="fa fa-{{ $task->status ? 'eye' : 'eye-slash' }}"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr wire:key="trade-no-tasks-{{ $trade->id }}" class="trade-task-child-row">
                                        <td></td>
                                        <td class="trade-task-child-name font-grey-silver">No Tasks.</td>
                                        <td></td>
                                        @if ($showActions)
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforelse
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="font-grey-silver">No Trades.</div>
            @endif
        </div>
    </div>

    <x-ui.modal :show="$showTradeModal" :title="$editingTradeId ? 'Edit Trade' : 'Add Trade'" close-action="closeModals" max-width="520px">
        <div class="form-group {{ $errors->has('tradeName') ? 'has-error' : '' }}">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" wire:model="tradeName" placeholder="Trade name">
            @error('tradeName')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveTrade" wire:loading.attr="disabled" wire:target="saveTrade">Save</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showTaskModal" :title="$editingTaskId ? 'Edit Task' : 'Add Task'" close-action="closeModals" max-width="520px">
        <div class="form-group {{ $errors->has('taskName') ? 'has-error' : '' }}">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" wire:model="taskName" placeholder="Task name">
            @error('taskName')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <div class="form-group {{ $errors->has('taskCode') ? 'has-error' : '' }}">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" wire:model="taskCode" placeholder="Task code">
            @error('taskCode')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeModals">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveTask" wire:loading.attr="disabled" wire:target="saveTask">Save</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showDeleteModal" :title="$deletingType === 'trade' ? 'Delete Trade' : 'Delete Task'" close-action="closeModals" confirm-action="deleteConfirmed" confirm-label="Yes, delete it">
        <div>
            @if ($deletingType === 'trade')
                This Trade has never been used on the Planner. Deleting it will also permanently delete its unused Tasks.
            @else
                This Task has never been used on the Planner and can be permanently deleted.
            @endif

            <div class="sws-confirm-item">{{ $deletingName }}</div>
        </div>
    </x-ui.confirm-modal>
</div>
