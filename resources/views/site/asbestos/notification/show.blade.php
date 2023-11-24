@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/notification">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
    </ul>
@stop

<style>
    @media screen and (min-width: 992px) {
        .datepicker-input {
            width: 200px !important;
        }
    }

    @media screen and (min-width: 1200px) {
        .datepicker-input {
            width: 250px !important;
        }
    }
</style>

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
                        {!! Form::model($asb, ['method' => 'PATCH', 'action' => ['Site\SiteAsbestosController@updateExtra', $asb->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

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
                                    <b>Job #:</b> {{ $asb->site->code }}<br>
                                    <b>Supervisor:</b> {{ $asb->site->supervisorName }}<br>
                                </div>
                            </div>
                            <hr>

                            {{-- Client + Supervisor Details --}}
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Client name + phone--}}
                                    <h4 class="font-green-haze">Individual (Client) Details</h4>
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Name:</b></div>
                                        <div class="col-md-8">{{ $asb->client_name }}</div>
                                        <div class="col-md-4"><b>Phone:</b></div>
                                        <div class="col-md-8">{{ $asb->client_phone }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Supervisor name + phone--}}
                                    <h4 class="font-green-haze">Contact Person (Supervisor) Details</h4>
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Name:</b></div>
                                        <div class="col-md-8">{{ ($asb->supervisor) ? $asb->supervisor->name : '' }}</div>
                                        <div class="col-md-4"><b>Phone:</b></div>
                                        <div class="col-md-8">{{ $asb->super_phone }}</div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            {{-- Site Details--}}
                            <h4 class="font-green-haze">Site Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Site Name:</b></div>
                                        <div class="col-md-8">{{ $asb->site->name }}</div>
                                    </div>
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Address:</b></div>
                                        <div class="col-md-8">{{ $asb->site->fulladdress }}</div>
                                    </div>
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Workplace type:</b></div>
                                        <div class="col-md-8">{{ $asb->workplace }}</div>
                                    </div>
                                    {{-- Open Hours --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Opening hours: </b></div>
                                        <div class="col-md-8">{{ $asb->hours_from }} to {{ $asb->hours_to }}</div>
                                    </div>
                                    {{-- Start --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Proposed start:</b></div>
                                        <div class="col-md-8">{{ $asb->date_from->format('d/m/Y') }}</div>
                                    </div>
                                    {{-- Finish --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Proposed finish:</b></div>
                                        <div class="col-md-8">{{ $asb->date_to->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Workers --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Number of workers: </b></div>
                                        <div class="col-md-8">{{ $asb->workers }}</div>
                                    </div>
                                    {{-- Coal / Mining --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Coal/mining workplace: </b></div>
                                        <div class="col-md-8">{{ ($asb->coalmine) ? 'Yes' : 'No' }}</div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <h4 class="font-green-haze">Asbestos Identification &nbsp;
                                <small>(Applicable to Friable / Asbestos in soils)</small>
                            </h4>
                            {{-- Hydiene Report --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Hygienist report:</b></div>
                                <div class="col-md-10">{!! ($asb->hygiene) ? "Yes &nbsp; ($asb->hygiene_report)" : 'No' !!}</div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Assessor Contact Details</h5>
                                    <hr style="padding: 0px; margin: 0px">
                                    {{-- Assessor Name --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Name:</b></div>
                                        <div class="col-md-8">{{ $asb->assessor_name }}</div>
                                    </div>

                                    {{-- Assessor Phone --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Phone:</b></div>
                                        <div class="col-md-8">{{ $asb->assessor_phone }}</div>
                                    </div>

                                    {{-- Assessor --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Licence No.:</b></div>
                                        <div class="col-md-8">{{ $asb->assessor_lic }}</div>
                                    </div>

                                    {{-- Assessor --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>Department:</b></div>
                                        <div class="col-md-8">{{ $asb->assessor_dept }}</div>
                                    </div>

                                    {{-- Report --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-4"><b>State:</b></div>
                                        <div class="col-md-8">{{ $asb->assessor_state }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Testing Laboratory</h5>
                                    <hr style="padding: 0px; margin: 0px">
                                </div>
                            </div>
                            <br>


                            <h4 class="font-green-haze">Asbestos Details &nbsp;
                                <small>(Type / Location / Amount)</small>
                            </h4>
                            {{-- Class--}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Asbestos Class:</b></div>
                                <div class="col-md-10">{{ ($asb->friable) ? 'Class A (Friable)' : 'Class B (Non-Friable)' }}</div>
                            </div>
                            {{-- Amount --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Amount:</b></div>
                                <div class="col-md-10">{{ $asb->amount }} (m2)</div>
                            </div>
                            {{-- Location --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Location:</b></div>
                                <div class="col-md-10">{{ $asb->location }}</div>
                            </div>
                            {{-- Type --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Type:</b></div>
                                <div class="col-md-10">{{ $asb->type }}</div>
                            </div>
                            {{-- Removalist --}}
                            <div class="row" style="line-height: 2">
                                <div class="col-md-2"><b>Licensed Asbestos Removalist:</b></div>
                                <div class="col-md-10">{{ $asb->removalist_name }}</div>
                            </div>


                            {{-- Asbestos Removal --}}
                            @if(!$asb->friable)
                                <br><h4 class="font-green-haze">Protective Equipment and Isolation / Encapsulation</h4>
                                <div class="row" style="line-height: 2">
                                    {{-- Equipment --}}
                                    <div class="col-md-2"><b>Personal equipment: </b></div>
                                    <div class="col-md-10">{!! $asb->equipment('SBC') !!}</div>
                                </div>
                                <div class="row" style="line-height: 2">
                                    {{-- Methods --}}
                                    <div class="col-md-2"><b>Isolate / Enclose: </b></div>
                                    <div class="col-md-10">{!! $asb->methods('SBC') !!}</div>
                                </div>
                                <div class="row" style="line-height: 2">
                                    {{-- Extent --}}
                                    <div class="col-md-2"><b>Extent of isolation: </b></div>
                                    <div class="col-md-10">{{ $asb->isolation }}</div>
                                </div>
                            @endif


                            {{-- Additional Details --}}
                            <br><h4 class="font-green-haze">Additional Infomation</h4>
                            @if(!$asb->friable)
                                {{-- Register --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Asbestos Register: </b></div>
                                    <div class="col-md-10">{{ ($asb->register) ? 'Yes - Asbestos Register was reviewed' : 'An Asbestos Register was not available for this site' }}</div>
                                </div>
                            @endif
                            <br>
                            @if (Auth::user()->allowed2('edit.site.asbestos', $asb))
                                @if(!$asb->friable)
                                    <div class="row">
                                        {{-- Safe Work Notification --}}
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('safework', 'Safe Work Notification', ['class' => 'control-label']) !!}
                                                <?php
                                                $safe_at = ($asb->safework_at) ? $asb->safework_at->format('d/m/Y') : '';
                                                $lodged = "Lodged"; //($asb->safework == 2 && $asb->safework_at) ? "Lodged - $safe_at" : 'Lodged';
                                                $accept = "Accepted"; //($asb->safework == 1 && $asb->safework_at) ? "Accepted - $safe_at" : 'Accepted';
                                                ?>
                                                {!! Form::select('safework', ['' => 'Not lodged', '2' => $lodged, '1' => $accept], null, ['class' => 'form-control bs-select']) !!}
                                            </div>
                                        </div>
                                        {{-- Safe Work Ref# --}}
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('safework', 'Safe Work Reference', ['class' => 'control-label']) !!}
                                                {!! Form::text('safework_ref', null, ['class' => 'form-control']) !!}
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        {{-- Supervisor Form --}}
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                {!! Form::label('safework', 'Supervisor form sent', ['class' => 'control-label']) !!}
                                                <br class="col-md-2 visible-sm visible-xs">
                                                <datepicker :value.sync="xx.supervisor_at" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                            </div>
                                            <input v-model="xx.supervisor_at" type="hidden" name="supervisor_at" value="{{  ($asb->supervisor_at) ? $asb->supervisor_at->format('d/m/Y') : ''}}">
                                        </div>
                                        {{-- Neighbour Form --}}
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                {!! Form::label('safework', 'Neighbours form sent', ['class' => 'control-label']) !!}
                                                <br class="col-md-2 visible-sm visible-xs">
                                                <datepicker :value.sync="xx.neighbours_at" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                            </div>
                                            <input v-model="xx.neighbours_at" type="hidden" name="neighbours_at" value="{{  ($asb->neighbours_at) ? $asb->neighbours_at->format('d/m/Y') : ''}}">
                                        </div>
                                    </div>
                                    <br>
                                @endif


                                <div class="row">
                                    {{-- Removal Date --}}
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            {!! Form::label('safework', 'Removal Date', ['class' => 'control-label']) !!}
                                            <br class="col-md-2 visible-sm visible-xs">
                                            <datepicker :value.sync="xx.removal_at" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                        </div>
                                        <input v-model="xx.removal_at" type="hidden" name="removal_at" value="{{  ($asb->removal_at) ? $asb->removal_at->format('d/m/Y') : ''}}">
                                    </div>
                                    {{-- Register Updated Date --}}
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            {!! Form::label('safework', 'Register Updated', ['class' => 'control-label']) !!}
                                            <br class="col-md-2 visible-sm visible-xs">
                                            <datepicker :value.sync="xx.reg_updated_at" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                        </div>
                                        <input v-model="xx.reg_updated_at" type="hidden" name="reg_updated_at" value="{{  ($asb->reg_updated_at) ? $asb->reg_updated_at->format('d/m/Y') : ''}}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn blue" style="margin-top: 25px"> Save</button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {!! Form::close() !!}

                        {{-- Actions --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $asb->id }}"></app-actions>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/asbestos/notification" class="btn default"> Back</a>
                            @if(Auth::user()->allowed2('del.site.asbestos', $asb))
                                @if ($asb->status)
                                    @if(Auth::user()->allowed2('edit.site.asbestos', $asb))
                                        <a href="/site/asbestos/notification/{{ $asb->id }}/edit" class="btn green"> Edit Notification</a>
                                    @endif
                                    <a href="/site/asbestos/notification/{{ $asb->id }}/status/0" class="btn red"> Close Notification</a>
                                @else
                                    <a href="/site/asbestos/notification/{{ $asb->id }}/status/1" class="btn green"> Re-open Notification</a>
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
                            <button v-show="xx.record_status == '1'" v-on:click.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
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
<script src="/js/libs/vue-strap.min.js"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script src="/js/vue-app-basic-functions.js"></script>
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
        supervisor_at: '', neighbours_at: '', removal_at: '', reg_updated_at: '',
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
        components: {
            datepicker: VueStrap.datepicker,
        },
    });

</script>
@stop

