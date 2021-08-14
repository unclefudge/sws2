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

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject font-dark bold uppercase"> Company Documents</span>
                        </div>
                        <div class="actions">
                            @if((Auth::user()->allowed2('add.site.incident')))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/company/{{ $incident->id }}/doc/upload" data-original-title="Upload">Upload</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                            {!! Form::select('status', ['1' => 'Current', '2' => 'Pending', '0' => 'Expired'], null, ['class' => 'form-control bs-select', 'id' => 'status',]) !!}
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Document</th>
                                <th> Dept.</th>
                                <th> Details</th>
                                <th width="10%"> Expiry</th>
                                <th width="10%"> Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div> <!-- end portlet -->
        </div>

    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $incident->displayUpdatedBy() !!}
        </div>
    </div>

@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
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
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
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
            var treatment = $("#treatment").select2("val");
            var injured_part = $("#injured_part").select2("val");

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
            if (treatment != null && treatment.includes('20')) $("#field_treatment_other").show(); // Other treatment
            if (injured_part != null && injured_part.includes('49')) $("#field_injured_part_other").show(); // Other part

            // Damage details
            //$("#damage_details").hide();
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

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });

</script>
@stop