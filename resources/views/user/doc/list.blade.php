@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Documents</span></li>
    </ul>
@stop


@section('content')
    <div class="page-content-inner">

        @include('user/_header')

        {{-- Compliance Documents --}}
        {{--}}
        @if (count($user->missingDocs()))
            <div class="row">
                @include('company/_compliance-docs')
            </div>
        @endif
        --}}

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject font-dark bold uppercase"> User Documents</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->isCompany($user->company_id) && Auth::user()->allowed2('add.user.doc'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/user/{{ $user->id }}/doc/upload" data-original-title="Upload">Upload</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <x-form.select name="status" label="Status" :options="['1' => 'Current', '0' => 'Expired']"/>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"> #</th>
                                <th> Document</th>
                                <th> Dept.</th>
                                <th> Details</th>
                                <th style="width:10%"> Issued</th>
                                <th style="width:10%"> Expiry</th>
                                <th style="width:10%"> Action</th>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        var table1 = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            ajax: {
                'url': '{!! url("user/$user->id/doc/dt/docs") !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'category_id', name: 'category_id'},
                {data: 'details', name: 'details'},
                {data: 'issued', name: 'issued'},
                {data: 'expiry', name: 'expiry'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [2, "asc"]
            ]
        });

        table1.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this file!<br><b>" + name + "</b>",
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

        $('#status').change(function () {
            table1.ajax.reload();
        });
    </script>
@stop