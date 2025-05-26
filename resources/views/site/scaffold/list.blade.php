@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Scaffold Handover Certificate</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Scaffold Handover Certificate</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->allowed2('add.site.scaffold.handover'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/scaffold/handover/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
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
                                <th style="width: 5%"> #</th>
                                <th> Site</th>
                                <th style="width:10%"> Due Date</th>
                                <th> Scaffolder</th>
                                <th style="width:10%"> Handover Date</th>
                                <th style="width:5%"> Action</th>
                            </tr>
                            </thead>
                        </table>
                        @if (Auth::user()->isCC())
                            <br>
                            <h4 class="font-green-haze">Ashby's Certificates</h4>
                            <div class="tabbable-line">
                                <ul class="nav nav-tabs ">
                                    <li class="active">
                                        <a href="#tab_15_1" data-toggle="tab" aria-expanded="true"> Pending </a>
                                    </li>
                                    <li class="">
                                        <a href="#tab_15_2" data-toggle="tab" aria-expanded="false"> Completed </a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    {{-- Pending --}}
                                    <div class="tab-pane active" id="tab_15_1">
                                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                            <thead>
                                            <tr class="mytable-header">
                                                <th> Site</th>
                                                <th style="width:10%"> Due Date</th>
                                            </tr>
                                            </thead>
                                            @foreach ($ashby as $scaff)
                                                @if ($scaff['status'] == 'outstanding')
                                                    <tr>
                                                        <td>{!! $scaff['name'] !!}</td>
                                                        <td>{!! $scaff['due_at'] !!}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                    {{-- Completed --}}
                                    <div class="tab-pane" id="tab_15_2">
                                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                            <thead>
                                            <tr class="mytable-header">
                                                <th> Site</th>
                                                <th style="width:10%"> Due Date</th>
                                            </tr>
                                            </thead>
                                            @foreach ($ashby as $scaff)
                                                @if ($scaff['status'] == 'completed')
                                                    <tr>
                                                        <td>{!! $scaff['name'] !!}</td>
                                                        <td>{!! $scaff['due_at'] !!}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
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
        var status = $('#status').val();

        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/scaffold/handover/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'view', name: 'view', orderable: false, searchable: false},
                {data: 'sitename', name: 'sites.name'},
                {data: 'due_at', name: 'due_at', orderable: false, searchable: false},
                {data: 'inspector_name', name: 'site_scaffold_handover.inspector_name'},
                {data: 'handoverdate', name: 'site_scaffold_handover.handover_date'},
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
                text: "You will not be able to recover this report!<br><b>" + name + "</b>",
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
                        toastr.error('Deleted document');
                    },
                }).always(function (data) {
                    $('#table1').DataTable().draw(false);
                });
            });
        });
    </script>
@stop