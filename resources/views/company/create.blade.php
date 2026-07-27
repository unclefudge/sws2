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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Company</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                <x-form.hidden name="parent_company" :value="Auth::user()->company->id"/>
                                <div class="row">
                                    <div class="col-md-7">
                                        {{-- Company Name --}}
                                        <div class="col-md-12">
                                            <x-form.input name="name" label="Company Name"/>
                                        </div>
                                        {{-- User Details --}}
                                        <div class="col-md-12">
                                            <x-form.input name="person_name" label="Persons Name"/>
                                        </div>
                                        <div class="col-md-12">
                                            <x-form.input name="email" label="Email"/>
                                        </div>
                                        <div class="col-md-12">
                                            <x-form.select name="category" label="Category" help="Used to determine which documents are required to be WHS compliant. Public Liability, Workers Comp. Sickness & Accident, Contractors Licence etc"
                                                           :options="array_merge(['' => 'Select one'], $companyTypes::all())" plugin="select2"/>
                                        </div>
                                        <div class="col-md-12">
                                            <x-form.select name="trades[]" label="Trade(s)" :options="Auth::user()->company->tradeListSelect()" plugin="select2" title="Select one or more trades" multiple/>
                                            <x-form.error name="planned_trades"/>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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
