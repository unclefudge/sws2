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

<?php
$pView = Auth::user()->allowed2('view.site.incident', $incident);
$pEdit = Auth::user()->allowed2('edit.site.incident', $incident);
$pAdd = Auth::user()->allowed2('add.site.incident');
$pDel = Auth::user()->allowed2('del.site.incident', $incident);

$qType = App\Models\Misc\FormQuestion::find(1);
$qTreatment = App\Models\Misc\FormQuestion::find(14);
$qInjuredPart = App\Models\Misc\FormQuestion::find(21);
$qInjuredNature = App\Models\Misc\FormQuestion::find(50);
$qInjuredMechanism = App\Models\Misc\FormQuestion::find(69);
$qInjuredAgency = App\Models\Misc\FormQuestion::find(92);
?>

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('site/incident/_header')

        <div class="row">
            @include('site/incident/_show-overview')

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Notification Details --}}
                @if ($pView)
                    @include('site/incident/_show-notification')
                    @include('site/incident/_edit-notification')
                @endif

                {{-- Injury Details --}}
                @if ($pView && $incident->isInjury())
                    @include('site/incident/_show-injury')
                    @include('site/incident/_edit-injury')
                @endif

                {{-- Damage Details --}}
                @if ($pView && $incident->isDamage())
                    @include('site/incident/_show-damage')
                    @include('site/incident/_edit-damage')
                @endif
            </div>

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Person Involved --}}
                @if ($pView)
                    @include('site/incident/_show-people')
                @endif

                {{-- Document --}}
                @if ($pView)
                    @include('site/incident/_show-docs')
                @endif

                {{-- Notes --}}
                @if ($pView)
                    @include('site/incident/_show-notes')
                    @include('site/incident/_add-notes')
                @endif

                {{-- Actions / ToDoos --}}
                @if ($pView)
                    @include('site/incident/_show-tasks')
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
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    {{--}}<link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />--}}
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-styles-head')
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.js" type="text/javascript"></script> {{-- using the non minimised version with commented out bits to fix error Uncaught TypeError: $(...).datepicker is not a function}} --}}
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site", width: "100%"});
        $("#type").select2({placeholder: "Check all applicable", width: "100%"});
        $("#treatment").select2({placeholder: "Check all applicable", width: "100%"});
        $("#injured_part").select2({placeholder: "Check all applicable", width: "100%"});
        $("#injured_nature").select2({placeholder: "Check all applicable", width: "100%"});
        $("#injured_mechanism").select2({placeholder: "Check all applicable", width: "100%"});
        $("#injured_agency").select2({placeholder: "Check all applicable", width: "100%"});

        updateFields();

        // On Change Site CC
        $("#site_cc").change(function () {
            updateFields();
        });

        // On Change Site ID
        $("#site_id").change(function () {
            updateFields();
        });

        // On Change Type
        $("#type").change(function () {
            updateFields();
        });

        // On Change Treatment
        $("#treatment").change(function () {
            updateFields();
        });

        // On Change Injured Part
        $("#injured_part").change(function () {
            updateFields();
        });

        function updateFields() {
            // Type
            if ($("#type_text").val())
                var types = $("#type_text").val().split(', ');
            else
                var types = $("#type").select2("val");

            // Notiification details
            $("#field_site_id").hide()
            $("#field_site_name").hide()
            if ($("#site_cc").val() == '1') $("#field_site_id").show(); // Site id
            if ($("#site_cc").val() == '0') $("#field_site_name").show() // Site name

            // Injury details
            $("#field_treatment_other").hide();
            $("#field_injured_part_other").hide();
            if ($("#type").val()) {
                var treatment = $("#treatment").select2("val");
                var injured_part = $("#injured_part").select2("val");
                if (treatment != null && treatment.includes('20')) $("#field_treatment_other").show(); // Other treatment
                if (injured_part != null && injured_part.includes('49')) $("#field_injured_part_other").show(); // Other part
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
    if (errors.FORM == 'notification' || errors.FORM == 'injury' || errors.FORM == 'damage' || errors.FORM == 'notes' || errors.FORM == 'compliance') {
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