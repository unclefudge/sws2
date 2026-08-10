@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/incident/{{ $incident->id}}/">Incident</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Record of Conversation</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                @include('site/incident/_header')

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Record of Conversation</span>
                            <span class="caption-helper"> ID: {{ $incident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentConversationController::class, 'store'], $incident->id) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')

                            <div class="form-body">

                                {{-- Name + Witness --}}
                                <div class="row">
                                    {{-- Name --}}
                                    <div class="col-md-5">
                                        <x-form.input name="name" label="Conversation between"/>
                                    </div>
                                    {{-- Witness --}}
                                    <div class="col-md-3">
                                        <x-form.input name="witness" label="Witness"/>
                                    </div>
                                </div>

                                {{-- Start / End --}}
                                <div class="row">
                                    {{-- Start --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="start" class="control-label">Conversation date</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" name="start" id="start" class="form-control" value="{{ old('start') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- End --}}
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="end" class="control-label">Conversation ended</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" name="end" id="end" class="form-control" value="{{ old('end') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="details" label="Conversation details" rows="5"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/incident/{{ $incident->id }}/admin" class="btn default"> Back</a>
                                    <button type="submit" class="btn green"> Save</button>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $incident->displayUpdatedBy() !!}
        </div>
    </div>

@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#user_id").select2({placeholder: "Select user"});

            // On Change User_id
            $("#user_id").change(function () {
                var user_id = $("#user_id").select2("val");
                if (user_id) {
                    $.ajax({
                        url: '/user/data/details/' + user_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            var fullname = data.firstname;

                            if (data.lastname) fullname = fullname + ' ' + data.lastname
                            $("#name").val(fullname);
                        },
                    })
                }
            });
        });

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });

        // Force datepicker to not be able to select dates after today
        $('.bs-datetime').datetimepicker({
            endDate: new Date(),
            format: 'dd/mm/yyyy hh:ii',
        });
    </script>
@stop

