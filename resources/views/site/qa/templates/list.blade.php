@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>Templates</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <!-- Templates -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Quality Assurance Template Library</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.site.qa.templates'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/qa/category" data-original-title="Add">Categories</a>
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/qa/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status2" id="status2" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table2">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
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
    // Templates
    var status2 = $('#status2').val();
    var table2 = $('#table2').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('site/qa/dt/qa_templates') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status2').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'updated_at', name: 'updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [1, "desc"]
        ]
    });

    $('select#status2').change(function () {
        table2.ajax.reload();
    });
</script>
@stop