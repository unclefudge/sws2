@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Trade Planner</span></li>
    </ul>
@stop

@section('content')

    <app-weekly></app-weekly>

    <style>
        .aside {
            z-index: 9999;
            height: 480px;
        }

        @media screen and (min-width: 1850px) {
            .aside {
                height: 100%;
            }
        }

        .datepicker-ctrl p {
            margin: 0px;
        }

        .modal-open .colorpicker, .modal-open .datepicker, .modal-open .daterangepicker {
            z-index: 888 !important;
        }

    </style>

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
                                <span class="caption-subject bold uppercase font-green-haze"> Trade Planner</span>
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
                                <button class="btn btn-circle btn-icon-only btn-default grey-steel disabled" style="margin: 3px">T</button>
                                @if (Auth::user()->hasPermission2('view.weekly.planner'))
                                    <button v-on:click="gotoURL('/planner/weekly')" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">W</button>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <select v-model="xx.params.trade_id" class="form-control bs-select" v-on:change="getCompanyForTrade" id="trade_id">
                                    <option value='' selected>Select Trade</option>
                                    @foreach (Auth::user()->company->tradeListPlannerSelect() as $id => $name)
                                        <option value="{{ $id }}"
                                                @if($id == $trade_id) selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>
                                        <span v-if="countUpcoming(xx.params.trade_id)">Upcoming Tasks</span>
                                        <span v-else>@{{ xx.trade_name }} Planner</span>
                                        <span class="pull-right" style="margin-top: -15px">
                                            @if (Auth::user()->hasPermission2('edit.trade.planner'))
                                                <div class="btn-group">
                                                    <a class="btn btn-circle green dropdown-toggle" data-toggle="dropdown" href="javascript:;">
                                                        <i class="fa fa-cog"> </i>&nbsp; Actions <i class="fa fa-angle-down"></i>
                                                    </a>
                                                    <ul class="dropdown-menu pull-right">
                                                        <li><a href="javascript:;" v-on:click="openSidebarAddstart()"> Add Job Start</a></li>
                                                        <li><a href="javascript:;" v-on:click="openSidebarMovestart()"> Move Job Start</a></li>
                                                        <li><a href="javascript:;" v-on:click="openSidebarAllocatejob()"> Allocate Job </a></li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </span>
                                    </h4>
                                </div>
                            </div>
                            <div v-if="countUpcoming(xx.params.trade_id)">
                                <div class="row" style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                                    <template v-for="upcoming in xx.upcoming_task">
                                        <div v-if="upcoming.trade_id == xx.params.trade_id" class="col-xs-2 ">@{{ upcoming.name }}</div>
                                    </template>
                                </div>
                                <div class="row">
                                    <template v-for="upcoming in xx.upcoming_task">
                                        <div v-if="upcoming.trade_id == xx.params.trade_id" class="col-xs-2 ">
                                            <template v-for="task in xx.upcoming_plan">
                                                <div v-if="xx.permission == 'edit'">
                                                    <div v-if="task.task_id == upcoming.id" class="hoverDiv0" v-on:click="openSidebarUpcoming(task)">
                                                        <small v-if="task.entity_type == 't'" class="font-yellow-gold">@{{ task.from | formatDate3 }} @{{ task.site_name | max10chars }}
                                                            (@{{ task.days }}
                                                            )
                                                        </small>
                                                        <small v-else class="font-grey-silver">@{{ task.from | formatDate3 }} @{{ task.site_name | max10chars }} (@{{ task.days }})</small>
                                                    </div>
                                                </div>
                                                <div v-if="xx.permission == 'view'">
                                                    <div v-if="task.task_id == upcoming.id">
                                                        <small v-if="task.entity_type == 't'" class="font-yellow-gold">@{{ task.from | formatDate3 }} @{{ task.site_name | max10chars }}
                                                            (@{{ task.days }}
                                                            )
                                                        </small>
                                                        <small v-else class="font-grey-silver">@{{ task.from | formatDate3 }} @{{ task.site_name | max10chars }} (@{{ task.days }})</small>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <hr>
                            </div>
                            <div v-show="xx.companies.length">
                                <h4 v-if="countUpcoming(xx.params.trade_id)">@{{ xx.trade_name }} Planner</h4>
                                <div class="row" style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                                    <div class="col-xs-2 ">Site</div>
                                    <div class="col-xs-2 ">Mon @{{ weekDateHeader(xx.mon_now, 0) }}</div>
                                    <div class="col-xs-2 ">Tue @{{ weekDateHeader(xx.mon_now, 1) }}</div>
                                    <div class="col-xs-2 ">Wed @{{ weekDateHeader(xx.mon_now, 2) }}</div>
                                    <div class="col-xs-2 ">Thu @{{ weekDateHeader(xx.mon_now, 3) }}</div>
                                    <div class="col-xs-2 ">Fri @{{ weekDateHeader(xx.mon_now, 4) }}</div>
                                </div>
                                <template v-for="company in xx.companies">
                                    <app-company :etype="company.type" :eid="company.id" :ename="company.name" :ecompliant="company.compliant"></app-company>
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

        <!--
           Upcoming Sidebar for editing entity
           -->
        <sidebarupcoming :show.sync="xx.showSidebarUpcoming" placement="left" header="Edit Planner" :width="350">
            <h3 v-if="xx.day_upcoming.entity_type == 't'" class="font-yellow-gold" style="margin: 0px">@{{  xx.day_upcoming.entity_name }}</h3>
            <h3 v-if="xx.day_upcoming.entity_type == 'c'" :class="{ 'font-green-jungle': xx.day_conflicts }" style="margin: 0px">@{{  xx.day_upcoming.entity_name }}</h3>
            <hr style="margin: 10px 0px">
            <h4>Task for @{{ xx.day_upcoming.from | formatDate2 }}</h4>

            <!--  Upcoming Task -->
            <div class="list-group">
                <li class="list-group-item" style="padding: 0px 10px">
                    <h4 class="font-blue">
                        <button class="btn btn-xs red pull-right" v-on:click="deleteTask(xx.day_upcoming)">x</button>
                        <b>@{{ xx.day_upcoming.task_name }}</b><br>
                        <small>@{{ xx.day_upcoming.site_name }}</small>
                    </h4>

                    <div class="row" style="padding: 3px;">
                        <!-- Day buttons -->
                        <div class="col-xs-7"><h4>Days: @{{ xx.day_upcoming.days }}</h4></div>
                        <div v-if="xx.enableActions" class="col-xs-5">
                            <button class="btn btn-sm default" :class="{'grey-cararra': xx.day_upcoming.days == 1 }" v-on:click="subTaskDays(xx.day_upcoming)">
                                <i class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default" v-on:click="addTaskDays(xx.day_upcoming)"><i class="fa fa-plus"></i></button>
                        </div>
                        <!-- disabled Day buttons -->
                        <div v-else class="col-xs-5">
                            <button class="btn btn-sm default disabled" :class="{'grey-cararra': xx.day_upcoming.days == 1 }">
                                <i class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default disabled"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="row" style="padding: 3px;">
                        <div class="col-xs-7"><h4 :class="{'font-red': xx.day_upcoming.from != xx.day_date }">Date: @{{ xx.day_upcoming.from | formatDate }}</h4>
                            <!-- @{{ xx.day_upcoming.to | formatDate }}--></div>
                        <!-- Move Buttons -->
                        <div class="col-xs-5">
                            <select v-model="xx.day_move_date" class='form-control bs-select' v-on:change="moveTaskToDate(xx.day_upcoming, xx.day_move_date)">
                                <option value="" selected>Move to</option>
                                <option v-if="!pastDate(xx.mon_now)" value="xx.mon_now">@{{ xx.mon_now | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 1))" value="@{{ weekDate(xx.mon_now, 1) }}">@{{ weekDate(xx.mon_now, 1) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 2))" value="@{{ weekDate(xx.mon_now, 2) }}">@{{ weekDate(xx.mon_now, 2) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 3))" value="@{{ weekDate(xx.mon_now, 3) }}">@{{ weekDate(xx.mon_now, 3) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 4))" value="@{{ weekDate(xx.mon_now, 4) }}">@{{ weekDate(xx.mon_now, 4) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 7))" value="@{{ weekDate(xx.mon_now, 7) }}">@{{ weekDate(xx.mon_now, 7) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 8))" value="@{{ weekDate(xx.mon_now, 8) }}">@{{ weekDate(xx.mon_now, 8) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 9))" value="@{{ weekDate(xx.mon_now, 9) }}">@{{ weekDate(xx.mon_now, 9) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 10))" value="@{{ weekDate(xx.mon_now, 10) }}">@{{ weekDate(xx.mon_now, 10) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 11))" value="@{{ weekDate(xx.mon_now, 11) }}">@{{ weekDate(xx.mon_now, 11) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 14))" value="@{{ weekDate(xx.mon_now, 14) }}">@{{ weekDate(xx.mon_now, 14) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 15))" value="@{{ weekDate(xx.mon_now, 15) }}">@{{ weekDate(xx.mon_now, 15) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 16))" value="@{{ weekDate(xx.mon_now, 16) }}">@{{ weekDate(xx.mon_now, 16) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 17))" value="@{{ weekDate(xx.mon_now, 17) }}">@{{ weekDate(xx.mon_now, 17) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 18))" value="@{{ weekDate(xx.mon_now, 18) }}">@{{ weekDate(xx.mon_now, 18) | formatDate2 }}</option>
                                <option v-if="!pastDate(weekDate(xx.mon_now, 19))" value="@{{ weekDate(xx.mon_now, 19) }}">@{{ weekDate(xx.mon_now, 19) | formatDate2 }}</option>
                            </select>
                        </div>
                    </div>
                </li>
            </div>

            <div v-if="xx.showAssign == false" class="row">
                <div class="col-xs-12 center-block">
                    <button class="btn btn-sm grey-mint center-block" v-on:click="assignSiteAndTradeOptions()">Assign tasks to another company</button>
                </div>
            </div>

            <!-- Assign Company options -->
            <div v-if="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div v-if="xx.assign_trade" class="col-xs-12">
                    <select-picker :name.sync="xx.assign_cid" :options.sync="xx.sel_company" :function="assignCompanyName"></select-picker>
                </div>
            </div>

            <!-- Assign Task options -->
            <div v-show="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div v-show="xx.assign_cid" class="col-xs-12">
                    <!--
                    <select v-model="xx.assign_tasks" class='form-control bs-select' v-on:change="assignTasks()">
                        <option value="" selected>Select Action</option>
                        <option value="all">All future tasks for this trade</option>
                        <option value="day">Only todays tasks for this trade</option>
                    </select>
                    -->
                    <select-picker :name.sync="xx.assign_tasks" :options.sync="xx.sel_assign_tasks" :function="assignTasks"></select-picker>
                </div>
            </div>
            <br>
            <button class="btn blue" v-on:click="xx.showSidebarUpcoming = false">close</button>

            <br><br>
            <hr>
            <!--<pre v-if="xx.dev">@{{ xx.day_date }}<br>assigntasks:@{{ xx.assign_tasks }}<br>assigncid:@{{ xx.assign_cid }}<br>@{{ xx.day_plan | json}}</pre>
            -->
        </sidebarupcoming>


        <!--
           Entity Sidebar for editing entity
           -->
        <sidebar :show.sync="xx.showSidebar" placement="left" header="Edit Planner" :width="350">
            <h3 v-if="xx.day_etype == 't'" class="font-yellow-gold" style="margin: 0px">@{{  xx.day_ename }}</h3>
            <h3 v-if="xx.day_etype == 'c'" :class="{ 'font-green-jungle': xx.day_conflicts }" style="margin: 0px">@{{  xx.day_ename }}
                <div v-if="xx.day_other_sites">
                    <small class="font-grey-silver">@{{{ xx.day_other_sites }}}</small>
                </div>
            </h3>

            <hr style="margin: 10px 0px">
            <h4>Tasks for @{{ xx.day_date | formatDate2 }}
                <button class="btn btn-circle btn-outline btn-xs green pull-right" v-on:click="showNewTask">
                    <i class="fa fa-plus"></i>Add
                </button>
            </h4>

            <div v-show="xx.showNewTask == true">
                <!-- Sites-->
                <div class="row form-group">
                    <div class="col-xs-12">
                        <select-picker :name.sync="xx.day_site_id" :options.sync="xx.sites" :function="showNewTask"></select-picker>
                    </div>
                </div>
                <!-- Tasks -->
                <div class="row form-group">
                    <div class="col-xs-12">
                        <select-picker v-if="xx.day_site_id != ''" :name.sync="xx.day_task_id" :options.sync="xx.sel_task" :function="addTask"></select-picker>
                    </div>
                </div>
                <br>
            </div>

            <!-- Current Tasks for Entity -->
            <div v-if="xx.day_plan.length" class="list-group">
                <li v-for="task in xx.day_plan | orderBy 'site_name' 'task_name'" class="list-group-item" style="padding: 0px 10px">
                    <h4 class="font-blue">
                        <!-- Hide Delete [x] for START + Pre Construction Tasks -->
                        <button v-if="task.task_id != 11 && task.task_id != 264" class="btn btn-xs red pull-right" v-on:click="deleteTask(task)">x</button>
                        <b>@{{ task.task_name }}</b><br>
                        <small>@{{ task.site_name }}</small>
                    </h4>

                    <div class="row" style="padding: 3px;">
                        <!-- Day buttons -->
                        <div class="col-xs-7"><h4>Days: @{{ task.days }}</h4></div>
                        <div v-if="xx.enableActions" class="col-xs-5">
                            <button class="btn btn-sm default" :class="{'grey-cararra': task.days == 1 }" v-on:click="subTaskDays(task)"><i
                                        class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default" v-on:click="addTaskDays(task)"><i class="fa fa-plus"></i></button>

                        </div>
                        <!-- disabled Day buttons -->
                        <div v-else class="col-xs-5">
                            <button class="btn btn-sm default disabled" :class="{'grey-cararra': task.days == 1 }"><i
                                        class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default disabled"><i class="fa fa-plus"></i></button>

                        </div>
                    </div>
                    <div class="row" style="padding: 3px;">
                        <div class="col-xs-7"><h4 :class="{'font-red': task.from != xx.day_date }">Date: @{{ task.from | formatDate }}</h4>
                            <!-- @{{ task.to | formatDate }}--></div>
                        <!-- Move Buttons -->
                        <div v-if="xx.enableActions" class="col-xs-5">
                            <button class="btn btn-sm default" :class="{'grey-cararra': todayDate(task.from)}" v-on:click="
                            moveTaskFromDate(task, '-', '1')"><i class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default" v-on:click="moveTaskFromDate(task, '+', '1')"><i class="fa fa-plus"></i></button>

                        </div>
                        <!-- disabled Move buttons -->
                        <div v-else class="col-xs-5">
                            <button class="btn btn-sm default disabled" :class="{'grey-cararra': todayDate(task.from)}"><i class="fa fa-minus"></i>
                            </button>
                            &nbsp;
                            <button class="btn btn-sm default disabled"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </li>
            </div>
            <div v-else class="list-group">
                <li class="list-group-item">No tasks for this day</li>
            </div>

            <div v-if="xx.showAssign == false && xx.day_plan.length" class="row">
                <div class="col-xs-12 center-block">
                    <button class="btn btn-sm grey-mint center-block" v-on:click="assignSiteAndTradeOptions">Assign tasks to another company</button>
                </div>
            </div>

            <!-- Assign Site options -->
            <div v-if="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <select-picker v-if="xx.sel_site.length > 2" :name.sync="xx.assign_site" :options.sync="xx.sel_site" :function="assignCompanyOptions"></select-picker>
                    <input v-else v-model="xx.assign_site" type="hidden" value="@{{ xx.sel_site[1].value }}">
                </div>
            </div>

            <!-- Assign Trade options -->
            <div v-if="xx.showAssign && xx.sel_trade.length > 2" class="row" style="padding-bottom: 10px">
                <div v-if="xx.assign_site != ''" class="col-xs-12">
                    <select-picker v-if="xx.sel_trade.length > 2" :name.sync="xx.assign_trade" :options.sync="xx.sel_trade" :function="assignCompanyOptions"></select-picker>
                </div>
            </div>
            <input v-if="xx.showAssign && xx.sel_trade.length < 3" v-model="xx.assign_trade" type="hidden">

            <!-- Assign Company options -->
            <div v-if="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div v-if="xx.assign_site && xx.assign_trade" class="col-xs-12">
                    <select-picker :name.sync="xx.assign_cid" :options.sync="xx.sel_company" :function="assignCompanyName"></select-picker>
                </div>
            </div>

            <!-- Assign Task options -->
            <div v-show="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div v-show="xx.assign_cid" class="col-xs-12">
                    <select v-model="xx.assign_tasks" class='form-control bs-select' v-on:change="assignTasks()" id="assignTasks">
                        <option value="" selected>Select Action</option>
                        <option value="all">All future tasks for this trade</option>
                        <option value="day">Only todays tasks for this trade</option>
                    </select>
                </div>
            </div>
            <br>

            <!-- Move Connected Tasks x days -->
            <template v-for="site in xx.day_sites">
                <div v-if="site.connected_tasks.length > 1">
                    <h3><b>@{{ site.site_name }}</b></h3>
                    <div class="well well-sm" style="padding: 10px">
                        <h3 style="margin-top: 0px">
                            <button class="btn btn-xs red pull-right" v-on:click="deleteConnectedTasks(site.site_id)">x</button>
                            Connected Tasks<br>
                    <span style="font-size: 12px;">(
                        <template v-for="(index, task) in site.connected_tasks">
                            @{{ task.task_name }}<span v-if="index != site.connected_tasks.length - 1 ">, </span>
                        </template>
                        )
                    </span>
                        </h3>
                        <!-- Actions for all Tasks from current date -->
                        <div class="row">
                            <!--<div class="col-xs-5">Move days</div>-->
                            <div class="col-xs-7">
                                <select v-model="xx.day_move_days" class="form-control bs-select"> <!-- style="height:28px; width: 50px;" -->
                                    <option value="1">Move 1 day</option>
                                    <option value="2">Move 2 days</option>
                                    <option value="3">Move 3 days</option>
                                    <option value="4">Move 4 days</option>
                                    <option value="5">Move 5 days</option>
                                    <option value="6">Move 6 days</option>
                                    <option value="7">Move 7 days</option>
                                    <option value="8">Move 8 days</option>
                                    <option value="9">Move 9 days</option>
                                    <option value="10">Move 10 days</option>
                                </select>
                            </div>
                            <div class="col-xs-5">
                                <button class="btn btn-sm default" :class="{'grey-cararra': todayDate(xx.day_date)}" v-on:click="moveEntityFromDate(site.site_id, xx.day_date, '-', xx.day_move_days)">
                                    <i
                                            class="fa fa-minus"></i></button>
                                &nbsp;
                                <button class="btn btn-sm default" v-on:click="moveEntityFromDate(site.site_id, xx.day_date, '+', xx.day_move_days)">
                                    <i class="fa fa-plus"></i></button>

                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </template>
            <button class="btn blue" v-on:click="xx.showSidebar = false">close</button>

            <br><br>
            <hr>
            <!--<pre v-if="xx.dev">@{{ xx.day_date }}<br>@{{ xx.day_eid }}<br>@{{ xx.day_eid2 }} - @{{ xx.other_sites }}<br>sites@{{ xx.day_sites | json }}
                    <br>plan@{{ xx.day_plan | json}}<br>str@{{ xx.connected_tasks | json}}</pre>
            -->
        </sidebar>

        <!-------------------------------------------
           Add Jobstart Sidebar for adding Job Start
         ------------------------------------------->
        <sidebaraddstart :show.sync="xx.showSidebarAddstart" placement="left" header="Job Start" :width="350">
            <h3 style="margin: 0px">Add Job Start</h3>
            <hr style="margin: 10px 0px">

            <!-- Sites -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <label for="xx.assign_site">Site</label>
                    <select-picker :name.sync="xx.assign_site" :options.sync="xx.sel_jobstart" :function="assignCompanyName"></select-picker>
                </div>
            </div>

            <label for="xx.assign_site">Date</label>
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <div class="pull-left">
                        <datepicker :value.sync="xx.jobstart" format="dd/MM/yyyy" :placeholder="choose date" :disabled-days-of-week="[0,6]"></datepicker>
                    </div>
                    <span class="input-group-btn pull-left"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span>
                </div>
            </div>
            <br>
            <button class="btn dark" v-on:click="xx.showSidebarAddstart = false">cancel</button>
            <button class="btn blue" v-on:click="saveJobstart" :disabled="!validJobstart()">Save</button>

            <br><br>
            <hr>
        </sidebaraddstart>

        <!--
           Move Jobstart Sidebar for adding Job Start
         -->
        <sidebarmovestart :show.sync="xx.showSidebarMovestart" placement="left" header="Job Start" :width="350">
            <h3 style="margin: 0px">Move Job Start</h3>
            <hr style="margin: 10px 0px">

            <!-- Sites -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <label for="xx.assign_site">Site</label>
                    <select-picker :name.sync="xx.assign_site" :options.sync="xx.sel_jobstart" :function="assignCompanyName"></select-picker>
                </div>
            </div>

            <label for="xx.assign_site">Date</label>
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <div class="pull-left">
                        <datepicker :value.sync="xx.jobstart" format="dd/MM/yyyy" :placeholder="choose date" :disabled-days-of-week="[0,6]"></datepicker>
                    </div>
                    <span class="input-group-btn pull-left"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span>
                </div>
            </div>
            <br>
            <button class="btn dark" v-on:click="xx.showSidebarMovestart = false">cancel</button>
            <button class="btn blue" v-on:click="moveJobstart" :disabled="!validJobstart()">Save</button>

            <br><br>
            <hr>
        </sidebarmovestart>

        <!--
           Jobstart Sidebar for adding Job Start
         -->
        <sidebarallocate :show.sync="xx.showSidebarAllocate" placement="left" header="Allocate Site" :width="350">
            <h3 style="margin: 0px">Allocate Site to Supervisor</h3>
            <hr style="margin: 10px 0px">

            <!-- Sites -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <label for="xx.assign_site">Site</label>
                    <select-picker :name.sync="xx.assign_site" :options.sync="xx.sel_joballocate" :function="assignCompanyName"></select-picker>
                </div>
            </div>

            <!-- Supervisors -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <label for="xx.assign_super">Supervisor</label>
                    <select-picker :name.sync="xx.assign_super" :options.sync="xx.sel_super" :function="assignCompanyName"></select-picker>
                </div>
            </div>
            <br>
            <button class="btn dark" v-on:click="xx.showSidebarAllocate = false">cancel</button>
            <button class="btn blue" v-on:click="saveSiteAllocate" :disabled="!validSiteAllocate()">Save</button>

            <br><br>
            <hr>
        </sidebarallocate>

    </template>


    <template id="company-template">
        <div class="row row-striped" style="border-bottom: 1px solid lightgrey;  overflow: hidden;">
            <div class="col-xs-2 sideColBG">
                <small class="text-uppercase" :class="{ 'font-yellow-gold': etype == 't' }">@{{ ename }}</small>
                <small v-if="ecompliant == 0" class="font-red"><br><span class="badge badge-danger badge-roundless">NON COMPLIANT</span></small>
                <small v-if="leaveSummary()" class="font-blue"><br>Leave: @{{ leaveSummary() }}</small>
            </div>
            <div class="col-xs-2 @{{ cellBG(xx.mon_now, 0)}}">
                <app-dayplan :date="weekDate(xx.mon_now, 0)" :etype="etype" :eid="eid" :ename="ename"></app-dayplan>
            </div>
            <div class="col-xs-2 @{{ cellBG(xx.mon_now, 1)}}">
                <app-dayplan :date="weekDate(xx.mon_now, 1)" :etype="etype" :eid="eid" :ename="ename"></app-dayplan>
            </div>
            <div class="col-xs-2 @{{ cellBG(xx.mon_now, 2)}}">
                <app-dayplan :date="weekDate(xx.mon_now, 2)" :etype="etype" :eid="eid" :ename="ename"></app-dayplan>
            </div>
            <div class="col-xs-2 @{{ cellBG(xx.mon_now, 3)}}">
                <app-dayplan :date="weekDate(xx.mon_now, 3)" :etype="etype" :eid="eid" :ename="ename"></app-dayplan>
            </div>
            <div class="col-xs-2 @{{ cellBG(xx.mon_now, 4)}}">
                <app-dayplan :date="weekDate(xx.mon_now, 4)" :etype="etype" :eid="eid" :ename="ename"></app-dayplan>
            </div>
        </div>
        <!--<pre v-if="xx.dev">@{{ date }}<br>@{{ etype }}.@{{ eid }}<br>@{{ ename }}<br>@{{ ecompliant }}<br></pre>
        -->
    </template>

    <!-- Day plan for each entity on planner -->
    <template id="dayplan-template">
        <div v-if="onleave" style="padding-left: 10px">
            <small class="label label-sm label-warning" style="font-size: 11px;">ON LEAVE &nbsp;<br></small>
        </div>
        <!-- Past Events - disable sidebar and dim entry -->
        <div v-show="pastDateTrade(date) == true" style="padding: 10px; opacity: 0.4">
            <div v-if="entity_sites.length">
                <template v-for="entity in entity_sites">
                    <div class="@{{ entityClass(entity) }}">
                        <small>@{{ entity.site_name | max15chars }} (@{{{ entity.tasks }}})</small>
                    </div>
                </template>
            </div>
        </div>
        <!-- Current Events -->
        <div v-else class="hoverDiv" v-on:click="openSidebar(date)">
            <div v-if="entity_sites.length">
                <template v-for="entity in entity_sites">
                    <div class="@{{ entityClass(entity) }}">
                        <small>@{{ entity.site_name | max15chars }} (@{{{ entity.tasks }}})</small>
                    </div>
                </template>
            </div>
        </div>

        <!--<pre v-if="xx.dev">@{{ date }}<br>@{{ etype }}.@{{ eid }}<br>@{{ onleave }}<br>@{{ day_sites | json }}<br>@{{ entity_plan | json }}</pre>
        -->
    </template>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-strap.min.js"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-app-planner-functions.js"></script>
<script src="/js/vue-app-planner-trade.js"></script>
@stop