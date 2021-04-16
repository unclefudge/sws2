@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('licenceTypes', 'App\Http\Utilities\LicenceTypes')
@inject('payrollTaxTypes', 'App\Http\Utilities\PayrollTaxTypes')
@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')

@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        <li><span>Create new company</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Company</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('company', ['action' => 'Company\CompanyController@store', 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            {!! Form::hidden('parent_company', Auth::User()->company->id) !!}
                            {!! Form::hidden('status', 2) !!}
                            <div class="row">
                                <div class="col-md-7">
                                    {{-- Company Name --}}
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('name', 'Company Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('name', $errors) !!}
                                        </div>
                                    </div>
                                    {{-- User Details --}}
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('person_name', $errors) !!}">
                                            {!! Form::label('person_name', 'Persons Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('person_name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('person_name', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('email', $errors) !!}">
                                            {!! Form::label('email', 'Email', ['class' => 'control-label']) !!}
                                            {!! Form::text('email', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('email', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('category', $errors) !!}">
                                            <label for="category" class="control-label">Category *</label>
                                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                               data-content="Used to determine which documents are required to be WHS compliant. Public Liability, Workers Comp. Sickness & Accident, Contractors Licence etc"
                                               data-original-title="Category"> <i class="fa fa-question-circle font-grey-silver"></i> </a>
                                            {!! Form::select('category',array_merge(['' => 'Select one'], $companyTypes::all()),
                                             null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('category', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('trades', $errors) !!} {!! fieldHasError('planned_trades', $errors) !!}">
                                            {!! Form::label('trades', 'Trade(s)', ['class' => 'control-label']) !!}
                                            {!! Form::select('trades', Auth::user()->company->tradeListSelect(),
                                             null, ['class' => 'form-control select2', 'name' => 'trades[]', 'title' => 'Select one or more trades', 'multiple', 'id' => 'trades']) !!}
                                            {!! fieldErrorMessage('trades', $errors) !!}
                                            {!! fieldErrorMessage('planned_trades', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <br>
                                    <div class="note note-warning">
                                        <p>This form will send an email to the specified company inviting them to join SafeWorksite.</p>
                                        <p><br>Once they have completed the sign up process you will be notified and will be able to access their details.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/company" class="btn default"> Back</a>
                                <button type="submit" class="btn green">Send Request</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop {{-- END Content --}}


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
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
        $("#trades").select2({placeholder: "Select one or more", width: '100%'});
        $("#category").select2({placeholder: "Select one", width: '100%'});
    });
</script>

@stop

