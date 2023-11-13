@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/supervisor/checklist">Supervisor Checklist</a><i class="fa fa-circle"></i></li>
        <li>{{ $checklist->date->addDays($day-1)->format('l') }}</li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        {{-- Reports --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Supervisor Checklist</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist" data-original-title="Current">Current Week</a>
                        </div>
                    </div>
                    {{--}}
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>--}}
                    <div class="portlet-body form">
                        {!! Form::model($checklist, ['method' => 'PATCH', 'action' => ['Misc\SuperChecklistController@update', $checklist->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        {!! Form::hidden('day', $day) !!}
                        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $checklist->id }}">
                        <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $checklist->status }}">
                        <input v-model="xx.record_resdate" type="hidden" id="record_resdate" value="{{ $checklist->resolved_at }}">
                        @include('form-error')


                        <h3>{{ $checklist->date->addDays($day-1)->format('l - j F, Y') }}</h3>
                        <h4>Supervisor: {{ $checklist->supervisor->name }}</h4>
                        <br>
                        <div class="form-body">

                            @foreach ($categories as $category)
                                @if ($category->isRequiredForSupervisor($checklist->supervisor, $day))
                                    <h4 class="font-green-haze"><b>{{$category->name}}</b>{!! ($category->description) ? ": <small>$category->description</small>" : '' !!}</h4>
                                    <hr class="field-hr">
                                    <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                        @foreach ($category->questions as $question)
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', $day)->first(); ?>

                                            @if ($response)
                                                <tr>
                                                    <td>{{$response->question->name}}</td>
                                                    <td style="min-width:200px; width: 200px">
                                                        <div class="form-group">
                                                            <div class="btn-group" min-width:150px>
                                                                <button id="r{{$response->id}}-y" class="btn button-resp {{ ($response->value == 'y') ? 'green' : '' }}" style="margin-right: 10px; width:50px" data-rid="{{$response->id}}" data-val="y" data-btype="green" data-bval="Yes">Yes</button>
                                                                <button id="r{{$response->id}}-n" class="btn button-resp {{ ($response->value == 'n') ? 'red' : '' }}" style="margin-right: 10px; width:50px" data-rid="{{$response->id}}" data-val="n" data-btype="red" data-bval="No">No</button>
                                                                <button id="r{{$response->id}}-na" class="btn button-resp {{ ($response->value == 'na') ? 'dark' : '' }}" style="margin-right: 10px; width:50px" data-rid="{{$response->id}}" data-val="na" data-btype="dark" data-bval="N/A">N/A</button>
                                                            </div>
                                                            <input type="hidden" id="r{{$response->id}}" name="r{{$response->id}}" value="{{$response->value}}">
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </table>
                                @endif
                            @endforeach

                            <div class="row">
                                <div class="col-md-12">
                                    Finally plan tomorrows run, Starting project 7 am along with time allocation and so on till office between 2:30 & 3 daily. Go home turn off, knowing that you have done all you can.
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <app-actions :table_id="{{ $checklist->id }}"></app-actions>
                                </div>
                            </div>
                            <br><br>

                            <div class="form-actions right">
                                <a href="/supervisor/checklist" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $checklist->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit hazard --}}
                        <button v-on:click.stop.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Note</th>
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
                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->
                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {

            //
            // Select Buttons
            //
            $('.button-resp').click(function (e) {
                e.preventDefault(e);
                var rid = $(this).attr('data-rid');
                var val = $(this).attr('data-val');
                var btype = $(this).attr('data-btype');
                var bval = $(this).attr('data-bval');
                // alert('r:'+rid+' v:'+val);

                // Loop through all buttons for selected question + remove active classes
                var buttons = document.querySelectorAll(`[data-rid='${rid}']`);
                for (var i = 0; i < buttons.length; i++) {
                    $('#' + buttons[i].id).removeClass('btn-default red green dark')
                }

                // Add active class to selected button
                if ($('#r' + rid).val() != val) {
                    $('#r' + rid + '-' + val).addClass(btype);
                    $('#r' + rid).val(val);
                    console.log('adding:' + btype + ' bval:' + bval + ' val:' + val + ' qval:' + $('#r' + rid).val());
                } else
                    $('#r' + rid).val('');

                // console.log(buttons[0].id);
                // console.log(buttons);
            });

            $(".signoff").click(function (e) {
                e.preventDefault();
                window.location.href = "/supervisor/checklist/{{$checklist->id}}/{{$day}}/signoff";
            });

        });

    </script>

    <!-- Vue -->
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
            table_name: 'supervisor_checklist', table_id: '', record_status: '', record_resdate: '',
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
        });

    </script>
@stop