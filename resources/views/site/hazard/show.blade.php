@extends('layout')
@inject('failureTypes', 'App\Http\Utilities\FailureTypes')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/hazard">Hazard Register</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Hazard</span>
                            <span class="caption-helper"> ID: {{ $hazard->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($hazard, ['method' => 'PATCH', 'action' => ['Site\SiteHazardController@update', $hazard->id]]) !!}
                        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $hazard->id }}">
                        <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $hazard->status }}">
                        <input v-model="xx.record_resdate" type="hidden" id="record_resdate" value="{{ $hazard->resolved_at }}">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $hazard->site->name }}</h2>
                                    {{ $hazard->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$hazard->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">RESOLVED</h2>
                                    @endif
                                    <b>Job #:</b> {{ $hazard->site->code }}<br>
                                    <b>Supervisor:</b> {{ $hazard->site->supervisorName }}<br>
                                </div>
                            </div>

                            <hr>
                            <div class="row" style="line-height: 1.5em">
                                <div class="col-md-8">
                                    <h4 class="font-green-haze">Hazard Details</h4>
                                    <b>Date Raised: </b>{!! $hazard->created_at->format('d/m/Y') !!}<br><br>
                                    @if ($hazard->status && Auth::user()->allowed2('del.site.hazard', $hazard))
                                        <div class="row" style="padding-left: 15px">
                                            <div class="col-md-3" style="padding-left: 0px">
                                                <b>Risk Rating</b><br>
                                                <div class="form-group {!! fieldHasError('rating', $errors) !!}">
                                                    {!! Form::select('rating', ['' => 'Select rating', '1' => "Low", '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                                                    {!! fieldErrorMessage('rating', $errors) !!}
                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                    @else
                                        <b>Risk Rating: </b>{!! $hazard->ratingTextColoured !!}<br><br>
                                    @endif
                                    <b>Location of Hazard:</b><br>{{ $hazard->location }}<br><br>
                                    <b>What is the hazard / safety issue:</b><br>{{ $hazard->reason }}<br><br>
                                    @if (!$hazard->status || !Auth::user()->allowed2('del.site.hazard', $hazard))
                                        <b>Failure Type:</b> {{ $hazard->failure_type }}<br><br>
                                        <b>Source:</b><br>{{ $hazard->source }}<br><br>
                                    @else
                                        {{-- Edit - Status Open --}}
                                        <div class="col-md-6" style="padding-left: 0px">
                                            <b>Failure Type</b><br>
                                            <div class="form-group {!! fieldHasError('failure', $errors) !!}">
                                                {!! Form::select('failure', $failureTypes::all(), null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('failure', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3" style="padding-left: 0px">
                                            <b>Status</b><br>
                                            <div class="form-group {!! fieldHasError('status', $errors) !!}">
                                                {!! Form::select('status', ['1' => 'Open', '0' => 'Resolved'], $hazard->status, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('status', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-9" style="padding-left: 0px">
                                            <b>Identification Source"</b><br>
                                            <div class="form-group {!! fieldHasError('source', $errors) !!}">
                                                {{--}}{!! Form::textarea('source', null, ['rows' => '3', 'class' => 'form-control']) !!}--}}
                                                {!! Form::select('source', ['' => 'Select source', 'WHS Inspection' => 'WHS Inspection', 'Worker Identification' => 'Worker Identification',
                                                'Supervisor' => 'Supervisor', 'Client Report' => 'Client Report', 'Regulator' => 'Regulator', 'Council' => 'Council', 'Public' => 'Public'],
                                                null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('source', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-2 hidden-sm hidden-xs" style="position: absolute; bottom: 10px;right: 0;">
                                            <button type="submit" class="btn green">Save</button>
                                        </div>
                                        <div class="col-md-2 visible-sm visible-xs">
                                            <button type="submit" class="btn green">Save</button>
                                            <br><br>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    {{--}}
                                    @if($hazard->attachment_url)
                                        <div style="padding-bottom: 20px">
                                            <a href="{{ $hazard->attachment_url }}" class="html5lightbox " title="{{ $hazard->reason }}" data-lityXXX>
                                                <?php $ext = pathinfo($hazard->attachment_url, PATHINFO_EXTENSION); ?>
                                                @if ($ext == 'pdf')
                                                    <i class="fa fa-4x fa-file-pdf-o"></i>
                                                @else
                                                    <img src="{{ $hazard->attachment_url }}" class="thumbnail img-responsive img-thumbnail">
                                                @endif
                                            </a>
                                        </div>
                                    @endif--}}
                                        {{-- Attachments --}}
                                        <h5><b>Attachments</b></h5>
                                        @if ($hazard->files->count())
                                            <hr style="margin: 10px 0px; padding: 0px;">
                                            {{-- Image attachments --}}
                                            <div class="row" style="margin: 0">
                                                @foreach ($hazard->files as $file)
                                                    @if ($file->type == 'image' && file_exists(substr($file->AttachmentUrl, 1)))
                                                        <div style="width: 60px; float: left; padding-right: 5px">
                                                            <a href="{{ $file->AttachmentUrl }}" target="_blank" class="html5lightbox " title="{{ $file->attachment }}" data-lity>
                                                                <img src="{{ $file->AttachmentUrl }}" class="thumbnail img-responsive img-thumbnail"></a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            {{-- File attachments  --}}
                                            <div class="row" style="margin: 0">
                                                @foreach ($hazard->files as $file)
                                                    @if ($file->type == 'file' && file_exists(substr($file->AttachmentUrl, 1)))
                                                        <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $file->AttachmentUrl }}" target="_blank"> {{ $file->name }}</a><br>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            None
                                        @endif
                                    <div>
                                        <br><input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $hazard->id }}"></app-actions>
                            </div>
                        </div>

                        {{-- ToDos--}}
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Assigned Tasks
                                    {{-- Show add if user has permission to edit hazard --}}
                                    @if ($hazard->status && Auth::user()->allowed2('edit.site.hazard', $hazard) && Auth::user()->isCompany($hazard->owned_by->id))
                                        <a href="/todo/create/hazard/{{ $hazard->id}}" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</a>
                                    @endif
                                </h3>
                                @if ($hazard->todos()->count())
                                    <table class="table table-striped table-bordered table-nohover order-column">
                                        <thead>
                                        <tr class="mytable-header">
                                            <th width="5%">#</th>
                                            <th> Action</th>
                                            <th width="15%">Created by</th>
                                            <th width="15%">Completed by</th>
                                            <th width="5%"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($hazard->todos() as $todo)
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
                                                    @if ($todo->attachment) <a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"></i></a> @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/site/hazard" class="btn default"> Back</a>
                            @if(!$hazard->status && Auth::user()->allowed2('del.site.hazard', $hazard))
                                <a href="/site/hazard/{{ $hazard->id }}/status/1" class="btn green"> Re-open Hazard</a>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $hazard->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit hazard --}}
                        <button v-show="xx.record_status == '1'" v-on:click="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Action</th>
                            <th width="20%"> Name</th>
                            <th width="5%"></th>
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
                                    <button v-show="action.created_by == xx.created_by" v-on:click="$root.$broadcast('edit-action-modal', action)"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                    -->
                                </td>
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
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    {{--}}<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>--}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/js/libs/moment.min.js" type="text/javascript"></script>

<!-- Vue -->
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script>
    // Get a reference to the file input element
    const inputElement = document.querySelector('input[type="file"]');

    // Create a FilePond instance
    const pond = FilePond.create(inputElement);
    FilePond.setOptions({
        server: {
            url: '/file/upload',
            fetch: null,
            revert: null,
            headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
        },
        allowMultiple: true,
    });


    Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

    var host = window.location.hostname;
    var dev = true;
    if (host == 'safeworksite.com.au')
        dev = false;

    var xx = {
        dev: dev,
        action: '', loaded: false,
        table_name: 'site_hazards', table_id: '', record_status: '', record_resdate: '',
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

