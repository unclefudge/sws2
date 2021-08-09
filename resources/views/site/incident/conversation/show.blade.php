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
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Record of Conversation</span>
                            <span class="caption-helper"> ID: {{ $incident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($conversation, ['method' => 'PATCH', 'action' => ['Site\Incident\SiteIncidentConversationController@update',$incident->id, $conversation->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="row">
                            <div class="col-md-12">
                                <b>The following person was involved in an incident on {{ $incident->date->format('d/m/Y') }} at {{ $incident->site->name }} ({{ $incident->site->full_address }})</b><br><br>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="font-green-haze">Record of Conversation</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            </div>
                        </div>

                        {{-- Name + Witness --}}
                        <div class="row">
                            {{-- Name --}}
                            <div class="col-md-5">
                                <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                    {!! Form::label('name', 'Conversation between', ['class' => 'control-label']) !!}
                                    {!! Form::text('name', null, ['class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('name', $errors) !!}
                                </div>
                            </div>
                            {{-- Witness --}}
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('witness', $errors) !!}">
                                    {!! Form::label('witness', 'Witness', ['class' => 'control-label']) !!}
                                    {!! Form::text('witness', null, ['class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('witness', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Start / End --}}
                        <div class="row">
                            {{-- Start --}}
                            <div class="col-md-4">
                                <div class="form-group {!! fieldHasError('start', $errors) !!}">
                                    {!! Form::label('start', 'Conversation started', ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('edit.site.incident', $incident))
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('start', $conversation->start->format('d/m/Y H:i'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('start', $errors) !!}
                                    @else
                                        {!! Form::text('start', $incident->start->format('d/m/Y H:i'), ['class' => 'form-control', 'disabled']) !!}
                                    @endif
                                </div>
                            </div>
                            {{-- End --}}
                            <div class="col-md-4">
                                <div class="form-group {!! fieldHasError('end', $errors) !!}">
                                    {!! Form::label('end', 'Conversation ended', ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('edit.site.incident', $incident))
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('end', $conversation->end->format('d/m/Y H:i'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('end', $errors) !!}
                                    @else
                                        {!! Form::text('end', $incident->end->format('d/m/Y H:i'), ['class' => 'form-control', 'disabled']) !!}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('details', $errors) !!}">
                                    {!! Form::label('details', 'Conversation details', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('details', null, ['rows' => '5', 'class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('details', $errors) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/site/incident/{{ $incident->id }}" class="btn default"> Back</a>
                            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                                <button id="btn-delete" class="btn red"> Delete</button>
                                <button type="submit" class="btn green"> Save</button>
                            @endif
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
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

    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

    $(document).ready(function () {
        /* Select2 */

        $("#btn-delete").click(function (e) {
            e.preventDefault();

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this conversation between:<br><b>" + $('#name').val() + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                alert('nuked');
                $.ajax({
                    url: '/site/incident/{{ $incident->id }}/conversation/{{ $conversation->id }}',
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted conversation');
                        window.location.href = "/site/incident/{{ $incident->id }}}";
                    },
                });
            });
        });
    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });
</script>
@stop

