@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>ToDo Tasks</span></li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject font-green-haze bold uppercase">ToDo Tasks</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <app-tasks></app-tasks>
                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- loading Spinner -->
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    <template id="tasks-template">
        <input v-model="xx.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
        <input v-model="xx.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
        <input v-model="xx.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">
        <input v-model="xx.assigned_tasks" type="hidden" id="assigned_tasks" value="1">
        <input v-model="xx.assigned_cc" type="hidden" id="assigned_cc" value="1">
        <input v-model="xx.task_type" type="hidden" id="task_type" value="all">
        <input v-model="xx.username" type="hidden" id="user" value="all">
        <input v-model="xx.active_record" type="hidden" id="task_type" value="1">

        <div class="row">
            <div class="col-md-3">
                <select-picker :name.sync="xx.assigned_tasks" :options.sync="xx.sel_assigned_tasks" :function="doNothing"></select-picker>
            </div>
            <div class="col-md-3">
                <select-picker :name.sync="xx.assigned_cc" :options.sync="xx.sel_assigned_cc" :function="updateUserList"></select-picker>
            </div>
            <div class="col-md-3">
                <select-picker :name.sync="xx.username" :options.sync="xx.sel_users" :function="doNothing"></select-picker>
            </div>
            <div class="col-md-3">
                <select-picker :name.sync="xx.active_record" :options.sync="xx.sel_active_record" :function="doNothing"></select-picker>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">
                <select-picker :name.sync="xx.task_type" :options.sync="xx.sel_task_types" :function="doNothing"></select-picker>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-4"><input v-model="xx.search" type="text" class="form-control" placeholder="Search"></div>
        </div>

        <div class="row" style="margin-top: 0px; padding-top: 0px">
            <div class="col-md-12">
                <span class="pull-right" style="margin-top: 20px"><b>Tasks: @{{ filteredListCount }}</b></span>
                <table v-show="xx.list.length" class="table table-striped table-bordered table-nohover order-column">
                    <thead>
                    <tr class="mytable-header">
                        <th><a href="#" class="mytable-header-link" v-on:click="sortBy('title')"> Task Name</a>
                            <i v-if="xx.sortKey == 'title' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'title' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th style="width: 25%"><a href="#" class="mytable-header-link" v-on:click="sortBy('assigned_names')"> Task Owner(s)</a>
                            <i v-if="xx.sortKey == 'assigned_names' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'assigned_names' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th style="width:10%"><a href="#" class="mytable-header-link" v-on:click="sortBy('due_at')"> Due Date</a>
                            <i v-if="xx.sortKey == 'due_at' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'due_at' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th style="width:12%"><a href="#" class="mytable-header-link" v-on:click="sortBy('lastupdated')"> Last updated</a>
                            <i v-if="xx.sortKey == 'lastupdated' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'lastupdated' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                    </tr>
                    <br>
                    </thead>
                    <tbody>
                    {{--}}<template v-for="task in xx.list |  filterStatus xx.status | filterBy xx.search | orderBy xx.sortKey xx.sortOrder">--}}
                    <template v-for="task in filteredList  | orderBy xx.sortKey xx.sortOrder">
                        <tr class="hoverDiv" v-on:click="task.expand = !task.expand"> {{--   v-if="verifyShow(task)"   filterAssigned xx.assigned_tasks | --}}
                            <td>@{{ task.title }}</td>
                            <td>@{{ task.assigned_names }}</td>
                            <td class="@{{ dueDateColour(task)  }}">@{{ task.due_at | formatDate}}</td>
                            <td>@{{ task.lastupdated_human}}</td>
                            {{--}}<td class="@{{ lastUpdateColour(task)  }}">@{{ task.lastupdated | formatDate}}</td>--}}
                        </tr>
                        <tr v-if="task.expand">
                            <td colspan="4" style="width: 100%; background: #333; color: #fff">
                                Task ID: @{{ task.id }}
                                <div style="background: #fff; color:#636b6f;  padding: 20px">
                                    <p v-html="task.info"></p>
                                </div>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>

                <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                -->

            </div>
        </div>
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
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-app-basic-functions.js"></script>
    <script>

        var xx = {
            record: {},
            dev: dev, spinner: false, assigned_tasks: 1, assigned_cc: 1, task_type: 'all', username: 'all', active_record: 1, tasks_found: 0, status: 0,
            sortKey: 'title', sortOrder: 1, search: '',
            today: moment().format('YYYY-MM-DD'), days7past: moment().subtract(7, 'days').format('YYYY-MM-DD'), days28past: moment().subtract(28, 'days').format('YYYY-MM-DD'),
            list: [], sel_assigned_tasks: [], sel_assigned_cc: [], sel_task_types: [], sel_active_record: [], sel_users_cc: [], sel_users_ext: [], sel_users_all: [], sel_users: []
        };

        Vue.component('app-tasks', {
            template: '#tasks-template',

            created: function () {
                this.getTodo();
            },
            data: function () {
                return {xx: xx};
            },
            computed: {
                searchLowercase() {
                    return this.xx.search.toLowerCase();
                },
                filteredList() {
                    // Task Title or Owner includes Search string
                    let result = this.xx.list.filter(task => {
                        return task.title.toLowerCase().includes(this.searchLowercase) || task.assigned_names.toLowerCase().includes(this.searchLowercase)
                    });

                    // Filter Assigned
                    if (this.xx.assigned_tasks == '0') // Only non-assigned tasks
                        result = result.filter(task => {
                            return task.assigned_names == '-';
                        });
                    if (this.xx.assigned_tasks == '1') // Only assigned tasks
                        result = result.filter(task => {
                            return task.assigned_names != '-';
                        });

                    // Filter Cape Cod Tasks
                    if (this.xx.assigned_cc == '1') // Only Cape Cod tasks
                        result = result.filter(task => {
                            return task.assigned_cc == '1';
                        });
                    if (this.xx.assigned_cc == '0') // Only External tasks
                        result = result.filter(task => {
                            return task.assigned_cc == '0';
                        });

                    // Filter Users
                    if (this.xx.username != 'all')
                        result = result.filter(task => {
                            //return task.assigned_names.toLowerCase().includes(this.username.toLowerCase);
                            return task.assigned_names.includes(this.xx.username);
                        });


                    // Filter Task type
                    if (this.xx.task_type != 'all')
                        result = result.filter(task => {
                            return task.type == this.xx.task_type;
                        });

                    // Filter Record Active
                    result = result.filter(task => {
                        return task.active == this.xx.active_record;
                    });

                    return result;
                },
                filteredListCount() {
                    return this.filteredList.length;
                },
            },
            filters: {
                formatDate: function (date) {
                    return (date) ? moment(date).format('DD/MM/YYYY') : '';
                },
                max15chars: function (str) {
                    return str.substring(0, 15);
                },
            },
            methods: {
                getTodo: function () {
                    this.xx.spinner = true;
                    setTimeout(function () {
                        this.xx.load_plan = true;
                        $.getJSON('/manage/report/todo/tasks', function (data) {
                            this.xx.list = data[0];
                            this.xx.sel_assigned_tasks = data[1];
                            this.xx.sel_assigned_cc = data[2];
                            this.xx.sel_task_types = data[3];
                            this.xx.sel_active_record = data[4];
                            this.xx.sel_users_cc = data[5];
                            this.xx.sel_users_ext = data[6];
                            this.xx.sel_users_all = data[7];
                            this.xx.sel_users = data[5];
                            this.xx.spinner = false;
                        }.bind(this));
                    }.bind(this), 100);
                },
                sortBy: function (sortKey) {
                    this.xx.sortOrder = (this.xx.sortKey == sortKey) ? this.xx.sortOrder * -1 : 1;
                    this.xx.sortKey = sortKey;
                },
                dueDateColour: function (task) {
                    if (moment(task.due_at).isBefore(this.xx.today))
                        return 'font-red';
                },
                updateUserList: function () {
                    if (this.xx.assigned_cc == 1)
                        this.xx.sel_users = this.xx.sel_users_cc;
                    if (this.xx.assigned_cc == 0)
                        this.xx.sel_users = this.xx.sel_users_ext;
                    if (this.xx.assigned_cc == 'all')
                        this.xx.sel_users = this.xx.sel_users_all;
                },
                lastUpdateColour: function (task) {
                    /*if (moment(task.lastupdated).isBefore(this.xx.days28past))
                        return 'font-red';
                    if (moment(task.lastupdated).isBefore(this.xx.days7past))
                        return 'font-yellow-gold';
                     */
                },
                doNothing: function () {
                    // empty function
                },
            },
        });

        var myApp = new Vue({
            el: 'body',
            data: {xx: xx},
        });
    </script>
@stop