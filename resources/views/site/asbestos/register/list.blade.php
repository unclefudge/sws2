@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Asbestos Register</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- In Progress --}}
        @if (Auth::user()->isCC() && count($progress))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> In Progress</span>
                            </div>
                            <div class="actions">
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="under_review">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th> Site</th>
                                    <th style="width:10%"> Updated</th>
                                    <th style="width:5%"></th>
                                </tr>
                                </thead>
                                @foreach ($progress as $report)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/site/asbestos/register/{{ $report->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td> {{ $report->site->name }}</td>
                                        <td> {{ $report->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if(Auth::user()->allowed2('del.site.inspection', $report))
                                                <button class="btn dark btn-xs sbold uppercase margin-bottom delete-report" data-id="{{ $report->id }}" data-name="{{ $report->site->name }}"><i class="fa fa-trash"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Asbestos Register  --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Ready for Construction</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.site.asbestos'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/asbestos/register/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 pull-right">
                            <div class="form-group">
                                <select name="status1" id="status1" class="form-control bs-select">
                                    <option value="1" selected>Active / Maintenance</option>
                                    <option value="-1" selected>Upcoming</option>
                                    <option value="0">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"> #</th>
                                <th> Site</th>
                                <th style="width:10%"> Updated</th>
                                <th style="width:5%"></th>
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

        var status1 = $('#status1').val();
        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/asbestos/register/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status1').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'sitename', name: 's.name',},
                {data: 'updated_at', name: 'a.updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('select#status1').change(function () {
            table1.ajax.reload();
        });

        // Warning message for deleting report
        $('.delete-report').click(function (e) {
            e.preventDefault();
            var url = "/site/asbestos/register/" + $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The report <b>" + name + "</b> will be deleted.<br><br><span class='font-red'><i class='fa fa-warning'></i> You will not be able to undo this action!</span>",
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
                        toastr.error('Deleted report');
                    },
                }).always(function (data) {
                    location.reload();
                });
            });
        });
    </script>
@stop