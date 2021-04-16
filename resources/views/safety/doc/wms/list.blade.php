@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>SWMS</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Safe Work Method Statements</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.wms'))
                                <a class="btn btn-circle blue btn-sm" href="/safety/doc/wms/create" data-original-title="Make a SWMS">Make a SWMS</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <a class="btn btn-circle btn-warning btn-sm" href="/filebank/doc/Creating_SWMS_Guide.pdf" target="_blank" data-original-title="Instruction Guide">Instruction Guide</a>
                        </div>
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status1" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="2">Pending</option>
                                    <option value="0">Draft</option>
                                    <option value="-1">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Safe Work Method Statement</th>
                                <th> Company</th>
                                <th> Principal Contractor</th>
                                <th width="10%"> Updated</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates -->
        @if(Auth::user()->hasPermission2('add.wms'))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Safe Work Method Template Library</span>
                            </div>
                            <div class="actions">
                                @if(Auth::user()->hasPermission2('add.wms') && in_array(Auth::user()->id, [3, 6, 351, 109]))
                                    <a class="btn btn-circle green btn-outline btn-sm" href="/safety/doc/wms/create" data-original-title="Add">Add</a>
                                @endif
                                <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 pull-right">
                                <div class="form-group">
                                    <select name="status2" id="status2" class="form-control bs-select">
                                        <option value="1" selected>Active</option>
                                        @if(Auth::user()->hasPermission2('add.wms') && in_array(Auth::user()->id, [3, 6, 351, 109]))
                                            <option value="2">Pending</option>
                                            <option value="0">Draft</option>
                                            <option value="-1">Archived</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table2">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th> Safe Work Method Statement</th>
                                    <th width="10%"> Updated</th>
                                    <th width="5%"></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    var status1 = $('#status1').val();
    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('safety/doc/dt/wms') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status1').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'd.name'},
            {data: 'company_name', name: 'c.name'},
            {data: 'principle', name: 'principle', searchable: false},
            {data: 'updated_at', name: 'd.updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [4, "desc"]
        ]
    });

    $('select#status1').change(function () {
        table1.ajax.reload();
    });

    // Template
    var status2 = $('#status2').val();
    var table2 = $('#table2').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('safety/doc/dt/wms_templates') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status2').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'd.name'},
            {data: 'updated_at', name: 'd.updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [1, "asc"]
        ]
    });

    $('select#status2').change(function () {
        table2.ajax.reload();
    });
</script>
@stop