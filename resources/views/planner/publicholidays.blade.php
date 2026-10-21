@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Non-working Dates</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Non-working Dates</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('edit.settings'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/planner/publicholidays/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <p>Public holidays and separate planner closures. Company shutdowns affect planner availability only. Closure date ranges include both the start and end date. Manage an entry to review changes or removal.</p>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                {{--}}<th style="width:5%"> #</th>--}}
                                <th style="width:10%"> Start Date</th>
                                <th style="width:10%"> End Date</th>
                                <th> Type</th>
                                <th> Name</th>
                                <th style="width:2%"></th>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        var table_list = $('#table_list').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('/planner/publicholidays/dt/dates') !!}',
                'type': 'GET',
                'data': function (d) {
                }
            },
            columns: [
                    {{--}}{data: 'view', name: 'calendar_entries.id', orderable: false, searchable: false},--}}
                {
                    data: 'nicedate', name: 'calendar_entries.date'
                },
                {data: 'niceend', name: 'calendar_entries.end_date'},
                {data: 'type_label', name: 'calendar_entries.type'},
                {data: 'name', name: 'calendar_entries.name', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [0, "asc"]
            ]
        });

    </script>

    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop