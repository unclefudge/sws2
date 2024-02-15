@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Toolbox Talks</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Toolbox ToDoo's --}}
        @if (Auth::user()->todoType('toolbox', 1)->count())
            <div class="row note note-warning">
                <h3>Outstanding Toolbox Talks </h3>
                <ul>
                    @foreach(Auth::user()->todoType('toolbox', 1) as $todo)
                        <li>Due: {!! ($todo->due_at) ? $todo->due_at->format('d/m/Y') : '-'!!} &nbsp; <a href="{{ $todo->url() }}">{{ $todo->name }} </a></li>
                    @endforeach
                </ul>

            </div>
        @endif
        {{-- Toolbox Talks --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Toolbox Talks Register</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.toolbox'))
                                <a class="btn btn-circle blue btn-sm" href="/safety/doc/toolbox2/create" data-original-title="Give a toolbox talk">Give a toolbox talk</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    @if (Auth::user()->hasPermission2('edit.toolbox'))
                                        <option value="2">Draft</option>
                                    @endif
                                    @if (Auth::user()->isCC())
                                        <option value="3">Pending</option>
                                    @endif
                                    <option value="0">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th width="20%"> Company</th>
                                <th width="10%"> Updated</th>
                                <th width="10%"> Completed</th>
                                <th width="10%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbox Templates -->
        @if (Auth::user()->hasPermission2('add.toolbox'))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Toolbox Template Library</span>
                            </div>
                            {{-- Only allowed Fudge/Kirstie/Ross access to add to library --}}
                            @if(in_array(Auth::user()->id, [3, 108, 1155]))
                                <div class="actions">
                                    @if(Auth::user()->hasPermission2('add.toolbox'))
                                        <a class="btn btn-circle green btn-outline btn-sm" href="/safety/doc/toolbox2/create" data-original-title="Add">Add</a>
                                    @endif
                                    <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                                </div>
                            @endif
                        </div>
                        {{-- Only allowed Fudge/Kirstie/Ross access to add to library --}}
                        @if(in_array(Auth::user()->id, [3, 108, 1155]))
                            <div class="row">
                                <div class="col-md-2 pull-right">
                                    <div class="form-group">
                                        <select name="status2" id="status2" class="form-control bs-select">
                                            <option value="1" selected>Active</option>
                                            @if (Auth::user()->hasPermission2('edit.toolbox'))
                                                <option value="2">Draft</option>
                                            @endif
                                            <option value="0">Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="status2" id="status2" value="1">
                        @endif
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table2">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th> Name</th>
                                    <th width="10%"> Updated</th>
                                    <th width="10%"></th>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        var status = $('#status').val();
        var table1 = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('safety/doc/dt/toolbox2') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 't.name'},
                {data: 'company_name', name: 'c.name'},
                {data: 'updated_at', name: 't.updated_at'},
                {data: 'completed', name: 'completed', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [3, "desc"]
            ]
        });

        $('select#status').change(function () {
            table1.ajax.reload();
        });

        table1.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to restore this talk!<br><b>" + name + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted talk');
                    },
                }).always(function (data) {
                    $('#table1').DataTable().draw(false);
                });
            });
        });

        //
        // Templates
        //
        var status2 = $('#status2').val();
        var table2 = $('#table2').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('safety/doc/dt/toolbox_templates') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status2').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 't.name'},
                {data: 'updated_at', name: 't.updated_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('select#status2').change(function () {
            table2.ajax.reload();
        });

        table2.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to restore this talk!<br><b>" + name + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted template');
                    },
                }).always(function (data) {
                    $('#table2').DataTable().draw(false);
                });
            });
        });
    </script>
@stop