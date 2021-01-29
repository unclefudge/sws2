@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('licenceTypes', 'App\Http\Utilities\LicenceTypes')
@inject('payrollTaxTypes', 'App\Http\Utilities\PayrollTaxTypes')
@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@inject('overrideTypes', 'App\Http\Utilities\OverrideTypes')
@extends('layout')


@if (Auth::user()->company->status != 2)
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
            <li><span>Profile</span></li>
        @else
            <li><span>Company Profile</span></li>
        @endif
    </ul>
@stop
@endif

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('company/_header')

        <div class="row">
            @include('company/_compliance-docs')

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Company Details --}}
                @if (Auth::user()->allowed2('view.company', $company))
                    @include('company/_show-company')
                    @include('company/_edit-company')
                @endif

                {{-- Business Details --}}
                @if (Auth::user()->allowed2('view.company.acc', $company))
                    @include('company/_show-business')
                    @include('company/_edit-business')
                @endif
            </div>

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Company Leave --}}
                @if (Auth::user()->allowed2('view.company.leave', $company))
                    @include('company/_show-leave')
                    @include('company/_edit-leave')
                    @include('company/_add-leave')
                @endif

                {{-- Construction --}}
                @if (Auth::user()->allowed2('view.company.con', $company))
                    @include('company/_show-construction')
                    @include('company/_edit-construction')
                @endif

                {{-- Compliance Management --}}
                @if (Auth::user()->allowed2('view.compliance.manage', $company))
                    @include('company/_show-compliance-manage')
                    @include('company/_edit-compliance-manage')
                    @include('company/_add-compliance-manage')
                @endif

                {{-- SWMS --}}
                {{--}}
                @if (Auth::user()->allowed2('view.company.whs', $company))
                    @include('company/_show-swms')
                @endif --}}

            </div>
        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $company->displayUpdatedBy() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#lic_type").select2({placeholder: "Select one or more", width: '100%',});
        $("#trades").select2({placeholder: "Select one or more", width: '100%'});
        $("#supervisors").select2({placeholder: "Select one or more", width: '100%'});

        if ($('#transient').val() == 1)
            $('#super-div').show();
        else
            $('#supervisors').val('');

        $('#transient').change(function (e) {
            $('#super-div').toggle();
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
                    if ($('#ot_'+type).val() == '1') {
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

    // Warning Message for making company inactive
    $('#status').change(function () {
        if ($('#status').val() == '0') {
            swal({
                title: "Deactivating a Company",
                text: "Once you make a company <b>Inactive</b> and save it will also:<br><br>" +
                "<div style='text-align: left'><ul>" +
                "<li>Make all users within this company 'Inactive'</li>" +
                "<li>Archive all active/pending documents & SWMS</li>" +
                "<li>Remove company from planner for all future events</li>" +
                "</ul></div>",
                allowOutsideClick: true,
                html: true,
            });
        }
    });

    // Warning message for deleting leave
    {{--}}}
    $('.delete_leave').click(function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var date = $(this).data('date');
        var note = $(this).data('note');

        swal({
            title: "Are you sure?",
            text: "You will not be able to restore this leave!<br><b>" + date + ': ' + note + "</b>",
            showCancelButton: true,
            cancelButtonColor: "#555555",
            confirmButtonColor: "#E7505A",
            confirmButtonText: "Yes, delete it!",
            allowOutsideClick: true,
            html: true,
        }, function () {
            window.location = "/company/{{ $company->id }}/leave/" + id;
        });
    });--}}


            @if (count($errors) > 0)
    var errors = {!! $errors !!};
    if (errors.FORM == 'company' || errors.FORM == 'construction' || errors.FORM == 'whs' || errors.FORM == 'compliance') {
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