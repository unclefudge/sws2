@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.export'))
            <li><a href="/site/export">Export</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Inspection Reports</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Inspection Reports</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <x-form.select name="type" label="Report type" :options="['electrical' => 'Electrical', 'plumbing' => 'Plumbing']" value="electrical"/>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"> #</th>
                                <th style="width:10%"> Completed</th>
                                <th style="width:5%"> Site</th>
                                <th> Name</th>
                                <th> Inspected by</th>
                                <th style="width:5%"></th>
                            </tr>
                            </thead>
                        </table>

                        <hr>
                        <a href="/manage/report" class="btn default pull-right">Back</a><br><br>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        var type = $('#type').val();
        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('manage/report/site_inspections/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.type = $('#type').val();
                }
            },
            columns: [
                {data: 'view', name: 'view', orderable: false, searchable: false},
                {data: 'nicedate', name: 'nicedate', searchable: false},
                {data: 'code', name: 'sites.code'},
                {data: 'sitename', name: 'sites.name'},
                {data: 'companyname', name: 'companys.name'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "desc"]
            ]
        });

        $('select#type').change(function () {
            table1.ajax.reload();
        });
    </script>
@stop