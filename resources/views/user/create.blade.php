@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')

@extends('layout')

@if (Auth::user()->company->status != 2)
    @section('breadcrumbs')
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            @if (Auth::user()->hasAnyPermissionType('user'))
                <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
            @endif
            <li><span>Create new user</span></li>
        </ul>
    @stop
@endif

@section('content')
    <div class="page-content-inner">
        @if (Auth::user()->company->status == 2)
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
                    <div class="col-sm-3 mt-step-col active">
                        <a href="/signup/company/{{ Auth::user()->company_id }}">
                            <div class="mt-step-number bg-white font-grey">2</div>
                        </a>
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
                <b>Step 3: Add all additional users that work on job sites.</b><br><br>All workers require their own login<br><br>
                <ul>
                    <li>Add users by clicking
                        <button class="btn dark btn-outline btn-xs" href="javascript:;"> Add User</button>
                    </li>
                </ul>
                Once you've added all your users please click
                <button class="btn dark btn-outline btn-xs" href="javascript:;"> Continue</button>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New User</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <x-form.select name="employment_type" label="Employment type * : What is the relationship of this worker to your business"
                                                       :options="['' => 'Select type', '1' => 'Employee - Our company employs them directly', '2' => 'External Employment Company - Our company employs them using an external labour hire business', '3' => 'Subcontractor - They are a separate entity that subcontracts to our company']"
                                                       value=""/>
                                    </div>
                                </div>

                                {{-- Company Creation field --}}
                                <div class="note note-warning" id="company_creation_fields">
                                    <b>This person is a separate entity (Soul Trader, Partnership, Trading Trust or Company).</b><br><br>
                                    This means that you need to collect extra documentation from them in order for you to be compliant.<br><br>
                                    {{--}}
                                    @if (Auth::user()->company->status == 2)
                                        Add this person once you have completed the Sign Up process
                                    @else
                                        Add this person via <a href="/company/create" class="btn dark btn-sm" data-original-title="Add" style="margin-left: 20px">Add Company</a>
                                    @endif
                                    --}}
                                </div>

                                {{-- User Creation field --}}
                                <div id="user_creation_fields">
                                    {{-- Login Details --}}
                                    <h3 class="font-green form-section">Login Details</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.input name="username" label="Username *" required/>
                                        </div>
                                        <div class="col-md-6">
                                            <x-form.input name="password" label="Password *" placeholder="User will be forced to choose new password upon login" required/>
                                        </div>
                                        {{--}}
                                        <div class="col-md-2 pull-right">
                                            <div class="form-group {{ $errors->has('security') ? 'has-error' : '' }}">
                                                <p class="myswitch-label" style="font-size: 14px">Security Access
                                                    <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                       data-content="Grants user the abilty to edit other users permissions with your company" data-original-title="Security Access">
                                                        <i class="fa fa-question-circle font-grey-silver"></i>
                                                    </a></p>
                                                <label for="security" class="control-label">&nbsp;</label>
                                                <input type="checkbox" name="security" value="1" class="make-switch" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger">
                                                <x-form.error name="security"/>
                                            </div>
                                        </div>--}}
                                    </div>

                                    {{-- Roles--}}
                                    <div class="row">
                                        @if(Auth::user()->company->subscription && count(Auth::user()->company->rolesSelect('int')))
                                            <x-form.hidden name="subscription" :value="1"/>
                                            <div class="col-md-6">
                                                <x-form.select name="roles[]" label="Role(s)" :options="Auth::user()->company->rolesSelect('int')" plugin="select2" multiple required/>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Contact Details --}}
                                    <h3 class="font-green form-section">Contact Details</h3>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-form.input name="firstname" label="First Name *" required/>
                                        </div>
                                        <div class="col-md-4">
                                            <x-form.input name="lastname" label="Last Name *" required/>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-form.input name="address" label="Address"/>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <x-form.input name="suburb" label="Suburb"/>
                                                </div>
                                                <div class="col-md-3">
                                                    <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                                </div>
                                                <div class="col-md-3">
                                                    <x-form.input name="postcode" label="Postcode"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Phone + Email -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.input name="phone" label="Phone"/>
                                        </div>
                                        <div class="col-md-5">
                                            <x-form.input name="email" label="Email *"/>
                                        </div>
                                    </div>


                                    {{-- Additional Details --}}
                                    <h3 class="font-green form-section">Additional Details</h3>

                                    {{-- Trades --}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {{ $errors->has('onsite') ? 'has-error' : '' }}">
                                                <x-form.select name="onsite" label="Apprentice" :options="['0' => 'No', '1' => 'Yes']"/>

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('trades') ? 'has-error' : '' }}">
                                                <x-form.select name="trades[]" label="Trades" :options="Auth::user()->company->tradeListSelect()" :value="Auth::user()->tradesSkilledIn->pluck('id')->toArray()" plugin="select2" title="Select one or more trades" multiple/>

                                            </div>
                                        </div>
                                    </div>
                                    {{-- Apprentice --}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {{ $errors->has('apprentice') ? 'has-error' : '' }}">
                                                <x-form.select name="apprentice" label="Apprentice" :options="['0' => 'No', '1' => 'Yes']"/>

                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {{ $errors->has('apprentice_start') ? 'has-error' : '' }}" id="apprentice_start_field">
                                                <div class="input-group date date-picker">
                                                    <x-form.datepicker name="apprentice_start" label="Start date" value=""/>
                                                    <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>--}}

                                    <!-- Notes -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="notes" label="Notes" rows="2"/>
                                            <span class="help-block"> For internal use only </span>
                                        </div>
                                    </div>
                                    <div class="form-actions right">
                                        @if (Auth::user()->company->status == 2)
                                            <a href="/signup/workers/{{ Auth::user()->company_id }}" class="btn default"> Back</a>
                                        @else
                                            <a href="/user" class="btn default"> Back</a>
                                        @endif
                                        <button type="submit" class="btn green"> Save</button>
                                    </div>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $('.date-picker').datepicker({autoclose: true, clearBtn: true, format: 'dd/mm/yyyy'});

        $(document).ready(function () {

            /* Select2 */
            $("#roles").select2({placeholder: "Select one or more roles", width: '100%'});
            $("#trades").select2({placeholder: "Select one or more", width: '100%'});

            $("#user_creation_fields").hide();
            $("#company_creation_fields").hide();
            $("#apprentice_start_field").hide();

            // Show User Creation fields
            if ($("#employment_type").val() == 1 || $("#employment_type").val() == 2)
                $("#user_creation_fields").show();

            // Show Company Creations field
            if ($("#employment_type").val() == 3)
                $("#company_creation_fields").show();

            $("#employment_type").on("change", function () {
                $("#user_creation_fields").hide();
                $("#company_creation_fields").hide();

                //if ($("#employment_type").val() == 1 || $("#employment_type").val() == 2)
                $("#user_creation_fields").show();
                if ($("#employment_type").val() == 3)
                    $("#company_creation_fields").show();
            });

            // Show Apprentice Start field
            if ($("#apprentice").val() == 1)
                $("#apprentice_start_field").show();

            $("#apprentice").on("change", function () {
                $("#apprentice_start_field").hide();
                if ($("#apprentice").val() == 1)
                    $("#apprentice_start_field").show();
            });


            // Show appropriate Subcontractor message
            /*$("#subcontractor_type").on("change", function () {
                $("#subcontractor_wc").hide();
                $("#subcontractor_sa").hide();
                if ($("#subcontractor_type").val() == '1' || $("#subcontractor_type").val() == '4')
                    $("#subcontractor_wc").show();
                if ($("#subcontractor_type").val() == '2' || $("#subcontractor_type").val() == '3')
                    $("#subcontractor_sa").show();
            });*/

        });
    </script>
@stop

