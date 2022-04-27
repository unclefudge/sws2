@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Asbestos Notifications</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Asbestos Notifications</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.site.asbestos'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/asbestos/notification/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status1" id="status1" class="form-control bs-select">
                                    <option value="1" selected>Open</option>
                                    <option value="0">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Site Name</th>
                                <th> Proposed Removal Dates</th>
                                <th> Amount</th>
                                <th> Supervisor</th>
                                <th width="10%"> Updated</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('site/asbestos/notification/dt/list') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status1').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            //{data: 'code', name: 's.code', orderable: false, searchable: false},
            {data: 'sitename', name: 's.name', orderable: false, searchable: false},
            {data: 'proposed_dates', name: 'proposed_dates', orderable: false, searchable: false},
            {data: 'amount', name: 'amount', orderable: false, searchable: false},
            {data: 'supervisor', name: 'supervisor', orderable: false, searchable: false},
            {data: 'updated_at', name: 'a.updated_at', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [1, "asc"]
        ]
    });

    $('select#status1').change(function () {
        table1.ajax.reload();
    });
</script>
@stop