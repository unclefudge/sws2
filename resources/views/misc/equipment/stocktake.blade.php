@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        <li><span>Stocktake</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Equipment Stocktake</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('stocktake', ['method' => 'PATCH', 'action' => ['Misc\EquipmentStocktakeController@update', ($location) ? $location->id : '0'], 'class' => 'horizontal-form']) !!}
                        {!! Form::hidden('site_id', ($location) ? $location->site_id : null, ['class' => 'control-label', 'id' => 'site_id']) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                @if ($location)
                                    <div class="col-md-6"><h3>{!! $location->name !!}</h3></div>
                                @endif
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('location_id', $errors) !!}">
                                        {!! Form::label('location_id', 'Change Location', ['class' => 'control-label']) !!}
                                        <select id="location_id" name="location_id" class="form-control select2" width="100%">
                                            <option></option>
                                            <optgroup label="Sites"></optgroup>
                                            @foreach ($sites as $id => $name)
                                                <option value="{{ $id }}" {{ ($location && $location->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                            <optgroup label="Other Locations"></optgroup>
                                            @foreach ($others as $id => $name)
                                                <option value="{{ $id }}" {{ ($location && $location->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        {!! fieldErrorMessage('location_id', $errors) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/equipment/inventory" class="btn default"> Back</a>
                        </div>
                    </div>
                    {!! Form::close() !!}

                    {{-- History --}}
                    @if ($location)
                        <h3 class="form-section">History</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_history">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="10%"> Date</th>
                                <th> By Whom</th>
                                <th> Summary</th>
                            </tr>
                            </thead>
                        </table>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="loadSpinnerOverlay" id="spinner" style="display: none">
                            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#location_id").select2({placeholder: "Select location", width: '100%'});
        $(".sel_add_item").select2({placeholder: "Add additional item", width: '100%'});
        // Cape Cod Store by default has all items excluded
        if ($("#site_id").val() == 25) {
            $(".itemrow-").addClass("font-grey-cascade");
            $(".itemactual-").hide();
        } else {
            $(".excludeitems").hide();
        }

        // Add extra items
        $("#btn-add-item").click(function (e) {
            e.preventDefault();
            $("#add-items").show();
            $(".add-item").show();
            $("#btn-add-item").hide();
        });

        // Exclude some items
        $("#btn-exclude").click(function (e) {
            e.preventDefault();
            $(".excludeitems").show();
            $("#exclude-div").hide();
        });

        // Location
        $("#location_id").change(function () {
            $("#equipment_list").hide();
            $("#btn-add-item").hide();
            $("#spinner").show();
            window.location.href = "/equipment/stocktake/" + $("#location_id").val();
        });


        $(".stockitem").click(function (e) {
            if ($("#site_id").val() == 25) {
                // Cape Cod Store by default has all items excluded
                if ($("#itemcheck-" + $(this).val()).prop('checked')) {
                    $("#itemrow-" + $(this).val()).removeClass("font-grey-cascade");
                    $("#itemactual-" + $(this).val()).show();
                } else {
                    $("#itemactual-" + $(this).val()).hide();
                    $("#itemrow-" + $(this).val()).addClass("font-grey-cascade");
                }
            } else {
                // All other location by default has all items included
                if ($("#itemcheck-" + $(this).val()).prop('checked')) {
                    $("#itemactual-" + $(this).val()).hide();
                    $("#itemrow-" + $(this).val()).addClass("font-grey-cascade");
                } else {
                    $("#itemrow-" + $(this).val()).removeClass("font-grey-cascade");
                    $("#itemactual-" + $(this).val()).show();
                }
            }
        });


        var table_history = $('#table_history').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('equipment/stocktake/dt/stocktake') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.location_id = "{{ ($location) ? $location->id : 0 }}";
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'created_at', name: 'created_at'},
                {data: 'created_by', name: 'created_by'},
                {data: 'summary', name: 'summary'},
            ],
            order: [
                [1, "desc"]
            ]
        });
    });
</script>
@stop