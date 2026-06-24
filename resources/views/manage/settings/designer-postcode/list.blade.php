@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><span>Designer Postcodes</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-map"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Designer Postcodes</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/settings/designer-postcode/create">Add</a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <x-form.select name="council" label="Council" :options="['' => 'All Councils'] + $councils" plugin="select2" placeholder="All Councils"/>
                        </div>

                        <div class="col-md-2 pull-right">
                            <x-form.select name="active" label="Status" :options="['1' => 'Active', '0' => 'Disabled', 'all' => 'All']" value="1"/>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:10%">Postcode</th>
                                <th>Suburb</th>
                                <th>Council</th>
                                <th style="width:5%"></th>
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
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $('#council').select2({placeholder: $('#council').data('placeholder'), allowClear: true, width: '100%'});
        //$("#council").select2({placeholder: "Select council", width: '100%'});

        var table_list = $('#table_list').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! url('/settings/designer-postcode/dt/postcodes') !!}',
                type: 'GET',
                data: function (d) {
                    d.council = $('#council').val();
                    d.active = $('#active').val();
                }
            },
            columns: [
                {data: 'postcode', name: 'postcode'},
                {data: 'suburb', name: 'suburb'},
                {data: 'council', name: 'council'},
                {data: 'view', name: 'view', orderable: false, searchable: false},
            ],
            order: [[1, "asc"]]
        });

        $('select#council, select#active').change(function () {
            table_list.ajax.reload();
        });
    </script>
@stop
