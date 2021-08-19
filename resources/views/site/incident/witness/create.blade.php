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
        <li><span>Witness Statements</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Witness Statements</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('SiteIncidentWitness', ['action' => ['Site\Incident\SiteIncidentWitnessController@store', $incident->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')
                        <div class="form-body">
                            {{-- User + Name --}}
                            <div class="row">
                                {{-- User Id --}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('user_id', $errors) !!}">
                                        {!! Form::label('user_id', 'Witness', ['class' => 'control-label']) !!}
                                        {!! Form::select('user_id', ['' => 'Select user'] + Auth::user()->company->usersSelect('select'),
                                             null, ['class' => 'form-control select2', 'name' => 'user_id', 'id'  => 'user_id',]) !!}
                                        {!! fieldErrorMessage('user_id', $errors) !!}
                                    </div>
                                </div>
                                {{-- Name --}}
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Full name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('assign_task', $errors) !!}">
                                        {!! Form::label('assign_task', 'Assign task for user to complete statement', ['class' => 'control-label']) !!}
                                        {!! Form::select('assign_task', ['1' => 'Yes - assign to user', '0' => 'No - complete on their behalf'],
                                             null, ['class' => 'form-control bs-select', 'name' => 'assign_task', 'id'  => 'assign_task',]) !!}
                                        {!! fieldErrorMessage('assign_task', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div id="statement_fields">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="font-green-haze">Witness Statement</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    </div>
                                </div>
                                {{-- Event Before --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('event_before', $errors) !!}">
                                            {!! Form::label('event_befor', 'In your own words describe the events leading up to the incident', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('event_before', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('event_before', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Event --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('event', $errors) !!}">
                                            {!! Form::label('event', 'In your own words describe the incident', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('event', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('event', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Event After --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('event_after', $errors) !!}">
                                            {!! Form::label('event_after', 'In your own words describe what happened after the incident', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('event_after', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('event_after', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                            {!! Form::label('notes', 'Notes (admin viewable only)', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                <a href="/site/incident/{{ $incident->id }}" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                            {!! Form::close() !!} <!-- END FORM-->
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

    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}

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

        $("#assign_task").change(function () {
            updateFields();
        });

        updateFields();

        function updateFields() {
            $("#statement_fields").hide();
            if ($("#assign_task").val() == '0') {
                $("#statement_fields").show();
            }
        }
    });
</script>
@stop

