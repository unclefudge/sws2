@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><span>Website Form Submissions</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze">Website Form Submissions</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <label class="control-label">Form</label>
                            <select id="filter_form_key" class="form-control select2">
                                <option value="all">All Forms</option>
                                @foreach($formOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="control-label">Status</label>
                            <select id="filter_status" class="form-control select2">
                                <option value="all">All Status</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr>
                                <th>Created</th>
                                <th>Form</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Suburb</th>
                                <th>Step</th>
                                <th>Status</th>
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

        var table = $('#table_list').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                url: '/settings/website-form-submission/dt/submissions',
                type: 'GET',
                data: function (d) {
                    d.form_key = $('#filter_form_key').val();
                    d.status = $('#filter_status').val();
                    d.search_text = $('#filter_search_text').val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'form_key', name: 'form_key'},
                {data: 'full_name', name: 'full_name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'suburb', name: 'suburb'},
                {data: 'step', name: 'step'},
                {data: 'status', name: 'status'},
            ],
            order: [[0, "desc"]]
        });

        $('#filter_form_key, #filter_status').on('change', function () {
            table.ajax.reload();
        });

        var searchTimer = null;

        $('#filter_search_text').on('keyup', function () {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(function () {
                table.ajax.reload();
            }, 350);
        });

        $('#filter_clear').on('click', function () {
            $('#filter_form_key').val('all').trigger('change.select2');
            $('#filter_status').val('all').trigger('change.select2');
            $('#filter_search_text').val('');

            table.ajax.reload();
        });
    </script>
@stop
