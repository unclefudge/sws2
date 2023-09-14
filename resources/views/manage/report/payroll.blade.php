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
        <li><span>Payroll</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Payroll Report</span>
                        </div>
                        <!--
                        <div class="actions">
                            <button type="submit" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</button>
                        </div>-->
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model('payroll', ['method' => 'PUT', 'action' => ['Misc\ReportController@payrollDates']]) !!}
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-7">
                                    @if ($from && $to)
                                        <h3>Companies between {{ $from }} - {{ $to }}</h3>
                                    @else
                                        <h3>All Companies</h3>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    {{--}}
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Date Range', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-reset>
                                            {!! Form::text('from', $from, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'from']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('to', $to, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF', 'id' => 'to']) !!}
                                        </div>
                                    </div>--}}
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('fin_year', 'Date Range', ['class' => 'control-label']) !!}
                                        {!! Form::select('fin_year', $select_fin, $fin_year, ['class' => 'form-control', 'id' => 'fin_year']) !!}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn green" style="margin-top: 25px">Go</button>
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-hover order-column" id="table2">
                                <thead>
                                <tr class="mytable-header">
                                    <th> ID2</th>
                                    <th> Company</th>
                                    <th> Business Entity</th>
                                    <th> No. of Staff</th>
                                    <th> GST</th>
                                    <th> Payroll Tax Exemption</th>
                                    <th> WC Policy No.</th>
                                    <th> WC Policy Exp</th>
                                    <th> WC Category</th>
                                    <th> Subcontractors Statement</th>
                                    <th> Active/Deactived</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $from_date = ($from) ? \Carbon\Carbon::parse($from_ymd) : null;
                                $to_date = ($to) ? \Carbon\Carbon::parse($to_ymd) : null;
                                $date_range = ($from && $to) ? true : false;
                                ?>
                                @foreach($companies as $company)
                                        <?php $activeDocData = $company->activeCompanyDocDateData(2, $from_ymd, $to_ymd); ?>
                                    {{-- Exclude CapeCod companies cc- --}}
                                    @if (strtolower(substr($company->name, 0, 3)) == 'cc-')
                                        @continue
                                    @endif
                                    {{-- Exclude Companies not active during date range --}}
                                    @if ($company->status == '0' && !$company->deactivated->between($from_date, $to_date))
                                        @continue
                                    @endif
                                    {{-- Exclude Companies created after the date range --}}
                                    @if ($company->status == '1' && $company->created_at->gt($to_date))
                                        @continue
                                    @endif
                                    <tr style="{{ ($company->status == 0) ? 'background:#fbe1e3' : '' }}">
                                        <td>{{ $company->id }}</td>
                                        <td>{{ $company->name }}</td>
                                        <td>{{ ($company->business_entity) ? $companyEntityTypes::name($company->business_entity) : '-' }}</td>
                                        <td>{{ $company->staffStatus(1)->count() }}</td>
                                        <td>{{ $company->gstYN }}</td>
                                        <td>{{$company->payrollTaxText}}</td>
                                        <td>
                                            @if ($date_range)
                                                {{ ($activeDocData['ref_no']) ? $activeDocData['ref_no'] : '-' }}
                                            @else
                                                {{ ($company->activeCompanyDoc(2)) ?  $company->activeCompanyDoc(2)->ref_no : '-'}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($date_range)
                                                {{ ($activeDocData['date_range']) ? $activeDocData['date_range'] : '-' }}
                                            @else
                                                {{ ($company->activeCompanyDoc(2)) ?  $company->activeCompanyDoc(2)->expiry->format('d/m/Y') : '-'}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($date_range)
                                                {{ ($activeDocData['ref_type']) ? $activeDocData['ref_type'] : '-' }}
                                            @else
                                                {{ ($company->activeCompanyDoc(2)) ?  $company->activeCompanyDoc(2)->ref_type : '-'}}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($date_range)
                                                {{ ($company->activeCompanyDocDate(4, $from_ymd, $to_ymd)->first()) ? $company->activeCompanyDocDate(4, $from_ymd, $to_ymd)->first()->expiry->format('d/m/Y') : '-' }}
                                            @else
                                                {{ ($company->activeCompanyDoc(4)) ?  $company->activeCompanyDoc(4)->expiry->format('d/m/Y') : '-'}}
                                            @endif
                                        </td>
                                        <td>{!! ($company->status) ? 'Active' : $company->deactivated->format('d/m/Y') !!}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {!! Form::close() !!}


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