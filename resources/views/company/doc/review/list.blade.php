@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            <li><a href="/company/doc/standard/">Standard Details</a><i class="fa fa-circle"></i></li>
        <li><span>Review</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject font-dark bold uppercase"> Standard Details Review</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Document</th>
                                <th> Assigned To</th>
                                <th> Stage</th>
                                <th> Updated</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        ajax: {
            'url': '{!! url("company/doc/standard/review/dt/docs") !!}',
            'type': 'GET',
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            //{data: 'name', name: 'name'},
            {data: 'assigned_to', name: 'assigned_to', orderable: false, searchable: false},
            {data: 'stage', name: 'stage'},
            {data: 'updated_at', name: 'updated_at'},
        ],
        order: [
            [1, "asc"]
        ]
    });
</script>
@stop