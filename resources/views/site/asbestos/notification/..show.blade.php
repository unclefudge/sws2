@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-file-text-o "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Asbestos Notification</span>
                            <span class="caption-helper"> ID: {{ $asb->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $asb->id }}">
                        <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $asb->status }}">
                        <input v-model="xx.record_resdate" type="hidden" id="record_resdate" value="{{ $asb->resolved_at }}">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $asb->site->name }}</h2>
                                    {{ $asb->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$asb->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">CLOSED</h2>
                                    @endif
                                    <b>Site No:</b> {{ $asb->site->code }}<br>
                                    <b>Supervisor(s):</b> {{ $asb->site->supervisorsSBC() }}<br>
                                </div>
                            </div>
                            <hr>
                            <h4 class="font-green-haze">Asbestos Details</h4>
                            {{-- Dates / Hours --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-3"><b>Proposed dates of removal:</b></div>
                                <div class="col-xs-3">{{ $asb->date_from->format('d/m/Y') }} to {{ $asb->date_to->format('d/m/Y') }}</div>
                                <div class="col-md-6"><b>Opening hours: </b> &nbsp; {{ $asb->hours_from }} to {{ $asb->hours_to }}</div>
                            </div>
                            {{-- Amount --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-3"><b>Amount to be removed (m2):</b></div>
                                <div class="col-md-9">{{ $asb->amount }}</div>
                            </div>
                            {{-- Class--}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-3"><b>Asbestos Class:</b></div>
                                <div class="col-md-9">{{ ($asb->friable) ? 'Class A (Friable)' : 'Class B (Non-Friable)' }}</div>
                            </div>
                            {{-- Type --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-3"><b>Type:</b></div>
                                <div class="col-md-9">{{ $asb->type }}</div>
                            </div>
                            {{-- Location --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-3"><b>Specific Location of Asbestos:</b></div>
                                <div class="col-md-9">{{ $asb->location }}</div>
                            </div>
                            {{-- Asbestos Removal --}}
                            @if(!$asb->friable)
                                <br><h4 class="font-green-haze">Asbestos Removal</h4>
                                <div class="row" style="line-height: 2">
                                    {{-- Workers --}}
                                    <div class="col-md-3"><b>Number of workers: </b></div>
                                    <div class="col-md-9">{{ $asb->workers }}</div>
                                    {{-- Equipment --}}
                                    <div class="col-md-3"><b>Protective Equipment to be used: </b></div>
                                    <div class="col-md-9">{!! $asb->equipment('bullet') !!}</div>
                                    <div class="col-md-12" style="line-height: 1">&nbsp;</div>
                                    {{-- Methods --}}
                                    <div class="col-md-3"><b>Methods used to isolate: </b></div>
                                    <div class="col-md-9">{!! $asb->methods('bullet') !!}</div>
                                    <div class="col-md-12" style="line-height: 1">&nbsp;</div>
                                    {{-- Extent --}}
                                    <div class="col-md-3"><b>Extent of isolation: </b></div>
                                    <div class="col-md-9">{{ $asb->isolation }}</div>
                                    {{-- Extent --}}
                                    <div class="col-md-3"><b>Reviewed Asbestos Register: </b></div>
                                    <div class="col-md-9">{{ ($asb->register) ? 'Yes' : 'An Asbestos Register was not available for this site' }}</div>
                                </div>
                            @endif

                            {{-- Licensed Asbestos Removal (10m2) --}}
                            @if($asb->amount > 9 && !$asb->friable)
                                <br><h4 class="font-green-haze">Licensed Asbestos Removal (10m2)</h4>
                                <div class="row">
                                    {{-- Supervisor --}}
                                    <div class="col-md-3"><b>Asbestos Supervisor: </b></div>
                                    <div class="col-md-9">{!! \App\User::find($asb->supervisor_id)->fullname !!}</div>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $asb->id }}"></app-actions>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/asbestos" class="btn default"> Back</a>
                            @if(Auth::user()->allowed2('del.site.asbestos', $asb))
                                @if ($asb->status)
                                    @if(Auth::user()->allowed2('edit.site.asbestos', $asb))
                                        <a href="/site/asbestos/{{ $asb->id }}/edit" class="btn green"> Edit Notification</a>
                                    @endif
                                    <a href="/site/asbestos/{{ $asb->id }}/status/0" class="btn red"> Close Notification</a>
                                @else
                                    <a href="/site/asbestos/{{ $asb->id }}/status/1" class="btn green"> Re-open Notification</a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.report_id" type="hidden" id="report_id" value="{{ $asb->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit hazard --}}
                        @if (Auth::user()->allowed2('edit.site.asbestos', $asb))
                            <button v-show="xx.record_status == '1'" v-on:click="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                        @endif
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Action</th>
                            <th width="20%"> Name</th>
                            <!--<th width="5%"></th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                                <!--<td>
                                    <button v-show="action.created_by == xx.created_by" v-on:click="$root.$broadcast('edit-action-modal', action)" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                    <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                </td>-->
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')

    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/js/libs/moment.min.js" type="text/javascript"></script>

<!-- Vue -->
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script>
    Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

    var host = window.location.hostname;
    var dev = true;
    if (host == 'safeworksite.com.au')
        dev = false;

    var xx = {
        dev: dev,
        action: '', loaded: false,
        table_name: 'site_asbestos', table_id: '', record_status: '', record_resdate: '',
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
                this.actionList.push(action);
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

