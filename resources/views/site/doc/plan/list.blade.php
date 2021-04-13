@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-life-ring"></i> Site Plans</h1>
    </div>
@stop

@section('content')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Site Plans</span></li>
    </ul>
    @stop
            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Site Plans</span>
                        </div>
                        <div class="actions">
                            @if(false && Auth::user()->hasPermission2('add.site.doc'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/user/create" data-original-title="Add">Add</a>
                            @endif
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6" id="sites_active">
                            <div class="form-group">
                                {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                    <optgroup label="Active Sites"></optgroup>
                                    {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id'), 1) !!}
                                </select>
                            </div>
                        </div>
                        @if (Auth::user()->hasPermission2('view.site.doc.upcoming'))
                            <div class="col-md-6" id="sites_upcoming">
                                <div class="form-group">
                                    {!! Form::label('site_id2', 'Site', ['class' => 'control-label']) !!}
                                    <select id="site_id2" name="site_id2" class="form-control select2" style="width:100%" placeholder="Select site">
                                        <optgroup label="Upcoming Sites"></optgroup>
                                        {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id2'), -1) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                    <select name="status" id="status" class="form-control bs-select">
                                        <option value="1" selected>Active</option>
                                        <option value="-1">Upcoming</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Document</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div> <!-- end portlet -->
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site",});
        $("#site_id2").select2({placeholder: "Select Site",});

        $('#status').change(function () {
            updateFields();
        });

        function updateFields() {
            $("#sites_active").hide();
            $("#sites_upcoming").hide();

            if ($("#status").val() == '-1')
                $("#sites_upcoming").show();
            else
                $("#sites_active").show();
        }

        updateFields();

        var site_id = $('#site_id').val();

        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/doc/type/dt/PLAN') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.site_id = $('#site_id').val();
                    d.site_id2 = $('#site_id2').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('#site_id').change(function () {
            table1.ajax.reload();
        });
        $('#site_id2').change(function () {
            table1.ajax.reload();
        });
        $('#status').change(function () {
            table1.ajax.reload();
        });

    });


</script>
@stop