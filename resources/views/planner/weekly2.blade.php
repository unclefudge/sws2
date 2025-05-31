@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Weekly Planner</span></li>
    </ul>
@stop

@section('content')

    <app-weekly></app-weekly>

    <template id="weekly-template">
        <input v-model="xx.mon_now" type="hidden" value="{{ $date }}">
        <input v-model="xx.params.date" type="hidden" value="{{ $date }}">
        <input v-model="xx.params.supervisor_id" type="hidden" value="{{ $supervisor_id }}">
        <input v-model="xx.params.site_id" type="hidden" value="{{ $site_id }}">
        <input v-model="xx.params.site_start" type="hidden" value="{{ $site_start }}">
        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Weekly Planner</span>
                            </div>
                            <div class="actions">
                                <button v-on:click="gotoURL('/planner/transient')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">L</button>
                                @if (Auth::user()->hasPermission2('view.preconstruction.planner'))
                                    <button v-on:click="gotoURL('/planner/preconstruction')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">P</button>
                                @endif
                                <button v-on:click="gotoURL('/planner/roster')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">R</button>
                                <button v-on:click="gotoURL('/planner/site')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">S</button>
                                <button v-on:click="gotoURL('/planner/trade')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">T</button>
                                <button class="btn btn-circle btn-icon-only grey-steel disabled" style="margin: 3px">W</button>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row" style="padding-bottom: 5px">
                                @if($supervisors)
                                    <div class="col-md-3">
                                        <!--<select v-model="xx.params.supervisor_id" class="form-control bs-select">
                                            @foreach ($supervisors as $id => $name)
                                            <option value="{{ $id }}"
                                                        @if(($id == 'all' && $supervisor_id == '') || $id == $supervisor_id)
                                                selected
                                            @endif>{{ $name }}</option>

                                        @endforeach
                                        </select>-->
                                        {!! Form::select('supervisor_id', $supervisors,
                                            Auth::user()->id, ['class' => 'form-control bs-select', 'id' => 'supervisor_id',]) !!}
                                        <!--<select-picker :name.sync="xx.params.supervisor_id" :options.sync="xx.sel_super" :selected="xx.params.supervisor_id" :function="updateSupervisor"></select-picker>-->
                                    </div>
                                @endif
                                <div class="col-md-5 text-center"><h4 class="bold font-green-haze">@{{ weeklyHeader(xx.mon_now, 0) }}</h4></div>
                                <div class="col-md-4 pull-right">
                                    <div class="btn-group btn-group-circle pull-right">
                                        <!--<a href="/planner/weekly/@{{ weekDate(xx.mon_now, -7) }}" class="btn blue-hoki">Prev Week</a>-->
                                        <button v-on:click="changeWeek(weekDate(xx.mon_now, -7))" class="btn blue-hoki">Prev Week</button>
                                        <button v-on:click="changeWeek(weekDate(xx.mon_this, 0))" class="btn blue-dark">This Week</button>
                                        <button v-on:click="changeWeek(weekDate(xx.mon_now, 7))" class="btn blue-hoki">Next Week</button>
                                    </div>
                                </div>
                            </div>
                            <div v-show="xx.sites.length">
                                <div class="row" style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                                    <div class="col-xs-2">Site</div>
                                    <div class="col-xs-2">Mon @{{ weekDateHeader(xx.mon_now, 0) }}<span v-if="publicHoliday(xx.mon_now, 0)" class="font-red"></br>@{{ publicHoliday(xx.mon_now, 0) }}</span></div>
                                    <div class="col-xs-2">Tue @{{ weekDateHeader(xx.mon_now, 1) }}<span v-if="publicHoliday(xx.mon_now, 1)" class="font-red"></br>@{{ publicHoliday(xx.mon_now, 1) }}</span></div>
                                    <div class="col-xs-2">Wed @{{ weekDateHeader(xx.mon_now, 2) }}<span v-if="publicHoliday(xx.mon_now, 2)" class="font-red"></br>@{{ publicHoliday(xx.mon_now, 2) }}</span></div>
                                    <div class="col-xs-2">Thu @{{ weekDateHeader(xx.mon_now, 3) }}<span v-if="publicHoliday(xx.mon_now, 3)" class="font-red"></br>@{{ publicHoliday(xx.mon_now, 3) }}</span></div>
                                    <div class="col-xs-2">Fri @{{ weekDateHeader(xx.mon_now, 4) }}<span v-if="publicHoliday(xx.mon_now, 4)" class="font-red"></br>@{{ publicHoliday(xx.mon_now, 4) }}</span></div>
                                </div>
                                <template v-for="site in xx.sites">
                                    <app-site :site_id="site.id" :site_name="site.name" :site_code="site.code"></app-site>
                                </template>

                            </div>
                        </div>
                    </div>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                    -->

                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
        <!-- loading Spinner -->
        <div v-show="xx.load_plan" style="background-color: #FFF; padding: 20px;">
            <div class="loadSpinnerOverlay">
                <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
            </div>
        </div>
    </template>


    <template id="site-template">
        <div v-show="showSite(site_id)" class="row row-striped" style="border-bottom: 1px solid lightgrey; overflow: hidden;">
            <div class="col-xs-2 sideColBG">
                <small>@{{ site_name }}<br>
                    <small>@{{ site_code }}</small>
                </small>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 0 ) == xx.today }">
                <app-dayplan :date="weekDate(xx.mon_now, 0)" :site_id="site_id"></app-dayplan>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 1 ) == xx.today }">
                <app-dayplan :date="weekDate(xx.mon_now, 1)" :site_id="site_id"></app-dayplan>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 2 ) == xx.today }">
                <app-dayplan :date="weekDate(xx.mon_now, 2)" :site_id="site_id"></app-dayplan>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 3 ) == xx.today }">
                <app-dayplan :date="weekDate(xx.mon_now, 3)" :site_id="site_id"></app-dayplan>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 4 ) == xx.today }">
                <app-dayplan :date="weekDate(xx.mon_now, 4)" :site_id="site_id"></app-dayplan>
            </div>
        </div>


        <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->
    </template>

    <!-- Day plan for each entity on planner -->
    <template id="dayplan-template">
        <!-- Past Events - disable sidebar and dim entry -->
        <div v-show="pastDate(date) == true" style="padding: 10px; opacity: 0.5">
            <div v-if="day_plan.length">
                <template v-for="entity in day_plan">
                    <div class="@{{ entityClass(entity) }}">
                        <small>@{{ entity.entity_name | max10chars }} (@{{{ entity.tasks }}})</small>
                    </div>
                </template>
            </div>
            <!-- Non-rostered -->
            <template v-for="user in non_rostered">
                <div>
                    <small>*@{{ user | max10chars }}</small>
                </div>
            </template>

        </div>
        <!-- Current Events -->
        <div v-else class="hoverDiv" v-on:click="viewSitePlan(site_id)">
            <div v-if="day_plan.length">
                <template v-for="entity in day_plan">
                    <div class="@{{ entityClass(entity) }}">
                        <small>@{{ entity.entity_name | max10chars }} (@{{{ entity.tasks }}})</small>
                    </div>
                </template>
            </div>
            <!-- Non-rostered -->
            <template v-for="user in non_rostered">
                <div>
                    <small class="font-grey-silver">*@{{ user | max10chars }}</small>
                </div>
            </template>
        </div>

        <!--<pre v-if="xx.dev">@{{ site_id }}<br>@{{ date }}<br>@{{ day_plan | json }}</pre>-->
    </template>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-app-planner-functions.js"></script>
    <script src="/js/vue-app-planner-weekly.js"></script>
@stop