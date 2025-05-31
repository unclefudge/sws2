@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/planner/publicholidays">Public Holidays</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Public Holiday</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('publicholiday', ['action' => ['Site\Planner\PublicHolidayController@store']]) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                        {!! Form::label('date', 'Date', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('date', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'placeholder' => 'dd/mm/yyyy']) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('completed', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/site/hazard" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit">Submit</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {

        });
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
        });

    </script>
@stop

