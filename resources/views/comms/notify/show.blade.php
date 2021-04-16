@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/comms/notify/">Notify</a><i class="fa fa-circle"></i></li>
        <li><span> Alert Notification item</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase"> Alert Notification</span>
                            <span class="caption-helper"> - ID: {{ $notify->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($notify, ['method' => 'PATCH', 'action' => ['Comms\NotifyController@update', $notify->id], 'files' => true]) !!}
                        {!! Form::hidden('company_id', $notify->company_id) !!}
                        {!! Form::hidden('type', $notify->type) !!}
                        {!! Form::hidden('title', $notify->name, ['id' => 'title']) !!}
                        {!! Form::hidden('mesg', $notify->info, ['id' => 'mesg']) !!}

                        @include('form-error')

                        <div class="form-body">
                            @if(!$notify->status)
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Completed</h3>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('title', 'Title', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $notify->name, ['class' => 'form-control', 'readonly', 'disabled']) !!}
                                    </div>

                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Date(s) alert wll be shown', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-start-date="0d">
                                            {!! Form::text('from', $notify->from->format('d/m/Y'), ['class' => 'form-control', 'readonly', 'disabled', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('to', $notify->to->format('d/m/Y'), ['class' => 'form-control', 'readonly', 'disabled', 'style' => 'background:#FFF']) !!}
                                        </div>
                                        {!! fieldErrorMessage('from', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                        {!! Form::label('action', 'Frequency of Alert', ['class' => 'control-label']) !!}
                                        {!! Form::select('action', ['once' => 'Only once', 'many' => 'For whole duration of date range'],
                                             $notify->action, ['class' => 'form-control bs-select', 'disabled']) !!}
                                        {!! fieldErrorMessage('action', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('info', 'Alert Message', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('info', $notify->info, ['rows' => '4', 'class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p>Alert Sent to the following {{ $notify->type }}(s):
                                        @if ($notify->type == 'site')
                                            <b>{!! \App\Models\Site\Site::find($notify->type_id)->name !!}</b>
                                        @else
                                            <span class="label {!! ($notify->viewedBy()->count() == $notify->assignedTo()->count()) ? 'label-success' : 'label-danger' !!}">{{ $notify->viewedBy()->count() }}
                                                / {{ $notify->assignedTo()->count() }}</span></p>
                                    @endif
                                    <p>
                                    @if ($notify->viewedBySBC()) <p><b>Viewed by:</b> {{ $notify->viewedBySBC() }}</p> @endif
                                    @if ($notify->unviewedBySBC()) <p><b>Unseen by:</b> {{ $notify->unviewedBySBC() }}</p> @endif
                                    </p>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/comms/notify" class="btn default"> Back</a>
                                <button class="btn dark" id="test_alert">View Test Alert</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {

        $(window).resize(function(){
            $(".sweet-alert").css("margin-top",-$(".sweet-alert").outerHeight()/2);
        });

        $("#test_alert").click(function (e) {
            e.preventDefault();
            swal($("#title").val(), $("#mesg").val());
        })

    });
</script>
@stop

