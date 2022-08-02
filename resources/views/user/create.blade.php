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
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create New User</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('user', ['action' => 'UserController@store', 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group {!! fieldHasError('employment_type', $errors) !!}">
                                        {!! Form::label('employment_type', 'Employment type * : What is the relationship of this worker to your business', ['class' => 'control-label']) !!}
                                        {!! Form::select('employment_type', ['' => 'Select type', '1' => 'Employee - Our company employs them directly',
                                        '2' => 'External Employment Company - Our company employs them using an external labour hire business',  '3' => 'Subcontractor - They are a separate entity that subcontracts to our company'],
                                                 '', ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('employment_type', $errors) !!}
                                    </div>
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
                                        <div class="form-group {!! fieldHasError('username', $errors) !!}">
                                            {!! Form::label('username', 'Username *', ['class' => 'control-label']) !!}
                                            {!! Form::text('username', null, ['class' => 'form-control', 'required']) !!}
                                            {!! fieldErrorMessage('username', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('password', $errors) !!}">
                                            {!! Form::label('password', 'Password *', ['class' => 'control-label']) !!}
                                            {!! Form::text('password', null, ['class' => 'form-control', 'required', 'placeholder' => 'User will be forced to choose new password upon login']) !!}
                                            {!! fieldErrorMessage('password', $errors) !!}
                                        </div>
                                    </div>
                                    {{--}}
                                    <div class="col-md-2 pull-right">
                                        <div class="form-group {!! fieldHasError('security', $errors) !!}">
                                            <p class="myswitch-label" style="font-size: 14px">Security Access
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Grants user the abilty to edit other users permissions with your company" data-original-title="Security Access">
                                                    <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a></p>
                                            {!! Form::label('security', "&nbsp;", ['class' => 'control-label']) !!}
                                            {!! Form::checkbox('security', '1', null,
                                             ['class' => 'make-switch',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                            {!! fieldErrorMessage('security', $errors) !!}
                                        </div>
                                    </div>--}}
                                </div>

                                {{-- Roles--}}
                                <div class="row">
                                    @if(Auth::user()->company->subscription && count(Auth::user()->company->rolesSelect('int')))
                                        {!! Form::hidden('subscription', 1) !!}
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('roles', $errors) !!}">
                                                {!! Form::label('roles', 'Role(s)', ['class' => 'control-label']) !!}
                                                {!! Form::select('roles', Auth::user()->company->rolesSelect('int'), null, ['class' => 'form-control select2-multiple', 'name' => 'roles[]', 'multiple', 'required']) !!}
                                                {!! fieldErrorMessage('roles', $errors) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Contact Details --}}
                                <h3 class="font-green form-section">Contact Details</h3>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('firstname', $errors) !!}">
                                            {!! Form::label('firstname', 'First Name *', ['form-control', 'required']) !!}
                                            {!! Form::text('firstname', null, ['class' => 'form-control', 'required']) !!}
                                            {!! fieldErrorMessage('firstname', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('lastname', $errors) !!}">
                                            {!! Form::label('lastname', 'Last Name *', ['class' => 'control-label']) !!}
                                            {!! Form::text('lastname', null, ['class' => 'form-control', 'required']) !!}
                                            {!! fieldErrorMessage('lastname', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('address', $errors) !!}">
                                            {!! Form::label('address', 'Address', ['class' => 'control-label']) !!}
                                            {!! Form::text('address', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('address', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group {!! fieldHasError('suburb', $errors) !!}">
                                                    {!! Form::label('suburb', 'Suburb', ['class' => 'control-label']) !!}
                                                    {!! Form::text('suburb', null, ['class' => 'form-control']) !!}
                                                    {!! fieldErrorMessage('suburb', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group {!! fieldHasError('state', $errors) !!}">
                                                    {!! Form::label('state', 'State', ['class' => 'control-label']) !!}
                                                    {!! Form::select('state', $ozstates::all(), 'NSW', ['class' => 'form-control bs-select']) !!}
                                                    {!! fieldErrorMessage('state', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                                                    {!! Form::label('postcode', 'Postcode', ['class' => 'control-label']) !!}
                                                    {!! Form::text('postcode', null, ['class' => 'form-control']) !!}
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
                                            {!! Form::text('phone', null, ['class' => 'form-control']) !!}
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
                                <h3 class="font-green form-section">Additional Details</h3>

                                {{-- Trades --}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('onsite', $errors) !!}">
                                            {!! Form::label('onsite', 'Apprentice', ['class' => 'control-label']) !!}
                                            {!! Form::select('onsite', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('onsite', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('trades', $errors) !!}">
                                            {!! Form::label('trades', 'Trades', ['class' => 'control-label']) !!}
                                            {!! Form::select('trades', Auth::user()->company->tradeListSelect(), Auth::user()->tradesSkilledIn->pluck('id')->toArray(), ['class' => 'form-control select2', 'name' => 'trades[]', 'title' => 'Select one or more trades', 'multiple', 'id' => 'trades']) !!}
                                            {!! fieldErrorMessage('trades', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                {{-- Apprentice --}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('apprentice', $errors) !!}">
                                            {!! Form::label('apprentice', 'Apprentice', ['class' => 'control-label']) !!}
                                            {!! Form::select('apprentice', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('apprentice', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('apprentice_start', $errors) !!}" id="apprentice_start_field">
                                            {!! Form::label('apprentice_start', 'Start date', ['class' => 'control-label']) !!}
                                            <div class="input-group date date-picker">
                                                {!! Form::text('apprentice_start', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                            </div>
                                            {!! fieldErrorMessage('apprentice_start', $errors) !!}
                                        </div>
                                    </div>
                                </div>--}}

                                <!-- Notes -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                            {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('notes', null, ['rows' => '2', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('notes', $errors) !!}
                                            <span class="help-block"> For internal use only </span>
                                        </div>
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
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
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

