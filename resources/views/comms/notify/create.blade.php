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
                            <span class="caption-subject font-green-haze bold uppercase">Create Alert Notification</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Comms\NotifyController::class, 'store']) }}" enctype="multipart/form-data">
                            @csrf
                        @include('form-error')

                        <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                        <x-form.hidden name="type" value="user"/>

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <x-form.input name="name" label="Title"/>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-3">
                                    <x-form.date-range from="from" to="to" label="Date(s) alert wll be shown" start-date="0d"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="action" label="Frequency of Alert" :options="['once' => 'Only once', 'many' => 'For whole duration of date range']" value="once"/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-5">
                                    <x-form.textarea name="info" label="Alert Message" rows="4"/>
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
                                    @if (Auth::user()->company->subscription)
                                        <x-form.select name="assign_to" label="Send Alert To" :options="['' => 'Select type', 'user' => 'User', 'company' => 'Company', 'role' => 'Role', 'site' => 'Site']"/>
                                    @else
                                        <x-form.select name="assign_to" label="Send Alert To" :options="['' => 'Select type', 'user' => 'User']"/>
                                    @endif
                                </div>
                                <div class="col-md-10" id="user_div" style="display: none">
                                    <x-form.select name="user_list[]" label="User(s)" :options="Auth::user()->company->usersSelect('ALL', 1)" plugin="select2" style="width:100%" multiple/>
                                </div>
                                <div class="col-md-10" id="company_div" style="display: none">
                                    <x-form.select name="company_list[]" label="Company(s)" :options="Auth::user()->company->companiesSelect('ALL')" plugin="select2" style="width:100%" multiple/>
                                </div>
                                <div class="col-md-10" id="group_div" style="display: none">
                                    <x-form.select name="group_list[]" label="Group(s)" :options="['primary.contact' => 'Primary Contacts']" plugin="select2" style="width:100%" multiple/>
                                </div>
                                <div class="col-md-10" id="role_div" style="display: none">
                                    <x-form.select name="role_list[]" label="Roles(s)" :options="App\Models\Misc\Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray()" plugin="select2" style="width:100%" multiple/>
                                </div>
                                <div class="col-md-10" id="site_div" style="display: none">
                                    <x-form.select name="site_list[]" label="Site(s)" :options="Auth::user()->company->sitesSelect('ALL')" plugin="select2" style="width:100%" multiple/>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/comms/notify" class="btn default"> Back</a>
                                <button class="btn dark" id="test_alert">View Test Alert</button>
                                <button type="submit" class="btn green">Create</button>
                            </div>
                        </div> <!--/form-body-->
                        </form>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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

