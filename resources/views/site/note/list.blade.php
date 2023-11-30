@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Notes</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Notes</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.site.note'))
                                <button class="btn btn-circle green btn-outline btn-sm" id="add_note">Add</button>
                            @endif
                                @if(Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                    <a class="btn btn-circle green btn-outline btn-sm" href="/site/note/settings" data-original-title="Settings">Settings</a>
                                @endif
                        </div>
                    </div>
                    {!! Form::hidden('site_id_set', $site_id, ['id' => 'site_id_set']) !!}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                {!! Form::select('site_id', $site_list, $site_id, ['class' => 'form-control select2', 'id' => 'site_id']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                {{--}}<th style="width:5%"> #</th>--}}
                                <th style="width:7%"> Date</th>
                                @if ($site_id == 'all')
                                    <th style="width:20%"> Site</th>
                                @endif
                                <th style="width:15%"> Category</th>
                                <th> Note</th>
                                <th style="width:15%"> Created by</th>
                                <th style="width:10%"></th>
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
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({
                placeholder: "Select Site",
            });

            $("#add_note").click(function (e) {
                e.preventDefault();
                var site_id = $("#site_id").val();
                window.location.href = "/site/"+site_id+"/notes/create";
            });

        });

        var site_id = $('#site_id').val();

        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/note/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.site_id = $('#site_id').val();
                }
            },
            columns: [
                //{data: 'id', name: 'site_notes.id', orderable: false, searchable: false},
                {data: 'date_created', name: 'site_notes.created_at', searchable: false},
                    @if ($site_id == 'all')
                {data: 'sitename', name: 'sites.name'},
                    @endif
                {data: 'category_id', name: 'site_notes.category_id'},
                {data: 'notes', name: 'site_notes.notes', orderable: false},
                {data: 'full_name', name: 'full_name', searchable: false, searchable: false,},
                {data: 'action', name: 'action', searchable: false, orderable: false},
                {data: 'firstname', name: 'users.firstname', visible: false},
                {data: 'lastname', name: 'users.lastname', visible: false},
                {data: 'name', name: 'categories.name', visible: false},
            ],
            order: [
                [0, "desc"]
            ]
        });

        table1.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this note!<br><b>" + name + "</b>",
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

        $('#site_id').change(function () {
            // Redirect if All sites selected to display extra column
            if ($('#site_id').val() == 'all' && $('#site_id_set').val() != 'all' )
                window.location.href = "/site/all/notes";

            // Redirect if individual site selected to hide extra column
            if ($('#site_id').val() != 'all' && $('#site_id_set').val() == 'all' )
                window.location.href = "/site/"+$('#site_id').val()+"/notes";

            // Otherwise judst reload table
            table1.ajax.reload();
        });
    </script>
@stop