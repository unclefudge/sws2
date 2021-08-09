@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/comms/notify/">Notify</a><i class="fa fa-circle"></i></li>
        <li><span>Create Alert Notification</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Alert Notification</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('notify', ['action' => ['Comms\NotifyController@store'], 'files' => true]) !!}
                        @include('form-error')

                        {!! Form::hidden('company_id', Auth::user()->company_id, ['class' => 'form-control', 'id' => 'company_id']) !!}
                        {!! Form::hidden('type', 'user', ['id' => 'type']) !!}

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Title', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Date(s) alert wll be shown', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-start-date="0d">
                                            {!! Form::text('from', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('to', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                        </div>
                                        {!! fieldErrorMessage('from', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                        {!! Form::label('action', 'Frequency of Alert', ['class' => 'control-label']) !!}
                                        {!! Form::select('action', ['once' => 'Only once', 'many' => 'For whole duration of date range'],
                                             'once', ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('action', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::label('info', 'Alert Message', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('info', null, ['rows' => '4', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-6">
                                    <br>
                                    <div class="note note-warning">
                                        Alert Notifications are displayed immediatly after a user logs in and either:
                                        <ul>
                                            <li>a) Only once</li>
                                            <li>b) Each login for the whole duration of date range</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('assign_to', $errors) !!}">
                                        {!! Form::label('assign_to', 'Send Alert To', ['class' => 'control-label']) !!}
                                        @if (Auth::user()->company->subscription)
                                            {!! Form::select('assign_to', ['' => 'Select type', 'user' => 'User', 'company' => 'Company', 'role' => 'Role', 'site' => 'Site'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        @else
                                            {!! Form::select('assign_to', ['' => 'Select type', 'user' => 'User'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        @endif
                                        {!! fieldErrorMessage('assign_to', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-10" id="user_div" style="display: none">
                                    <div class="form-group {!! fieldHasError('user_list', $errors) !!}">
                                        {!! Form::label('user_list', 'User(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('user_list', Auth::user()->company->usersSelect('ALL', 1),
                                             null, ['class' => 'form-control select2', 'name' => 'user_list[]', 'multiple' => 'multiple', 'width' => '100%']) !!}
                                        {!! fieldErrorMessage('user_list', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-10" id="company_div" style="display: none">
                                    <div class="form-group {!! fieldHasError('company_list', $errors) !!}">
                                        {!! Form::label('company_list', 'Company(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('company_list', Auth::user()->company->companiesSelect('ALL'),
                                             null, ['class' => 'form-control select2', 'name' => 'company_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('company_list', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-10" id="group_div" style="display: none">
                                    <div class="form-group {!! fieldHasError('group_list', $errors) !!}">
                                        {!! Form::label('group_list', 'Group(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('group_list', ['primary.contact' => 'Primary Contacts'],
                                             null, ['class' => 'form-control select2', 'name' => 'group_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('group_list', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-10" id="role_div" style="display: none">
                                    <div class="form-group {!! fieldHasError('role_list', $errors) !!}">
                                        {!! Form::label('role_list', 'Roles(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('role_list', App\Models\Misc\Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray(),
                                             null, ['class' => 'form-control select2', 'name' => 'role_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('role_list', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-10" id="site_div" style="display: none">
                                    <div class="form-group {!! fieldHasError('site_list', $errors) !!}">
                                        {!! Form::label('site_list', 'Site(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_list', Auth::user()->company->sitesSelect('ALL'),
                                             null, ['class' => 'form-control select2', 'name' => 'site_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('site_list', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/comms/notify" class="btn default"> Back</a>
                                <button class="btn dark" id="test_alert">View Test Alert</button>
                                <button type="submit" class="btn green">Create</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                                <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#user_list").select2({
            placeholder: "Select",
            width: '100%',
        });
        $("#company_list").select2({
            placeholder: "Select",
            width: '100%'
        });

        $("#group_list").select2({
            placeholder: "Select",
            width: '100%'
        });

        $("#role_list").select2({
            placeholder: "Select",
            width: '100%'
        });

        $("#site_list").select2({
            placeholder: "Select",
            width: '100%'
        });

        $("#test_alert").click(function (e) {
            e.preventDefault();
            swal($("#name").val(), $("#info").val());
        })

        /*
        $("#test_alert").click(function (e) {
            e.preventDefault();
            var string = $("#info").val().replace(/(?:\r\n|\r|\n)/g, '<br />');
            swal({
                title: $("#name").val(),
                text: '<span style="text-align:left">' + string + '</span>',
                html: true
            });
        })*/

        // On Change Assign To
        $("#assign_to").change(function () {
            showAssignedList();
        });


        function showAssignedList() {
            $("#user_div").hide();
            $("#company_div").hide();
            $("#group_div").hide();
            $("#role_div").hide();
            $("#site_div").hide();
            $("#type").val('user');

            // Assign to User selected
            if ($("#assign_to").val() == 'user')
                $("#user_div").show();
            // Assign to Company selected
            if ($("#assign_to").val() == 'company')
                $("#company_div").show();
            // Assign to Group selected
            if ($("#assign_to").val() == 'group')
                $("#group_div").show();
            // Assign to Role selected
            if ($("#assign_to").val() == 'role')
                $("#role_div").show();
            // Assign to Group selected
            if ($("#assign_to").val() == 'site') {
                $("#site_div").show();
                $("#type").val('site');
            }
        }

        showAssignedList();
    });
</script>
@stop

