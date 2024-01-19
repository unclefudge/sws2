@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Project Supply Info</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Project Supply Info</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('del.site.project.supply'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/supply/settings" data-original-title="Ssetting">Settings</a>
                            @endif
                            @if(Auth::user()->hasPermission2('add.site.project.supply'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/supply/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>

                    @if (Auth::user()->permissionLevel('view.site.qa', 3) == 99)
                        <input type="hidden" id="supervisor_sel" value="1">
                        <div class="col-md-4">
                            {!! Form::select('supervisor', ['all' => 'All sites', 'signoff' => 'Require Sign Off'] + Auth::user()->company->reportsTo()->supervisorsSelect(), ($signoff) ? 'signoff' : session('/site/qa:supervisor'), ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}
                        </div>
                    @else
                        <input type="hidden" id="supervisor_sel" value="0">
                    @endif
                    <div class="row">
                        <div class="col-md-3 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Completed</option>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var status = $('#status').val();
        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/supply/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.supervisor_sel = $('#supervisor_sel').val();
                    d.supervisor = $('#supervisor').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'sitename', name: 's.name'},
                {data: 'updated_at', name: 'p.updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('select#status').change(function () {
            table1.ajax.reload();
        });

        $('select#supervisor').change(function () {
            var supervisor = $('#supervisor').val();
            $.ajax({
                url: '/session/update',
                type: "POST",
                dataType: 'json',
                data: {key: '/site/qa:supervisor', val: supervisor},
                success: function (data) {
                    let x = JSON.stringify(data);
                    //console.log(x);
                },
                error: function (error) {
                    console.log(`Error ${error}`);
                }
            }).always(function (data) {
                $('#table1').DataTable().draw(true);
            });
        });
    </script>
@stop