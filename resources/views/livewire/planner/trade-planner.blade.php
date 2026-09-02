<div class="page-content-inner" x-data="{ search: '' }" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @include('livewire.planner.partials.sticky-controls')
<div class="trade-planner-v2">
        @if ($preview)
            <div class="note note-info sws-livewire-preview">
                <span><strong>{{ $plannerTitle }} preview:</strong> this is the new Livewire version. The normal {{ $plannerTitle }} is unchanged.</span>
                <a href="{{ $this->plannerUrl($plannerMode === 'labourer' ? '/planner/transient' : '/planner/trade') }}" class="btn btn-sm default">View normal version</a>
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
                            @if ($plannerMode === 'labourer')<button type="button" class="btn btn-circle btn-icon-only grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Labourer">L</button>@else<a href="{{ $this->plannerUrl('/planner/transient') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Labourer">L</a>@endif
                            @if ($canViewPreconstructionPlanner)<a href="{{ $this->plannerUrl('/planner/preconstruction') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Pre-construction">P</a>@endif
                            @if ($canViewRoster)<a href="{{ $this->plannerUrl('/planner/roster') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Roster">R</a>@endif
                            @if ($canViewSitePlanner)<a href="{{ $this->plannerUrl('/planner/site') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Site">S</a>@endif
                            @if ($plannerMode === 'trade')<button type="button" class="btn btn-circle btn-icon-only grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Trade">T</button>@else<a href="{{ $this->plannerUrl('/planner/trade') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Trade">T</a>@endif
                            @if ($canViewWeeklyPlanner)<a href="{{ $this->plannerUrl('/planner/weekly') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Weekly">W</a>@endif
                            @if ($isCc)<div><input type="text" class="form-control" x-model.debounce.200ms="search" placeholder="Search Site Names"></div>@endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div class="row planner-sticky-controls">
                            <div class="col-md-3">
                                @if ($plannerMode === 'labourer')
                                    <input type="text" class="form-control" value="Labourer" disabled readonly>
                                @else
                                    <form method="GET" action="{{ $tradeUrl }}">
                                        <input type="hidden" name="date" value="{{ $date }}">
                                        @if ($supervisorId)<input type="hidden" name="supervisor_id" value="{{ $supervisorId }}">@endif
                                        @if ($siteId)<input type="hidden" name="site_id" value="{{ $siteId }}">@endif
                                        @if ($siteStart)<input type="hidden" name="site_start" value="{{ $siteStart }}">@endif
                                        <select name="trade_id" class="form-control bs-select" onchange="this.form.submit()">
                                            <option value="">Select Trade</option>
                                            @foreach ($tradeOptions as $trade)<option value="{{ $trade['id'] }}" @selected($trade['id'] === $tradeId)>{{ $trade['name'] }}</option>@endforeach
                                        </select>
                                    </form>
                                @endif
                            </div>

                            <div class="col-md-5 text-center">
                                <form method="GET" action="{{ $tradeUrl }}">
                                    @if ($tradeId)<input type="hidden" name="trade_id" value="{{ $tradeId }}">@endif
                                    @if ($supervisorId)<input type="hidden" name="supervisor_id" value="{{ $supervisorId }}">@endif
                                    @if ($siteId)<input type="hidden" name="site_id" value="{{ $siteId }}">@endif
                                    @if ($siteStart)<input type="hidden" name="site_start" value="{{ $siteStart }}">@endif
                                    <select name="date" class="form-control bs-select" onchange="this.form.submit()">
                                        @foreach ($weekOptions as $option)<option value="{{ $option['date'] }}" @selected($option['date'] === $date)>{{ $option['label'] }}</option>@endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="col-md-4 pull-right">
                                <div class="btn-group btn-group-circle pull-right">
                                    <a href="{{ $previousWeekUrl }}" class="btn blue-hoki">Prev Week</a>
                                    <a href="{{ $thisWeekUrl }}" class="btn blue-dark">This Week</a>
                                    <a href="{{ $nextWeekUrl }}" class="btn blue-hoki">Next Week</a>
                                </div>
                                @if ($plannerMode === 'trade')<livewire:planner.job-actions :show-menu="true" wire:key="trade-planner-job-actions" />@endif
                            </div>
                        </div>

                        <div class="trade-key">
                            <div><span class="keybox state-green"></span><span class="planner-key-label">Exceeded Max #Jobs</span></div><br>
                            <div><span class="keybox state-blue"></span><span class="planner-key-label">All On-Site</span></div><br>
                            <div><span class="keybox state-red"></span><span class="planner-key-label">Not All On-Site</span></div><br>
                            <div><span class="keybox state-purple"></span><span class="planner-key-label">Not Rostered</span></div>
                            <span class="keybox state-orange"></span><span class="planner-key-label">Generic Trade</span><br>
                        </div>

                        @if ($upcoming)
                            <h4 class="bold">Upcoming Tasks</h4>
                            <div class="upcoming-grid">
                                @foreach ($upcoming as $category)
                                    <div class="upcoming-col" wire:key="upcoming-category-{{ $category['id'] }}">
                                        <div class="upcoming-heading">{{ $category['name'] }}</div>
                                        <div class="upcoming-list">
                                            @foreach ($category['plans'] as $task)
                                                @if ($canEdit && $task['from'] > now()->format('Y-m-d'))
                                                    <button type="button" class="upcoming-task" wire:click="openUpcomingTask({{ $task['id'] }})">
                                                        <small class="{{ $task['entity_type'] === 't' ? 'font-yellow-gold' : 'font-grey-silver' }}">{{ $this->formatDate($task['from'], 'd/m') }} {{ mb_substr($task['site_name'], 0, 18) }} ({{ $task['days'] }})</small>
                                                    </button>
                                                @else
                                                    <div class="upcoming-task"><small class="{{ $task['entity_type'] === 't' ? 'font-yellow-gold' : 'font-grey-silver' }}">{{ $this->formatDate($task['from'], 'd/m') }} {{ mb_substr($task['site_name'], 0, 18) }} ({{ $task['days'] }})</small></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($tradeId)
                            <h4 class="bold">{{ $tradeName }} Planner</h4>
                            <div class="trade-grid-wrap">
                                <div class="trade-grid">
                                    <div class="trade-row trade-row-header">
                                        <div class="trade-name-col">Company</div>
                                        @foreach ($days as $day)
                                            <div class="trade-day-col trade-day-heading">
                                                {{ $day['day'] }} {{ $day['label'] }}
                                                @if ($day['holiday'])<br><span class="font-red">{{ $day['holiday'] }}</span>@endif
                                            </div>
                                        @endforeach
                                    </div>

                                    @foreach ($rows as $row)
                                        <div class="trade-row row-striped" wire:key="trade-row-{{ $row['key'] }}">
                                            <div class="trade-name-col">
                                                <small class="text-uppercase {{ $row['type'] === 't' ? 'font-yellow-gold' : '' }}">{{ $row['name'] }}</small>
                                                @if (!$row['compliant'])<br><span class="badge badge-danger badge-roundless">NON COMPLIANT</span>@endif
                                                @if ($row['leave_summary'])<br><small class="font-blue">Leave: {{ $row['leave_summary'] }}</small>@endif
                                            </div>

                                            @foreach ($row['days'] as $index => $cell)
                                                <div class="trade-day-col {{ $days[$index]['is_today'] ? 'trade-day-today' : '' }} {{ $cell['leave'] ? 'trade-day-leave' : '' }}">
                                                    @if ($cell['editable'])
                                                        <button type="button" class="trade-day-content {{ $cell['class'] }} {{ $days[$index]['holiday'] ? 'trade-day-holiday' : '' }}" wire:click="openEditor('{{ $row['type'] }}', {{ $row['id'] }}, '{{ $cell['date'] }}')">
                                                            @if ($cell['leave'])<span class="label label-sm label-warning">ON LEAVE</span>@endif
                                                            @forelse ($cell['sites'] as $site)
                                                                <div class="trade-site" data-search="{{ mb_strtolower($site['name']) }}" x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-cloak>
                                                                    <small>{{ mb_substr($site['name'], 0, 18) }} (
                                                                        @foreach ($site['tasks'] as $task)@if (!$loop->first), @endif<span class="{{ $task['highlight'] ? 'label label-info' : '' }}">{{ $task['code'] }}</span>@endforeach
                                                                    )</small>
                                                                    @if ($site['maintenance'])<br><span class="label label-info"><small>Maintenance Request</small></span>@endif
                                                                </div>
                                                            @empty
                                                                <span class="font-grey-silver">&nbsp;</span>
                                                            @endforelse
                                                        </button>
                                                    @else
                                                        <div class="trade-day-content trade-day-past {{ $cell['class'] }} {{ $days[$index]['holiday'] ? 'trade-day-holiday' : '' }}">
                                                            @if ($cell['leave'])<span class="label label-sm label-warning">ON LEAVE</span>@endif
                                                            @foreach ($cell['sites'] as $site)
                                                                <div class="trade-site" data-search="{{ mb_strtolower($site['name']) }}" x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-cloak>
                                                                    <small>{{ mb_substr($site['name'], 0, 18) }} (
                                                                        @foreach ($site['tasks'] as $task)@if (!$loop->first), @endif<span class="{{ $task['highlight'] ? 'label label-info' : '' }}">{{ $task['code'] }}</span>@endforeach
                                                                    )</small>
                                                                    @if ($site['maintenance'])<br><span class="label label-info"><small>Maintenance Request</small></span>@endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="planner-empty">Select a trade to view its planner.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($showEditor)
            @php
                $hasConnectedTasks = false;
                foreach ($editorSites as $editorSite) {
                    if (collect($editorTasks)->where('site_id', $editorSite['id'])->count() > 1) {
                        $hasConnectedTasks = true;
                        break;
                    }
                }
            @endphp
            <div class="trade-editor-backdrop" wire:click="closeEditor"></div>
            <div class="trade-editor-wrap">
                <section class="trade-editor" aria-label="Edit planner" x-data="{ action: '' }">
                    <div class="trade-editor-title">
                        <div>
                            <small class="bold uppercase">Edit planner</small>
                            <h3 class="planner-editor-heading">{{ $editorEntityName }}</h3>
                            <div>Tasks for {{ $this->formatDate($editorDate, 'D d/m/Y') }}</div>
                        </div>
                        <button type="button" class="trade-editor-close" wire:click="closeEditor" aria-label="Close"><i class="fa fa-times"></i></button>
                    </div>

                    <div class="trade-saving" wire:loading.delay wire:target="addPlannerTask,changePlannerTaskDays,setPlannerTaskDays,movePlannerTaskTo,movePlannerEntity,deleteConfirmedPlannerAction,reassignPlannerTasks"><i class="fa fa-spinner fa-pulse"></i> Saving planner change…</div>

                    <div class="trade-editor-body">
                        @if ($plannerMessage)<div class="alert alert-success">{{ $plannerMessage }}</div>@endif
                        @if ($plannerError)<div class="alert alert-danger">{{ $plannerError }}</div>@endif
                        @if (!$this->editorCanEdit())<div class="note note-warning">Past dates are view-only on the {{ $plannerTitle }}.</div>@endif

                        <div class="trade-current-heading">
                            <div>
                                <h4 class="bold">Current tasks</h4>
                                <div class="trade-action-help planner-help-reset">Change the number of days or choose a new workday from the calendar.</div>
                            </div>
                            @if ($this->editorCanEdit())
                                <div class="trade-current-actions">
                                    <button type="button" class="btn btn-sm" x-bind:class="action === 'add' ? 'default' : 'green'" x-on:click="action = action === 'add' ? '' : 'add'">
                                        <i class="fa" x-bind:class="action === 'add' ? 'fa-chevron-up' : 'fa-plus'"></i>
                                        <span x-show="action !== 'add'">Add task</span><span x-show="action === 'add'" x-cloak>Hide add task</span>
                                    </button>
                                    @if ($hasConnectedTasks)
                                        <button type="button" class="btn btn-sm" x-bind:class="action === 'connected' ? 'default' : 'blue-hoki'" x-on:click="action = action === 'connected' ? '' : 'connected'">
                                            <i class="fa fa-link"></i>
                                            <span x-show="action !== 'connected'">Connected tasks</span><span x-show="action === 'connected'" x-cloak>Hide connected</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($this->editorCanEdit())
                            <div class="trade-action-panel" x-show="action === 'add'" x-cloak>
                                <h4 class="bold planner-section-heading">Add a task</h4>
                                @if (count($editorSites) === 1 && $newSiteId)
                                    <p class="trade-action-help">Adding another task for <strong>{{ $editorEntityName }}</strong> at <strong>{{ $editorSites[0]['name'] }}</strong> on {{ $this->formatDate($editorDate, 'D d/m') }}.</p>
                                @else
                                    <p class="trade-action-help">Choose the site first, then choose the task to add on {{ $this->formatDate($editorDate, 'D d/m') }}.</p>
                                    <div wire:ignore wire:key="trade-new-site-{{ $newSiteId ?: 'none' }}">
                                        <x-form.select name="tradeNewSite" label="Site" placeholder="Select site" :value="$newSiteId" data-width="100%" data-live-search="true" data-container="body" data-size="8" x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()" x-on:change="$wire.set('newSiteId', $el.value)">
                                            @foreach ($sites as $site)<option value="{{ $site['id'] }}" @selected((string)$newSiteId === (string)$site['id'])>{{ $site['text'] ?? $site['name'] }}</option>@endforeach
                                        </x-form.select>
                                    </div>
                                @endif
                                <div wire:ignore wire:key="trade-new-task-{{ $newTaskId ?: 'none' }}">
                                    <x-form.select name="tradeNewTask" label="Task" placeholder="Select task" :value="$newTaskId" data-width="100%" data-live-search="true" data-container="body" data-size="8"
                                        x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                        x-on:change="$wire.set('newTaskId', $el.value)">
                                        @foreach ($availableTasks as $task)<option value="{{ $task['id'] }}" @selected((string)$newTaskId === (string)$task['id'])>{{ $task['name'] }}</option>@endforeach
                                    </x-form.select>
                                </div>
                                <button type="button" class="btn green" wire:click="addPlannerTask" @disabled(!$newSiteId || !$newTaskId)><i class="fa fa-plus"></i> Add task</button>
                            </div>

                            @if ($hasConnectedTasks)
                                <div class="trade-action-panel" x-show="action === 'connected'" x-cloak>
                                    <h4 class="bold planner-section-heading">Move connected tasks</h4>
                                    <p class="trade-action-help">These tasks belong to the same site. Move or remove them together to keep the plan aligned.</p>
                                    @foreach ($editorSites as $site)
                                        @php($connected = collect($editorTasks)->where('site_id', $site['id']))
                                        @if ($connected->count() > 1)
                                            <div class="trade-connected">
                                                <strong>{{ $site['name'] }}</strong><br>
                                                <small>Connected: {{ $connected->pluck('task_name')->join(', ') }}</small>
                                                <div class="trade-task-actions">
                                                    <div wire:ignore wire:key="trade-connected-days-{{ $site['id'] }}">
                                                        <x-form.select name="tradeConnectedDays{{ $site['id'] }}" label="Move by" :value="$connectedMoveDays" data-width="120px" data-container="body"
                                                            x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                                            x-on:change="$wire.set('connectedMoveDays', Number($el.value))">
                                                            @for ($move = 1; $move <= 10; $move++)<option value="{{ $move }}" @selected($connectedMoveDays === $move)>{{ $move }} day{{ $move === 1 ? '' : 's' }}</option>@endfor
                                                        </x-form.select>
                                                    </div>
                                                    <button type="button" class="btn btn-sm default" wire:click="movePlannerEntity({{ $site['id'] }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}', -{{ $connectedMoveDays }})" aria-label="Move connected tasks earlier"><i class="fa fa-arrow-left"></i></button>
                                                    <button type="button" class="btn btn-sm default" wire:click="movePlannerEntity({{ $site['id'] }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}', {{ $connectedMoveDays }})" aria-label="Move connected tasks later"><i class="fa fa-arrow-right"></i></button>
                                                    <button type="button" class="btn btn-sm red sws-ml-auto" wire:click="confirmPlannerEntityDeletion({{ $site['id'] }}, '{{ $editorEntityType }}', {{ $editorEntityId }}, '{{ $editorDate }}')" aria-label="Remove connected tasks"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <div class="trade-current-tasks">
                            @forelse ($editorTasks as $task)
                                <div class="trade-task-card" wire:key="editor-task-{{ $task['id'] }}">
                                    <div class="trade-task-layout">
                                        <div class="trade-task-summary">
                                            <strong class="trade-task-name font-blue">{{ $task['task_name'] }}</strong>
                                            <div class="trade-task-site">{{ $task['site_name'] }}</div>
                                            <div class="trade-task-start">Start: <span class="{{ $task['from'] !== $editorDate ? 'font-red' : '' }}">{{ $this->formatDate($task['from']) }}</span></div>
                                        </div>
                                        @if ($this->editorCanEdit())
                                            <div class="trade-task-tools">
                                                <div>
                                                    <label class="control-label">Days</label>
                                                    <div class="trade-stepper">
                                                        <button type="button" class="btn default" wire:click="changePlannerTaskDays({{ $task['id'] }}, -1)" @disabled((int)$task['days'] <= 1) aria-label="Remove one day"><i class="fa fa-minus"></i></button>
                                                        <input type="number" class="form-control" min="1" max="365" step="1" value="{{ $task['days'] }}" wire:change="setPlannerTaskDays({{ $task['id'] }}, $event.target.value)" aria-label="Task duration in days">
                                                        <button type="button" class="btn default" wire:click="changePlannerTaskDays({{ $task['id'] }}, 1)" @disabled((int)$task['days'] >= 365) aria-label="Add one day"><i class="fa fa-plus"></i></button>
                                                    </div>
                                                </div>
                                                <div class="trade-task-calendar" wire:ignore x-data="{}" x-init="
                                                    $nextTick(() => {
                                                        const picker = $($refs.picker);
                                                        if (!$.fn.datepicker) return;
                                                        if (picker.data('datepicker')) picker.datepicker('destroy');
                                                        picker.datepicker({
                                                            rtl: typeof App !== 'undefined' ? App.isRTL() : false,
                                                            orientation: 'auto',
                                                            autoclose: true,
                                                            container: 'body',
                                                            format: 'dd/mm/yyyy',
                                                            startDate: 'today',
                                                            daysOfWeekDisabled: [0, 6],
                                                            datesDisabled: @js($task['picker_disabled_dates'] ?? $publicHolidayDates)
                                                        });
                                                        picker.off('changeDate.tradeMove{{ $task['id'] }}').on('changeDate.tradeMove{{ $task['id'] }}', event => {
                                                            if (!event.date) return;
                                                            const pad = value => String(value).padStart(2, '0');
                                                            const selected = event.date.getFullYear() + '-' + pad(event.date.getMonth() + 1) + '-' + pad(event.date.getDate());
                                                            $wire.movePlannerTaskTo({{ $task['id'] }}, selected);
                                                        });
                                                    });
                                                ">
                                                    <label for="tradeMoveDate{{ $task['id'] }}" class="control-label">Move to date</label>
                                                    <div class="input-group date trade-move-date-picker" x-ref="picker">
                                                        <input type="text" id="tradeMoveDate{{ $task['id'] }}" class="form-control" placeholder="Select date" readonly>
                                                        <span class="input-group-btn"><button type="button" class="btn default date-set" aria-label="Choose move date"><i class="fa fa-calendar"></i></button></span>
                                                    </div>
                                                </div>
                                                <span class="trade-task-card-buttons">
                                                    @if ($reassignTaskId === (int)$task['id'])
                                                        <button type="button" class="btn btn-sm grey-mint" wire:click="cancelReassign"><i class="fa fa-chevron-up"></i> Hide</button>
                                                    @else
                                                        <button type="button" class="btn btn-sm grey-mint" wire:click="startReassign({{ $task['id'] }})"><i class="fa fa-exchange"></i> Reassign</button>
                                                    @endif
                                                    @if (!in_array((int)$task['task_id'], [11, 264], true))<button type="button" class="btn btn-sm red" wire:click="confirmPlannerTaskDeletion({{ $task['id'] }})" aria-label="Delete task"><i class="fa fa-trash"></i></button>@endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($this->editorCanEdit() && $reassignTaskId === (int)$task['id'])
                                        <div class="trade-reassign-box">
                                            <strong>Reassign {{ $task['task_name'] }}</strong>
                                            <p class="trade-action-help planner-reassign-help">Choose the new company and whether to move this task or all future tasks.</p>
                                            <div wire:ignore wire:key="trade-reassign-company-{{ $task['id'] }}">
                                                <x-form.select name="tradeReassignCompany{{ $task['id'] }}" label="Company" placeholder="Select company" :value="$reassignTarget" data-width="100%" data-live-search="true" data-container="body" data-size="8"
                                                    x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                                    x-on:change="$wire.set('reassignTarget', $el.value)">
                                                    @foreach ($reassignTargets as $target)<option value="{{ $target['value'] }}" @selected((string)$reassignTarget === (string)$target['value'])>{{ $target['name'] }}</option>@endforeach
                                                </x-form.select>
                                            </div>
                                            <div wire:ignore wire:key="trade-reassign-scope-{{ $task['id'] }}">
                                                <x-form.select name="tradeReassignScope{{ $task['id'] }}" label="Which tasks?" placeholder="Select tasks" :value="$reassignScope" data-width="100%" data-container="body"
                                                    x-init="if ($.fn.selectpicker && !$($el).parent().hasClass('bootstrap-select')) $($el).selectpicker()"
                                                    x-on:change="$wire.set('reassignScope', $el.value)">
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
                                <div class="font-grey-silver">No tasks for this day. Choose <strong>Add task</strong> above to create one.</div>
                            @endforelse
                        </div>

                        <div class="trade-editor-section"><button type="button" class="btn default" wire:click="closeEditor">Close</button></div>
                    </div>
                </section>
            </div>
        @endif

        <x-ui.confirm-modal :show="$showPlannerDeleteModal" :title="$plannerDeleteTitle" :confirm-label="$plannerDeleteConfirmLabel" confirm-action="deleteConfirmedPlannerAction" close-action="closePlannerDeleteModal">
            <div>{{ $plannerDeleteMessage }}</div>
            @if ($plannerDeleteItem)<div class="sws-confirm-item">{{ $plannerDeleteItem }}</div>@endif
        </x-ui.confirm-modal>
    </div>
</div>
