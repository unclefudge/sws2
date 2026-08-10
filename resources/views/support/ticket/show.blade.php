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
        <form method="POST" action="{{ action([\App\Http\Controllers\Support\SupportTicketController::class, 'addAction']) }}" enctype="multipart/form-data">
            @csrf
            <x-form.hidden name="ticket_id" :value="$ticket->id"/>
            <div class="m-heading-1 border-green m-bordered" style="margin: 0 0 20px;">
                <h3>{{ $ticket->name }}
                    <small>(Ticket ID: {{ $ticket->id }})</small>
                    @if (!$ticket->status)
                        <span class="font-red uppercase pull-right" style="font-weight: 300">Ticket Closed {{ $ticket->resolved_at->format('d/m/Y') }}</span>
                    @endif
                </h3>
                <div class="row">
                    <div class="col-xs-1">Priority</div>
                    <div class="col-md-2">
                        @if ($ticket->status)
                            <x-form.select name="priority" :options="['0' => 'None', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'In Progress']" :value="$ticket->priority"/>
                        @else
                            <x-form.input name="priority" :value="$ticket->priority_text" disabled/>
                        @endif
                    </div>
                    <div class="col-md-1"></div>
                    @if ($ticket->type )
                        <div class="col-md-1">ETA</div>
                        <div class="col-md-2">
                            <x-form.input name="eta" :value="$ticket->eta ? $ticket->eta->format('d/m/Y') : 'to be reviewed'" disabled/>
                        </div>
                        @if (Auth::user()->id == '3')
                            {{-- Only Fudge to edit ETA --}}
                            <div class="col-md-3">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <div class="input-group date date-picker">
                                            <input type="text" name="eta_set" id="eta_set" class="form-control form-control-inline" value="{{ $ticket->eta }}" readonly style="background:#FFF" data-date-format="dd-mm-yyyy">
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
                                <x-form.input name="hours" :value="$ticket->hours < 8 ? $ticket->hours . ' hr' : $ticket->hours / 8 . ' day'" disabled/>
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
                        @if ($ticket->status)
                            <x-form.select name="assigned_to" :options="['' => 'None', '3' => 'Fudge', '108' => 'Kirstie', '1155' => 'Ross']" :value="$ticket->assigned_to"/>
                        @else
                            <x-form.input name="assign_to_text" :value="$ticket->assigned_to ? $ticket->assigned->fullname : 'None'" disabled/>
                        @endif
                    </div>
                    <div class="col-md-1"></div>
                    @if (Auth::user()->id == '3' && $ticket->type)
                        <div class="col-md-1">Time</div>
                        <div class="col-md-2">
                            <x-form.input name="hours" :value="$ticket->hours < 8 ? $ticket->hours . ' hr' : $ticket->hours / 8 . ' day (' . $ticket->hours . ' hr)'"/>
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
                                <span class="caption-subject font-green-haze bold uppercase">Ticket Actions</span>
                                <span class="caption-helper"></span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            @if ($ticket->status && ((!$ticket->type && Auth::user()->allowed2('edit.support.ticket', $ticket )) || ($ticket->type && Auth::user()->hasPermission2('edit.support.ticket.upgrade')) ))
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="action" label="Add Action" rows="8"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div>
                                            <h5><b>Attachments</b></h5>
                                            <x-form.filepond name="filepond[]" label="Attachments" :multiple="true"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <br><br>
                                        <button type="submit" class="btn green pull-right" id="submit">Save Action</button>
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
                                                @php
                                                    $attachments = $action->attachments;
                                                    $images = $attachments->where('type', 'image');
                                                    $files  = $attachments->where('type', 'file');
                                                @endphp

                                                <h5><b>Attachments</b></h5>
                                                @if ($attachments->isNotEmpty())
                                                    <hr style="margin: 10px 0px; padding: 0px;">
                                                    {{-- Image attachments --}}
                                                    @if ($images->isNotEmpty())
                                                        <div class="row" style="margin: 0">
                                                            @foreach ($images as $attachment)
                                                                <div style="width: 60px; float: left; padding-right: 5px">
                                                                    <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                                        <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- File attachments --}}
                                                    @if ($files->isNotEmpty())
                                                        <div class="row" style="margin: 0">
                                                            @foreach ($files as $attachment)
                                                                <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a><br>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @else
                                                    <div>None</div>
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
        </form>
    </div>
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
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
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

