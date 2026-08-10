@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')
@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-user"></i> User Profile</h1>
    </div>
@stop

@if (Auth::user()->company->status != 2)
    @section('breadcrumbs')
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            @if (Auth::user()->hasAnyPermissionType('user'))
                <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
            @endif
            <li><a href="/user/{{ $user->id }}">Profile</a><i class="fa fa-circle"></i></li>
            <li><span>Edit</span></li>
        </ul>
    @stop
@endif


@section('content')
    <div class="page-content-inner">
        @if (Auth::user()->company->status == 2)
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
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-user "></i>
                            <span class="caption-subject font-green-haze bold uppercase">User Profile</span>
                            <span class="caption-helper"> ID: {{ $user->id }}</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->allowed2('edit.user', $user) && Auth::user()->company->status == 1)
                                <a href="/user/{{ $user->id }}/security" class="btn btn-circle green btn-outline btn-sm">
                                    <i class="fa fa-lock"></i> @if (Auth::user()->hasPermission2('edit.user.security'))
                                        Edit
                                    @endif Security Settings</a>
                            @endif
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-12">
                                <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'update'], $user->username) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h1 class="sbold hidden-sm hidden-xs" style="{!! ($user->name) ? 'margin: 0px' : 'margin: 0 0 15px 0' !!}}">{{ $user->name }}<br>
                                                    <small class='font-grey-cascade'>{{ $user->company->name_alias }}</small>
                                                </h1>
                                                <h3 class="sbold visible-sm visible-xs">{{ $user->name }}
                                                    <small class='font-grey-cascade' style="margin:0px"> {{ $user->company->name_alias }}</small>
                                                </h3>
                                                @if ($user->hasPermission2('edit.user.security') )
                                                    <span class='label label-warning'>Security Access</span>
                                                @endif
                                                @if ($user->id == $user->company->primary_user )
                                                    <span class='label label-info'>Primary Contact</span>
                                                @endif
                                                @if ($user->id == $user->company->secondary_user )
                                                    <span class='label label-info'>Secondary Contact</span>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <!-- Inactive User -->
                                                @if(!$user->status)
                                                    <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Inactive User</h3>
                                                @endif
                                                @if ($user->roles2->count() > 0)
                                                    <br><br>
                                                    @if ($user->rolesSBC() && Auth::user()->isCompany($user->company_id))
                                                        <b>Roles: </b>{{ $user->rolesSBC() }}<br>
                                                    @endif
                                                    @if ($user->company->parent_company && $user->parentRolesSBC())
                                                        <b>{{ $user->company->reportsTo()->name }} Roles:</b> {{ $user->parentRolesSBC() }}
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        @include('form-error')

                                        {{-- Login Details --}}
                                        <h3 class="font-green form-section">Login Details</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-form.input name="username" label="Username" :value="$user->username" required/>
                                            </div>
                                            @if(Auth::user()->allowed2('del.user', $user))
                                                <div class="col-md-3 pull-right">
                                                    <div class="form-group">
                                                        @if (Auth::user()->id == $user->id)
                                                            <x-form.select name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$user->status" disabled/>
                                                        @else
                                                            <x-form.select name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$user->status"/>
                                                        @endif
                                                        @if (Auth::user()->id == $user->id)
                                                            <span class="font-red">(can't disable own account)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @if (!Auth::user()->password_reset)
                                            <button class="btn dark" id="butt_password">Edit Password</button>
                                        @else
                                            <x-form.hidden name="password_force" :value="1"/>
                                        @endif
                                        <div class="row" @if (!Auth::user()->password_reset) style="display:none" @endif id="password_div">
                                            @if (Auth::user()->id != $user->id)
                                                <div class="col-md-6">
                                                    <x-form.input name="newpassword" label="Password" placeholder="User will be forced to choose new password upon login"/>
                                                </div>
                                            @else
                                                @if (Auth::user()->password_reset)
                                                    <div class="note note-warning">
                                                        <br><br><b>Your password has been reset and you are required to change it.</b><br><br><br>
                                                    </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="@if (Auth::user()->password_reset) has-error @endif">
                                                        <x-form.input name="password" label="Password" type="password"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="@if (Auth::user()->password_reset) has-error @endif">
                                                        <x-form.input name="password_confirmation" label="Re-type Password" type="password"/>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Contact Details --}}
                                        <h3 class="font-green form-section">Contact Details</h3>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <x-form.input name="firstname" label="First Name" :value="$user->firstname" required/>
                                            </div>
                                            <div class="col-md-4">
                                                <x-form.input name="lastname" label="Last Name" :value="$user->lastname" required/>
                                            </div>
                                        </div>
                                        {{-- Address --}}
                                        <div class="row">
                                            <div class="col-md-5">
                                                <x-form.input name="address" label="Address" :value="$user->address"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="suburb" label="Suburb" :value="$user->suburb"/>
                                            </div>
                                            <div class="col-md-2">
                                                <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                            </div>
                                            <div class="col-md-2">
                                                <x-form.input name="postcode" label="Postcode" :value="$user->postcode"/>
                                            </div>
                                        </div>

                                        {{-- Phone + Email --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-form.input name="phone" label="Phone" :value="$user->phone"/>
                                            </div>
                                            <div class="col-md-5">
                                                <x-form.input name="email" label="Email" :value="$user->email" required/>
                                            </div>
                                        </div>

                                        {{-- Additional Info --}}
                                        <h3 class="font-green form-section">Additional Information</h3>
                                        {{-- Employment Type --}}
                                        @if (Auth::user()->id != $user->id || (Auth::user()->hasPermission2('edit.user.security') && Auth::user()->isCompany($user->company_id)))
                                            <div class="row">
                                                <div class="col-md-6">
                                                    {{--  Are you an Employee, Subcontractor or employed by External Employment Company? --}}
                                                    <x-form.select name="employment_type" label="Employment type: What is the relationship of this worker to your business *"
                                                                   :options="['' => 'Select type', '1' => 'Employee - Our company employs them directly', '2' => 'External Employment Company - Our company employs them using an external labour hire business', '3' => 'Subcontractor - They are a separate entity that subcontracts to our company']"
                                                                   :value="$user->employment_type"/>
                                                </div>
                                                <div class="col-md-6">
                                                    <div style="display:none" id="subcontract_type_field">
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
                                        @endif

                                        {{-- Notes --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <x-form.textarea name="notes" label="Notes" :value="$user->notes" rows="2"/>
                                                <span class="help-block"> For internal use only </span>
                                            </div>
                                        </div>

                                        <div class="form-actions right">
                                            @if (Auth::user()->company->status == 2)
                                                <button type="submit" class="btn green"> Continue</button>
                                            @else
                                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                                <button type="submit" class="btn green"> Save</button>
                                            @endif
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
            {!! $user->displayUpdatedBy() !!}
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });

        $('#butt_password').click(function (e) {
            e.preventDefault();
            $('#password_div').show();
            $('#butt_password').hide();
        });

        $(document).ready(function () {

            /* Select2 */

            // Show Subcontractor field
            if ($("#employment_type").val() == '3')
                $("#subcontract_type_field").show();

            $("#employment_type").on("change", function () {
                $("#subcontract_type_field").hide();
                if ($("#employment_type").val() == '3')
                    $("#subcontract_type_field").show();
                else
                    $("#subcontractor_type").val(0);
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