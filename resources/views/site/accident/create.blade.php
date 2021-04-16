@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription)
            <li><a href="/site/accident">Site Accidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Lodge Accident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="note note-warning">
            To be completed by the Primary Contractor AND Construction Supervisor immediately after:
            <ul>
                <li>A lost time injury or</li>
                <li>A incident with the potenital cause serious injury / illness occurs</li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Lodge Accident Report</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('siteAccident', ['action' => 'Site\SiteAccidentController@store', 'class' => 'horizontal-form']) !!}
                        {!! Form::hidden('status', '1') !!}

                        @include('form-error')
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                        {!! Form::label('date', 'Date / Time of Incident', ['class' => 'control-label']) !!}
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('date', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('date', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Workers details</h4>
                            <!-- Name / Age / Occupation -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('company', $errors) !!}">
                                        {!! Form::label('company', 'Company', ['class' => 'control-label']) !!}
                                        {!! Form::text('company', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('company', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('age', $errors) !!}">
                                        {!! Form::label('age', 'Age', ['class' => 'control-label']) !!}
                                        {!! Form::text('age', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('age', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('occupation', $errors) !!}">
                                        {!! Form::label('occupation', 'Occupation', ['class' => 'control-label']) !!}
                                        {!! Form::text('occupation', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('occupation', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Incident details</h4>
                            <!-- Location + Nature -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Location of Incident (be specific)', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('location', null, ['rows' => '2', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('nature', $errors) !!}">
                                        {!! Form::label('nature', 'Nature of Injury / Illness', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('nature', null, ['rows' => '2', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('nature', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Description -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::label('info', 'Description of Incident (describe in detail)', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('info', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Damage / Referred -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group {!! fieldHasError('damage', $errors) !!}">
                                        {!! Form::label('damage', 'Damage to Equipment / Property', ['class' => 'control-label']) !!}
                                        {!! Form::text('damage', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('damage', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('referred', $errors) !!}">
                                        {!! Form::label('referred', 'Referred / Transferred to', ['class' => 'control-label']) !!}
                                        {!! Form::select('referred', ['' => 'Select option', 'Hospital' => 'Hospital', 'Doctors' => 'Doctors',
                                         'Home' => 'Home', 'Continued Work' => 'Continued Work', 'Other' => 'Other'],
                                         null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('referred', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Preventative Action -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                        {!! Form::label('action', 'Recommended Preventative Action', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('action', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('action', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/accident" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


    @section('page-level-plugins-head')

            <!--<link href="../assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />-->


    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    @stop

    @section('page-level-plugins')
            <!--<script src="../assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="../assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="../assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="../assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="../assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>-->

    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site"});

    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate : new Date()
    });
</script>
@stop


