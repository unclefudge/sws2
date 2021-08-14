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
        <li><span>Witness Statement</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Witness Statement</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($witness, ['method' => 'PATCH', 'action' => ['Site\Incident\SiteIncidentWitnessController@update',$incident->id, $witness->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        {{-- User + Name --}}
                        <div class="row">
                            @if (Auth::user()->allowed2('edit.site.incident', $incident))
                                {{-- User Id --}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('user_id', $errors) !!}">
                                        {!! Form::label('user_id', 'Witness', ['class' => 'control-label']) !!}
                                        {!! Form::select('user_id', ['' => 'Select user'] + Auth::user()->company->usersSelect('select'),
                                             null, ['class' => 'form-control select2', 'name' => 'user_id', 'id'  => 'user_id',]) !!}
                                        {!! fieldErrorMessage('user_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                            @endif
                            {{-- Name --}}
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                    {!! Form::label('name', 'Full name', ['class' => 'control-label']) !!}
                                    {!! Form::text('name', null, ['class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly' ]) !!}
                                    {!! fieldErrorMessage('name', $errors) !!}
                                </div>
                            </div>
                        </div>

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
                                    {!! Form::textarea('event_before', null, ['rows' => '3', 'class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('event_before', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Event --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('event', $errors) !!}">
                                    {!! Form::label('event', 'In your own words describe the incident', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('event', null, ['rows' => '3', 'class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('event', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Event After --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('event_after', $errors) !!}">
                                    {!! Form::label('event_after', 'In your own words describe what happened after the incident', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('event_after', null, ['rows' => '3', 'class' => 'form-control', (Auth::user()->allowed2('edit.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('event_after', $errors) !!}
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}

<script type="text/javascript">
    $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

    $(document).ready(function () {
        /* Select2 */
        //$("#type").select2({placeholder: "Check all applicable"});
        $("#user_id").select2({placeholder: "Select user"});

        updateFields();

        // On Change Type
        $("#type").change(function () {
            updateFields();
        });

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

        function updateFields() {
            $("#field_type_other").hide();

            // Type Other
            if ($("#type").val() == '13')
                $("#field_type_other").show();
        }

        $("#btn-delete").click(function (e) {
            e.preventDefault();

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this witness statement!<br><b>" + $('#name').val() + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: '/site/incident/{{ $incident->id }}/witness/{{ $witness->id }}',
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted witness statement');
                        window.location.href = "/site/incident/{{ $witness->id }}/admin";
                    },
                });
            });
        });

    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop

