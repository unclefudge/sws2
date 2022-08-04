@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Site Planner</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <input v-model="xx.params.date" type="hidden" value="{{ $date }}">
        <input v-model="xx.params.supervisor_id" type="hidden" value="{{ $supervisor_id }}">
        <input v-model="xx.params.site_id" type="hidden" value="{{ $site_id }}">
        <input v-model="xx.params.site_start" type="hidden" value="{{ $site_start }}">
        <input v-model="xx.status" type="hidden" value="{{ ($site) ? $site->status : 0 }}">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title tabbable-line">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Site Planner</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('view.trade.planner'))
                                <a href="javascript: postAndRedirect('/planner/transient', xx.params)" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">L</a>
                            @endif
                            @if (Auth::user()->hasPermission2('view.preconstruction.planner'))
                                <a href="javascript: postAndRedirect('/planner/preconstruction', xx.params)" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">P</a>
                            @endif
                            @if (Auth::user()->hasPermission2('view.roster'))
                                <a href="javascript: postAndRedirect('/planner/roster', xx.params)" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">R</a>
                            @endif
                            <button class="btn btn-circle btn-icon-only grey-steel disabled" style="margin: 3px">S</button>
                            @if (Auth::user()->hasPermission2('view.trade.planner'))
                                <a href="javascript: postAndRedirect('/planner/trade', xx.params)" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">T</a>
                            @endif
                            @if (Auth::user()->hasPermission2('view.weekly.planner'))
                                <a href="javascript: postAndRedirect('/planner/weekly', xx.params)" class="btn btn-circle btn-icon-only btn-default" style="margin: 3px">W</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select id="site_id" name="site_id" class="form-control select2" width="100%">
                                        <option></option>
                                        <?php $found = false; ?>
                                        @foreach (Auth::user()->authSitesSelect('view.site.planner', 1, 'prompt', 'started') as $id => $name)
                                            <option value="{{ $id }}" {{ ($site && $site->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            <?php if ($site && $site->id == $id) $found = true; ?>
                                        @endforeach
                                        <optgroup label="Maintenance"></optgroup>
                                        @foreach (Auth::user()->authSitesSelect('view.site.planner', 2, 'prompt') as $id => $name)
                                            <option value="{{ $id }}" {{ ($site && $site->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            <?php if ($site && $site->id == $id) $found = true; ?>
                                        @endforeach

                                        @if ($site && !$found)
                                            <optgroup label="Other"></optgroup>
                                            <option value="{{ $site->id }}" selected>{{ $site->name }}</option>
                                        @endif

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select v-model="xx.params.site_start" class="form-control bs-select" id="site_start">
                                        <option value="week" @if($site_start == 'week') selected @endif>This Week</option>
                                        <option value="first" @if($site_start == 'first') selected @endif>First Task</option>
                                        <option value="start" @if($site_start == 'start') selected @endif>Start of Job</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 pull-right">
                                @if ($site && $site->status == 2)
                                    <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Maintenance</h3>
                                @endif
                                @if ($site && $site->status == 1 && Auth::user()->hasPermission2('edit.trade.planner'))
                                    <span v-if="!pastDate(xx.start_date)"><a href="/planner/site/{{$site->id}}/status/0" class="btn blue" style="margin: 3px">Move Site to Pre-construction</a></span>
                                    <span v-else><h5><b>Start Job:</b> @{{ formatDate(xx.start_date) }}</h5></span>
                                @endif
                            </div>
                        </div>

                        @if($site)
                            <app-siteplan :site_id="{{ $site->id }}"></app-siteplan>
                        @endif

                    </div>
                </div>
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
    </style>

    <!--
        Main Site Planner Content
    -->
    <template id="siteplan-template">
        <!--
            Header Sidebar for editing planner
        -->
        <sidebarheader :show.sync="xx.showSidebarHeader" placement="left" header="Edit Planner" :width="350">
            <h4>Tasks for @{{ xx.day_date | formatDate2 }}
                <button class="btn btn-circle btn-outline btn-xs green pull-right" v-on:click="xx.showNewTask = true">
                    <i class="fa fa-plus"></i>Add
                </button>
            </h4>

            <div v-show="xx.showNewTask == true">
                <!-- Trades -->
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-xs-12">
                        <select-picker :name.sync="xx.assign_trade" :options.sync="xx.sel_trade" :function="updateCompanyOptions"></select-picker>
                    </div>
                </div>
                <!-- Companies -->
                <div v-if="xx.assign_trade != ''" class="row" style="padding-bottom: 10px">
                    <div class="col-xs-12">
                        <select-picker :name.sync="xx.day_eid" :options.sync="xx.sel_company" :function="updateTaskOptions"></select-picker>
                    </div>
                </div>
                <!-- Tasks -->
                <div v-if="xx.day_etype != '' && xx.sel_task.length" class="row" style="padding-bottom: 10px">
                    <div class="col-xs-12">
                        <select-picker :name.sync="xx.day_task_id" :options.sync="xx.sel_task" :function="addTask"></select-picker>
                    </div>
                </div>
                <br>
            </div>

            <!-- Current Tasks for Day -->
            <div v-if="xx.day_plan.length" class="list-group">
                <li v-for="task in xx.day_plan" class="list-group-item" style="padding: 0px 10px">
                    <h4 class="font-blue">
                        <!-- Hide Delete [x] for START + Pre Construction Tasks -->
                        <button v-if="task.task_id != 11 && task.task_id != 264" class="btn btn-xs red pull-right" v-on:click="deleteTask(task)">x</button>
                        <b>@{{ task.task_name }}</b><br>
                        <small :class="{ 'font-yellow-gold': task.entity_type == 't' }">@{{ task.entity_name }}</small>
                    </h4>

                    <div class="row" style="padding: 3px;">
                        <div class="col-xs-7"><h4>Days: @{{ task.days }}</h4></div>
                        <div class="col-xs-5">
                            <button class="btn btn-sm default" :class="{'grey-cararra': task.days == 1 }" v-on:click="subTaskDays(task)"><i
                                        class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default" v-on:click="addTaskDays(task)"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="row" style="padding: 3px;">
                        <div class="col-xs-7"><h4 :class="{'font-red': task.from != xx.day_date }">Date: @{{ task.from | formatDate }}</h4>
                            <!-- @{{ task.to | formatDate }}--></div>
                        <div class="col-xs-5">
                            <button class="btn btn-sm default" :class="{'grey-cararra': todayDate(task.from)}"
                                    v-on:click="moveTaskFromDate(task, '-', '1')"><i class="fa fa-minus"></i></button>
                            &nbsp;
                            <button class="btn btn-sm default" v-on:click="moveTaskFromDate(task, '+', '1')"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </li>
            </div>
            <div v-else class="list-group">
                <li class="list-group-item">No tasks for this day</li>
            </div>

            <!-- Move Whole Job x days -->
            <div class="well well-sm" style="padding: 10px">
                <h3 style="margin-top: 0px">Move Job
                    <small>(from @{{ xx.day_date | formatDate2 }})</small>
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
                        <button class="btn btn-sm default" :class="{'grey-cararra': todayDate(xx.day_date)}" v-on:click="moveJobFromDate(xx.day_date, '-', xx.day_move_days)"><i
                                    class="fa fa-minus"></i></button>
                        &nbsp;
                        <button class="btn btn-sm default" v-on:click="moveJobFromDate(xx.day_date, '+', xx.day_move_days)">
                            <i class="fa fa-plus"></i></button>

                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-xs-12 center-block">
                        <button class="btn btn-sm grey-mint center-block" v-on:click="xx.showClearModal = true">Clear Whole Site</button>
                    </div>
                </div>
            </div>

            <br>
            <button class="btn blue" v-on:click="xx.showSidebarHeader = false">close</button>

            <br><br>
            <hr>
            <!--<pre v-if="xx.dev">@{{ xx.day_date }}<br>@{{ xx.day_plan | json}}</pre>
            -->

        </sidebarheader>

        <!--
            Clear Site Confirm Modal
        -->
        <modal :show.sync="xx.showClearModal" :width="300">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Clear Whole Site</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <p class="text-center">Delete all tasks from @{{ xx.day_date | formatDate }} onwards?</p>
                <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> This action can't be undone</p>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn btn-default" v-on:click='xx.showClearModal = false'>Cancel</button>
                <button type="button" class="btn btn-success" v-on:click="clearSiteFromDate()">Continue</button>
            </div>
        </modal>

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

            <!-- Company Tasks -->
            <div class="row form-group" v-if="xx.showNewTask">
                <div class="col-xs-12">
                    <select-picker :name.sync="xx.day_task_id" :options.sync="xx.sel_task" :function="addTask"></select-picker>
                </div>
                <br>
            </div>

            <!-- Current Tasks for Entity -->
            <div v-if="xx.day_plan.length" class="list-group">
                <li v-for="task in xx.day_plan" class="list-group-item" style="padding: 0px 10px">
                    <div v-if="todayTask(task)">
                        <h4 class="font-blue">
                            <!-- Hide Delete [x] for START + Pre Construction Tasks -->
                            <button v-if="task.task_id != 11 && task.task_id != 264" class="btn btn-xs red pull-right" v-on:click="deleteTask(task)">x</button>
                            <b>@{{ task.task_name }}</b><br>
                            <small :class="{ 'font-yellow-gold': task.entity_type == 't' }">@{{ task.entity_name }}</small>
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
                    </div>
                </li>
            </div>
            <div v-else class="list-group">
                <li class="list-group-item">No tasks for this day</li>
            </div>

            <!-- Reassign Tasks -->
            <div v-if="xx.showAssign == false" class="row">
                <div class="col-xs-12 center-block">
                    <button class="btn btn-sm grey-mint center-block" v-on:click="assignTradeOptions()">Assign tasks to another company</button>
                </div>
            </div>

            <!-- Assign Trade options -->
            <div v-if="xx.showAssign" class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <select-picker v-if="xx.sel_trade.length > 2" :name.sync="xx.assign_trade" :options.sync="xx.sel_trade"
                                   :function="assignCompanyOptions"></select-picker>
                    <input v-else v-model="xx.assign_trade" type="hidden">
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
                    <select v-model="xx.assign_tasks" class='form-control bs-select' v-on:change="assignTasks()">
                        <option value="" selected>Select Action</option>
                        <option value="all">All future tasks for this trade</option>
                        <option value="day">Only todays tasks for this trade</option>
                    </select>
                </div>
            </div>

            <br>
            <!-- Move Connected Tasks x days -->
            <div v-if="xx.connected_tasks.length > 1" class="well well-sm" style="padding: 10px">
                <h3 style="margin-top: 0px">
                    <button class="btn btn-xs red pull-right" v-on:click="deleteConnectedTasks()">x</button>
                    Connected Tasks<br>
                    <span style="font-size: 12px;">(
                        <template v-for="(index, task) in xx.connected_tasks">
                            @{{ task.task_name }}<span v-if="index != xx.connected_tasks.length - 1 ">, </span>
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
                        <button class="btn btn-sm default" :class="{'grey-cararra': todayDate(xx.day_date)}" v-on:click="moveEntityFromDate(xx.day_date, '-', xx.day_move_days)"><i
                                    class="fa fa-minus"></i></button>
                        &nbsp;
                        <button class="btn btn-sm default" v-on:click="moveEntityFromDate(xx.day_date, '+', xx.day_move_days)">
                            <i class="fa fa-plus"></i></button>

                    </div>
                </div>
            </div>

            <br>
            <button class="btn blue" v-on:click="xx.showSidebar = false">close</button>

            <br><br>
            <hr>
            <!--<pre v-if="xx.dev">@{{ xx.day_date }}<br>@{{ xx.day_eid }}<br>@{{ xx.day_eid2 }}<br>@{{ xx.other_sites }}
                    <br>plan@{{ xx.day_plan | json}}<br>str@{{ xx.connected_tasks | json}}</pre>
            -->

        </sidebar>

        <!--
            Events for a given week
        -->
        <div :show.sync="xx.plan.length">
            <template v-for="x in xx.total_weeks">
                <div v-if="showWeek(weekDate(xx.first_mon, x*7+0))" class="row"
                     style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                    <!-- Week No. -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+0))" class="col-xs-2" style="color: #999">Week @{{ calcWeekNumber(x) }}</div>
                    <div v-else class="col-xs-2">Week @{{ calcWeekNumber(x) }}</div>
                    <!-- Monday -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+0))" class="col-xs-2" style="padding-left: 25px; color: #999">
                        Mon @{{ weekDateHeader(xx.first_mon, x*7+0) }}</div>
                    <div v-else class="col-xs-2 hoverHead" style="padding-left: 25px"
                         v-on:click="openSidebarHeader(weekDate(xx.first_mon, x*7+0))"> Mon @{{ weekDateHeader(xx.first_mon, x*7+0) }}</div>
                    <!-- Tuesday -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+1))" class="col-xs-2" style="padding-left: 25px; color: #999">
                        Tue @{{ weekDateHeader(xx.first_mon, x*7+1) }}</div>
                    <div v-else class="col-xs-2 hoverHead" style="padding-left: 25px"
                         v-on:click="openSidebarHeader(weekDate(xx.first_mon, x*7+1))"> Tue @{{ weekDateHeader(xx.first_mon, x*7+1) }}</div>
                    <!-- Wednesday -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+2))" class="col-xs-2" style="padding-left: 25px; color: #999">
                        Wed @{{ weekDateHeader(xx.first_mon, x*7+2) }}</div>
                    <div v-else class="col-xs-2 hoverHead" style="padding-left: 25px"
                         v-on:click="openSidebarHeader(weekDate(xx.first_mon, x*7+2))"> Wed @{{ weekDateHeader(xx.first_mon, x*7+2) }}</div>
                    <!-- Thursday -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+3))" class="col-xs-2" style="padding-left: 25px; color: #999">
                        Thu @{{ weekDateHeader(xx.first_mon, x*7+3) }}</div>
                    <div v-else class="col-xs-2 hoverHead" style="padding-left: 25px"
                         v-on:click="openSidebarHeader(weekDate(xx.first_mon, x*7+3))"> Thu @{{ weekDateHeader(xx.first_mon, x*7+3) }}</div>
                    <!-- Friday -->
                    <div v-if="pastDate(weekDate(xx.first_mon, x*7+4))" class="col-xs-2" style="padding-left: 25px; color: #999">
                        Fri @{{ weekDateHeader(xx.first_mon, x*7+4) }}</div>
                    <div v-else class="col-xs-2 hoverHead" style="padding-left: 25px"
                         v-on:click="openSidebarHeader(weekDate(xx.first_mon, x*7+4))"> Fri @{{ weekDateHeader(xx.first_mon, x*7+4) }}</div>
                </div>

                <!-- Show Site Plan for each Entity on given week -->
                <div v-if="showWeek(weekDate(xx.first_mon, x*7+0))" class="row">
                    <app-weekof :mon="weekDate(xx.first_mon, x*7+0)"></app-weekof>
                </div>
            </template>
        </div>

        <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
        -->
    </template>

    <!--
        Weekly row on planner
    -->
    <template id="weekof-template">
        <div v-if="entities.length">
            <!-- individual row for each entity on plan for given week -->
            <template v-for="entity in entities">
                <div class="row row-striped" style="border-bottom: 1px solid lightgrey; padding: 0px; margin: 0px; overflow: hidden; ">
                    <!-- display: flex; align-items: center; -->
                    <div class="col-xs-2 sideColBG"><!--@{{ entity }}-->&nbsp;</div>
                    <div class="col-xs-2" v-bind:class="{ 'todayBG': mon == xx.today }">
                        <app-dayplan :date="mon" :entity="entity"></app-dayplan>
                    </div>
                    <div class="col-xs-2" v-bind:class="{ 'todayBG': tue == xx.today }">
                        <app-dayplan :date="tue" :entity="entity"></app-dayplan>
                    </div>
                    <div class="col-xs-2" v-bind:class="{ 'todayBG': wed == xx.today }">
                        <app-dayplan :date="wed" :entity="entity"></app-dayplan>
                    </div>
                    <div class="col-xs-2" v-bind:class="{ 'todayBG': thu == xx.today }">
                        <app-dayplan :date="thu" :entity="entity"></app-dayplan>
                    </div>
                    <div class="col-xs-2" v-bind:class="{ 'todayBG': fri == xx.today }">
                        <app-dayplan :date="fri" :entity="entity"></app-dayplan>
                    </div>
                </div>
            </template>
        </div>
        <div v-else>
            <!-- empty weeks -->
            <div class="row" style="border-bottom: 1px solid lightgrey; padding: 0px; margin: 0px;  overflow: hidden; min-height: 20px;">
                <div class="col-xs-2 sideColBG">&nbsp;</div>
                <div class="col-xs-10"></div>
            </div>
        </div>
    </template>

    <!-- Day plan for each entity on planner -->
    <template id="dayplan-template">
        <!-- Past Events - disable sidebar and dim entry -->
        <div v-show="pastDate(date) == true" style="padding: 10px; opacity: 0.4">
            <template v-for="task in entity_plan">
                <div class="@{{ taskNameClass(task) }}"><b>@{{ task.task_name }}</b></div>
            </template>
            <div v-if="entity_plan.length" :class="{ 'font-yellow-gold': etype == 't' }">
                <small>@{{ ename }}</small>
            </div>
        </div>
        <!-- Current Events -->
        <div v-else class="hoverDiv" v-on:click="openSidebar(date)">
            <div v-if="entity_plan.length">
                <template v-for="task in entity_plan">
                    <div class="@{{ taskNameClass(task) }}"><b>@{{ task.task_name }}</b></div>
                </template>
                <div v-if="etype == 't'" class="font-yellow-gold">
                    <small>@{{ ename }}</small>
                </div>
                <div v-if="etype == 'c'" :class="{ 'font-green-jungle': conflicts != '' }">
                    <small>@{{ ename }}</small>
                    <div class="font-grey-silver">
                        <small>@{{{ conflicts }}}</small>
                    </div>
                </div>
                <div v-if="onleave != 'empty'" class="label label-warning">on leave</div>
            </div>
        </div>


        <!-- <pre v-if="xx.dev">@{{ date }}<br>@{{ etype }}.@{{ eid }}<br>@{{ conflicts }}<br>@{{ onleave }}</pre> -->
        <!-- <pre v-if="xx.dev">@{{ date }}<br>@{{ etype }}.@{{ eid }}<br>@{{ entity_plan | json }}</pre>-->
    </template>

@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

{{-- Metronic + custom Page Scripts --}}
@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <!--<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>-->
    <script src="/js/libs/vue.1.0.24.js" type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-app-planner-functions.js"></script>
    <script src="/js/vue-app-planner-site.js"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select site", width: '100%'});

            $('#site_id').change(function () {
                xx.params.site_id = $(this).val();
                if ($('#site_start').val() == 'start') {
                    xx.params.site_start = 'start';
                    postAndRedirect('/planner/site', xx.params);
                } else {
                    xx.params.site_start = 'week';
                    postAndRedirect('/planner/site', xx.params);
                }
            });
        });
    </script>
@stop