@inject('ozstates', 'App\Http\Utilities\OzStates')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Incident Report</span></li>
    </ul>
@stop

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('site/incident/_header')
        <?php
        $qConditions = App\Models\Misc\FormQuestion::find(113);
        $qConFactorDefences = App\Models\Misc\FormQuestion::find(125);
        $qConFactorITactions = App\Models\Misc\FormQuestion::find(148);
        $qConFactorWorkplace = App\Models\Misc\FormQuestion::find(167);
        $qConFactorHuman = App\Models\Misc\FormQuestion::find(192);
        $qRootCause = App\Models\Misc\FormQuestion::find(219);
        $qPreventive = App\Models\Misc\FormQuestion::find(236);
        ?>

        <div class="row">
            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Conditions --}}
                @if (Auth::user()->allowed2('view.site.incident', $incident))
                    @include('site/incident/_show-conditions')
                    @include('site/incident/_edit-conditions')
                @endif

                {{-- Con Factors --}}
                @if (Auth::user()->allowed2('view.site.incident', $incident))
                    @include('site/incident/_show-confactors')
                    @include('site/incident/_edit-confactors')
                @endif
            </div>

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Root Cause --}}
                @if (Auth::user()->allowed2('view.site.incident', $incident))
                    @include('site/incident/_show-rootcause')
                    @include('site/incident/_edit-rootcause')
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                {{-- Prevent Reoccuurence --}}
                @if (Auth::user()->allowed2('view.site.incident', $incident))
                    @include('site/incident/_show-prevent')
                    @include('site/incident/_edit-prevent')
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $incident->displayUpdatedBy() !!}
        </div>
    </div>

@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#response_113").select2({placeholder: "Check all applicable", width: "100%"}); // Conditions
        $("#response_125").select2({placeholder: "Check all applicable", width: "100%"}); // Absent / Failed Defences
        $("#response_148").select2({placeholder: "Check all applicable", width: "100%"}); // Individual / Team Actions
        $("#response_167").select2({placeholder: "Check all applicable", width: "100%"}); // Workplace Conditions
        $("#response_192").select2({placeholder: "Check all applicable", width: "100%"}); // Human Factors
        $("#response_219").select2({placeholder: "Check all applicable", width: "100%"}); // Root Cause
        $("#response_236").select2({placeholder: "Check all applicable", width: "100%"}); // Prevent

        updateFields();

        // On Change Notifiable
        $("#notifiable").change(function () {
            $("#edit_regulator").hide();
            if ($("#notifiable").val() == '1') {
                $("#show_regulator").show();
            } else
                $("#show_regulator").hide();
        });

        // On Change Conditions
        $("#response_113").change(function () {
            updateFields()
        });

        // On Change Absent / Failed Responses
        $("#response_125").change(function () {
            updateFields()
        });

        // On Change Individual / Team Actions
        $("#response_148").change(function () {
            updateFields()
        });

        // On Change Workplace Conditions
        $("#response_167").change(function () {
            updateFields()
        });

        // On Change Human Factors
        $("#response_192").change(function () {
            updateFields()
        });

        // On Change Root Cause
        $("#response_219").change(function () {
            updateFields()
        });

        function updateFields() {
            var response_113 = $("#response_113").select2("val"); // Conditions
            var response_125 = $("#response_125").select2("val"); // Absent / Failed Defences
            var response_148 = $("#response_148").select2("val"); // Individual / Team Actions
            var response_167 = $("#response_167").select2("val"); // Workplace Conditions
            var response_192 = $("#response_192").select2("val"); // Human Factors
            var response_219 = $("#response_219").select2("val"); // Root Cause

            // Conditions
            var condition_ids = ['114', '115', '116', '117', '118', '119', '120', '121', '122', '123', '124'];
            condition_ids.forEach(function (id, index) {
                $("#field_response_" + id).hide()
                if (response_113 != null && response_113.includes(id)) $("#field_response_" + id).show();
            });

            // Absent / Failed Defences
            $("#field_response_147").hide();
            if (response_125 != null && response_125.includes('147')) $("#field_response_147").show(); // Other response

            // Individual / Team Actions
            $("#field_response_166").hide();
            if (response_148 != null && response_148.includes('166')) $("#field_response_166").show(); // Other response

            // Workplace Conditions
            $("#field_response_191").hide();
            if (response_167 != null && response_167.includes('191')) $("#field_response_191").show(); // Other response

            // Human Factors
            $("#field_response_218").hide();
            if (response_192 != null && response_192.includes('218')) $("#field_response_218").show(); // Other response

            // Root Cause
            var rootcause_ids = ['220', '221', '222', '223', '224', '225', '226', '227', '228', '229', '230', '231', '232', '233', '234', '235'];
            rootcause_ids.forEach(function (id, index) {
                $("#field_response_" + id).hide()
                if (response_219 != null && response_219.includes(id)) $("#field_response_" + id).show();
            });
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
    if (errors.FORM == 'conditions' || errors.FORM == 'confactors' || errors.FORM == 'rootcause' || errors.FORM == 'prevent') {
        $('#show_' + errors.FORM).hide();
        $('#edit_' + errors.FORM).show();
    }

    console.log(errors)
    @endif

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });


</script>
@stop