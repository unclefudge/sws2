@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Equipment Transfers</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {!! Form::model('EquipmentTransfersPDF', ['action' => 'Misc\ReportEquipmentController@equipmentTransfersPDF', 'class' => 'horizontal-form']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Equipment Transfers Last 7 Days</span>
                            <span class="caption-helper"> &nbsp; {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</span>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</button>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> Qty</th>
                                    <th> Item</th>
                                    <th> From</th>
                                    <th> To</th>
                                    <th width="10%"> Date</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
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
    });

    var table1 = $('#table1').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('/manage/report/equipment/dt/transfers') !!}',
            'type': 'GET',
            'data': function (d) {
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        columns: [
            {data: 'qty', name: 'equipment_log.qty'},
            {data: 'name', name: 'equipment.name'},
            {data: 'trans_from', name: 'trans_from'},
            {data: 'trans_to', name: 'trans_to'},
            {data: 'created_at', name: 'equipment_log.created_at'},
            //{data: 'firstname', name: 'users.firstname', visible: false},
            //{data: 'lastname', name: 'users.lastname', visible: false},
        ],
        order: [
            [4, "desc"]
        ]
    });

    $('#from').change(function () {
        table1.ajax.reload();
    });
    $('#to').change(function () {
        table1.ajax.reload();
    });
</script>
@stop