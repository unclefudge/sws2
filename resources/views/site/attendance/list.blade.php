@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Attendance</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {!! Form::model('SitePlannerExport', ['action' => 'Site\Planner\SitePlannerExportController@attendancePDF', 'class' => 'horizontal-form']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Attendance Report</span>
                        </div>
                    </div>
                    <div class="portlet-body form">

                        <div class="row">
                            <div class="col-md-6" id="site_all">
                                <div class="form-group">
                                    <label for="site_id" class="control-label">Site</label>
                                    <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                        {{--}}{!! Auth::user()->company->siteCheckinSelectOptions() !!} --}}
                                        {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                    {!! Form::label('from', 'Dates', ['class' => 'control-label']) !!}
                                    <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-reset>
                                        {!! Form::text('from', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'from']) !!}
                                        <span class="input-group-addon"> to </span>
                                        {!! Form::text('to', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'to']) !!}
                                    </div>
                                    {!! fieldErrorMessage('start_date', $errors) !!}
                                </div>
                            </div>
                        </div>

                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="20%"> Date / Time</th>
                                    <th> Site</th>
                                    <th> Name</th>
                                    @if (Auth::user()->company->subscription > 1)
                                        <th> Company</th>
                                    @endif
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site", width: '100%'});
    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

    var active = $('#status').val();

    var table1 = $('#table1').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('/site/attendance/dt/attendance') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_id = $('#site_id').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        columns: [
            {data: 'date', name: 'site_attendance.date'},
            {data: 'sites.name', name: 'sites.name'},
            {data: 'full_name', name: 'full_name', orderable: false, searchable: false},
                @if (Auth::user()->company->subscription > 1)
            {
                data: 'companys.name', name: 'companys.name'
            },
                @endif
            {
                data: 'firstname', name: 'users.firstname', visible: false
            },
            {data: 'lastname', name: 'users.lastname', visible: false},
        ],
        order: [
            [0, "desc"]
        ]
    });

    $('select#site_id').change(function () {
        table1.ajax.reload();
    });

    $('#from').change(function () {
        table1.ajax.reload();
    });
    $('#to').change(function () {
        table1.ajax.reload();
    });
</script>
@stop