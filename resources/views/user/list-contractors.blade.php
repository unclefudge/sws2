@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-user"></i> Contractor Management</h1>
    </div>
@stop

@section('content')

    @section('breadcrumbs')
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            <li><span>Contractors</span></li>
        </ul>
    @stop

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Contractor List</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.user'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/user/create" data-original-title="Add">Add</a>
                            @endif
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                        </div>
                    </div>
                    <x-form.hidden name="status" :value="1"/>
                    <!--
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    -->
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th> Name</th>
                                <th> Company</th>
                                <th> Phone</th>
                                <th> Email</th>
                                <th> Last Login</th>
                                <!--<th style="width:5%"></th>-->
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">

        var active = $('#status').val();

        var table1 = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('user/dt/contractors') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status').val();
                }
            },
            columns: [
                //{data: 'id', name: 'users.id', orderable: false, searchable: false},
                {data: 'full_name', name: 'full_name'},
                {data: 'name', name: 'companys.name'},
                {data: 'phone', name: 'users.phone'},
                {data: 'email', name: 'users.email'},
                {data: 'last_login', name: 'users.last_login'},
                //{data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('select#status').change(function () {
            table1.ajax.reload();
        });
    </script>
@stop