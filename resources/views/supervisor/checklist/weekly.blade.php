@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/supervisor/checklist">Supervisor Checklist</a><i class="fa fa-circle"></i></li>
        <li>Weekly</li>
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
                            {{--}}
                            @if(Auth::user()->allowed2('add.super.checklist'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/create/{{ $template->id }}" data-original-title="Add">Add</a>
                            @endif --}}
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
                        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $checklist->id }}">
                        <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $checklist->status }}">
                        <input v-model="xx.record_resdate" type="hidden" id="record_resdate" value="{{ $checklist->resolved_at }}">
                        @include('form-error')

                        <h3>Weekending: {{ $checklist->date->addDays(4)->format('j F, Y') }}</h3>
                        <h4>Supervisor: {{ $checklist->supervisor->name }}</h4>
                        <br>
                        <div class="form-body">

                            @foreach ($categories as $category)
                                <h4 class="font-green-haze"><b>{{$category->name}}</b>{!! ($category->description) ? ": <small>$category->description</small>" : '' !!}</h4>
                                <hr class="field-hr">
                                <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                    <tr>
                                        <td></td>
                                        <td>MON</td>
                                        <td>TUE</td>
                                        <td>WED</td>
                                        <td>THU</td>
                                        <td>FRI</td>
                                    </tr>
                                    @foreach ($category->questions as $question)
                                        <tr>
                                            <td>{{$question->name}}</td>

                                            {{-- Mon --}}
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', 1)->first(); ?>

                                            <td class="text-center" style="min-width:50px; width: 50px">{!! $question->isRequiredForSupervisor($checklist->supervisor, 1) ? $response->button : '' !!}</td>
                                            {{-- Tue --}}
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', 2)->first(); ?>
                                            <td class="text-center" style="min-width:50px; width: 50px">{!! $question->isRequiredForSupervisor($checklist->supervisor, 2) ? $response->button : '' !!}</td>
                                            {{-- Wed --}}
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', 3)->first(); ?>
                                            <td class="text-center" style="min-width:50px; width: 50px">{!! $question->isRequiredForSupervisor($checklist->supervisor, 3) ? $response->button : '' !!}</td>
                                            {{-- Thu --}}
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', 4)->first(); ?>
                                            <td class="text-center" style="min-width:50px; width: 50px">{!! $question->isRequiredForSupervisor($checklist->supervisor, 4) ? $response->button : '' !!}</td>
                                            {{-- Fri --}}
                                                <?php $response = \App\Models\Misc\Supervisor\SuperChecklistResponse::where('checklist_id', $checklist->id)->where('question_id', $question->id)->where('day', 5)->first(); ?>
                                            <td class="text-center" style="min-width:50px; width: 50px">{!! $question->isRequiredForSupervisor($checklist->supervisor, 5) ? $response->button : '' !!}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endforeach

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <app-actions :table_id="{{ $checklist->id }}"></app-actions>
                                </div>
                            </div>
                            <br><br>

                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><b>WEEKLY SUPERVISOR CHECKLIST ELECTRONIC SIGN-OFF</b></h5>
                                    <p>The above checklist items have been verified by the site construction supervisor.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 text-right">Site Supervisor:</div>
                                <div class="col-sm-9">
                                    @if ($checklist->supervisor_sign_by)
                                        {!! \App\User::find($checklist->supervisor_sign_by)->full_name !!}, &nbsp;{{ $checklist->supervisor_sign_at->format('d/m/Y') }}
                                    @elseif (Auth::user()->isSupervisor() || Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @else
                                        <span class="font-red">Pending</span>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">Site Manager:</div>
                                <div class="col-sm-9">
                                    @if ($checklist->manager_sign_by)
                                        {!! \App\User::find($checklist->manager_sign_by)->full_name !!}, &nbsp;{{ $checklist->manager_sign_at->format('d/m/Y') }}
                                    @elseif (!$checklist->supervisor_sign_by)
                                        <span class="font-red">Waiting for Site Supervisor Sign Off</span>
                                    @elseif (Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @endif
                                </div>
                            </div>
                            <br><br>

                            <div class="form-actions right">
                                @if ($checklist->date == $mon)
                                    <a href="/supervisor/checklist" class="btn default"> Back</a>
                                @else
                                    <a href="/supervisor/checklist/past/{{ $checklist->date->format('Y-m-d') }}" class="btn default"> Back</a>
                                @endif
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
                window.location.href = "/supervisor/checklist/{{$checklist->id}}/weekly/signoff";
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