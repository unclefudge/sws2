@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Weekly Planner</span></li>
    </ul>
@stop

@section('content')
    <style>
        .keybox {
            float: left;
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 10px 5px 0px;
            clear: both;
        }

        .state-blue {
            background-color: #3598dc;
        }

        .state-purple {
            background-color: #8E44AD;
        }

        .state-orange {
            background-color: #E87E04;
        }

        .state-green {
            background-color: #26c281;
        }

        .state-red {
            background-color: #e7505a;
        }

        .state-black {
            background-color: #000;
        }

        .stickyKey {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 51px;
            z-index: 10;
            background: #ffffff;
            padding: 5px 0 5px 0;
        }
    </style>
    <div class="page-content-inner">
        <input v-model="xx.mon_now" type="hidden" value="{{ $date }}">
        <input v-model="xx.params.date" type="hidden" value="{{ $date }}">
        <input v-model="xx.params.supervisor_id" type="hidden" value="{{ $supervisor_id }}">
        <input v-model="xx.params.site_id" type="hidden" value="{{ $site_id }}">
        <input v-model="xx.params.site_start" type="hidden" value="{{ $site_start }}">
        <input v-model="xx.user_company_id" type="hidden" value="{{ Auth::user()->company_id }}">
        <input v-model="xx.show_contact" type="hidden" value="{{ (Auth::user()->company->parent_company) ? '1': '0' }}">
        {{-- Extended Weekly view for trades
        13-Bricklayer, 4-Electrician, 5-Floor and Wall Tiler, 6-Gyprocker, 7-Painter, 8-Plumber, 9-Roofer, 20-Roof Plumber, 27-Renderer, 11-Stairs --}}
        {{--<input v-model="xx.plan_ahead" type="hidden" value="{{ (!empty(array_intersect([13, 4, 5, 6, 7, 8, 9, 20, 27, 11], Auth::user()->company->tradesSkilledIn->pluck('id')->toArray()))) ? '34': '20' }}"> --}}
        <input v-model="xx.plan_ahead" type="hidden" value="62">
        <input v-model="xx.view_siteplan" type="hidden" value="{{ Auth::user()->hasPermission2('view.site.planner') ? 1 : 0 }}">
        @if (Auth::user()->company->parent_company && Auth::user()->company->reportsTo()->id == 3)
            <div class="note note-warning">
                This is a guide only. Contact with Site Supervisor is still required.
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Planner</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('view.trade.planner'))
                                <button v-on:click="gotoURL('/planner/transient')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">L</button>
                            @endif
                            @if (Auth::user()->hasPermission2('view.preconstruction.planner'))
                                <button v-on:click="gotoURL('/planner/preconstruction')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">P</button>
                            @endif
                            @if (Auth::user()->hasPermission2('view.roster'))
                                <button v-on:click="gotoURL('/planner/roster')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">R</button>
                            @endif
                            @if (Auth::user()->hasPermission2('view.site.planner'))
                                <button v-on:click="gotoURL('/planner/site')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">S</button>
                            @endif
                            @if (Auth::user()->hasPermission2('view.trade.planner'))
                                <button v-on:click="gotoURL('/planner/trade')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">T</button>
                            @endif
                            <button class="btn btn-circle btn-icon-only grey-steel disabled" style="margin: 3px">W</button>
                            @if (Auth::user()->isCC())
                                <div>
                                    <input v-model="xx.search" type="text" class="form-control" placeholder="Search Site Names"/>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row" style="padding-bottom: 5px">
                            <div class="col-md-3">
                                {!! Form::select('supervisor_id', $supervisors, $supervisor_id, ['class' => 'form-control bs-select', 'id' => 'supervisor_id',]) !!}
                            </div>
                            <div class="col-md-5 text-center">
                                {{--}}<h4 class="bold font-green-haze">@{{ weeklyHeader(xx.mon_now, 0) }}</h4> --}}
                                <select v-model="xx.week_selected" class="form-control bs-select" v-on:change="changeWeekSelected" id="week_selected">
                                    <option value="-14">@{{ weeklyHeader(xx.mon_now, -14) }}</option>
                                    <option value="-7">@{{ weeklyHeader(xx.mon_now, -7) }}</option>
                                    <option value="0" selected>@{{ weeklyHeader(xx.mon_now, 0) }}</option>
                                    <option value="7">@{{ weeklyHeader(xx.mon_now, 7) }}</option>
                                    <option value="14">@{{ weeklyHeader(xx.mon_now, 14) }}</option>
                                    <option value="21">@{{ weeklyHeader(xx.mon_now, 21) }}</option>
                                    <option value="28">@{{ weeklyHeader(xx.mon_now, 28) }}</option>
                                    <option value="35">@{{ weeklyHeader(xx.mon_now, 35) }}</option>
                                    <option value="42">@{{ weeklyHeader(xx.mon_now, 42) }}</option>
                                    <option value="49">@{{ weeklyHeader(xx.mon_now, 49) }}</option>
                                    <option value="56">@{{ weeklyHeader(xx.mon_now, 56) }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 pull-right">
                                <div class="btn-group btn-group-circle pull-right">
                                    @if(true || Auth::user()->company->subscription)
                                        <button v-on:click="changeWeek(weekDate(xx.mon_now, -7))" class="btn blue-hoki">Prev Week</button>
                                    @endif
                                    <button v-on:click="changeWeek(weekDate(xx.mon_this, 0))" class="btn blue-dark">This Week</button>
                                    <button v-if="viewWeek(weekDate(xx.mon_now, 7))" v-on:click="changeWeek(weekDate(xx.mon_now, 7))" class="btn blue-hoki">Next Week</button>
                                </div>
                            </div>
                        </div>

                        {{-- Key Map --}}
                        @if (Auth::user()->isCC())
                            <div style="position: fixed; bottom:0px; right: 0px; width: 250px; z-index: 10; padding: 10px; background: #ffffff">
                                <div><span class="keybox state-green"></span><span style="float:left; margin-right: 20px;">Exceeded Max #Jobs </span></div>
                                <br>
                                <div><span class="keybox state-blue"></span><span style="float:left; margin-right: 20px;">All On-Site </span></div>
                                <br>
                                <div><span class="keybox state-red"></span><span style="float:left; margin-right: 20px;">Not All On-Site </span></div>
                                <br>
                                <div><span class="keybox state-purple"></span><span style="float:left; margin-right: 20px;">Not Rostered</span></div>
                                <span class="keybox state-orange"></span><span style="float:left; margin-right: 20px;">Generic Trade </span><br>
                            </div>
                        @endif

                        <div v-show="xx.sites.length">
                            <div class="row" style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                                <div class="col-xs-2">Site</div>
                                <div class="col-xs-2">Mon @{{ weekDateHeader(xx.mon_now, 0) }}</div>
                                <div class="col-xs-2">Tue @{{ weekDateHeader(xx.mon_now, 1) }}</div>
                                <div class="col-xs-2">Wed @{{ weekDateHeader(xx.mon_now, 2) }}</div>
                                <div class="col-xs-2">Thu @{{ weekDateHeader(xx.mon_now, 3) }}</div>
                                <div class="col-xs-2">Fri @{{ weekDateHeader(xx.mon_now, 4) }}</div>
                            </div>
                            <template v-for="site in xx.sites">
                                <app-site :site_id="site.id" :site_name="site.name" :site_code="site.code" :site_contact="site.supervisors_contact" :site_address="site.address" :site_status="site.status" :site_preconstruct="site.start" :site_order="site.order"
                                          :site_prac_complete="site.prac_complete"></app-site>
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

    <!--<template id="weekly-template"></template> -->


    <template id="site-template">
        <div v-show="showSite(site_id) && site_code != '0007'" class="row row-striped" style="border-bottom: 1px solid lightgrey; overflow: hidden;">
            <div class="col-xs-2 sideColBG">
                <small>@{{ site_name | max20chars }}<br>
                    <small>
                        <span v-if="xx.show_contact == 1"><br><span v-html="site_address"></span><br>@{{ site_contact }}</span>
                        {{--}}<span v-else>@{{ site_code }}</span>--}}
                        <span v-if="site_status == 2" style="color: red"><br>Maintenance</span>
                        <span v-if="site_order == 3" style="color: red"><br>Prac Completed @{{ site_prac_complete }}</span>
                        <span v-if="xx.user_company_id == 3 && preConstruct(site_preconstruct)" style="color: blue"><br>*** Pre-construction ***<br>Jobstart: @{{ preConstruct(site_preconstruct) }}</span>
                    </small>
                </small>
            </div>

            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 0 ) == xx.today }">
                <div v-if="xx.view_siteplan == 1" class="hoverDiv" v-on:click="viewSitePlan(site_id)">
                    <app-dayplan :date="weekDate(xx.mon_now, 0)" :site_id="site_id"></app-dayplan>
                </div>
                <div v-if="xx.view_siteplan == 0">
                    <app-dayplan :date="weekDate(xx.mon_now, 0)" :site_id="site_id"></app-dayplan>
                </div>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 1 ) == xx.today }">
                <div v-if="xx.view_siteplan == 1" class="hoverDiv" v-on:click="viewSitePlan(site_id)">
                    <app-dayplan :date="weekDate(xx.mon_now, 1)" :site_id="site_id"></app-dayplan>
                </div>
                <div v-if="xx.view_siteplan == 0">
                    <app-dayplan :date="weekDate(xx.mon_now, 1)" :site_id="site_id"></app-dayplan>
                </div>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 2 ) == xx.today }">
                <div v-if="xx.view_siteplan == 1" class="hoverDiv" v-on:click="viewSitePlan(site_id)">
                    <app-dayplan :date="weekDate(xx.mon_now, 2)" :site_id="site_id"></app-dayplan>
                </div>
                <div v-if="xx.view_siteplan == 0">
                    <app-dayplan :date="weekDate(xx.mon_now, 2)" :site_id="site_id"></app-dayplan>
                </div>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 3 ) == xx.today }">
                <div v-if="xx.view_siteplan == 1" class="hoverDiv" v-on:click="viewSitePlan(site_id)">
                    <app-dayplan :date="weekDate(xx.mon_now, 3)" :site_id="site_id"></app-dayplan>
                </div>
                <div v-if="xx.view_siteplan == 0">
                    <app-dayplan :date="weekDate(xx.mon_now, 3)" :site_id="site_id"></app-dayplan>
                </div>
            </div>
            <div class="col-xs-2" v-bind:class="{ 'todayBG': weekDate(xx.mon_now, 4 ) == xx.today }">
                <div v-if="xx.view_siteplan == 1" class="hoverDiv" v-on:click="viewSitePlan(site_id)">
                    <app-dayplan :date="weekDate(xx.mon_now, 4)" :site_id="site_id"></app-dayplan>
                </div>
                <div v-if="xx.view_siteplan == 0">
                    <app-dayplan :date="weekDate(xx.mon_now, 4)" :site_id="site_id"></app-dayplan>
                </div>
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

        <!-- <pre v-if="xx.dev">@{{ site_id }}<br>@{{ date }}<br>@{{ day_plan | json }}</pre>-->
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