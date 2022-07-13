@inject('ozstates', 'App\Http\Utilities\OzStates')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Profile</span></li>
    </ul>
@stop

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('site/_header')

        <div class="row">
            @include('site/_show-safety')

            <div class="col-lg-6 col-xs-12 col-sm-12">

                @if (Auth::user()->allowed2('view.site', $site))
                    {{-- Site Details --}}
                    @include('site/_show-site')
                    @include('site/_edit-site')

                    {{-- Client Details --}}
                    @include('site/_show-client')
                    @include('site/_edit-client')
                @endif

                    {{-- Client Planner Email --}}
                    @if (Auth::user()->hasAnyPermissionType('client.planner.email'))
                        @include('site/_show-clientemail')
                    @endif
            </div>


            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Admin Details --}}
                @if (Auth::user()->allowed2('view.site.admin', $site))
                    @include('site/_show-admin')
                    @include('site/_edit-admin')
                @endif

                {{-- Attendance Details --}}
                @if (Auth::user()->allowed2('view.site.attendance', $site))
                    @include('site/_show-attendance')
                @endif

            </div>

        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $site->displayUpdatedBy() !!}
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
        $("#roles").select2({placeholder: "Select one or more", width: '100%'});
        $("#trades").select2({placeholder: "Select one or more", width: '100%'});
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
    if (errors.FORM == 'site' || errors.FORM == 'client' || errors.FORM == 'admin' || errors.FORM == 'attendance' || errors.FORM == 'compliance') {
        $('#show_' + errors.FORM).hide();
        $('#edit_' + errors.FORM).show();
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