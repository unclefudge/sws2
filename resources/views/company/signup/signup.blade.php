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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Auth\RegistrationController::class, 'refStore']) }}" class="horizontal-form">
                        @csrf
                        @include('form-error')

                        <div class="form-body">
                            {{-- Login Details --}}
                            <h3 class="font-green form-section">Login Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="username" label="Username" required/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password" class="control-label">Password</label>
                                        <input type="password" class="form-control" name="password" value="{{ old('password') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="control-label">Password Confirmation</label>
                                        <input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}" required>
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Details --}}
                            <h3 class="font-green form-section">Contact Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="firstname" label="First Name"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="lastname" label="Last Name"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="address" label="Address"/>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <x-form.input name="suburb" label="Suburb"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <x-form.input name="postcode" label="Postcode"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone + Email -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <x-form.input name="phone" label="Phone"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <x-form.input name="email" label="Email"/>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Details --}}
                            <h3 class="font-green form-section">Additional Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <x-form.select name="employment_type" label="Employment Type" :options="['' => 'Select type', '1' => 'Employee', '2' => 'Subcontractor', '3' => 'External Employment Company']"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="display:none" id="subcontract_type_field">
                                        <x-form.select name="subcontractor_type" label="Subcontractor Entity" :options="$companyEntity::all()"/>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop