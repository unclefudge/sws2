@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Pre Con Review</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Pre Con Review</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('edit.site.precon'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/precon/settings"
                                   data-original-title="Settings">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">


                        <div class="col-md-2 pull-right">
                            <x-form.select name="stage1" id="stage1" :options="$stageOptions" :value="$selectedStage"/>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%">#</th>
                                <th>Site</th>
                                <th style="width:10%">Updated</th>
                                <th style="width:10%"></th>
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
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var table1 = $('#table1').DataTable({
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/precon/dt/precon') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.stage = $('#stage1').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'sitename', name: 's.name', orderable: true},
                {data: 'last_updated', name: 'last_updated', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "desc"]
            ]
        });

        $('select#stage1').change(function () {
            table1.ajax.reload();
        });


        $(document).on('click', '.restore-report', function () {
            $.ajax({url: '/site/precon/' + $(this).data('id') + '/update', type: 'PATCH', data: {status: 1}})
                .done(function () { table1.ajax.reload(); })
                .fail(function () { toastr.error('Unable to restore Pre Con Review'); });
        });

        // Warning message for deleting report
        $(document).on('click', '.delete-report', function (e) {
            e.preventDefault();
            var url = "/site/precon/" + $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The Pre Con Review <b>" + name + "</b> will be moved to Disabled.",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, disable it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Pre Con Review moved to Disabled');
                    },
                }).always(function (data) {
                    location.reload();
                });
            });
        });
    </script>
@stop
