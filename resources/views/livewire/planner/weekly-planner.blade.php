<div class="page-content-inner" x-data="{ search: '' }" x-on:sws-toastr.stop="toastr[$event.detail.type]($event.detail.message)">
    @include('livewire.planner.partials.sticky-controls')
<div class="weekly-planner-v2">
        @if ($preview)
            <div class="note note-info sws-livewire-preview">
                <span><strong>Weekly Planner preview:</strong> this is the new Livewire version. The normal Weekly Planner is unchanged.</span>
                <a href="{{ $this->plannerUrl('/planner/weekly') }}" class="btn btn-sm default">View normal version</a>
            </div>
        @endif

        @if ($showGuideWarning)
            <div class="note note-warning">This is a guide only. Contact with Site Supervisor is still required.</div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Planner</span>
                            @if ($preview)<span class="label label-info sws-preview-label">Preview</span>@endif
                        </div>

                        <div class="actions">
                            @if ($canViewTradePlanner)
                                <a href="{{ $this->plannerUrl('/planner/transient') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Labourer">L</a>
                            @endif
                            @if ($canViewPreconstructionPlanner)
                                <a href="{{ $this->plannerUrl('/planner/preconstruction') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Pre-construction">P</a>
                            @endif
                            @if ($canViewRoster)
                                <a href="{{ $this->plannerUrl('/planner/roster') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Roster">R</a>
                            @endif
                            @if ($canViewSitePlanner)
                                <a href="{{ $this->plannerUrl('/planner/site') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Site">S</a>
                            @endif
                            @if ($canViewTradePlanner)
                                <a href="{{ $this->plannerUrl('/planner/trade') }}" class="btn btn-circle btn-icon-only btn-default popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Trade">T</a>
                            @endif
                            <button type="button" class="btn btn-circle btn-icon-only grey-steel disabled popovers planner-toolbar-link" data-container="body" data-trigger="hover" data-placement="top" data-content="Weekly">W</button>

                            @if ($isCc)
                                <div><input type="text" class="form-control" x-model.debounce.200ms="search" placeholder="Search Site Names"></div>
                            @endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div class="row planner-sticky-controls planner-sticky-controls-compact">
                            <div class="col-md-3">
                                <form method="GET" action="{{ $weeklyUrl }}">
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    @if ($siteId)<input type="hidden" name="site_id" value="{{ $siteId }}">@endif
                                    @if ($siteStart)<input type="hidden" name="site_start" value="{{ $siteStart }}">@endif
                                    <select name="supervisor_id" class="form-control bs-select" onchange="this.form.submit()">
                                        @foreach ($supervisors as $value => $label)
                                            <option value="{{ $value }}" @selected((string)$value === $supervisorId)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="col-md-5 text-center">
                                <form method="GET" action="{{ $weeklyUrl }}">
                                    <input type="hidden" name="supervisor_id" value="{{ $supervisorId }}">
                                    @if ($siteId)<input type="hidden" name="site_id" value="{{ $siteId }}">@endif
                                    @if ($siteStart)<input type="hidden" name="site_start" value="{{ $siteStart }}">@endif
                                    <select name="date" class="form-control bs-select" onchange="this.form.submit()">
                                        @foreach ($weekOptions as $option)
                                            <option value="{{ $option['date'] }}" @selected($option['date'] === $date)>{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <div class="col-md-4 pull-right">
                                <div class="btn-group btn-group-circle pull-right">
                                    <a href="{{ $previousWeekUrl }}" class="btn blue-hoki">Prev Week</a>
                                    <a href="{{ $thisWeekUrl }}" class="btn blue-dark">This Week</a>
                                    @if ($canViewNextWeek)<a href="{{ $nextWeekUrl }}" class="btn blue-hoki">Next Week</a>@endif
                                </div>
                            </div>
                        </div>

                        @if ($isCc)
                            <div class="planner-key">
                                <div><span class="keybox state-green"></span><span class="planner-key-label">Exceeded Max #Jobs</span></div><br>
                                <div><span class="keybox state-blue"></span><span class="planner-key-label">All On-Site</span></div><br>
                                <div><span class="keybox state-red"></span><span class="planner-key-label">Not All On-Site</span></div><br>
                                <div><span class="keybox state-purple"></span><span class="planner-key-label">Not Rostered</span></div>
                                <span class="keybox state-orange"></span><span class="planner-key-label">Generic Trade</span><br>
                            </div>
                        @endif

                        <div class="planner-grid-wrap">
                            <div class="planner-grid">
                                <div class="row planner-row planner-row-header">
                                    <div class="col-xs-2">Site</div>
                                    @foreach ($days as $day)
                                        <div class="col-xs-2">
                                            {{ $day['day'] }} {{ $day['label'] }}
                                            @if ($day['holiday'])<br><span class="font-red">{{ $day['holiday'] }}</span>@endif
                                        </div>
                                    @endforeach
                                </div>

                                @forelse ($rows as $row)
                                    <div class="row planner-row row-striped" data-search="{{ $row['search_name'] }}" x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-cloak>
                                        <div class="col-xs-2 sideColBG planner-site">
                                            <small>
                                                {{ $row['name_short'] }}<br>
                                                <small>
                                                    @if ($showContact)<br>{!! $row['address'] !!}<br>{{ $row['supervisors_contact'] }}@endif
                                                    @if ((int)$row['status'] === 2)<br><span class="font-red">Maintenance</span>@endif
                                                    @if ((int)$row['order'] === 3)<br><span class="font-red">Prac Completed {{ $row['prac_complete'] }}</span>@endif
                                                    @if ($userCompanyId === 3 && $row['preconstruction_date'])<br><span class="font-blue">*** Pre-construction ***<br>Jobstart: {{ $row['preconstruction_date'] }}</span>@endif
                                                    @if ($userCompanyId === 3 && $row['completion_formatted'])<br><span class="{{ $row['completion_soon'] ? 'font-red' : '' }}">Completion: {{ $row['completion_formatted'] }}</span>@endif
                                                </small>
                                            </small>
                                        </div>

                                        @foreach ($row['days'] as $index => $dayPlan)
                                            <div class="col-xs-2 planner-day {{ $days[$index]['is_today'] ? 'todayBG' : '' }}">
                                                @if ($canViewSitePlanner)
                                                    <a href="{{ $row['site_url'] }}" class="planner-day-link {{ $dayPlan['past'] ? 'planner-day-past' : '' }} {{ $days[$index]['holiday'] ? 'planner-day-holiday' : '' }}">
                                                        @include('livewire.planner.weekly-day', ['dayPlan' => $dayPlan])
                                                    </a>
                                                @else
                                                    <div class="planner-day-content {{ $dayPlan['past'] ? 'planner-day-past' : '' }} {{ $days[$index]['holiday'] ? 'planner-day-holiday' : '' }}">
                                                        @include('livewire.planner.weekly-day', ['dayPlan' => $dayPlan])
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @empty
                                    <div class="planner-empty">No sites are available for this selection.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
