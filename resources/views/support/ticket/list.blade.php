@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-ticket"></i> Support Tickets</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Support Tickets</span></li>
    </ul>
    @stop

    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <!-- Tickets -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Support Tickets</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/support/ticket/create" data-original-title="Add">Add</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Open</option>
                                    <option value="0">Resolved</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="5%"> ID</th>
                                <th width="10%"> Updated</th>
                                <th width="20%"> Updated by</th>
                                <th> Name</th>
                                <th width="5%"> Priority</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upgrades -->
        @if (Auth::user()->isCC() && Auth::user()->hasPermission2('view.support.ticket.upgrade'))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Development Upgrades</span>
                            </div>
                            <div class="actions">
                                @if (Auth::user()->isCC() && Auth::user()->hasPermission2('add.support.ticket.upgrade'))
                                    <a class="btn btn-circle green btn-outline btn-sm" href="/support/ticket/create" data-original-title="Add">Add</a>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 pull-right">
                                <div class="form-group">
                                    <select name="status2" id="status2" class="form-control bs-select">
                                        <option value="1" selected>Open</option>
                                        <option value="0">Resolved</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table_list2">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th width="5%"> ID</th>
                                    <th width="10%"> Updated</th>
                                    <th width="20%"> Updated by</th>
                                    <th> Name</th>
                                    <th width="5%"> Priority</th>
                                    <th width="5%"> Assigned To</th>
                                    <th width="5%"> ETA</th>
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

    var status = $('#status').val();
    var table_list = $('#table_list').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('/support/ticket/dt/tickets') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status').val();
            }
        },
        columns: [
            {data: 'view', name: 'view', orderable: false, searchable: false},
            {data: 'id', name: 't.id', orderable: false, searchable: false},
            {data: 'nicedate', name: 't.updated_at'},
            {data: 'fullname', name: 'fullname', orderable: false, searchable: false},
            {data: 'name', name: 't.name'},
            {data: 'priority', name: 't.priority', orderable: false, searchable: false},
        ],
        order: [
            [2, "desc"]
        ]
    });

    $('select#status').change(function () {
        table_list.ajax.reload();
    });

    //
    // Upgrades
    //
    var status2 = $('#status2').val();
    var table_list2 = $('#table_list2').DataTable({
        processing: true,
        serverSide: true,
        iDisplayLength: 100,
        ajax: {
            'url': '{!! url('/support/ticket/dt/upgrades') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status2').val();
            }
        },
        columns: [
            {data: 'view', name: 'view', orderable: false, searchable: false},
            {data: 'id', name: 't.id', orderable: false, searchable: false},
            {data: 'nicedate', name: 't.updated_at'},
            {data: 'fullname', name: 'fullname', orderable: false, searchable: false},
            {data: 'name', name: 't.name'},
            {data: 'priority', name: 't.priority'},
            {data: 'assigned_to', name: 't.assigned_to'},
            //{data: 'hours', name: 't.hours'},
            {data: 'niceeta', name: 't.eta'},
        ],
        order: [
            [5, "desc"]
        ]
    });

    $('select#status2').change(function () {
        table_list2.ajax.reload();
    });
</script>

<script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop