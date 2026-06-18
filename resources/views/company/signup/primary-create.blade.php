@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')

@extends('layout-guest')

@section('content')
    <div class="page-content-inner">
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
        <div class="note note-warning">
            <p><b>Step 1: Add information relating to the business owner (primary user) that will have full access to the website.</b></p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Business Owner (primary user)</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('user', ['action' => 'Auth\RegistrationController@primaryStore', 'class' => 'horizontal-form']) !!}
                        {!! Form::hidden('signup_key', $company->signup_key) !!}
                        @include('form-error')

                        <div class="form-body">
                            {{-- Login Details --}}
                            <h3 class="font-green form-section">Login Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('username', $errors) !!}">
                                        {!! Form::label('username', 'Username *', ['class' => 'control-label']) !!}
                                        {!! Form::text('username', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                        {!! fieldErrorMessage('username', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('password', $errors) !!}">
                                        {!! Form::label('password', 'Password *', ['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" name="password" value="{{ old('password') }}">
                                        {!! fieldErrorMessage('password', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('password_confirmation', $errors) !!}">
                                        {!! Form::label('password_confirmation', 'Password Confirmation *', ['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}">
                                        {!! fieldErrorMessage('password_confirmation', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Details --}}
                            <h3 class="font-green form-section">Contact Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('firstname', $errors) !!}">
                                        {!! Form::label('firstname', 'First Name *', ['class' => 'control-label']) !!}
                                        {!! Form::text('firstname', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('firstname', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('lastname', $errors) !!}">
                                        {!! Form::label('lastname', 'Last Name *', ['class' => 'control-label']) !!}
                                        {!! Form::text('lastname', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('lastname', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('address', $errors) !!}">
                                        {!! Form::label('address', 'Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('address', '', ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('address', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('suburb', $errors) !!}">
                                                {!! Form::label('suburb', 'Suburb', ['class' => 'control-label']) !!}
                                                {!! Form::text('suburb', '', ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('suburb', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('state', $errors) !!}">
                                                {!! Form::label('state', 'State', ['class' => 'control-label']) !!}
                                                {!! Form::select('state', $ozstates::all(),
                                                 'NSW', ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('state', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                                                {!! Form::label('postcode', 'Postcode', ['class' => 'control-label']) !!}
                                                {!! Form::text('postcode', '', ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('postcode', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone + Email -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('phone', $errors) !!}">
                                        {!! Form::label('phone', 'Phone', ['class' => 'control-label']) !!}
                                        {!! Form::text('phone', '', ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('phone', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('email', $errors) !!}">
                                        {!! Form::label('email', 'Email *', ['class' => 'control-label']) !!}
                                        {!! Form::text('email', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('email', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Details --}}
                            <h3 class="font-green form-section">Additional Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    {{--  Are you an Employee, Subcontractor or employed by External Employment Company? --}}
                                    <div class="form-group {!! fieldHasError('employment_type', $errors) !!}">
                                        {!! Form::label('employment_type', 'Employment type: What is the relationship of this worker to your business *', ['class' => 'control-label']) !!}
                                        {!! Form::select('employment_type', ['' => 'Select type', '1' => 'Employee - Our company employs them directly',
                                        '2' => 'External Employment Company - Our company employs them using an external labour hire business',  '3' => 'Subcontractor - They are a separate entity that subcontracts to our company'],
                                                 '', ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('employment_type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="display:none" id="subcontract_type_field">
                                        <br>
                                        <div class="note note-warning">
                                            You can not add a subcontractor as a Primary User for your company
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <button type="submit" class="btn green" id="continue">Continue</button>
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
    <script>
        $(document).ready(function () {

            /* Select2 */

            $("#continue").show();

            // Show Subcontractor field
            if ($("#employment_type").val() == '3') {
                $("#subcontract_type_field").show();
                $("#continue").show();
            }

            $("#employment_type").on("change", function () {
                $("#subcontract_type_field").hide();
                $("#continue").show();

                if ($("#employment_type").val() == '3') {
                    $("#subcontract_type_field").show();
                    $("#continue").hide();
                }
            });
        });
    </script>
@stop