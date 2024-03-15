@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Supervisors</span></li>
    </ul>
@stop

@section('content')
    <style>
        .aside {
            z-index: 9999;
        }
    </style>

    <app-supers></app-supers>

    <!-- loading Spinner -->
    <div v-show="xx.showSpinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    <template id="supers-template">
        <input v-model="xx.user_company_id" type="hidden" value="{{ Auth::user()->company_id }}">
        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title tabbable-line">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Supervisor List</span>
                            </div>
                            <div class="actions">
                                <div class="actions">
                                    @if (Auth::user()->hasPermission2('edit.area.super'))
                                        <a class="btn btn-circle green btn-outline btn-sm" href="javascript:;" v-on:click="openSidebar(0)">Add</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="note note-warning">
                                <p>An Area Supervisor (ie. senior supervisor of another) is granted access to the sites of all the supervisors under them.</p>
                                @if (Auth::user()->isCC())
                                    <p><br>In regards to Quality Assurance Reports they will be:</p>
                                    <ul>
                                        <li>granted ability to Sign Off as Site Manager</li>
                                        <li>notified of overdue QA tasks associated with their sites</li>
                                    </ul>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table v-show="xx.supers.length" class="table table-striped table-bordered table-hover order-column">
                                        <thead>
                                        <tr class="mytable-header">
                                            <th width="3%"></th>
                                            <th width="50%"><a href="#" v-on:click="sortBy('name')"> Name </a></th>
                                            <th width="5%">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Area Supervisors -->
                                        <template v-for="sup in xx.supers | orderBy xx.sortKey xx.sortOrder">
                                            <tr v-if="!sup.parent_id">
                                                <!-- Open / close Icon -->
                                                <td class="text-center">
                                                    <div v-if="hasSubSuper(sup.id)">
                                                        <span v-if="sup.open" v-on:click="sup.open = ! sup.open" class="finger"><i class="fa fa-minus-circle" style="color: #e7505a;"></i></span>
                                                        <span v-if="!sup.open" v-on:click="sup.open = ! sup.open" class="finger"><i class="fa fa-plus-circle" style="color: #32c5d2;"></i></span>
                                                    </div>
                                                </td>
                                                <!-- Super Name -->
                                                <td>@{{ sup.name }}</td>
                                                <td>
                                                    @if (Auth::user()->hasPermission2('edit.area.super'))
                                                        <span v-on:click="delAreaSuper(sup)" class="finger"><i class="fa fa-trash" style=""></i></span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Hideable dropdown of Child Supers -->
                                            <tr v-if="sup.open && hasSubSuper(sup.id)" style="background-color: #444D58" class="nohover">
                                                <td colspan="3">
                                                    <table class="table table-striped table-hover order-column" style="margin-bottom: 10px; margin-top: 5px; width: 300px">
                                                        <tbody>
                                                        <template v-for="sup_child in xx.supers">
                                                            <tr v-if="sup_child.parent_id == sup.id">
                                                                <td>@{{ sup_child.name }}</td>
                                                                <td width="5%" class="text-center">
                                                                    @if (Auth::user()->hasPermission2('edit.area.super'))
                                                                        <span v-on:click="delSuper(sup_child)" class="finger"><i class="fa fa-trash" style=""></i></span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </template>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                    <p v-else>No Supervisors !</p>
                                </div>
                            </div>

                            <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                            -->

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--
           Sidebar for editing entity
           -->
        <sidebar :show.sync="xx.showSidebar" placement="right" header="Supervisor Management" :width="350">
            <h3 style="margin: 0px">Add Supervisor</h3>
            <hr style="margin: 10px 0px">
            <h5>Employee</h5>
            <!-- Select Staff options -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <select-picker :name.sync="xx.supervisor.user_id" :options.sync="xx.sel_staff" :function="doNothing"></select-picker>
                </div>
            </div>
            <h5>Area Supervisor</h5>
            <!-- Select Senior Supervisor options -->
            <div class="row" style="padding-bottom: 10px">
                <div class="col-xs-12">
                    <select-picker :name.sync="xx.supervisor.parent_id" :options.sync="xx.sel_area_supers" :function="doNothing"></select-picker>
                </div>
            </div>
            <br>
            <button class="btn default" v-on:click="xx.showSidebar = false">cancel</button>
            <button class="btn btn-success" v-on:click="addSuper(xx.supervisor)" :disabled="xx.supervisor.user_id == 0">save</button>

            <br><br>
            <hr>
        </sidebar>
    </template>

@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-app-basic-functions.js"></script>
    <script type="text/javascript">

        var xx = {
            dev: dev, permission: '', user_company_id: '',
            showSpinner: false, showSidebar: false, showModal: false,
            sortKey: 'name', sortOrder: 0, //super: '', super_parent: '',
            supervisor: {user_id: '', parent_id: ''},
            supers: [], sel_area_supers: [], sel_staff: [],
        };

        Vue.component('app-supers', {
            template: '#supers-template',

            created: function () {
                this.getSupers();
            },
            data: function () {
                return {xx: xx};
            },
            components: {
                sidebar: VueStrap.aside,
                datepicker: VueStrap.datepicker,
                modal: VueStrap.modal,
            },
            methods: {
                gotoURL: function (url) {
                    postAndRedirect(url, this.xx.params);
                },
                getSupers: function () {
                    // Get plan from database and initialise planner variables
                    setTimeout(function () {
                        this.xx.showSpinner = true;
                        this.xx.supers = [];
                        $.getJSON('/site/supervisor/data/supers/', function (data) {
                            this.xx.supers = data[0];
                            this.xx.sel_staff = data[1];
                            this.xx.showSpinner = false;
                            //this.xx.sel_area_supers = updateAreaSupers(this.xx.supers);
                            this.updateAreaSupers();
                        }.bind(this));
                    }.bind(this), 100);
                },
                openSidebar: function (super_id) {
                    // Open Jobstart sidebar and initialise data
                    this.xx.showSidebar = true;
                    this.xx.supervisor.user_id = "0";
                    if (super_id) {
                        this.xx.supervisor.parent_id = super_id.toString();
                    }

                },
                addSuper: function (supervisor) {
                    var staff = objectFindByKey(this.xx.sel_staff, 'value', supervisor.user_id);
                    var superdata = {
                        user_id: parseInt(supervisor.user_id), name: staff.text, parent_id: parseInt(supervisor.parent_id), company_id: parseInt(this.xx.user_company_id), open: false
                    };

                    if (this.verifySuper(superdata)) {
                        $.ajax({
                            url: '/site/supervisor',
                            type: 'POST',
                            data: superdata,
                            success: function (result) {
                                toastr.success('Added new supervisor ' + superdata.name);
                                superdata.id = result.id;
                                this.xx.supers.push(superdata);
                                //this.xx.sel_area_supers = updateAreaSupers(this.xx.supers);
                                this.updateAreaSupers();
                                // Open Area Supervisor list
                                if (supervisor.parent_id != '0') {
                                    var area_supervisor = objectFindByKey(this.xx.supers, 'id', supervisor.parent_id);
                                    area_supervisor.open = true;
                                }

                            }.bind(this),
                            error: function (result) {
                                toastr.error('Failed new supervisor ' + superdata.name);
                            }
                        });
                    }
                    this.xx.supervisor.user_id = "0";
                    this.xx.showSidebar = false;
                },
                delSuper: function (supervisor) {
                    supervisor._method = 'delete';
                    $.ajax({
                        url: '/site/supervisor/' + supervisor.id,
                        type: 'POST',
                        data: supervisor,
                        success: function (result) {
                            delete supervisor._method;
                            this.xx.supers.$remove(supervisor);
                            //this.xx.sel_area_supers = updateAreaSupers(this.xx.supers);
                            this.updateAreaSupers();
                            toastr.success('Deleted supervisor ' + supervisor.name);
                        }.bind(this),
                        error: function (result) {
                            toastr.error('Failed to delete supervisor ' + supervisor.name);
                        }
                    });
                },
                delAreaSuper: function (area_supervisor) {
                    swal({
                        title: "Are you sure?",
                        text: "This will also delete all supervisors under <b>" + area_supervisor.name + "</b>",
                        showCancelButton: true,
                        cancelButtonColor: "#555555",
                        confirmButtonColor: "#E7505A",
                        confirmButtonText: "Yes, delete it!",
                        allowOutsideClick: true,
                        html: true,
                    }, function () {
                        // Delete Area Supervisor + all sub supervisors
                        this.delSuper(area_supervisor);
                    }.bind(this));
                },
                verifySuper: function (supervisor) {
                    for (var i = 0; i < this.xx.supers.length; i++) {
                        // ensure supervisor isn't already an Area Supervisor
                        if (supervisor.parent_id == 0 && (this.xx.supers[i].user_id == supervisor.user_id && this.xx.supers[i].parent_id == 0)) {
                            toastr.error(supervisor.name + ' is already a Area Supervisor');
                            return false;
                        }
                        // ensure supervisor isn't already under the same Area Supervisor
                        if (this.xx.supers[i].user_id == supervisor.user_id && this.xx.supers[i].parent_id == supervisor.parent_id) {
                            toastr.error(supervisor.name + ' already exists');
                            return false;
                        }
                        // ensure supervisor isn't their own Area Supervisor
                        if (this.xx.supers[i].user_id == supervisor.user_id && this.xx.supers[i].id == supervisor.parent_id) {
                            toastr.error(supervisor.name + " can't be their own Area Supervisor");
                            return false
                        }

                    }
                    return true;
                },
                updateAreaSupers: function () {
                    this.xx.sel_area_supers = [{value: 0, text: 'No assigned Area Supervisor'}];
                    for (var i = 0; i < this.xx.supers.length; i++) {
                        if (this.xx.supers[i].parent_id == 0) {
                            this.xx.sel_area_supers.push({value: this.xx.supers[i].id, text: this.xx.supers[i].name});
                        }
                    }
                },
                hasSubSuper: function (super_id) {
                    for (var i = 0; i < this.xx.supers.length; i++) {
                        if (this.xx.supers[i].parent_id == super_id)
                            return true;
                    }
                    return false;
                },
                doNothing: function () {
                },
                sortBy: function (key) {
                    // toggles between 0 and -1 if sortKey is currently active
                    this.xx.sortOrder = (this.xx.sortKey == key) ? ~this.xx.sortOrder : '-1';
                    this.xx.sortKey = key;
                },
            },
        });

        var myApp = new Vue({
            el: 'body',
            data: {xx: xx},
        });

    </script>
@stop