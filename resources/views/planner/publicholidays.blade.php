@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Public Holidays</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Public Holidays</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('edit.settings'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/planner/publicholidays/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                {{--}}<th style="width:5%"> #</th>--}}
                                <th style="width:10%"> Date</th>
                                <th style="width:10%"> Day</th>
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
                    {{--}}{data: 'view', name: 'public_holidays.id', orderable: false, searchable: false},--}}
                {
                    data: 'nicedate', name: 'public_holidays.date'
                },
                {data: 'day', name: 'public_holidays.date', orderable: false},
                {data: 'name', name: 'public_holidays.name', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [0, "asc"]
            ]
        });

        table_list.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this record!<br><b>" + name + "</b>",
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
                        toastr.error('Deleted date');
                    },
                }).always(function (data) {
                    $('#table_list').DataTable().draw(false);
                });
            });
        });

        $('select#site_group').change(function () {
            table_list.ajax.reload();
        });

        $('select#status').change(function () {
            if ($('#status').val() == 0)
                table_list.column('3').visible(true);
            else
                table_list.column('3').visible(false);
            table_list.ajax.reload();
        });
    </script>

    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop