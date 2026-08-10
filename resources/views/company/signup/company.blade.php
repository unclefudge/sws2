@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
@extends('layout-guest')

@section('content')
    <div class="page-content-inner">

        {{-- Company Signup Progress --}}
        <div class="mt-element-step">
            <div class="row step-line" id="steps">
                <div class="col-sm-3 mt-step-col first active">
                    <a href="/signup/user/{{ Auth::user()->company->primary_user }}">
                        <div class="mt-step-number bg-white font-grey">1</div>
                    </a>
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
        <div class="note note-warning">
            <p><b>Step 2: Add information relating to your company.</b></p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-users "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Company Info</span>
                            <span class="caption-helper"> ID: {{ $company->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-12">
                                <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanySignUpController::class, 'companyUpdate'], $company->id) }}">
                                    @csrf
                                    <x-form.hidden name="signup_step" value="3"/>
                                    <div class="form-body">
                                        <h1 class="sbold hidden-sm hidden-xs" style="margin: -20px 0 15px 0">{{ $company->name }}</h1>
                                        <h3 class="sbold visible-sm visible-xs">{{ $company->name }}</h3>

                                        @include('form-error')
                                        {{-- Contact Details --}}
                                        <h3 class="font-green form-section">Company Details</h3>
                                        {{-- Name --}}
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <x-form.input name="name" label="Company Name *" :value="$company->name" required/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Address --}}
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <x-form.input name="address" label="Address *" :value="$company->address" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <x-form.input name="suburb" label="Suburb *" :value="$company->suburb" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <x-form.select name="state" label="State *" :options="$ozstates::all()" :value="$company->state ?: 'NSW'" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <x-form.input name="postcode" label="Postcode *" :value="$company->postcode" required/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Phone + Email --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <x-form.input name="phone" label="Phone *" :value="$company->phone" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <x-form.input name="email" label="Email *" :value="$company->email" required/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Primary Contact --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <x-form.select name="primary_user" label="Primary User Contact *" :options="$company->usersSelect('prompt')" :value="$company->primary_user" required/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Business Details --}}
                                        <h3 class="font-green form-section">Business Details</h3>
                                        {{-- ABN + Entity + Group + GST --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <x-form.select name="business_entity" label="Business Entity *" :options="$companyEntityTypes::all()" :value="$company->business_entity" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <x-form.input name="abn" label="ABN *" :value="$company->abn" required/>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <x-form.select name="gst" label="GST Registered *" :options="['1' => 'Yes', '0' => 'No']" :value="$company->gst" required/>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-actions right">
                                            <button type="submit" class="btn green"> Continue</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $company->displayUpdatedBy() !!}
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
@stop