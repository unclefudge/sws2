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
        <div class="note note-warning">
            <p>Please complete the below form to register with SafeWorksite</p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Registration</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('user', ['action' => 'Auth\RegistrationController@refStore', 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            {{-- Login Details --}}
                            <h3 class="font-green form-section">Login Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('username', $errors) !!}">
                                        {!! Form::label('username', 'Username', ['class' => 'control-label']) !!}
                                        {!! Form::text('username', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                        {!! fieldErrorMessage('username', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('password', $errors) !!}">
                                        {!! Form::label('password', 'Password', ['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" name="password" value="{{ old('password') }}" required>
                                        {!! fieldErrorMessage('password', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('password_confirmation', $errors) !!}">
                                        {!! Form::label('password_confirmation', 'Password Confirmation', ['class' => 'control-label']) !!}
                                        <input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}" required>
                                        {!! fieldErrorMessage('password_confirmation', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Details --}}
                            <h3 class="font-green form-section">Contact Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('firstname', $errors) !!}">
                                        {!! Form::label('firstname', 'First Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('firstname', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('firstname', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('lastname', $errors) !!}">
                                        {!! Form::label('lastname', 'Last Name', ['class' => 'control-label']) !!}
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
                                        {!! Form::label('email', 'Email', ['class' => 'control-label']) !!}
                                        {!! Form::text('email', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('email', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Details --}}
                            <h3 class="font-green form-section">Additional Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('employment_type', $errors) !!}">
                                        {!! Form::label('employment_type', 'Employment Type', ['class' => 'control-label']) !!}
                                        {!! Form::select('employment_type', ['' => 'Select type', '1' => 'Employee', '2' => 'Subcontractor',  '3' => 'External Employment Company'],
                                                 null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('employment_type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('subcontractor_type', $errors) !!}" style="display:none" id="subcontract_type_field">
                                        {!! Form::label('subcontractor_type', 'Subcontractor Entity', ['class' => 'control-label']) !!}
                                        {!! Form::select('subcontractor_type', $companyEntity::all(),
                                                 null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('subcontractor_type', $errors) !!}
                                        <br><br>
                                        <div class="note note-warning" style="display: none" id="subcontractor_wc">
                                            A separate Worker's Compensation Policy is required for this Subcontractor
                                        </div>
                                        <div class="note note-warning" style="display: none" id="subcontractor_sa">
                                            A separate Sickness & Accident Policy is required for this Subcontractor
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{--
                            <div class="form-actions right">
                                <button type="submit" class="btn green">Sign Up</button>
                            </div>
                            --}}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop {{-- END Content --}}