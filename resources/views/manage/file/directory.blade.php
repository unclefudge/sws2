@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/manage/file">File Manager</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Directory</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {!! Form::model('sitedoc', ['action' => 'Site\SiteDocController@create']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> File Manager</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyPermission2('add.site.doc|add.safety.doc'))
                                <button type="submit" class="btn btn-circle green btn-outline btn-sm" data-original-title="Add">Add</button>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('site_id', '&nbsp;', ['class' => 'control-label']) !!}
                                {!! Form::select('site_id', Auth::user()->company->sitesSelect('all'),
                                     $site_id, ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::label('type', '&nbsp;', ['class' => 'control-label']) !!}
                                {!! Form::select('type', Auth::user()->companyDocTypeSelect('view', 'all'),
                                     $type, ['class' => 'form-control bs-select']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="7%"> Type</th>
                                <th width="20%"> Site</th>
                                <th> Document</th>
                                <th width="10%"> Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div> <!-- end portlet -->
        </div>
        {!! Form::close() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({
            placeholder: "Select Site",
        });

    });

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    var site_id = $('#site_id').val();

    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            'url': '{!! url('site/doc/dt/docs') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_id = $('#site_id').val();
                d.type = $('#type').val();
            }
        },
        columns: [
            {data: 'id', name: 'd.id', orderable: false, searchable: false},
            {data: 'type', name: 'd.type'},
            {data: 'site_name', name: 's.name'},
            {data: 'name', name: 'd.name'},
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

    // Reload table on change of site_id or type
    $('#site_id').change(function () {
        table1.ajax.reload();
    });

    $('#type').change(function () {
        table1.ajax.reload();
    });
</script>
@stop