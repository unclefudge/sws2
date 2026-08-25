<div class="page-content-inner attendance-planner-v2">
    @include('livewire.planner.partials.sticky-controls')

    @once
        <style>
            body .loadSpinnerOverlay,
            body [v-show="xx.showSpinner"] { display:none !important; }
            .attendance-planner-v2 .planner-toolbar-link { margin:3px; }
            .attendance-planner-v2 .attendance-list-wrap { margin-top:25px; }
            .attendance-planner-v2 .attendance-column-headings { margin:0; }
            .attendance-planner-v2 .attendance-column-headings,
            .attendance-planner-v2 .attendance-list-table { width:100%; table-layout:fixed; }
            .attendance-planner-v2 .attendance-column-headings > thead > tr > th { padding:8px; border:0 !important; background:transparent !important; color:#4b555f; font-size:16px; font-weight:600; vertical-align:bottom; }
            .attendance-planner-v2 .attendance-site-container { margin-bottom:28px; }
            .attendance-planner-v2 .attendance-list-table { margin:0; border-collapse:collapse; }
            .attendance-planner-v2 .attendance-list-table > tbody > tr > td { vertical-align:middle; background:#fff !important; }
            .attendance-planner-v2 .attendance-site-header > th { background:#eaf2f8 !important; color:#4b555f !important; font-weight:600; }
            .attendance-planner-v2 .attendance-user-child-row:hover > td { background:#f8fafb !important; }
            .attendance-planner-v2 .attendance-user-child-row.is-rostered > td,
            .attendance-planner-v2 .attendance-user-child-row.is-rostered:hover > td { background:#FFFCF3 !important; }
            .attendance-planner-v2 .attendance-expand { width:24px; height:24px; padding:0; border:1px solid #cdd4da; border-radius:50%; background:#fff; color:#5b6770; line-height:22px; text-align:center; }
            .attendance-planner-v2 .attendance-expand:hover,
            .attendance-planner-v2 .attendance-expand:focus { border-color:#36c6d3; color:#36c6d3; outline:none; }
            .attendance-planner-v2 .attendance-expand[disabled] { cursor:default; border-color:#e1e5e8; color:#bfc5ca; }
            .attendance-planner-v2 .attendance-company-name { font-weight:600; }
            .attendance-planner-v2 .attendance-company-tasks { margin-left:5px; color:#65717b; }
            .attendance-planner-v2 .attendance-user-child-name { position:relative; padding-left:34px !important; color:#65717b; }
            .attendance-planner-v2 .attendance-user-child-row.font-grey-silver .attendance-user-child-name,
            .attendance-planner-v2 .attendance-user-child-name.font-grey-silver { color:#c5c7c9 !important; }
            .attendance-planner-v2 .attendance-user-child-name:before { content:""; position:absolute; left:18px; top:0; bottom:50%; width:8px; border-left:1px solid #d7dde2; border-bottom:1px solid #d7dde2; }
            .attendance-planner-v2 .attendance-user-name-button { padding:0; border:0; background:transparent; color:inherit; text-align:left; }
            .attendance-planner-v2 .attendance-user-name-button:hover,
            .attendance-planner-v2 .attendance-user-name-button:focus { color:#36c6d3; outline:none; }
            .attendance-planner-v2 .attendance-user-name-button[disabled] { cursor:default; color:inherit; }
            .attendance-planner-v2 .attendance-actions { width:90px; white-space:nowrap; text-align:center; }
            .attendance-planner-v2 .attendance-actions .btn + .btn { margin-left:3px; }
            .attendance-planner-v2 .attendance-all-toggle { width:28px; height:26px; padding:2px 5px; }
            .attendance-planner-v2 .attendance-user-toggle { padding:2px 5px; border:0; background:transparent; color:#69757f; }
            .attendance-planner-v2 .attendance-user-toggle:hover,
            .attendance-planner-v2 .attendance-user-toggle:focus { color:#36c6d3; outline:none; }
            .attendance-planner-v2 .attendance-user-toggle.is-rostered { color:#26a69a; }
            .attendance-planner-v2 .attendance-user-toggle[disabled] { cursor:default; color:#bfc5ca; }
            .attendance-planner-v2 .planner-empty { padding:25px 0; color:#8b96a0; }
            @media (max-width:767px) {
                .attendance-planner-v2 .planner-day-nav { margin-top:10px; }
                .attendance-planner-v2 .attendance-list-wrap { overflow-x:auto; }
                .attendance-planner-v2 .attendance-column-headings,
                .attendance-planner-v2 .attendance-list-table { min-width:850px; }
                .attendance-planner-v2 .attendance-user-child-name { padding-left:24px !important; }
                .attendance-planner-v2 .attendance-user-child-name:before { left:10px; }
            }
        </style>
    @endonce

    @if ($preview)
        <div class="note note-info" style="display:flex; align-items:center; justify-content:space-between; gap:15px">
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
                        @if ($preview)<span class="label label-info" style="margin-left:8px">Preview</span>@endif
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
                    <div class="row planner-sticky-controls" style="padding-bottom:5px">
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
                                    <col style="width:45px">
                                    <col style="width:44%">
                                    <col>
                                    <col style="width:90px">
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
                                        <col style="width:45px">
                                        <col style="width:44%">
                                        <col>
                                        <col style="width:90px">
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
