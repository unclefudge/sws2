@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.accident'))
            <li><a href="/site/accident">Site Accidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Accident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="m-heading-1 border-green m-bordered" style="margin: 0 0 20px;">
            <h3>{{ $accident->site->name }}
                <small>(Site: {{ $accident->site->code }})</small>
            </h3>
            <p>{{ $accident->site->address }}, {{ $accident->site->suburb }}</p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-file-text-o "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Accident Report</span>
                            <span class="caption-helper"> ID: {{ $accident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteAccidentController::class, 'update'], $accident->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    <div class="col-md-8">
                                        <table class="table col2-table">
                                            <tr>
                                                <th style="width:150px">Completed by:</th>
                                                <td>{{ $accident->createdBy->fullname }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date:</th>
                                                <td>{{ $accident->created_at->format('d/m/y g:i a') }}</td>
                                            </tr>
                                            @if(($accident->created_by == $accident->updated_by) && ($accident->created_at != $accident->updated_at))
                                                <tr>
                                                    <th>Updated:</th>
                                                    <td>{{ $accident->updated_at->format('d/m/y g:i a') }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="status" id="status" value="1" class="make-switch"
                                                   @if($accident->status) checked @endif
                                                   data-on-text="Open" data-on-color="success"
                                                   data-off-text="Closed" data-off-color="danger"
                                                   @if(!Auth::user()->allowed2('del.site.accident', $accident)) readonly @endif>
                                            <p class="myswitch-label" style="font-size: 14px;">&nbsp; Status </p>
                                        </div>
                                    </div>
                                </div>
                                @if(!$accident->status)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="font-red uppercase" style="margin:0 0 10px 15px;">
                                                <span>Accident Closed {{ $accident->resolved_at->format('d/m/Y') }}</span>
                                            </h4>
                                        </div>
                                    </div>
                            @endif
                        </div>

                        <h3 class="form-section" style="margin-top: 0px">Report</h3>

                        <div class="row">
                            <div class="col-md-6">
                                @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                    <x-form.select name="site_id" label="Site" :options="Auth::user()->company->sitesSelect('prompt')" :value="$accident->site_id"/>
                                @else
                                    <x-form.hidden name="site_id" :value="$accident->site_id"/>
                                    <x-form.input name="site_name" label="Site" :value="$accident->site->name" disabled/>
                                @endif
                            </div>
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date" class="control-label">Date / Time of Incident</label>
                                    @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                        <div class="input-group date form_datetime">
                                            <input type="text" name="date" id="date" class="form-control" value="{{ old('date', $accident->date->format('d F Y - H:i')) }}" readonly style="background:#FFF">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    @else
                                        <input type="text" name="date" id="date" class="form-control" value="{{ $accident->date->format('d F Y - H:i') }}" readonly>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.input name="supervisor" label="Supervisor" :value="$accident->supervisor" readonly/>
                            </div>
                        </div>


                        <h4 class="font-green-haze">Workers details</h4>
                        <!-- Name / Age / Occupation -->
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.input name="name" label="Name" :value="$accident->name" readonly/>
                            </div>
                            <div class="col-md-3">
                                <x-form.input name="company" label="Company" :value="$accident->company" readonly/>
                            </div>
                            <div class="col-md-2">
                                <x-form.input name="age" label="Age" :value="$accident->age" readonly/>
                            </div>
                            <div class="col-md-4">
                                <x-form.input name="occupation" label="Occupation" :value="$accident->occupation" readonly/>
                            </div>
                        </div>

                        <h4 class="font-green-haze">Incident details</h4>
                        <!-- Location + Nature -->
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.textarea name="location" label="Location of Incident (be specific)" rows="2" :value="$accident->location" readonly/>
                            </div>
                            <div class="col-md-6">
                                <x-form.textarea name="nature" label="Nature of Injury / Illness" rows="2" :value="$accident->nature" readonly/>
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12">
                                <x-form.textarea name="info" label="Description of Incident (describe in detail)" rows="3" :value="$accident->info" readonly/>
                            </div>
                        </div>
                        <!-- Damage / Referred -->
                        <div class="row">
                            <div class="col-md-8">
                                <x-form.input name="damage" label="Damage to Equipment / Property" :value="$accident->damage" readonly/>
                            </div>
                            <div class="col-md-4">
                                @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                    <x-form.select name="referred" label="Referred / Transferred to" :options="['' => 'Select option', 'Hospital' => 'Hospital', 'Doctors' => 'Doctors', 'Home' => 'Home', 'Continued Work' => 'Continued Work', 'Other' => 'Other']" :value="$accident->referred"/>
                                @else
                                    <x-form.input name="referred" label="Referred / Transferred to" :value="$accident->referred" readonly/>
                                @endif
                            </div>
                        </div>
                        <!-- Preventative Action -->
                        <div class="row">
                            <div class="col-md-12">
                                <x-form.textarea name="action" label="Recommended Preventative Action" rows="3" :value="$accident->action" readonly/>
                            </div>
                        </div>

                        @if(Auth::user()->allowed2('edit.site.accident', $accident))
                            <hr>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <app-actions :table_id="{{ $accident->id }}"></app-actions>
                                </div>
                            </div>

                            {{-- ToDos--}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Assigned Tasks
                                        {{-- Show add if user has permission to edit hazard --}}
                                        @if ($accident->status && Auth::user()->allowed2('edit.site.accident', $accident) && Auth::user()->isCompany($accident->owned_by->id))
                                            <a href="/todo/create/accident/{{ $accident->id}}" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</a>
                                        @endif
                                    </h3>
                                    @if ($accident->todos()->count())
                                        <table class="table table-striped table-bordered table-nohover order-column">
                                            <thead>
                                            <tr class="mytable-header">
                                                <th style="width:5%">#</th>
                                                <th> Action</th>
                                                <th style="width:15%">Created by</th>
                                                <th style="width:15%">Completed by</th>
                                                <th style="width:5%"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($accident->todos() as $todo)
                                                <tr>
                                                    <td>
                                                        <div class="text-center"><a href="/todo/{{ $todo->id }}"><i class="fa fa-search"></i></a></div>
                                                    </td>
                                                    <td>
                                                        {{ $todo->info }}<br><br><i>Assigned to: {{ $todo->assignedToBySBC() }}</i>
                                                        @if ($todo->comments)
                                                            <br><b>Comments:</b> {{ $todo->comments }}
                                                        @endif
                                                    </td>
                                                    <td>{!! App\User::findOrFail($todo->created_by)->full_name  !!}<br>{{ $todo->created_at->format('d/m/Y')}}</td>
                                                        <?php
                                                        $done_by = App\User::find($todo->done_by);
                                                        $done_at = ($done_by) ? $todo->done_at->format('d/m/Y') : '';
                                                        $done_by = ($done_by) ? $done_by->full_name : 'unknown';
                                                        ?>
                                                    <td>@if ($todo->status && !$todo->done_by)
                                                            <span class="font-red">Outstanding</span>
                                                        @else
                                                            {!! $done_by  !!}<br>{{ $done_at }}
                                                        @endif</td>
                                                    <td>
                                                        @if ($todo->attachment)
                                                            <a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"></i></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            {{-- Additional Information block removed/unused --}}
                            <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $accident->id }}">
                            <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $accident->status }}">
                            <input v-model="xx.record_resdate" type="hidden" id="record_resdate" value="{{ $accident->resolved_at }}">
                            @if (Auth::user()->isCompany($accident->site->company_id))
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes" rows="3" :value="$accident->notes" @if(!$accident->status) readonly @endif/>
                                        <span class="help-block"> Only viewable by parent company</span>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                <a href="/site/accident" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('edit.site.accident', $accident))
                                    @if($accident->status || Auth::user()->allowed2('del.site.accident', $accident))
                                        <button type="submit" class="btn green"> Save</button>
                                    @endif
                                @endif
                            </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $accident->displayUpdatedBy() !!}
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $accident->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit hazard --}}
                        <button v-show="xx.record_status == '1'" v-on:click.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:10%">Date</th>
                            <th> Action</th>
                            <th style="width:20%"> Name</th>
                            <th style="width:5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                                <td>
                                    <!--<button v-show="xx.record_status != 0" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm>"> Assign Task</span>
                                    </button>-->
                                    <!--
                                    <button v-show="action.created_by == xx.created_by" v-on:click.prevent="$root.$broadcast('edit-action-modal', action)"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                    -->
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                    -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <!--<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>-->
    <script src="/js/libs/moment.min.js" type="text/javascript"></script>

    <!-- Vue -->
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script>
        Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var host = window.location.hostname;
        var dev = true;
        if (host == 'safeworksite.com.au')
            dev = false;

        var xx = {
            dev: dev,
            action: '', loaded: false,
            table_name: 'site_accidents', table_id: '', record_status: '', record_resdate: '',
            created_by: '', created_by_fullname: '',
        };

        Vue.component('app-actions', {
            template: '#actions-template',
            props: ['table', 'table_id', 'status'],

            created: function () {
                this.getActions();
            },
            data: function () {
                return {xx: xx, actionList: []};
            },
            events: {
                'addActionEvent': function (action) {
                    this.actionList.unshift(action);
                },
            },
            methods: {
                getActions: function () {
                    $.getJSON('/action/' + this.xx.table_name + '/' + this.table_id, function (actions) {
                        this.actionList = actions;
                    }.bind(this));
                },
            },
        });

        Vue.component('ActionModal', {
            template: '#actionModal-template',
            props: ['show'],
            data: function () {
                var action = {};
                return {xx: xx, action: action, oAction: ''};
            },
            events: {
                'add-action-modal': function () {
                    var newaction = {};
                    this.oAction = '';
                    this.action = newaction;
                    this.xx.action = 'add';
                    this.show = true;
                },
                'edit-action-modal': function (action) {
                    this.oAction = action.action;
                    this.action = action;
                    this.xx.action = 'edit';
                    this.show = true;
                }
            },
            methods: {
                close: function () {
                    this.show = false;
                    this.action.action = this.oAction;
                },
                addAction: function (action) {
                    var actiondata = {
                        action: action.action,
                        table: this.xx.table_name,
                        table_id: this.xx.table_id,
                        niceDate: moment().format('DD/MM/YY'),
                        created_by: this.xx.created_by,
                        fullname: this.xx.created_by_fullname,
                    };

                    console.log(actiondata);
                    this.$http.post('/action', actiondata)
                        .then(function (response) {
                            toastr.success('Created new action ');
                            actiondata.id = response.data.id;
                            this.$dispatch('addActionEvent', actiondata);
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed adding new action');
                        });

                    this.close();
                },
                updateAction: function (action) {
                    this.$http.patch('/action/' + action.id, action)
                        .then(function (response) {
                            toastr.success('Saved Action');
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed to save action [' + action.id + ']');
                        });
                    this.show = false;
                },
            }
        });

        var myApp = new Vue({
            el: 'body',
            data: {xx: xx},
        });

    </script>
@stop

