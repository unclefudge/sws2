<div class="page-content-inner roster-planner-v2">
    @once
        <style>
            .roster-planner-v2 .keybox {
                float: left;
                display: inline;
                width: 20px;
                height: 20px;
                margin: 0 10px 5px 0;
                clear: both;
            }

            .roster-planner-v2 .state-blue {
                background: #3598dc;
            }

            .roster-planner-v2 .state-purple {
                background: #8e44ad;
            }

            .roster-planner-v2 .state-orange {
                background: #e87e04;
            }

            .roster-planner-v2 .state-black {
                background: #000;
            }

            .roster-planner-v2 .planner-key {
                position: fixed;
                right: 0;
                bottom: 0;
                z-index: 10;
                width: 250px;
                padding: 10px;
                background: #fff;
            }

            .roster-planner-v2 .planner-toolbar-link {
                margin: 3px;
            }

            .roster-planner-v2 .roster-list-wrap {
                margin-top: 25px;
            }

            .roster-planner-v2 .roster-column-headings {
                margin: 0;
            }

            .roster-planner-v2 .roster-column-headings,
            .roster-planner-v2 .roster-list-table {
                width: 100%;
                table-layout: fixed;
            }

            .roster-planner-v2 .roster-column-headings > thead > tr > th {
                padding: 8px;
                border: 0 !important;
                background: transparent !important;
                color: #4b555f;
                font-size: 16px;
                font-weight: 600;
                vertical-align: bottom;
            }

            .roster-planner-v2 .roster-site-container {
                margin-bottom: 28px;
            }

            .roster-planner-v2 .roster-list-table {
                margin: 0;
                border-collapse: collapse;
            }

            .roster-planner-v2 .roster-list-table > tbody > tr > td {
                vertical-align: middle;
                background: #fff !important;
            }

            .roster-planner-v2 .roster-site-header > th {
                background: #eaf2f8 !important;
                color: #4b555f !important;
                font-weight: 600;
            }

            .roster-planner-v2 .roster-user-child-row:hover > td {
                background: #f8fafb !important;
            }

            .roster-planner-v2 .roster-user-child-row.is-rostered > td,
            .roster-planner-v2 .roster-user-child-row.is-rostered:hover > td {
                /*background: #FFFCF3 !important;*/
            }

            .roster-planner-v2 .roster-expand {
                width: 24px;
                height: 24px;
                padding: 0;
                border: 1px solid #cdd4da;
                border-radius: 50%;
                background: #fff;
                color: #5b6770;
                line-height: 22px;
                text-align: center;
            }

            .roster-planner-v2 .roster-expand:hover,
            .roster-planner-v2 .roster-expand:focus {
                border-color: #36c6d3;
                color: #36c6d3;
                outline: none;
            }

            .roster-planner-v2 .roster-expand[disabled] {
                cursor: default;
                border-color: #e1e5e8;
                color: #bfc5ca;
            }

            .roster-planner-v2 .roster-company-name {
                font-weight: 600;
            }

            .roster-planner-v2 .roster-company-tasks {
                margin-left: 5px;
                color: #65717b;
            }

            .roster-planner-v2 .roster-user-child-name {
                position: relative;
                padding-left: 34px !important;
                color: #65717b;
            }

            .roster-planner-v2 .roster-user-child-row.font-grey-silver .roster-user-child-name,
            .roster-planner-v2 .roster-user-child-name.font-grey-silver {
                color: #c5c7c9 !important;
            }

            .roster-planner-v2 .roster-user-child-name:before {
                content: "";
                position: absolute;
                left: 18px;
                top: 0;
                bottom: 50%;
                width: 8px;
                border-left: 1px solid #d7dde2;
                border-bottom: 1px solid #d7dde2;
            }

            .roster-planner-v2 .roster-user-details {
                margin-left: 15px;
                color: #8b96a0;
            }

            .roster-planner-v2 .roster-user-name-button {
                padding: 0;
                border: 0;
                background: transparent;
                color: inherit;
                text-align: left;
            }

            .roster-planner-v2 .roster-user-name-button:hover,
            .roster-planner-v2 .roster-user-name-button:focus {
                color: #36c6d3;
                outline: none;
            }

            .roster-planner-v2 .roster-user-name-button[disabled] {
                cursor: default;
                color: inherit;
            }

            .roster-planner-v2 .roster-actions {
                width: 90px;
                white-space: nowrap;
                text-align: center;
            }

            .roster-planner-v2 .roster-actions .btn + .btn {
                margin-left: 3px;
            }

            .roster-planner-v2 .roster-all-toggle {
                width: 28px;
                height: 26px;
                padding: 2px 5px;
            }

            .roster-planner-v2 .roster-user-toggle {
                padding: 2px 5px;
                border: 0;
                background: transparent;
                color: #69757f;
            }

            .roster-planner-v2 .roster-user-toggle:hover,
            .roster-planner-v2 .roster-user-toggle:focus {
                color: #36c6d3;
                outline: none;
            }

            .roster-planner-v2 .roster-user-toggle.is-rostered {
                color: #26a69a;
            }

            .roster-planner-v2 .roster-user-toggle[disabled] {
                cursor: default;
                color: #bfc5ca;
            }

            .roster-planner-v2 .planner-empty {
                padding: 25px 0;
                color: #8b96a0;
            }

            @media (max-width: 767px) {
                .roster-planner-v2 .planner-key {
                    position: static;
                    width: auto;
                    margin-bottom: 15px;
                }

                .roster-planner-v2 .planner-day-nav {
                    margin-top: 10px;
                }

                .roster-planner-v2 .roster-list-wrap {
                    overflow-x: auto;
                }

                .roster-planner-v2 .roster-column-headings,
                .roster-planner-v2 .roster-list-table {
                    min-width: 850px;
                }

                .roster-planner-v2 .roster-user-child-name {
                    padding-left: 24px !important;
                }

                .roster-planner-v2 .roster-user-child-name:before {
                    left: 10px;
                }
            }
        </style>
    @endonce

    @if ($preview)
        <div class="note note-info" style="display:flex; align-items:center; justify-content:space-between; gap:15px">
            <span><strong>Roster Planner preview:</strong> this is the new Livewire version. The normal Roster Planner is unchanged.</span>
            <a href="{{ $this->plannerUrl('/planner/roster') }}" class="btn btn-sm default">View normal version</a>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="portlet light">
                <div class="portlet-title tabbable-line">
                    <div class="caption font-dark">
                        <i class="icon-layers"></i>
                        <span class="caption-subject bold uppercase font-green-haze">Site Roster</span>
                        @if ($preview)
                            <span class="label label-info" style="margin-left:8px">Preview</span>
                        @endif
                    </div>

                    <div class="actions">
                        @if ($canViewTradePlanner)
                            <a href="{{ $this->plannerUrl('/planner/transient') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Labourer">L</a>
                        @endif
                        @if ($canViewPreconstructionPlanner)
                            <a href="{{ $this->plannerUrl('/planner/preconstruction') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Pre-construction">P</a>
                        @endif
                        <button type="button" class="btn btn-circle btn-icon-only btn-default grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Roster">R</button>
                        @if ($canViewSitePlanner)
                            <a href="{{ $this->plannerUrl('/planner/site') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Site">S</a>
                        @endif
                        @if ($canViewTradePlanner)
                            <a href="{{ $this->plannerUrl('/planner/trade') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Trade">T</a>
                        @endif
                        @if ($canViewWeeklyPlanner)
                            <a href="{{ $this->plannerUrl('/planner/weekly') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Weekly">W</a>
                        @endif
                    </div>
                </div>

                <div class="portlet-body">
                    <div class="row" style="padding-bottom:5px">
                        <div class="col-md-3">
                            <select class="form-control bs-select" wire:change="changeSupervisor($event.target.value)">
                                @foreach ($supervisors as $value => $label)
                                    <option value="{{ $value }}" @selected((string)$value === $supervisorId)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 text-center"><h4 class="bold font-green-haze">{{ $this->dateLabel() }}</h4></div>
                        <div class="col-md-4 pull-right planner-day-nav">
                            <div class="btn-group btn-group-circle pull-right">
                                <button type="button" wire:click="changeDay('previous')" class="btn blue-hoki">Prev Day</button>
                                <button type="button" wire:click="changeDay('today')" class="btn blue-dark">Today</button>
                                <button type="button" wire:click="changeDay('next')" class="btn blue-hoki">Next Day</button>
                            </div>
                        </div>
                    </div>

                    <div class="planner-key">
                        <div><span class="keybox state-purple"></span><span style="float:left; margin-right:20px">Roster not Completed</span></div>
                        <br>
                        <div><span class="keybox state-blue"></span><span style="float:left; margin-right:20px">Company All On-Site</span></div>
                        <br>
                        <div><span class="keybox state-black"></span><span style="float:left; margin-right:20px">Company partially On-Site</span></div>
                        <br>
                        <span class="keybox state-orange"></span><span style="float:left; margin-right:20px">Generic Trade</span><br>
                    </div>

                    @if (count($sites))
                        <div class="roster-list-wrap">
                            <table class="table roster-column-headings">
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

                            @foreach ($sites as $site)
                                <div class="roster-site-container" wire:key="site-{{ $site['id'] }}">
                                    <table class="table table-bordered table-nohover order-column roster-list-table">
                                        <colgroup>
                                            <col style="width:45px">
                                            <col style="width:44%">
                                            <col>
                                            <col style="width:90px">
                                        </colgroup>
                                        <thead>
                                        <tr class="mytable-header roster-site-header">
                                            <th></th>
                                            <th colspan="3">{{ $site['name'] }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach ($site['roster'] as $entity)
                                            @continue(!$this->canSeeEntity($entity))
                                            @php($openKey = 'site-' . $site['id'] . '-' . $entity['key'])
                                            @php($isOpen = $this->isOpen($openKey))
                                            <tr wire:key="entity-{{ $site['id'] }}-{{ str_replace('.', '-', $entity['key']) }}">
                                                <td class="text-center">
                                                    @if ($entity['entity_type'] === 'c')
                                                        <button type="button" class="roster-expand" wire:click="toggleEntity('{{ $openKey }}')" title="{{ $isOpen ? 'Collapse' : 'Expand' }}" @disabled($this->isFuture())>
                                                            <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="roster-company-name {{ $this->entityClass($entity) }}">{{ $entity['entity_name'] }}</span>
                                                    <small class="roster-company-tasks">({{ $entity['tasks'] }})</small>
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
                                                <td class="roster-actions">
                                                    @if ($isOpen && $this->canManageToday() && count($entity['attendance']))
                                                        <button type="button" wire:click="checkAll({{ $site['id'] }}, '{{ $entity['key'] }}', 'add')" class="btn green btn-xs btn-outline roster-all-toggle" title="Check all"><i class="fa fa-check-square-o"></i><span class="sr-only">Check all</span>
                                                        </button>
                                                        <button type="button" wire:click="checkAll({{ $site['id'] }}, '{{ $entity['key'] }}', 'delete')" class="btn default btn-xs roster-all-toggle" title="Uncheck all"><i class="fa fa-square-o"></i><span class="sr-only">Uncheck all</span></button>
                                                    @endif
                                                </td>
                                            </tr>

                                            @if ($isOpen && !$this->isFuture())
                                                @forelse ($entity['attendance'] as $user)
                                                    @php($rosterLocked = (bool)$user['attended'] && (bool)$user['roster_id'])
                                                    <tr class="roster-user-child-row {{ $user['roster_id'] ? 'is-rostered' : '' }} {{ !$user['attended'] && !$this->canManageToday() ? 'font-grey-silver' : '' }}" wire:key="roster-user-{{ $site['id'] }}-{{ $user['user_id'] }}">
                                                        <td class="text-center">
                                                            @if ($this->canManageToday())
                                                                <button type="button" class="roster-user-toggle {{ $user['roster_id'] ? 'is-rostered' : '' }}" wire:click="toggleRoster({{ $site['id'] }}, {{ $user['user_id'] }})"
                                                                        title="{{ $user['roster_id'] ? 'Remove from roster' : 'Add to roster' }}" @disabled($rosterLocked)>
                                                                    <i class="fa fa-lg {{ $user['roster_id'] ? 'fa-check-square-o' : 'fa-square-o' }}"></i>
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td colspan="3" class="roster-user-child-name">
                                                            @if ($this->canManageToday())
                                                                <button type="button" class="roster-user-name-button" wire:click="toggleRoster({{ $site['id'] }}, {{ $user['user_id'] }})"
                                                                        title="{{ $user['roster_id'] ? 'Remove from roster' : 'Add to roster' }}" @disabled($rosterLocked)>{{ $user['name'] }}</button>
                                                            @else
                                                                {{ $user['name'] }}
                                                            @endif
                                                            @if ($user['attended'] || $user['other_sites'])
                                                                <span class="roster-user-details">
                                                                @if ($user['attended'])
                                                                        {{ $this->formatTime($user['attended'], true) }}
                                                                    @endif
                                                                    @if ($user['other_sites'])
                                                                        {{ $user['other_sites'] }}
                                                                    @endif
                                                            </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr class="roster-user-child-row" wire:key="roster-no-users-{{ $site['id'] }}-{{ str_replace('.', '-', $entity['key']) }}">
                                                        <td></td>
                                                        <td colspan="3" class="roster-user-child-name font-grey-silver">No users available.</td>
                                                    </tr>
                                                @endforelse
                                            @endif
                                        @endforeach

                                        @foreach ($site['non_roster'] as $entity)
                                            @continue(!$this->canSeeEntity($entity))
                                            @php($openKey = 'site-' . $site['id'] . '-non-' . $entity['key'])
                                            @php($isOpen = $this->isOpen($openKey))
                                            <tr wire:key="non-entity-{{ $site['id'] }}-{{ str_replace('.', '-', $entity['key']) }}">
                                                <td class="text-center">
                                                    <button type="button" class="roster-expand" wire:click="toggleEntity('{{ $openKey }}')" title="{{ $isOpen ? 'Collapse' : 'Expand' }}">
                                                        <i class="fa fa-angle-{{ $isOpen ? 'down' : 'right' }}"></i>
                                                    </button>
                                                </td>
                                                <td><span class="roster-company-name font-grey-silver">{{ $entity['entity_name'] }}</span> <small class="font-red">(Not Rostered)</small></td>
                                                <td>
                                                    <small>
                                                        @foreach ($entity['attendance'] as $user)
                                                            @if ($user['attended'])
                                                                <span>{{ $user['name'] }} ({{ $this->formatTime($user['attended']) }}){{ !$loop->last ? ', ' : '' }}</span>
                                                            @endif
                                                        @endforeach
                                                    </small>
                                                </td>
                                                <td></td>
                                            </tr>

                                            @if ($isOpen)
                                                @foreach ($entity['attendance'] as $user)
                                                    <tr class="roster-user-child-row" wire:key="non-roster-user-{{ $site['id'] }}-{{ $user['user_id'] }}">
                                                        <td></td>
                                                        <td colspan="3" class="roster-user-child-name font-grey-silver">
                                                            {{ $user['name'] }}
                                                            @if ($user['attended'] || $user['other_sites'])
                                                                <span class="roster-user-details">
                                                                @if ($user['attended'])
                                                                        {{ $this->formatTime($user['attended'], true) }}
                                                                    @endif
                                                                    @if ($user['other_sites'])
                                                                        {{ $user['other_sites'] }}
                                                                    @endif
                                                            </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="planner-empty">No attendance for this selection.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
