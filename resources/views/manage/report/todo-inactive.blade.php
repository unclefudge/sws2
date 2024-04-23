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
                            <span class="caption-subject font-green-haze bold uppercase">ToDo Tasks Assigned to Inactive Users</span>
                            <small> &nbsp;Note: Toolbox, SWMS & Company/User Docs Tasks are excluded</small>
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
                <select-picker :name.sync="xx.assigned_cc" :options.sync="xx.sel_assigned_cc" :function="updateUserList"></select-picker>
            </div>
            <div class="col-md-3">
                <select-picker :name.sync="xx.username" :options.sync="xx.sel_users" :function="doNothing"></select-picker>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-4"><input v-model="xx.search" type="text" class="form-control" placeholder="Search"></div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-8">
            </div>
            <div class="col-md-2">
                <button v-on:click="reassignTasks" class="btn blue">Reassign selected tasks</button>
            </div>
            <div class="col-md-2">
                <button v-on:click="deleteTasks" class="btn btn-danger">Delete selected tasks</button>
            </div>
        </div>

        <div class="row" style="margin-top: 0px; padding-top: 0px">
            <div class="col-md-12">
                <span class="pull-right" style="margin-top: 20px"><b>Tasks: @{{ filteredListCount }}</b></span>
                <table v-show="xx.list.length" class="table table-striped table-bordered table-nohover order-column">
                    <thead>
                    <tr class="mytable-header">
                        <th></th>
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
                        <tr> {{--   v-if="verifyShow(task)"   filterAssigned xx.assigned_tasks | --}}
                            <td>
                                <input v-model="task.checked" type="checkbox" name="checked">
                            </td>
                            <td class="hoverDiv" v-on:click="task.expand = !task.expand">@{{ task.title }}</td>
                            <td class="hoverDiv" v-on:click="task.expand = !task.expand">@{{ task.assigned_names }}</td>
                            <td class="hoverDiv" v-on:click="task.expand = !task.expand">@{{ task.due_at | formatDate}}</td>
                            <td class="hoverDiv" v-on:click="task.expand = !task.expand">@{{ task.lastupdated_human}}</td>
                            {{--}}<td class="@{{ lastUpdateColour(task)  }}">@{{ task.lastupdated | formatDate}}</td>--}}
                        </tr>
                        <tr v-if="task.expand">
                            <td colspan="5" style="width: 100%; background: #333; color: #fff">
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

        {{--  Reassign  Modal --}}
        <confirm-Reassign :show.sync="xx.reassignModal" effect="fade" class="modal fade bs-modal-lg" header="Ra-assign Tasks">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Re-assign Tasks</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-12">This action will re-assign all the selected tasks to another user.<br><br></div>
                </div>
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-3">Assign To</div>
                    <div class="col-md-9">
                        {!! Form::select('assignto', Auth::user()->company->reportsTo()->usersSelect('prompt', 1), null, ['class' => 'form-control select2', 'title' => 'Select user', 'id' => 'assignto']) !!}
                    </div>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn dark btn-outline" v-on:click="xx.reassignModal = false">Cancel</button>
                <button type="button" class="btn green" v-on:click="reassignItems()">&nbsp; Save &nbsp;</button>
            </div>
        </confirm-Reassign>

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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-app-basic-functions.js"></script>
    <script src="/js/vue-modal-component.js"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#assignto").select2({placeholder: "Select user", width: '100%'});
        });
    </script>

    <script>

        Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        var xx = {
            record: {},
            dev: dev, spinner: false, assigned_tasks: 1, assigned_cc: 1, task_type: 'all', username: 'all', action: '', tasks_found: 0, status: 0,
            reassignModal: false, assign_to: '',
            sortKey: 'title', sortOrder: 1, search: '',
            today: moment().format('YYYY-MM-DD'), days7past: moment().subtract(7, 'days').format('YYYY-MM-DD'), days28past: moment().subtract(28, 'days').format('YYYY-MM-DD'),
            list: [], sel_assigned_tasks: [], sel_assigned_cc: [], sel_task_types: [], sel_action: [], sel_users_cc: [], sel_users_ext: [], sel_users_all: [], sel_users: [],
            sel_users_active: [],
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
                    //if (this.xx.task_type != 'all')
                    //    result = result.filter(task => {
                    //        return task.type == this.xx.task_type;
                    //    });

                    // Filter Record Active
                    //result = result.filter(task => {
                    //    return task.active == this.xx.active_record;
                    //});

                    return result;
                },
                filteredListCount() {
                    return this.filteredList.length;
                },
            },
            components: {
                confirmReassign: VueStrap.modal,
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
                        $.getJSON('/manage/report/todo_inactive/tasks', function (data) {
                            this.xx.list = data[0];
                            this.xx.sel_assigned_tasks = data[1];
                            this.xx.sel_assigned_cc = data[2];
                            this.xx.sel_task_types = data[3];
                            this.xx.sel_action = data[4];
                            this.xx.sel_users_cc = data[5];
                            this.xx.sel_users_ext = data[6];
                            this.xx.sel_users_all = data[7];
                            this.xx.sel_users_active = data[8];
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
                doNothing: function () {
                    // empty function
                },
                reassignTasks: function () {
                    this.xx.reassignModal = true;
                },
                deleteTasks: function () {
                    //this.xx.confirmDeleteModal = true;

                    var tasklist = this.xx.list;

                    swal({
                        title: "Are you sure?",
                        text: 'You will not be able to recover these tasks',
                        showCancelButton: true,
                        cancelButtonColor: "#555555",
                        confirmButtonColor: "#E7505A",
                        confirmButtonText: "Yes, delete them!",
                        allowOutsideClick: true,
                        html: true,
                    }, function () {
                        let checked = tasklist.filter((t) => {
                            return t.checked == true
                        });
                        //console.log(checked);
                        checkedList = [];
                        if (checked.length) {
                            for (var i = 0; i < checked.length; i++) {
                                checkedList.push(checked[i].id);
                            }
                            //console.log(checkedList);
                            $.ajax({
                                url: '/manage/report/todo_inactive/delete',
                                type: 'POST',
                                data: {checkedList: checkedList},
                                success: function (result) {
                                    console.log('deleted tasks');
                                    window.location.href = "/manage/report/todo_inactive";
                                },
                                error: function (result) {
                                    alert("Failed to delete tasks");
                                    console.log('Failed to delete tasks');
                                }
                            });
                        }
                    });
                },
                reassignItems: function () {
                    var assign = $('#assignto').val();

                    if (assign) {
                        let checked = this.xx.list.filter((t) => {
                            return t.checked == true
                        });

                        checkedList = [];
                        if (checked.length) {
                            for (var i = 0; i < checked.length; i++) {
                                checkedList.push(checked[i].id);
                            }
                            console.log(checkedList);
                            $.ajax({
                                url: '/manage/report/todo_inactive/reassign',
                                type: 'POST',
                                data: {checkedList: checkedList, assign_to: assign},
                                success: function (result) {
                                    console.log('reassigned tasks');
                                    window.location.href = "/manage/report/todo_inactive";
                                },
                                error: function (result) {
                                    alert("Failed to delete tasks");
                                    console.log('Failed to delete tasks');
                                }
                            });
                        }
                    } else {
                        alert('You need to select a user to assign the tasks to')
                    }

                },
                checkedList: function () {

                }
            },
        });

        var myApp = new Vue({
            el: 'body',
            data: {xx: xx},
        });
    </script>
@stop