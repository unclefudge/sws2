<div class="page-content-inner" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @include('livewire.planner.partials.sticky-controls')
<div class="site-planner-v2" x-data="{
        draggingTaskId: null,
        draggingEntityKey: '',
        draggingFromDate: '',
        draggingBlockedDates: [],
        startPlannerDrag(event, taskId, entityKey, fromDate, blockedDates) {
            this.draggingTaskId = Number(taskId);
            this.draggingEntityKey = entityKey;
            this.draggingFromDate = fromDate;
            this.draggingBlockedDates = blockedDates;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(taskId));
        },
        canDropPlannerDate(date) {
            return this.draggingTaskId !== null && !this.draggingBlockedDates.includes(date);
        },
        clearPlannerDrag() {
            this.draggingTaskId = null;
            this.draggingEntityKey = '';
            this.draggingFromDate = '';
            this.draggingBlockedDates = [];
            document.querySelectorAll('.site-planner-v2 .site-drop-active').forEach(element => element.classList.remove('site-drop-active'));
        }
    }">
        @if ($preview)
            <div class="note note-info sws-livewire-preview">
                <span><strong>{{ $plannerTitle }} preview:</strong> this is the new Livewire version. The normal {{ $plannerTitle }} is unchanged.</span>
                <a href="{{ $this->plannerUrl($plannerMode === 'preconstruction' ? '/planner/preconstruction' : '/planner/site') }}" class="btn btn-sm default">View normal version</a>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">{{ $plannerTitle }}</span>
                            @if ($preview)<span class="label label-info sws-preview-label">Preview</span>@endif
                        </div>

                        <div class="actions">
                            @if ($canViewTradePlanner)<a href="{{ $this->plannerUrl('/planner/transient') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Labourer">L</a>@endif
                            @if ($plannerMode === 'preconstruction')<button type="button" class="btn btn-circle btn-icon-only grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Pre-construction">P</button>@elseif ($canViewPreconstructionPlanner)<a href="{{ $this->plannerUrl('/planner/preconstruction') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Pre-construction">P</a>@endif
                            @if ($canViewRoster)<a href="{{ $this->plannerUrl('/planner/roster') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Roster">R</a>@endif
                            @if ($plannerMode === 'site')<button type="button" class="btn btn-circle btn-icon-only grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Site">S</button>@elseif ($canViewSitePlanner)<a href="{{ $this->plannerUrl('/planner/site') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Site">S</a>@endif
                            @if ($canViewTradePlanner)<a href="{{ $this->plannerUrl('/planner/trade') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Trade">T</a>@endif
                            @if ($canViewWeeklyPlanner)<a href="{{ $this->plannerUrl('/planner/weekly') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Weekly">W</a>@endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <form method="GET" action="{{ $siteUrl }}" class="planner-sticky-controls">
                            <input type="hidden" name="date" value="{{ $date }}">
                            @if ($supervisorId)<input type="hidden" name="supervisor_id" value="{{ $supervisorId }}">@endif
                            <div class="row padding-bottom-10">
                                <div class="col-md-4">
                                    <div wire:ignore x-data="{}" x-init="$nextTick(() => { const select = $($refs.select); if ($.fn.selectpicker && !select.parent().hasClass('bootstrap-select')) select.selectpicker(); })">
                                        <select name="site_id" class="form-control bs-select" data-live-search="true" data-width="100%" x-ref="select" onchange="this.form.submit()">
                                            <option value="">Select site</option>
                                            @if (!empty($siteOptions['preconstruction']))
                                                <optgroup label="Pre-construction sites">@foreach (($siteOptions['preconstruction'] ?? []) as $option)<option value="{{ $option['id'] }}" @selected($siteId === $option['id'])>{{ $option['name'] }}</option>@endforeach</optgroup>
                                            @endif
                                            @if (!empty($siteOptions['active']))
                                                <optgroup label="Active sites">@foreach (($siteOptions['active'] ?? []) as $option)<option value="{{ $option['id'] }}" @selected($siteId === $option['id'])>{{ $option['name'] }}</option>@endforeach</optgroup>
                                            @endif
                                            @if (!empty($siteOptions['maintenance']))
                                                <optgroup label="Maintenance">@foreach (($siteOptions['maintenance'] ?? []) as $option)<option value="{{ $option['id'] }}" @selected($siteId === $option['id'])>{{ $option['name'] }}</option>@endforeach</optgroup>
                                            @endif
                                            @if (!empty($siteOptions['other']))
                                                <optgroup label="Other">@foreach (($siteOptions['other'] ?? []) as $option)<option value="{{ $option['id'] }}" @selected($siteId === $option['id'])>{{ $option['name'] }}</option>@endforeach</optgroup>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div wire:ignore x-data="{}" x-init="$nextTick(() => { const select = $($refs.select); if ($.fn.selectpicker && !select.parent().hasClass('bootstrap-select')) select.selectpicker(); })">
                                        <select name="site_start" class="form-control bs-select" data-width="100%" x-ref="select" onchange="this.form.submit()">
                                            <option value="week" @selected($siteStart === 'week')>This Week</option>
                                            <option value="first" @selected($siteStart === 'first')>First Task</option>
                                            <option value="start" @selected($siteStart === 'start')>Start of Job</option>
                                        </select>
                                    </div>
                                </div>
                                @if ($siteId)
                                    <div class="col-md-3 pull-right">
                                        @if ($siteStatus === 2)<h3 class="pull-right font-red uppercase planner-maintenance-heading">Maintenance</h3>@endif
                                        @if (!empty($plannerVars['start_date']))<h5><b>Start Job:</b> {{ $this->formatDate($plannerVars['start_date']) }}</h5>@endif
                                        @if ($isCc && !empty($plannerVars['completion_date']))<h5><b>Completion:</b> {{ $this->formatDate($plannerVars['completion_date']) }}</h5>@endif
                                        @if ($canMoveToPreconstruction)<button type="button" class="btn blue" wire:click="confirmMoveToPreconstruction">Move Site to Pre-construction</button>@endif
                                        @if ($canManagePreconstruction)
                                            <button type="button" class="btn blue" wire:click="activatePreconstructionSite">Make Site Active</button>
                                            <button type="button" class="btn red" wire:click="confirmCancelPreconstructionSite">Cancel Site</button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </form>

                        @if ($isCc)
                            <div class="site-key">
                                <div><span class="keybox state-green"></span><span class="planner-key-label">Exceeded Max #Jobs</span></div><br>
                                <div><span class="keybox state-blue"></span><span class="planner-key-label">All On-Site</span></div><br>
                                <div><span class="keybox state-red"></span><span class="planner-key-label">Not All On-Site</span></div><br>
                                <div><span class="keybox state-purple"></span><span class="planner-key-label">Not Rostered</span></div>
                                <span class="keybox state-orange"></span><span class="planner-key-label">Generic Trade</span><br>
                            </div>
                        @endif

                        @if ($siteId)
                            @if ($undoToken && $plannerMessage)
                                <div class="site-toast site-toast-success" wire:key="site-toast-{{ $noticeVersion }}" x-data="{ visible: true }" x-show="visible" x-init="setTimeout(() => visible = false, 12000)" x-transition:leave.opacity.duration.300ms>
                                    <i class="fa fa-check-circle"></i>
                                    {{ $plannerMessage }}
                                    <button type="button" class="site-toast-undo" wire:click="undoLastPlannerMove" wire:loading.attr="disabled" wire:target="undoLastPlannerMove">Undo</button>
                                    <button type="button" class="site-toast-close" x-on:click="visible = false" aria-label="Dismiss message">&times;</button>
                                </div>
                            @elseif (!$showEditor && ($plannerMessage || $plannerError))
                                <span class="sr-only" role="status" wire:key="site-planner-message-{{ $noticeVersion }}" x-data x-init="toastr.{{ $plannerError ? 'error' : 'success' }}(@js($plannerError ?: $plannerMessage))">{{ $plannerError ?: $plannerMessage }}</span>
                            @endif
                            <div class="site-plan-wrap">
                                @forelse ($weeks as $week)
                                    <section class="site-week" wire:key="site-week-{{ $week['key'] }}">
                                        <div class="site-week-header">
                                            <div class="site-week-number">Week {{ $week['number'] }}</div>
                                            @foreach ($week['days'] as $day)
                                                @if ($day['editable'])
                                                    <button type="button" class="site-day-head" wire:click="openDayEditor('{{ $day['date'] }}')"
                                                        x-bind:class="{ 'site-day-drop-ready': canDropPlannerDate('{{ $day['date'] }}') && {{ $day['droppable'] ? 'true' : 'false' }}, 'site-drop-disabled': draggingTaskId && (!canDropPlannerDate('{{ $day['date'] }}') || !{{ $day['droppable'] ? 'true' : 'false' }}) }"
                                                        x-on:dragover="if (canDropPlannerDate('{{ $day['date'] }}') && {{ $day['droppable'] ? 'true' : 'false' }}) { $event.preventDefault(); $event.dataTransfer.dropEffect = 'move'; $el.classList.add('site-drop-active'); } else if (draggingTaskId) { $event.dataTransfer.dropEffect = 'none'; }"
                                                        x-on:dragleave="$el.classList.remove('site-drop-active')"
                                                        x-on:drop.stop="if (canDropPlannerDate('{{ $day['date'] }}') && {{ $day['droppable'] ? 'true' : 'false' }}) { $event.preventDefault(); const taskId = draggingTaskId; const fromDate = draggingFromDate; clearPlannerDrag(); $wire.dropPlannerTask(taskId, fromDate, '{{ $day['date'] }}'); }">
                                                        <span>{{ $day['day'] }} {{ $day['label'] }} @if ($day['holiday'])<br><small class="font-red">{{ $day['holiday'] }}</small>@endif</span>
                                                    </button>
                                                @else
                                                    <div class="site-day-head"><span>{{ $day['day'] }} {{ $day['label'] }} @if ($day['holiday'])<br><small class="font-red">{{ $day['holiday'] }}</small>@endif</span></div>
                                                @endif
                                            @endforeach
                                        </div>

                                        @forelse ($week['rows'] as $row)
                                            <div class="site-row" wire:key="site-week-{{ $week['key'] }}-{{ $row['key'] }}">
                                                <div class="site-entity {{ $row['type'] === 't' ? 'site-generic' : '' }}"><strong>{{ $row['name'] }}</strong></div>
                                                @foreach ($row['days'] as $cell)
                                                    <div class="site-cell {{ $cell['editable'] ? 'site-editable' : 'site-past' }} {{ $cell['is_today'] ? 'site-today' : '' }} {{ $cell['leave'] ? 'site-leave' : '' }}"
                                                        @if ($cell['editable'])
                                                            role="button" tabindex="0"
                                                            x-bind:class="{ 'site-drop-ready': canDropPlannerDate('{{ $cell['date'] }}') && draggingEntityKey === '{{ $row['key'] }}' && {{ $cell['droppable'] ? 'true' : 'false' }}, 'site-drop-disabled': draggingTaskId && (!canDropPlannerDate('{{ $cell['date'] }}') || draggingEntityKey !== '{{ $row['key'] }}' || !{{ $cell['droppable'] ? 'true' : 'false' }}) }"
                                                            x-on:click="$wire.openEditor('{{ $row['type'] }}', {{ $row['id'] }}, '{{ $cell['date'] }}')"
                                                            x-on:keydown.enter.prevent="$wire.openEditor('{{ $row['type'] }}', {{ $row['id'] }}, '{{ $cell['date'] }}')"
                                                            x-on:dragover="if (canDropPlannerDate('{{ $cell['date'] }}') && draggingEntityKey === '{{ $row['key'] }}' && {{ $cell['droppable'] ? 'true' : 'false' }}) { $event.preventDefault(); $event.dataTransfer.dropEffect = 'move'; $el.classList.add('site-drop-active'); } else if (draggingTaskId) { $event.dataTransfer.dropEffect = 'none'; }"
                                                            x-on:dragleave="$el.classList.remove('site-drop-active')"
                                                            x-on:drop.stop="if (canDropPlannerDate('{{ $cell['date'] }}') && draggingEntityKey === '{{ $row['key'] }}' && {{ $cell['droppable'] ? 'true' : 'false' }}) { $event.preventDefault(); const taskId = draggingTaskId; const fromDate = draggingFromDate; clearPlannerDrag(); $wire.dropPlannerTask(taskId, fromDate, '{{ $cell['date'] }}'); }"
                                                        @endif>
                                                        @foreach ($cell['tasks'] as $task)
                                                            <div class="site-task-chip {{ $cell['class'] }} {{ $cell['holiday'] ? 'site-holiday-task' : '' }}" draggable="{{ $task['draggable'] ? 'true' : 'false' }}"
                                                                @if ($task['draggable']) x-on:dragstart.stop="startPlannerDrag($event, {{ $task['id'] }}, '{{ $row['key'] }}', '{{ $cell['date'] }}', @js($task['blocked_move_dates']))" x-on:dragend.stop="clearPlannerDrag()" @endif>
                                                                <strong class="{{ in_array($task['task_code'], ['START', 'STARTCarp'], true) ? 'label label-info' : '' }}">{{ $task['task_name'] }}</strong>
                                                                @if ($task['maintenance'])<br><span class="label label-info"><small>Maintenance Request</small></span>@endif
                                                            </div>
                                                        @endforeach
                                                        @if ($cell['conflict'] && $cell['tasks'])<small class="font-green-jungle">{{ $cell['conflict'] }}</small>@endif
                                                        @if ($cell['leave'])<br><span class="label label-warning">{{ $cell['leave'] }}</span>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @empty
                                            <div class="site-empty-week">No tasks scheduled this week.</div>
                                        @endforelse
                                    </section>
                                @empty
                                    <div class="planner-empty">No planner weeks are available for this site.</div>
                                @endforelse
                            </div>
                        @else
                            <div class="planner-empty">Select a site to view its planner.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="site-drag-saving" wire:loading.delay wire:target="dropPlannerTask,preparePlannerTaskMove,confirmPlannerTaskMove,undoLastPlannerMove"><i class="fa fa-spinner fa-pulse"></i> Updating planner task…</div>

        @if ($showEditor)
            <div class="site-editor-backdrop" wire:click="closeEditor"></div>
            <div class="site-editor-wrap">
                <section class="site-editor" aria-label="Edit site planner" x-data="{ action: '' }">
                    <div class="site-editor-title">
                        <div>
                            <small class="bold uppercase">Edit site planner</small>
                            <h3 class="planner-editor-heading">{{ $editorEntityName }}</h3>
                            <div>{{ $siteName }} · {{ $this->formatDate($editorDate, 'D d/m/Y') }}</div>
                        </div>
                        <button type="button" class="site-editor-close" wire:click="closeEditor" aria-label="Close"><i class="fa fa-times"></i></button>
                    </div>

                    <div class="site-saving" wire:loading.delay wire:target="addPlannerTask,changePlannerTaskDays,setPlannerTaskDays,preparePlannerTaskMove,confirmPlannerTaskMove,undoLastPlannerMove,movePlannerEntity,movePlannerSite,deleteConfirmedPlannerAction,clearConfirmedPlannerSite,reassignPlannerTasks"><i class="fa fa-spinner fa-pulse"></i> Saving planner change…</div>

                    <div class="site-editor-body">
                        @if ($plannerMessage && !$undoToken)<div class="alert alert-success">{{ $plannerMessage }}</div>@endif
                        @if ($plannerError)<div class="alert alert-danger">{{ $plannerError }}</div>@endif
                        @if (!$this->editorCanEdit())<div class="note note-warning">Past dates and public holidays are view-only on the Site Planner.</div>@endif

                        <div class="site-current-heading">
                            <div>
                                <h4 class="bold">Current tasks</h4>
                                <div class="site-action-help planner-help-reset">Adjust a task here, or close this window and drag the task from any remaining day to another date.</div>
                            </div>
                            @if ($this->editorCanEdit())
                                <div class="site-current-actions">
                                    <button type="button" class="btn btn-sm" x-bind:class="action === 'add' ? 'default' : 'green'" x-on:click="action = action === 'add' ? '' : 'add'">
                                        <i class="fa" x-bind:class="action === 'add' ? 'fa-chevron-up' : 'fa-plus'"></i>
                                        <span x-show="action !== 'add'">Add task</span><span x-show="action === 'add'" x-cloak>Hide add task</span>
                                    </button>
                                    @if (count($connectedTasks) > 1 && $editorEntityType)
                                        <button type="button" class="btn btn-sm" x-bind:class="action === 'connected' ? 'default' : 'blue-hoki'" x-on:click="action = action === 'connected' ? '' : 'connected'">
                                            <i class="fa fa-link"></i> <span x-show="action !== 'connected'">Connected tasks</span><span x-show="action === 'connected'" x-cloak>Hide connected</span>
                                        </button>
                                    @endif
                                    @if (!$editorEntityType)
                                        <button type="button" class="btn btn-sm" x-bind:class="action === 'site' ? 'default' : 'blue-hoki'" x-on:click="action = action === 'site' ? '' : 'site'">
                                            <i class="fa fa-arrows-h"></i> <span x-show="action !== 'site'">Site actions</span><span x-show="action === 'site'" x-cloak>Hide site actions</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($this->editorCanEdit())
                            <div class="site-action-panel" x-show="action === 'add'" x-cloak>
                                <h4 class="bold planner-section-heading">Add a task</h4>
                                @if ($editorEntityType)
                                    <p class="site-action-help">Adding another task to <strong>{{ $editorEntityName }}</strong> for {{ $this->formatDate($editorDate, 'D d/m') }}.</p>
                                @else
                                    <p class="site-action-help">Choose the trade, company and task for {{ $this->formatDate($editorDate, 'D d/m') }}.</p>
                                    <div wire:ignore wire:key="site-add-trade-{{ $newTradeId ?: 'none' }}">
                                        <x-form.select name="siteAddTrade" label="Trade" placeholder="Select trade" :value="$newTradeId" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('newTradeId', $el.value)">
                                            @foreach ($tradeOptions as $trade)<option value="{{ $trade['id'] }}" @selected((string)$newTradeId === (string)$trade['id'])>{{ $trade['name'] }}</option>@endforeach
                                        </x-form.select>
                                    </div>
                                    @if ($newTradeId)
                                        <div wire:ignore wire:key="site-add-target-{{ $newTradeId }}-{{ $newTarget ?: 'none' }}">
                                            <x-form.select name="siteAddTarget" label="Company" placeholder="Select company" :value="$newTarget" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('newTarget', $el.value)">
                                                @foreach ($addTargets as $target)<option value="{{ $target['value'] }}" @selected((string)$newTarget === (string)$target['value'])>{{ $target['name'] }}</option>@endforeach
                                            </x-form.select>
                                        </div>
                                    @endif
                                @endif
                                @if ($editorEntityType || $newTarget)
                                    <div wire:ignore wire:key="site-add-task-{{ $editorEntityType ?: $newTarget }}-{{ $editorEntityId ?: 'all' }}-{{ $newTaskId ?: 'none' }}">
                                        <x-form.select name="siteAddTask" label="Task" placeholder="Select task" :value="$newTaskId" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('newTaskId', $el.value)">
                                            @foreach ($addTaskOptions as $task)<option value="{{ $task['id'] }}" @selected((string)$newTaskId === (string)$task['id'])>{{ $task['name'] }}</option>@endforeach
                                        </x-form.select>
                                    </div>
                                @endif
                                <button type="button" class="btn green" wire:click="addPlannerTask" @disabled(!$newTaskId || (!$editorEntityType && (!$newTradeId || !$newTarget)))><i class="fa fa-plus"></i> Add task</button>
                            </div>

                            @if (count($connectedTasks) > 1 && $editorEntityType)
                                <div class="site-action-panel" x-show="action === 'connected'" x-cloak>
                                    <h4 class="bold planner-section-heading">Move connected tasks</h4>
                                    <p class="site-action-help">Move or remove this uninterrupted run of {{ $editorEntityName }} tasks together.</p>
                                    <div class="site-connected">
                                        <strong>Connected: {{ collect($connectedTasks)->pluck('task_name')->join(', ') }}</strong>
                                        <div class="site-connected-actions">
                                            <div wire:ignore wire:key="site-connected-days-{{ $connectedMoveDays }}">
                                                <x-form.select name="siteConnectedDays" label="Move by" :value="$connectedMoveDays" data-width="120px" data-container="body"
                                                    x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('connectedMoveDays', Number($el.value))">
                                                    @for ($move = 1; $move <= 10; $move++)<option value="{{ $move }}" @selected($connectedMoveDays === $move)>{{ $move }} day{{ $move === 1 ? '' : 's' }}</option>@endfor
                                                </x-form.select>
                                            </div>
                                            <button type="button" class="btn default" wire:click="movePlannerEntity({{ $siteId }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}', -{{ $connectedMoveDays }})" aria-label="Move connected tasks earlier"><i class="fa fa-arrow-left"></i></button>
                                            <button type="button" class="btn default" wire:click="movePlannerEntity({{ $siteId }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}', {{ $connectedMoveDays }})" aria-label="Move connected tasks later"><i class="fa fa-arrow-right"></i></button>
                                            <button type="button" class="btn red sws-ml-auto" wire:click="confirmPlannerEntityDeletion({{ $siteId }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}')" aria-label="Remove connected tasks"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (!$editorEntityType)
                                <div class="site-action-panel" x-show="action === 'site'" x-cloak>
                                    <h4 class="bold planner-section-heading">Move or clear the site</h4>
                                    <p class="site-action-help">These actions affect every task from {{ $this->formatDate($editorDate) }} onwards.</p>
                                    <div class="site-connected-actions site-whole-actions">
                                        <div class="site-whole-move-controls">
                                            <div class="site-whole-move-select" wire:ignore wire:key="site-move-days-{{ $siteMoveDays }}">
                                                <x-form.select name="siteMoveDays" label="Move site by" :value="$siteMoveDays" data-width="130px" data-container="body" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('siteMoveDays', Number($el.value))">
                                                    @for ($move = 1; $move <= 10; $move++)<option value="{{ $move }}" @selected($siteMoveDays === $move)>{{ $move }} day{{ $move === 1 ? '' : 's' }}</option>@endfor
                                                </x-form.select>
                                            </div>
                                            <div class="site-whole-move-arrows">
                                                <button type="button" class="btn default" wire:click="movePlannerSite({{ $siteId }}, '{{ $editorDate }}', -{{ $siteMoveDays }})" aria-label="Move whole site earlier"><i class="fa fa-arrow-left"></i></button>
                                                <button type="button" class="btn default" wire:click="movePlannerSite({{ $siteId }}, '{{ $editorDate }}', {{ $siteMoveDays }})" aria-label="Move whole site later"><i class="fa fa-arrow-right"></i></button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn red site-clear-site-button sws-ml-auto" wire:click="confirmClearPlannerSite"><i class="fa fa-trash"></i> Clear site</button>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div>
                            @forelse ($editorTasks as $task)
                                <div class="site-task-card" wire:key="site-editor-task-{{ $task['id'] }}">
                                    <div class="site-task-layout">
                                        <div>
                                            <strong class="site-task-name font-blue">{{ $task['task_name'] }}</strong>
                                            <div class="site-task-entity {{ $task['entity_type'] === 't' ? 'font-yellow-gold' : '' }}">{{ $task['entity_name'] }}</div>
                                            <div class="site-task-start">Start: <span class="{{ $task['from'] !== $editorDate ? 'font-red' : '' }}">{{ $this->formatDate($task['from']) }}</span></div>
                                        </div>
                                        @if ($this->editorCanEdit())
                                            @if ((string)$task['task_code'] === 'START')
                                                <div class="site-task-tools site-job-start-tools">
                                                    <div class="site-job-start-note"><strong>Protected Job Start schedule</strong><br><small>Use the dedicated action so its linked preset tasks stay aligned.</small></div>
                                                    @if ($canMoveJobStart)<button type="button" class="btn grey-mint" wire:click="openMoveJobStart"><i class="fa fa-exchange"></i> Move Job Start</button>@endif
                                                </div>
                                            @else
                                            <div class="site-task-tools">
                                                <div>
                                                    <label class="control-label">Days</label>
                                                    <div class="site-stepper">
                                                        <button type="button" class="btn default" wire:click="changePlannerTaskDays({{ $task['id'] }}, -1)" @disabled((int)$task['days'] <= 1) aria-label="Remove one day"><i class="fa fa-minus"></i></button>
                                                        <input type="number" class="form-control" min="1" max="365" step="1" value="{{ $task['days'] }}" wire:change="setPlannerTaskDays({{ $task['id'] }}, $event.target.value)" aria-label="Task duration in days">
                                                        <button type="button" class="btn default" wire:click="changePlannerTaskDays({{ $task['id'] }}, 1)" @disabled((int)$task['days'] >= 365) aria-label="Add one day"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                                <div wire:ignore x-data="{}" x-init="
                                                    $nextTick(() => {
                                                        const picker = $($refs.picker);
                                                        if (!$.fn.datepicker) return;
                                                        if (picker.data('datepicker')) picker.datepicker('destroy');
                                                        picker.datepicker({ rtl: typeof App !== 'undefined' ? App.isRTL() : false, orientation:'auto', autoclose:true, container:'body', format:'dd/mm/yyyy', startDate:'today', daysOfWeekDisabled:[0,6], datesDisabled:@js($task['picker_disabled_dates']) });
                                                        picker.off('changeDate.siteMove{{ $task['id'] }}').on('changeDate.siteMove{{ $task['id'] }}', event => {
                                                            if (!event.date) return;
                                                            const pad = value => String(value).padStart(2, '0');
                                                            const selected = event.date.getFullYear() + '-' + pad(event.date.getMonth() + 1) + '-' + pad(event.date.getDate());
                                                            $wire.preparePlannerTaskMove({{ $task['id'] }}, '{{ $editorDate }}', selected);
                                                        });
                                                    });
                                                ">
                                                    <label for="siteMoveDate{{ $task['id'] }}" class="control-label">Move to date</label>
                                                    <div class="input-group date site-move-date-picker" x-ref="picker">
                                                        <input type="text" id="siteMoveDate{{ $task['id'] }}" class="form-control" placeholder="Select date" readonly>
                                                        <span class="input-group-btn"><button type="button" class="btn default date-set" aria-label="Choose move date"><i class="fa fa-calendar"></i></button></span>
                                                    </div>
                                                </div>
                                                <span class="site-task-buttons">
                                                    @if ($reassignTaskId === (int)$task['id'])
                                                        <button type="button" class="btn btn-sm grey-mint" wire:click="cancelReassign"><i class="fa fa-chevron-up"></i> Hide</button>
                                                    @else
                                                        <button type="button" class="btn btn-sm grey-mint" wire:click="startReassign({{ $task['id'] }})"><i class="fa fa-exchange"></i> Reassign</button>
                                                    @endif
                                                    @if (!in_array((int)$task['task_id'], [11,264], true))<button type="button" class="btn btn-sm red" wire:click="confirmPlannerTaskDeletion({{ $task['id'] }})" aria-label="Delete task"><i class="fa fa-trash"></i></button>@endif
                                                </span>
                                            </div>
                                            @endif
                                        @endif
                                    </div>

                                    @if ($this->editorCanEdit() && $reassignTaskId === (int)$task['id'])
                                        <div class="site-reassign-box">
                                            <strong>Reassign {{ $task['task_name'] }}</strong>
                                            <p class="site-action-help planner-reassign-help">Choose the new company and whether to move this task or all future tasks.</p>
                                            <div wire:ignore wire:key="site-reassign-target-{{ $task['id'] }}">
                                                <x-form.select name="siteReassignTarget{{ $task['id'] }}" label="Company" placeholder="Select company" :value="$reassignTarget" data-width="100%" data-live-search="true" data-container="body" data-size="8"
                                                    x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('reassignTarget', $el.value)">
                                                    @foreach ($reassignTargets as $target)<option value="{{ $target['value'] }}" @selected((string)$reassignTarget === (string)$target['value'])>{{ $target['name'] }}</option>@endforeach
                                                </x-form.select>
                                            </div>
                                            <div wire:ignore wire:key="site-reassign-scope-{{ $task['id'] }}">
                                                <x-form.select name="siteReassignScope{{ $task['id'] }}" label="Which tasks?" placeholder="Select tasks" :value="$reassignScope" data-width="100%" data-container="body"
                                                    x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('reassignScope', $el.value)">
                                                    <option value="task" @selected($reassignScope === 'task')>Only this task</option>
                                                    <option value="all" @selected($reassignScope === 'all')>All future tasks for this trade</option>
                                                </x-form.select>
                                            </div>
                                            <button type="button" class="btn blue" wire:click="reassignPlannerTasks" @disabled(!$reassignTarget || !$reassignScope)>Assign tasks</button>
                                            <button type="button" class="btn default" wire:click="cancelReassign">Cancel</button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="font-grey-silver">No tasks for this selection. Choose <strong>Add task</strong> above to create one.</div>
                            @endforelse
                        </div>

                        <div class="margin-top-20"><button type="button" class="btn default" wire:click="closeEditor">Close</button></div>
                    </div>
                </section>
            </div>
        @endif

        <livewire:planner.job-actions :show-menu="false" wire:key="site-planner-job-actions" />

        @if ($showMoveConfirm && $pendingMove)
            <div class="site-move-confirm-backdrop" wire:click="cancelPlannerTaskMove"></div>
            <div class="site-move-confirm-wrap">
                <section class="site-move-confirm" role="dialog" aria-modal="true" aria-labelledby="siteMoveConfirmTitle" wire:key="site-move-confirm-{{ $pendingMove['task_id'] }}-{{ $pendingMove['source'] }}-{{ $pendingMove['target'] }}">
                    <div class="site-move-confirm-title"><h4 id="siteMoveConfirmTitle">Confirm task move</h4></div>
                    <div class="site-move-confirm-body">
                        <h4 class="bold planner-section-heading">{{ $pendingMove['task_name'] }}</h4>
                        @if ($pendingMove['entity_name'])<div>{{ $pendingMove['entity_name'] }}</div>@endif

                        @if ($pendingMove['split'])
                            <div class="site-move-warning">
                                <strong>This will split the task.</strong><br>
                                {{ $pendingMove['kept_days'] }} earlier day{{ $pendingMove['kept_days'] === 1 ? '' : 's' }} will stay in place and {{ $pendingMove['moved_days'] }} remaining day{{ $pendingMove['moved_days'] === 1 ? '' : 's' }} will move.
                            </div>
                        @else
                            <p class="planner-confirm-note">This will move the complete {{ $pendingMove['total_days'] }}-day task.</p>
                        @endif

                        <div class="site-move-summary">
                            <div><small>Move from</small><br><strong>{{ $this->formatDate($pendingMove['source'], 'D d/m/Y') }}</strong></div>
                            <div><small>Move to</small><br><strong>{{ $this->formatDate($pendingMove['target'], 'D d/m/Y') }}</strong>@if ($pendingMove['moved_days'] > 1)<br><small>Finishes {{ $this->formatDate($pendingMove['target_end'], 'D d/m/Y') }}</small>@endif</div>
                        </div>

                        <div class="site-move-confirm-actions">
                            <button type="button" class="btn default" wire:click="cancelPlannerTaskMove">Cancel</button>
                            <button type="button" class="btn blue" wire:click="confirmPlannerTaskMove" wire:loading.attr="disabled" wire:target="confirmPlannerTaskMove">
                                <i class="fa fa-arrows"></i> {{ $pendingMove['split'] ? 'Split and move' : 'Move' }} {{ $pendingMove['moved_days'] }} day{{ $pendingMove['moved_days'] === 1 ? '' : 's' }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        @endif

        <x-ui.confirm-modal :show="$showPlannerDeleteModal" :title="$plannerDeleteTitle" :confirm-label="$plannerDeleteConfirmLabel" confirm-action="deleteConfirmedPlannerAction" close-action="closePlannerDeleteModal">
            <div>{{ $plannerDeleteMessage }}</div>
            @if ($plannerDeleteItem)<div class="sws-confirm-item">{{ $plannerDeleteItem }}</div>@endif
        </x-ui.confirm-modal>

        <x-ui.confirm-modal :show="$showClearSiteModal" title="Clear site planner?" confirm-label="Yes, clear site" confirm-action="clearConfirmedPlannerSite" close-action="closeClearSiteModal">
            <div>This will permanently remove every site task from {{ $this->formatDate($editorDate, 'D d/m/Y') }} onwards. This cannot be undone.</div>
            <div class="sws-confirm-item">{{ $siteName }}</div>
        </x-ui.confirm-modal>

        <x-ui.confirm-modal :show="$showPreconstructionModal" title="Move site to Pre-construction?" confirm-label="Yes, move site" confirm-action="moveToPreconstruction" close-action="closePreconstructionModal">
            <div>This will change the site back to Pre-construction.</div>
            <div class="sws-confirm-item">{{ $siteName }}</div>
            <div class="note note-warning planner-confirm-warning"><strong>Planner tasks dated today or later will be permanently deleted.</strong><br>The site's Project Supply record will also be deleted. Historical planner tasks will remain.</div>
        </x-ui.confirm-modal>

        <x-ui.confirm-modal :show="$showCancelPreconstructionSiteModal" title="Cancel this site?" confirm-label="Yes, cancel site" confirm-action="cancelPreconstructionSite" close-action="closeCancelPreconstructionSiteModal">
            <div>This will mark the site as cancelled.</div>
            <div class="sws-confirm-item">{{ $siteName }}</div>
            <div class="note note-warning planner-confirm-warning"><strong>All planner tasks for this site will be permanently deleted.</strong><br>The site's Project Supply record will also be deleted. This cannot be undone.</div>
        </x-ui.confirm-modal>
    </div>
</div>
