@inject('licenceTypes', 'App\Http\Utilities\LicenceTypes')
@inject('payrollTaxTypes', 'App\Http\Utilities\PayrollTaxTypes')
@extends('layout-guest')

@if (Auth::user()->company->status != 2)
    @section('breadcrumbs')
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
                <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
                <li><span>Profile</span></li>
            @else
                <li><span>Workers</span></li>
            @endif
        </ul>
    @stop
@endif

@section('content')
    <div class="page-content-inner">

        {{-- Company Signup Progress --}}
        <div class="mt-element-step">
            <div class="row step-line" id="steps">
                <div class="col-sm-3 mt-step-col first active">
                    <a href="/signup/user/{{ Auth::user()->company->primary_user }}">
                        <div class="mt-step-number bg-white font-grey">1</div>
                    </a>
                    <div class="mt-step-title uppercase font-grey-cascade">Business Owner</div>
                    <div class="mt-step-content font-grey-cascade">Add primary user</div>
                </div>
                <div class="col-sm-3 mt-step-col active">
                    <a href="/signup/company/{{ Auth::user()->company_id }}">
                        <div class="mt-step-number bg-white font-grey">2</div>
                    </a>
                    <div class="mt-step-title uppercase font-grey-cascade">Company Info</div>
                    <div class="mt-step-content font-grey-cascade">Add company info</div>
                </div>
                <div class="col-sm-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">3</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Workers</div>
                    <div class="mt-step-content font-grey-cascade">Add workers</div>
                </div>
                <div class="col-sm-3 mt-step-col last">
                    <div class="mt-step-number bg-white font-grey">4</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                    <div class="mt-step-content font-grey-cascade">Upload documents</div>
                </div>
            </div>
        </div>
        <div class="note note-warning">
            <b>Step 3: Add all additional users that work on job sites.</b><br><br>All workers require their own login<br><br>
            <ul>
                <li>Add users by clicking
                    <button class="btn dark btn-outline btn-xs" href="javascript:;"> Add User</button>
                </li>
            </ul>
            Once you've added all your users please click
            <button class="btn dark btn-outline btn-xs" href="javascript:;"> Continue</button>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-users "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Workers</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-12">
                                <h1 class="sbold hidden-sm hidden-xs" style="margin: 0 0 15px 0">{{ $company->name }}</h1>

                                <a href="/user/create" class="btn dark pull-right">Add User</a>

                                {{-- Staff --}}
                                <h3 class="form-section font-green">Staff</h3>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-striped table-bordered table-hover order-column" id="table_staff">
                                            <thead>
                                            <tr class="mytable-header">
                                                <th> Name</th>
                                                <th> Phone</th>
                                                <th> Email</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/signup/summary/{{ $company->id }}" class="btn green pull-right" style="margin-left: 20px">Continue</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $company->displayUpdatedBy() !!}
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var table_staff = $('#table_staff').DataTable({
            processing: true,
            serverSide: true,
            //bFilter: false,
            //bLengthChange: false,
            ajax: {
                'url': '/company/dt/users',
                'type': 'GET',
                'data': function (d) {
                    d.company_id = {{ $company->id }};
                    d.staff = 'staff';
                    d.status = 1;
                }
            },
            columns: [
                {data: 'full_name', name: 'full_name'},
                {data: 'phone', name: 'phone', orderable: false},
                {data: 'email', name: 'email', orderable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });
    </script>
@stop