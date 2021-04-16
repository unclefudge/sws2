@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Documents</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Document Management</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyPermission2('add.site.doc|add.safety.doc'))
                                <button type="submit" class="btn btn-circle green btn-outline btn-sm" data-original-title="Add">Add</button>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6" id="site_active">
                            <div class="form-group">
                                <!--<label for="site_id_active" class="control-label">Active Sites ({!! Auth::user()->company->sites(1)->count() !!})</label>-->
                                {!! Form::select('site_id_active', Auth::user()->company->sitesSelect('ALL', 1), null, ['class' => 'form-control select2', 'id' => 'site_id_active']) !!}
                            </div>
                        </div>
                        <div class="col-md-6" id="site_completed">
                            <div class="form-group">
                                <!--<label for="site_id_completed" class="control-label">Completed Sites ({!! Auth::user()->company->sites('0')->count() !!})</label>-->
                                {!! Form::select('site_id_completed', Auth::user()->company->sitesSelect('ALL', 0), null, ['class' => 'form-control select2', 'id' => 'site_id_completed']) !!}
                            </div>
                        </div>
                        <div class="col-md-6" id="site_upcoming">
                            <div class="form-group">
                                <!--<label for="site_id_all" class="control-label">Any Site ({!! Auth::user()->company->sites()->count() !!})</label>-->
                                {!! Form::select('site_id_upcoming', Auth::user()->company->sitesSelect('ALL', -1), null, ['class' => 'form-control select2', 'id' => 'site_id_all']) !!}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                {!! Form::select('type', Auth::user()->siteDocTypeSelect('view', 'all'), $type, ['class' => 'form-control bs-select', 'id' => 'type']) !!}
                            </div>
                        </div>
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="-1">Upcoming</option>
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
        $("#site_id_active").select2({placeholder: "Select Site", width: '100%'});
        $("#site_id_completed").select2({placeholder: "Select Site", width: '100%'});
        $("#site_id_upcoming").select2({placeholder: "Select Site", width: '100%'});

        $('#site_completed').hide();
        $('#site_upcoming').hide();

    });

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    var site_id = $('#site_id').val();

    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        ajax: {
            'url': '{!! url('site/doc/dt/docs') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_id_all = $('#site_id_all').val();
                d.site_id_active = $('#site_id_active').val();
                d.site_id_completed = $('#site_id_completed').val();
                d.type = $('#type').val();
                d.status = $('#status').val();
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
    $('#status').change(function () {
        if ($('#status').val() == 1) {
            $('#site_active').show();
            $('#site_completed').hide();
            $('#site_upcoming').hide();
        } else if ($('#status').val() == '0') {
            $('#site_active').hide();
            $('#site_completed').show();
            $('#site_upcoming').hide();
        } else {
            $('#site_active').hide();
            $('#site_completed').hide();
            $('#site_upcoming').show();
        }

        table1.ajax.reload();
    });
    $('#site_id_active').change(function () {
        table1.ajax.reload();
    });
    $('#site_id_completed').change(function () {
        table1.ajax.reload();
    });
    $('#site_upcoming').change(function () {
        table1.ajax.reload();
    });
    $('#type').change(function () {
        table1.ajax.reload();
    });
</script>
@stop