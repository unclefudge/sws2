@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/leave">Company leave</a><i class="fa fa-circle"></i></li>
        <li><span>Edit leave</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Edit Leave </span>
                            <span class="caption-helper"> - {{ $leave->company->name_alias }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('company_leave', ['method' => 'PATCH', 'action' => ['Company\CompanyLeaveController@update', $leave->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('company_id', $errors) !!}">
                                        {!! Form::label('company_name', 'Company', ['class' => 'control-label']) !!}
                                        {!! Form::text('company_name', $leave->company->name_alias, ['class' => 'form-control', 'readonly']) !!}
                                        {!! Form::hidden('company_id', $leave->company_id) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Leave From', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy">
                                            {!! Form::text('from', $leave->from->format('d/m/Y'), ['class' => 'form-control', 'readonly', ($leave->from->lt(Carbon\Carbon::now())) ? 'disabled' : '', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('to', $leave->to->format('d/m/Y'), ['class' => 'form-control', 'readonly', ($leave->from->lt(Carbon\Carbon::now())) ? 'disabled' : '', 'style' => 'background:#FFF']) !!}
                                        </div>
                                        {!! fieldErrorMessage('start_date', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <h3 class="form-section"></h3>
                            <!-- Notes -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', $leave->notes, ['rows' => '2', 'class' => 'form-control', ($leave->from->lt(Carbon\Carbon::now())) ? 'readonly' : '']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                        <span class="help-block"> For internal use only </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/company/leave" class="btn default"> Back</a>
                                @if (($leave->from->gt(Carbon\Carbon::now())))
                                    <button type="submit" class="btn green">Save</button>
                                @endif
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
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
@stop

