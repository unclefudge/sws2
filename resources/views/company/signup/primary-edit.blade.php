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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanySignUpController::class, 'userUpdate'], $user->id) }}" class="horizontal-form">
                        @csrf
                        @include('form-error')

                        <div class="form-body">
                            {{-- Login Details --}}
                            <h3 class="font-green form-section">Login Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="username" label="Username *" :value="$user->username" required/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password" class="control-label">Password *</label>
                                        <input type="password" class="form-control" name="password" value="{{ old('password') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="control-label">Password Confirmation *</label>
                                        <input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Contact Details --}}
                            <h3 class="font-green form-section">Contact Details</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="firstname" label="First Name *" :value="$user->firstname"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="lastname" label="Last Name *" :value="$user->lastname"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-form.input name="address" label="Address" :value="$user->address"/>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <x-form.input name="suburb" label="Suburb" :value="$user->suburb"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <x-form.select name="state" label="State" :options="$ozstates::all()" :value="$user->state ?: 'NSW'"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <x-form.input name="postcode" label="Postcode" :value="$user->postcode"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone + Email -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <x-form.input name="phone" label="Phone" :value="$user->phone"/>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <x-form.input name="email" label="Email *" :value="$user->email"/>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Details --}}
                            <h3 class="font-green form-section">Additional Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    {{--  Are you an Employee, Subcontractor or employed by External Employment Company? --}}
                                    <div class="form-group">
                                        <x-form.select name="employment_type" label="Employment type: What is the relationship of this worker to your business *" :options="['' => 'Select type', '1' => 'Employee - Our company employs them directly', '2' => 'External Employment Company - Our company employs them using an external labour hire business', '3' => 'Subcontractor - They are a separate entity that subcontracts to our company']" :value="$user->employment_type"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="display:none" id="subcontract_type_field">
                                        <x-form.select name="subcontractor_type" label="Subcontractor Entity" :options="$companyEntity::all()" :value="$user->subcontractor_type"/>
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
                            <div class="form-actions right">
                                <button type="submit" class="btn green">Continue</button>
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

            // Show Subcontractor field
            if ($("#employment_type").val() == '3')
                $("#subcontract_type_field").show();

            $("#employment_type").on("change", function () {
                $("#subcontract_type_field").hide();
                if ($("#employment_type").val() == '3')
                    $("#subcontract_type_field").show();
            });

            // Show appropriate Subcontractor message
            $("#subcontractor_type").on("change", function () {
                $("#subcontractor_wc").hide();
                $("#subcontractor_sa").hide();
                if ($("#subcontractor_type").val() == '1' || $("#subcontractor_type").val() == '4')
                    $("#subcontractor_wc").show();
                if ($("#subcontractor_type").val() == '2' || $("#subcontractor_type").val() == '3')
                    $("#subcontractor_sa").show();
            });
        });
    </script>
@stop