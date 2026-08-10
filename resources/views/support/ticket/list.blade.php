@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Support Tickets</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Support Tickets</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/support/hours" data-original-title="Hours">Support Hours</a>
                            @endif
                            <a class="btn btn-circle green btn-outline btn-sm" href="/support/ticket/create" data-original-title="Add">Add</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <x-form.select name="status" :options="['1' => 'Open', '0' => 'Closed']" value="1"/>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"> #</th>
                                <th style="width:5%"> ID</th>
                                <th style="width:10%"> Updated</th>
                                <th style="width:20%"> Updated by</th>
                                <th> Name</th>
                                <th style="width:5%"> Priority</th>
                                <th style="width:5%"> Assigned To</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
                                <x-form.select name="status2" :options="['1' => 'Open', '0' => 'Closed']" value="1"/>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table_list2">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th style="width:5%"> ID</th>
                                    <th style="width:10%"> Updated</th>
                                    <th style="width:20%"> Updated by</th>
                                    <th> Name</th>
                                    <th style="width:5%"> Priority</th>
                                    <th style="width:5%"> Assigned To</th>
                                    <th style="width:5%"> ETA</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
                {data: 'priority', name: 't.priority', orderable: true, searchable: false},
                {data: 'assigned_to', name: 't.assigned_to'},
            ],
            order: [
                [5, "desc"], [2, "desc"]
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