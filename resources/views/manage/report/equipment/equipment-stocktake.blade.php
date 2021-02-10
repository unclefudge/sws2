@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Equipment Stocktake</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Equipment Stocktake</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="note" style="background-color: #e1e5ec; border-color: #acb5c3">
                            <div class="row">
                                <div class="col-md-2"><h3>Filter by</h3></div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Dates', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-reset>
                                            {!! Form::text('from', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'from']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('to', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'to']) !!}
                                        </div>
                                        {!! fieldErrorMessage('start_date', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    {!! Form::label('completed', 'Stocktake was peformed between given dates', ['class' => 'control-label']) !!}
                                    {!! Form::select('completed', ['1' => 'Yes', '0' => 'No '], 1, ['class' => 'form-control bs-select']) !!}
                                    </div>
                            </div>
                        </div>

                        <div class="portlet-body">
                            <div id="stocktake-done">
                                <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th width="10%"> Date</th>
                                        <th> Location</th>
                                        <th> Summary</th>
                                        <th width="15%"> By</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div id="stocktake-not">
                            <table class="table table-striped table-bordered table-hover order-column" id="table2">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th width="95%"> Locations where stocktake hasn't been performed</th>
                                </tr>
                                </thead>
                            </table>
                                </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- loading Spinner -->
    <div style="background-color: #FFF; padding: 20px; display: none" id="spinner">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        //$('#view_pdf').click(function (e) {
        $('form').submit(function (e) {
            $('#spinner').show();
            return true;
        });

        $('#stocktake-done').hide();
        $('#stocktake-not').hide();

        if ($('#completed').val() == '1')
            $('#stocktake-done').show();
        else
            $('#stocktake-not').show();


    })
    ;

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

    var active = $('#status').val();

    var table1 = $('#table1').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('/manage/report/equipment/dt/stocktake') !!}',
            'type': 'GET',
            'data': function (d) {
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        columns: [
            {data: 'created_at', name: 'created_at'},
            {data: 'location', name: 'location'},
            {data: 'summary', name: 'summary'},
            {data: 'full_name', name: 'full_name', orderable: false, searchable: false},
            {data: 'firstname', name: 'users.firstname', visible: false},
            {data: 'lastname', name: 'users.lastname', visible: false},
        ],
        order: [
            [0, "desc"]
        ]
    });

    var table2 = $('#table2').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('/manage/report/equipment/dt/stocktake-not') !!}',
            'type': 'GET',
            'data': function (d) {
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
        ],
        order: [
            [1, "asc"]
        ]
    });

    $('#from').change(function () {
        table1.ajax.reload();
    });
    $('#to').change(function () {
        table1.ajax.reload();
    });

    $('#completed').change(function () {
        $('#stocktake-done').hide();
        $('#stocktake-not').hide();
        if ($('#completed').val() == '1')
            $('#stocktake-done').show();
        else
            $('#stocktake-not').show();
    });
</script>
@stop