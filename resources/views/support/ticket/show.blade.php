@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-ticket"></i> Support Tickets </h1>
    </div>
@stop
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/ticket">Support Tickets</a><i class="fa fa-circle"></i></li>
        <li><span>Manage ticket</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner form">
        {!! Form::model('support_ticket_action', ['action' => ['Support\SupportTicketController@addAction'], 'files' => true]) !!}
        {!! Form::hidden('ticket_id', $ticket->id) !!}
        <div class="m-heading-1 border-green m-bordered" style="margin: 0 0 20px;">
            <h3>{{ $ticket->name }}
                <small>(Ticket ID: {{ $ticket->id }})</small>
                @if (!$ticket->status)
                    <span class="font-red uppercase pull-right" style="font-weight: 300">Ticket Resolved {{ $ticket->resolved_at->format('d/m/Y') }}</span>
                @endif
            </h3>
            <div class="row">
                <div class="col-xs-1">Priority</div>
                <div class="col-md-2">
                    <div class="form-group {!! fieldHasError('priority', $errors) !!}">
                        @if ($ticket->status)
                            {!! Form::select('priority', ['0' => 'None', '1' => 'Low', '2' => 'Medium', '3' =>'High', '4' =>'In Progress'], $ticket->priority, ['class' => 'form-control bs-select', 'id' => 'priority']) !!}
                        @else
                            {!! Form::text('priority', $ticket->priority_text, ['class' => 'form-control', 'disabled']) !!}
                        @endif
                    </div>
                </div>
                <div class="col-md-1"></div>
                @if ($ticket->type )
                    <div class="col-md-1">ETA</div>
                    <div class="col-md-2">
                        {!! Form::text('eta', ($ticket->eta) ? $ticket->eta->format('d/m/Y') : 'to be reviewed', ['class' => 'form-control', 'disabled']) !!}
                    </div>
                    @if (Auth::user()->id == '3')
                        {{-- Only Fudge to edit ETA --}}
                        <div class="col-md-3">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <div class="input-group date date-picker">
                                        {!! Form::text('eta_set', $ticket->eta, ['class' => 'form-control form-control-inline', 'readonly',
                                        'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'id' => 'eta_set']) !!}
                                        <span class="input-group-btn">
                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                            </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn blue" id="eta_update">Save</button>
                            </div>
                        </div>
                    @else
                        <div class="col-md-1">Hours</div>
                        <div class="col-md-2">
                            {!! Form::text('hours', ($ticket->hours < 8) ? $ticket->hours . ' hr' : $ticket->hours / 8 . ' day', ['class' => 'form-control', 'disabled']) !!}
                        </div>
                    @endif
                @endif
                <div class="col-md-2">
                    @if ($ticket->status && Auth::user()->allowed2('edit.support.ticket', $ticket ))
                        <button type="button" class="btn green" id="ticket_close"> Close Ticket</button>
                    @elseif (Auth::user()->allowed2('edit.support.ticket', $ticket ))
                        <button type="button" class="btn blue" id="ticket_open"> Re-open Ticket</button>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-xs-1">Assigned</div>
                <div class="col-md-2">
                    <div class="form-group {!! fieldHasError('assigned_to', $errors) !!}">
                        @if ($ticket->status)
                            {!! Form::select('assigned_to', ['' => 'None', '3' => 'Fudge', '108' => 'Kirstie', '351' =>'Tara', '1155' =>'Ross'], $ticket->assigned_to, ['class' => 'form-control bs-select', 'id' => 'assigned_to']) !!}
                        @else
                            {!! Form::text('assign_to_text', $ticket->assigned_to ? $ticket->assigned->fullname : 'None', ['class' => 'form-control', 'disabled']) !!}
                        @endif
                    </div>
                </div>
                <div class="col-md-1"></div>
                @if (Auth::user()->id == '3' && $ticket->type)
                    <div class="col-md-1">Time</div>
                    <div class="col-md-2">
                        {!! Form::text('hours', ($ticket->hours < 8) ? $ticket->hours . ' hr' : $ticket->hours / 8 .' day'.' ('.$ticket->hours.' hr)', ['class' => 'form-control', 'id' => 'hours']) !!}
                    </div>
                    <div class="col-md-3">
                        <button class="btn blue" id="hour_update">Save</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Ticket Actions</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @if ($ticket->status && ((!$ticket->type && Auth::user()->allowed2('edit.support.ticket', $ticket )) || ($ticket->type && Auth::user()->hasPermission2('edit.support.ticket.upgrade')) ))
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                        {!! Form::label('action', 'Add Action', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('action', null, ['rows' => '8', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('action', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div>
                                        <h5><b>Attachments</b></h5>
                                        <input type="file" class="filepond" name="filepond[]" multiple/>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <br><br>
                                    <button type="submit" class="btn green pull-right">Save Action</button>
                                    <br><br>
                                </div>
                            </div>
                            <hr>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                @foreach($ticket->actions->sortByDesc('created_at') as $action)
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{{ $action->created_at->format('d/m/Y g:i a') }} <span class="pull-right"><a href="/user/{{ $action->user->id }}">{{ $action->user->fullname }}</a></span></div>
                                        <div class="panel-body">
                                            {!! nl2br(e($action->action)) !!}<br><br>

                                            {{-- Attachments --}}
                                            @if ($action->files->count())
                                                <h5><b>Attachments</b></h5>
                                                <hr style="margin: 10px 0px; padding: 0px;">
                                                {{-- Image attachments --}}
                                                <div class="row" style="margin: 0">
                                                    @foreach ($action->files as $file)
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
                                                    @foreach ($action->files as $file)
                                                        @if ($file->type == 'file' && file_exists(substr($file->AttachmentUrl, 1)))
                                                            <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $file->AttachmentUrl }}" target="_blank"> {{ $file->name }}</a><br>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/support/ticket" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- END Content -->
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}

    {{--}}<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">--}}

    {{--}}<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>--}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    {{--}}<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>--}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
        });

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

        $(document).ready(function () {
            $('#priority').change(function () {
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/priority/' + $('#priority').val();
            });

            $('#assigned_to').change(function () {
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/assigned/' + $('#assigned_to').val();
            });

            $('#eta_update').click(function (e) {
                e.preventDefault();
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/eta/' + $('#eta_set').val();
            });

            $('#hour_update').click(function (e) {
                e.preventDefault();
                //alert($('#hours').val());
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/hours/' + $('#hours').val();
            });

            $('#ticket_close').click(function () {
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/status/0';
            });

            $('#ticket_open').click(function () {
                window.location.href = '/support/ticket/' + {{ $ticket->id }} + '/status/1';
            });
        });

    </script>
@stop

