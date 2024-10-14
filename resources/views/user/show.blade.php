@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')
@inject('overrideTypes', 'App\Http\Utilities\OverrideTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Profile</span></li>
    </ul>
@stop

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('user/_header')

        <div class="row">
            @include('user/_compliance-docs')

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Contact Details --}}
                @if (Auth::user()->allowed2('view.user.contact', $user))
                    @include('user/_show-contact')
                    @include('user/_edit-contact')
                @endif

                {{-- Login Details --}}
                @if (Auth::user()->allowed2('view.user', $user))
                    @include('user/_show-login')
                    @include('user/_edit-login')
                @endif
            </div>


            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Security Details --}}
                @if (Auth::user()->allowed2('view.user.security', $user))
                    @include('user/_show-security')
                @endif

                {{-- construction --}}
                @if (Auth::user()->allowed2('view.user.construction', $user))
                    @include('user/_show-construction')
                    @include('user/_edit-construction')
                @endif

                {{-- Compliance Management --}}
                @if (Auth::user()->allowed2('view.compliance.manage', $user->company))
                    @include('user/_show-compliance-manage')
                    @include('user/_edit-compliance-manage')
                    @include('user/_add-compliance-manage')
                @endif

                {{-- Attendance --}}
                @if (in_array($user->id, Auth::user()->authUsers('view.site.attendance')->pluck('id')->toArray()))
                    @include('user/_show-attendance')
                @endif

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
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#roles").select2({placeholder: "Select one or more", width: '100%'});
            $("#trades").select2({placeholder: "Select one or more", width: '100%'});

            $('#password').click(function (e) {
                if ($('#user').val() == 1)
                    $('#password_confirmation_div').show();
                $('#password_update').val(1);
            });

            if ($('#apprentice').val() == 1)
                $('#apprentice-div').show();
            else
                $('#apprentice_start').val('');

            $('#apprentice').change(function (e) {
                $('#apprentice-div').toggle();
            });

            $('#compliance_type').change(function (e) {
                overide();
            });

            function overide() {
                var type = $('#compliance_type').val();
                if (type != '') {
                    $('#add_compliance_fields').show();
                    $('#save_compliance').show();
                    if (type == 'cdu')
                        $('#add_compliance_required').hide();
                    else {
                        $('#add_compliance_required').show();
                        var cat = type.substring(2, type.length);
                        if ($('#ot_' + type).val() == '1') {
                            $('#creq_yes').show();
                            $('#creq_not').hide();
                            $('#required').val('0');
                        } else {
                            $('#creq_yes').hide();
                            $('#creq_not').show();
                            $('#required').val('1');
                        }
                        $('#required').trigger('change');
                    }
                } else {
                    $('#add_compliance_fields').hide();
                    $('#save_compliance').hide();
                }
            }
        });

        function editForm(name) {
            $('#show_' + name).hide();
            $('#edit_' + name).show();
            $('#add_' + name).hide();
        }

        function cancelForm(e, name) {
            e.preventDefault();
            $('#show_' + name).show();
            $('#edit_' + name).hide();
            $('#add_' + name).hide();
        }

        function addForm(name) {
            $('#show_' + name).hide();
            $('#edit_' + name).hide();
            $('#add_' + name).show();
        }

        @if (count($errors) > 0)
        var errors = {!! $errors !!};
        if (errors.FORM == 'contact' || errors.FORM == 'login' || errors.FORM == 'security' || errors.FORM == 'construction' || errors.FORM == 'compliance') {
            $('#show_' + errors.FORM).hide();
            $('#edit_' + errors.FORM).show();
        }
        if (errors.FORM == 'leave.add') {
            $('#show_leave').hide();
            $('#edit_leave').hide();
            $('#add_leave').show();
        }
        if (errors.FORM == 'compliance.add') {
            $('#show_compliance').hide();
            $('#edit_compliance').hide();
            $('#add_compliance').show();
        }

        console.log(errors)
        @endif

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });

    </script>
@stop