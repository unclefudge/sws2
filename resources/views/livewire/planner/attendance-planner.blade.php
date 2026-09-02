<div class="page-content-inner attendance-planner-v2" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @include('livewire.planner.partials.sticky-controls')
@if ($preview)
        <div class="note note-info sws-livewire-preview">
            <span><strong>Attendance Planner preview:</strong> this is the new Livewire version. The normal Attendance Planner is unchanged.</span>
            <a href="{{ $this->plannerUrl('/planner/roster1') }}" class="btn btn-sm default">View normal version</a>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="portlet light">
                <div class="portlet-title tabbable-line">
                    <div class="caption font-dark">
                        <i class="icon-layers"></i>
                        <span class="caption-subject bold uppercase font-green-haze">Site Roster</span>
                        @if ($preview)<span class="label label-info sws-preview-label">Preview</span>@endif
                    </div>

                    <div class="actions">
                        @if ($canViewTradePlanner)
                            <a href="{{ $this->plannerUrl('/planner/transient') }}" class="btn btn-circle btn-icon-only btn-default planner-toolbar-link">L</a>
                        @endif
                        @if ($canViewPreconstructionPlanner)
                            <a href="{{ $this->plannerUrl('/planner/preconstruction') }}" class="btn btn-circle btn-icon-only btn-default planner-toolbar-link">P</a>
                        @endif
                        <button type="button" class="btn btn-circle btn-icon-only btn-default grey-steel disabled planner-toolbar-link">R</button>
                        @if ($canViewSitePlanner)
                            <a href="{{ $this->plannerUrl('/planner/site') }}" class="btn btn-circle btn-icon-only btn-default planner-toolbar-link">S</a>
                        @endif
                        @if ($canViewTradePlanner)
                            <a href="{{ $this->plannerUrl('/planner/trade') }}" class="btn btn-circle btn-icon-only btn-default planner-toolbar-link">T</a>
                        @endif
                        @if ($canViewWeeklyPlanner)
                            <a href="{{ $this->plannerUrl('/planner/weekly') }}" class="btn btn-circle btn-icon-only btn-default planner-toolbar-link">W</a>
                        @endif
                    </div>
                </div>

                <div class="portlet-body">
                    <div class="row planner-sticky-controls planner-sticky-controls-compact">
                        <div class="col-md-4">
                            <select class="form-control bs-select" wire:change="changeSite($event.target.value)">
                                @foreach ($siteOptions as $value => $label)
                                    <option value="{{ $value }}" @selected((string)$value === (string)($siteId ?? ''))>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-center"><h4 class="bold font-green-haze">{{ $this->dateLabel() }}</h4></div>
                        <div class="col-md-4 pull-right planner-day-nav">
                            <div class="btn-group btn-group-circle pull-right">
                                <button type="button" wire:click="changeDay('previous')" class="btn blue-hoki">Prev Day</button>
                                <button type="button" wire:click="changeDay('today')" class="btn blue-dark">Today</button>
                                <button type="button" wire:click="changeDay('next')" class="btn blue-hoki">Next Day</button>
                            </div>
                        </div>
                    </div>

                    @if ($siteId)
                        @php($selectedSiteName = $siteOptions[(string)$siteId] ?? 'Selected Site')
                        <div class="attendance-list-wrap">
                            <table class="table attendance-column-headings">
                                <colgroup>
                                    <col class="planner-col-toggle">
                                    <col class="planner-col-name">
                                    <col>
                                    <col class="planner-col-actions">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Company</th>
                                    <th>Users planned to be On-Site <small class="font-grey-silver">(greyed currently not logged-in)</small></th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                            </table>

                            <div class="attendance-site-container">
                                <table class="table table-bordered table-nohover order-column attendance-list-table">
                                    <colgroup>
                                        <col class="planner-col-toggle">
                                        <col class="planner-col-name">
                                        <col>
                                        <col class="planner-col-actions">
                                    </colgroup>
                                    <thead>
                                    <tr class="mytable-header attendance-site-header">
                                        <th></th>
                                        <th colspan="3">{{ $selectedSiteName }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($rostered as $entity)
                                        @continue(!$this->canSeeEntity($entity))
                                        @php($openKey = 'entity-' . $entity['key'])
                                        @php($isOpen = $this->isOpen($openKey))
                                        <tr wire:key="entity-{{ $siteId }}-{{ str_replace('.', '-', $entity['key']) }}">
                                            <td class="text-center">
                                                @if ($entity['entity_type'] === 'c')
                                                    <button type="button" class="attendance-expand" wire:click="toggleEntity('{{ $openKey }}')" title="{{ $isOpen ? 'Collapse' : 'Expand' }}" @disabled($this->isFuture())>
                                                        <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="attendance-company-name {{ $this->entityClass($entity) }}">{{ $entity['entity_name'] }}</span>
                                                <small class="attendance-company-tasks">({{ $entity['tasks'] }})</small>
                                            </td>
                                            <td>
                                                <small>
                                                    @foreach ($entity['attendance'] as $user)
                                                        @if ($user['attended'])
                                                            <span>{{ $user['name'] }} ({{ $this->formatTime($user['attended']) }}),</span>
                                                        @elseif ($user['roster_id'])
                                                            <span class="font-grey-silver">{{ $user['name'] }},</span>
                                                        @endif
                                                    @endforeach
                                                </small>
                                            </td>
                                            <td class="attendance-actions">
                                                @if ($isOpen && $this->canManageToday() && count($entity['attendance']))
                                                    <button type="button" wire:click="checkAll('{{ $entity['key'] }}', 'add')" class="btn green btn-xs btn-outline attendance-all-toggle" title="Check all"><i class="fa fa-check-square-o"></i><span class="sr-only">Check all</span></button>
                                                    <button type="button" wire:click="checkAll('{{ $entity['key'] }}', 'delete')" class="btn default btn-xs attendance-all-toggle" title="Uncheck all"><i class="fa fa-square-o"></i><span class="sr-only">Uncheck all</span></button>
                                                @endif
                                            </td>
                                        </tr>

                                        @if ($isOpen && !$this->isFuture())
                                            @forelse ($entity['attendance'] as $user)
                                                @php($rosterLocked = (bool)$user['attended'] && (bool)$user['roster_id'])
                                                <tr class="attendance-user-child-row {{ $user['roster_id'] ? 'is-rostered' : '' }} {{ !$user['attended'] && !$this->canManageToday() ? 'font-grey-silver' : '' }}" wire:key="roster-user-{{ $siteId }}-{{ $user['user_id'] }}">
                                                    <td></td>
                                                    <td class="attendance-user-child-name">
                                                        @if ($this->canManageToday())
                                                            <button type="button" class="attendance-user-name-button" wire:click="toggleRoster({{ $user['user_id'] }})" title="{{ $user['roster_id'] ? 'Remove from roster' : 'Add to roster' }}" @disabled($rosterLocked)>{{ $user['name'] }}</button>
                                                        @else
                                                            {{ $user['name'] }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($user['attended']){{ $this->formatTime($user['attended'], true) }}@endif
                                                        @if ($user['other_sites'])<span class="font-grey-silver">{{ $user['other_sites'] }}</span>@endif
                                                    </td>
                                                    <td class="attendance-actions">
                                                        @if ($this->canManageToday())
                                                            <button type="button" class="attendance-user-toggle {{ $user['roster_id'] ? 'is-rostered' : '' }}" wire:click="toggleRoster({{ $user['user_id'] }})" title="{{ $user['roster_id'] ? 'Remove from roster' : 'Add to roster' }}" @disabled($rosterLocked)>
                                                                <i class="fa fa-lg {{ $user['roster_id'] ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="attendance-user-child-row" wire:key="roster-no-users-{{ $siteId }}-{{ str_replace('.', '-', $entity['key']) }}">
                                                    <td></td>
                                                    <td class="attendance-user-child-name font-grey-silver">No users available.</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            @endforelse
                                        @endif
                                    @endforeach

                                    @foreach ($unrostered as $entity)
                                        @continue(!$this->canSeeEntity($entity))
                                        @php($openKey = 'non-' . $entity['key'])
                                        @php($isOpen = $this->isOpen($openKey))
                                        <tr wire:key="non-entity-{{ $siteId }}-{{ str_replace('.', '-', $entity['key']) }}">
                                            <td class="text-center">
                                                <button type="button" class="attendance-expand" wire:click="toggleEntity('{{ $openKey }}')" title="{{ $isOpen ? 'Collapse' : 'Expand' }}">
                                                    <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                                </button>
                                            </td>
                                            <td><span class="attendance-company-name font-grey-silver">{{ $entity['entity_name'] }}</span> <small class="font-red">(Not Rostered)</small></td>
                                            <td>
                                                <small>
                                                    @foreach ($entity['attendance'] as $user)
                                                        @if ($user['attended'])<span>{{ $user['name'] }} ({{ $this->formatTime($user['attended']) }}){{ !$loop->last ? ', ' : '' }}</span>@endif
                                                    @endforeach
                                                </small>
                                            </td>
                                            <td></td>
                                        </tr>

                                        @if ($isOpen)
                                            @foreach ($entity['attendance'] as $user)
                                                <tr class="attendance-user-child-row" wire:key="non-roster-user-{{ $siteId }}-{{ $user['user_id'] }}">
                                                    <td></td>
                                                    <td class="attendance-user-child-name font-grey-silver">{{ $user['name'] }}</td>
                                                    <td>
                                                        @if ($user['attended']){{ $this->formatTime($user['attended'], true) }}@endif
                                                        @if ($user['other_sites'])<span class="font-grey-silver">{{ $user['other_sites'] }}</span>@endif
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach

                                    @if (!count($rostered) && !count($unrostered))
                                        <tr><td colspan="4" class="planner-empty">No attendance for this site.</td></tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="planner-empty">Select a site to view attendance.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
