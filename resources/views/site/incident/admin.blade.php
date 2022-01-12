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
$qConditions = App\Models\Misc\FormQuestion::find(113);
$qConFactorDefences = App\Models\Misc\FormQuestion::find(125);
$qConFactorITactions = App\Models\Misc\FormQuestion::find(148);
$qConFactorWorkplace = App\Models\Misc\FormQuestion::find(167);
$qConFactorHuman = App\Models\Misc\FormQuestion::find(192);
$qRootCause = App\Models\Misc\FormQuestion::find(219);
?>

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('site/incident/_header')

        <div class="row">
            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Details --}}
                @if ($pView)
                    @include('site/incident/_show-details')
                    @include('site/incident/_edit-details')
                @endif

                {{-- Regulator --}}
                @if ($pView)
                    @include('site/incident/_show-regulator')
                    @include('site/incident/_edit-regulator')
                @endif
            </div>

            <div class="col-lg-6 col-xs-12 col-sm-12">
                {{-- Witness Statements --}}
                @if ($pView)
                    @include('site/incident/_show-witness')
                @endif

                {{-- Conversations --}}
                @if ($pView)
                    @include('site/incident/_show-conversation')
                @endif

                {{-- Sign Offs --}}
                @if ($pView)
                    @include('site/incident/_show-review')
                    @include('site/incident/_edit-review')
                    @include('site/incident/_add-review')
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
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        $("#assign_review").select2({placeholder: "Select User", width: "100%"});

        // On Change Notifiable
        $("#notifiable").change(function () {
            $("#edit_regulator").hide();
            if ($("#notifiable").val() == '1') {
                $("#show_regulator").show();
            } else
                $("#show_regulator").hide();
        });
    });

    $(".btn-delete[data-remote]").click(function (e) {
    //table1.on('click', '.btn-delete[data-remote]', function (e) {
        e.preventDefault();
        var url = $(this).data('remote');
        var name = $(this).data('name');

        swal({
            title: "Are you sure?",
            text: "Delete incident review for<br><b>" + name + "</b>",
            showCancelButton: true,
            cancelButtonColor: "#555555",
            confirmButtonColor: "#E7505A",
            confirmButtonText: "Yes, delete it!",
            allowOutsideClick: true,
            html: true,
        }, function () {
            $.ajax({
                url: url,
                type: 'DELETE',
                dataType: 'json',
                data: {method: '_DELETE', submit: true},
                success: function (data) {
                    toastr.error('Deleted request');
                    window.location.href = "/site/incident/{{ $incident->id }}/admin";
                },
            });
        });
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
    if (errors.FORM == 'review') {
        $('#show_' + errors.FORM).hide();
        $('#add_' + errors.FORM).show();
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