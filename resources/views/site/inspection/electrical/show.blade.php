@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
            <li><a href="/site/inspection/electrical">Electrical Inspection Report</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Electrical Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                        <div class="actions">
                            @if($report->status == '0')
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/electrical/{{ $report->id }}/report" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            {!! Form::model($report, ['method' => 'POST', 'action' => ['Site\SiteInspectionElectricalController@signoff', $report->id], 'class' => 'horizontal-form']) !!}

                            <div class="row">
                                <div class="col-md-6"><h3 style="margin: 0px"> {{ $report->site->code }} - {{ $report->site->name }}</h3></div>
                                <div class="col-md-6">
                                    <h2 style="margin: 0px; padding-right: 20px">
                                        @if($report->status == '0')
                                            <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</small></span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                        @endif
                                        @if($report->status == '1')
                                            <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                            <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                        @endif
                                    </h2>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Job details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Inspection --}}
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Date</b></div>
                                        <div class="col-md-8">{{ ($report->inspected_at) ?  $report->inspected_at->format('d/m/Y g:i a') : '' }}</div>
                                    </div>
                                    <div class="row" style="padding: 0px 5px;">
                                        <div class="col-md-4">Inspection carried out by</div>
                                        <div class="col-md-8">{{ ($report->assignedTo) ? $report->assignedTo->name : '' }}<br>Licence No. {{ $report->inspected_lic }}</div>
                                    </div>
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Signature</b></div>
                                        <div class="col-md-8">{{ $report->inspected_name }}</div>
                                    </div>
                                </div>
                                {{-- Client --}}
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-2"><b>Client</b></div>
                                        <div class="col-md-10">{{ $report->client_name }}</div>
                                    </div>
                                    <div class="row" style="padding: 0px 5px;">
                                        <div class="col-md-2 hidden-sm hidden-xs">&nbsp;</div>
                                        <div class="col-md-10">{{ $report->client_address }}<br><br></div>
                                    </div>
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-2 hidden-sm hidden-xs">&nbsp;</div>
                                        <div class="col-md-10">Client contact was made: &nbsp; {{ ($report->client_contacted) ? 'Yes' : 'No' }}</div>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            @if ($report->info)
                            <h4 class="font-green-haze">Admin Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">{!! nl2br($report->info) !!}</div>
                            </div>
                            @endif

                            {{-- Gallery --}}
                            <br>
                            <div class="row" id="photos-show">
                                <div class="col-md-7">
                                    <h4>Photos</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_gallery')
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-4" id="docs-show">
                                    <h4>Documents</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_docs')
                                </div>
                            </div>


                            {{-- Existing --}}
                            @if ($report->existing)
                                <h4 class="font-green-haze">Condition of existing wiring</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                The existing wiring was found to be:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->existing) !!}</div>
                                </div>
                                <br>
                            @endif

                            {{-- Required --}}
                            @if ($report->required || $report->required_cost)
                                <h4 class="font-green-haze">Required work to meet compliance</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->required) !!}</div>
                                </div>
                                @if ($report->required_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->required_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Required --}}
                            @if ($report->recommend || $report->recommend_cost)
                                <h4 class="font-green-haze">Recommended works</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                Work not essential but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->recommend) !!}</div>
                                </div>
                                @if ($report->required_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->recommend_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Additional --}}
                            @if ($report->notes)
                                <h4 class="font-green-haze">Client Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->notes) !!}</div>
                                </div>
                            @endif

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <app-actions :table_id="{{ $report->id }}"></app-actions>
                                </div>
                            </div>

                            {{-- Sign Off --}}
                            <br>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><b>INSPECTION REPORT ELECTRONIC SIGN-OFF</b></h5>
                                    <p>The above report have been reviewed by the following people.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 text-right">Electrical Manager:</div>
                                <div class="col-sm-9">
                                    <div class="col-md-6">
                                        @if ($report->supervisor_sign_by)
                                            {!! \App\User::find($report->supervisor_sign_by)->full_name !!}, &nbsp;{{ $report->supervisor_sign_at->format('d/m/Y') }}
                                        @elseif($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report) && (Auth::user()->id == 1164 || Auth::user()->hasAnyRole2('web-admin|mgt-general-manager')))
                                            <div class="form-group {!! fieldHasError('approve_version', $errors) !!}">
                                                {!! Form::select('supervisor_sign_by', ['' => 'Do you approve this inspection report', 'n' => 'No', 'y' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'supervisor_sign_by']) !!}
                                            </div>
                                        @else
                                            <span class="font-red">Pending Sign Off</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 text-right">Construction Manager:</div>
                                <div class="col-sm-9">
                                    <div class="col-md-6">
                                        @if ($report->manager_sign_by)
                                            {!! \App\User::find($report->manager_sign_by)->full_name !!}, &nbsp;{{ $report->manager_sign_at->format('d/m/Y') }}
                                        @elseif($report->status == 3 && $report->supervisor_sign_by && Auth::user()->allowed2('edit.site.inspection', $report) && Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
                                            <div class="form-group {!! fieldHasError('approve_version', $errors) !!}">
                                                {!! Form::select('manager_sign_by', ['' => 'Do you approve this inspection report', 'n' => 'No', 'y' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'manager_sign_by']) !!}
                                            </div>
                                        @else
                                            <span class="font-red">Pending Sign Off</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->allowed2('edit.site.inspection', $report))
                            <div class="form-actions right">
                                <a href="/site/inspection/electrical" class="btn default"> Back</a>
                                @if($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report))
                                    <button type="submit" class="btn green"> Save</button>
                                    {!! Form::close() !!}
                                @elseif (!$report->status && Auth::user()->allowed2('sig.site.inspection', $report))
                                    <a href="/site/inspection/electrical/{{ $report->id }}/status/1" class="btn green"> Re-open Report</a>
                                @endif
                            </div>
                            {!! Form::close() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $report->displayUpdatedBy() !!}
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $report->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="font-green-haze">Additional Notes for {{ ($report->ownedBy->nickname) ? $report->ownedBy->nickname :  $report->ownedBy->name }}
                        <button v-on:click.stop.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h4>
                    <hr>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Details</th>
                            <th width="20%"> Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/moment.min.js" type="text/javascript"></script>
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
        table_name: 'site_inspection_electrical', table_id: '', record_status: '', stage: '', next_review_date: '', client_contacted: '',
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
            'add-action-modal': function (e) {
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
                //alert('add action');

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

