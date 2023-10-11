@inject('payrollTaxTypes', 'App\Http\Utilities\PayrollTaxTypes')
@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')

@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Cronjobs</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> CronJobs</span>
                        </div>
                        <!--
                        <div class="actions">
                            <button type="submit" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</button>
                        </div>-->
                    </div>
                    <div class="portlet-body form">
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table2">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:10%"> Type</th>
                                    <th style="width:10%"> Frequency</th>
                                    <th> Name</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($cronjobs as $freq => $jobs)
                                    @foreach ($jobs as $job => $func)
                                        <tr>
                                            <td>Cron</td>
                                            <td>{{ $freq }}</td>
                                            <td>{{ $job }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                {{-- Reports --}}
                                @foreach($reportjobs as $freq => $jobs)
                                    @foreach ($jobs as $job => $func)
                                        <tr>
                                            <td>Report</td>
                                            <td>{{ $freq }}</td>
                                            <td>{{ $job }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <!-- loading Spinner -->
    <div style="background-color: #FFF; padding: 20px; display: none" id="spinner">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $(document).ready(function () {
            /* Select2 */

        });
        /*
        var table1 = $('#table1').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
        'url': '{!! url('/manage/report/payroll/dt/payroll') !!}',
'type': 'GET',
'data': function (d) {
d.from = $('#from').val();
d.to = $('#to').val();
}
},
columns: [
{data: 'id', name: 'id'},
{data: 'name', name: 'name'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
{data: 'gst', name: 'gst'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
{data: 'business_entity', name: 'business_entity'},
/*{data: 'full_name', name: 'full_name', orderable: false, searchable: false},*/

        /*
        <th> ID</th>
        <th> Company</th>
        <th> Business Entity</th>
        <th> No. of Staff</th>
        <th> GST</th>
        <th> Payroll Tax Exemption</th>
        <th> WC Policy No.</th>
        <th> WC Policy Exp</th>
        <th> WC Category</th>
        <th> Subcontractors Statement</th>
        <th> Inactived</th>

        ],
        order: [
        [0, "desc"]
        ]
        });*/

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop