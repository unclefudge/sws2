@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')

@extends('layout-guest')

@section('pagetitle')
    <div class="page-title">
        <h1>Welcome to SafeWorksite</h1>
    </div>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Sign Up</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <p>Your company {{ $company->name }} has been invited to join SafeWorksite by <b>{{ $company->reportsTo()->name }}</b>.</p>
                            <p>You will be guided through the following steps to set up your company.</p>
                            {{-- Company Signup Progress --}}
                            <div class="mt-element-step">
                                <div class="row step-line" id="steps">
                                    <div class="col-sm-3 mt-step-col first">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Business Owner</div>
                                        <div class="mt-step-content font-grey-cascade">Add primary user</div>
                                    </div>
                                    <div class="col-sm-3 mt-step-col">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Company Info</div>
                                        <div class="mt-step-content font-grey-cascade">Add company info</div>
                                    </div>
                                    <div class="col-sm-3 mt-step-col">
                                        <div class="mt-step-number bg-white font-grey">3</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Workers</div>
                                        <div class="mt-step-content font-grey-cascade">Add workers</div>
                                    </div>
                                    <div class="col-sm-3 mt-step-col last">
                                        <div class="mt-step-number bg-white font-grey">4</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                        <div class="mt-step-content font-grey-cascade">Upload documents</div>
                                    </div>
                                </div>
                            </div>
                            <p>
                                <b>Step 1:</b> Add the business owner (primary user)<br>
                                <b>Step 2:</b> Add your company information<br>
                                <b>Step 3:</b> All your workers (remaining users)<br>
                                <b>Step 4:</b> Uploaded required documents<br>
                            </p>
                            <div class="form-actions right">
                                <a href="/signup/primary/{{ $company->signup_key }}" class="btn green">Continue</a>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop