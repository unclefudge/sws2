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
                            <span class="caption-subject font-green-haze bold uppercase"> Alert Notification</span>
                            <span class="caption-helper"> - ID: {{ $notify->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Comms\NotifyController::class, 'update'], $notify->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <x-form.hidden name="company_id" :value="$notify->company_id"/>
                        <x-form.hidden name="type" :value="$notify->type"/>
                        <x-form.hidden name="title" :value="$notify->name"/>
                        <x-form.hidden name="mesg" :value="$notify->info"/>

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
                                    <x-form.input name="name" label="Title" :value="$notify->name" readonly disabled/>

                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-3">
                                    <x-form.date-range from="from" to="to" label="Date(s) alert wll be shown" :from-value="$notify->from->format('d/m/Y')" :to-value="$notify->to->format('d/m/Y')" start-date="0d" disabled/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="action" label="Frequency of Alert" :options="['once' => 'Only once', 'many' => 'For whole duration of date range']" :value="$notify->action" disabled/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <x-form.textarea name="info" label="Alert Message" :value="$notify->info" rows="4" readonly/>
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
                                    @if ($notify->viewedBySBC())
                                        <p><b>Viewed by:</b> {{ $notify->viewedBySBC() }}</p>
                                    @endif
                                    @if ($notify->unviewedBySBC()) <p><b>Unseen by:</b> {{ $notify->unviewedBySBC() }}</p> @endif
                                    </p>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/comms/notify" class="btn default"> Back</a>
                                <button class="btn dark" id="test_alert">View Test Alert</button>
                            </div>
                        </div>
                        </form>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {

            $(window).resize(function () {
                $(".sweet-alert").css("margin-top", -$(".sweet-alert").outerHeight() / 2);
            });

            $("#test_alert").click(function (e) {
                e.preventDefault();
                swal($("#title").val(), $("#mesg").val());
            })

        });
    </script>
@stop

