@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
@inject('companyDocTypes', 'App\Http\Utilities\CompanyDocTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
            <li><span>Profile</span></li>
        @else
            <li><span>Company Profile</span></li>
        @endif
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('company/_header')

        <div class="row">
            <div class="col-md-12">
                {{-- Staff --}}
                <div class="portlet light">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">Users</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.user') )
                                <a class="btn btn-circle green btn-outline btn-sm" href="/user/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">

                        <div class="row">
                            @if (count($company->companies()) > 1)
                                <div class="col-md-3">
                                    <x-form.select name="staff" :options="['staff' => 'Staff only', 'all' => 'All users']" :value="Auth::user()->isCompany($company->id) ? 'all' : 'staff'"/>
                                </div>
                            @else
                                <input type="hidden" name="staff" id="staff" value="staff">
                            @endif
                            <div class="col-md-2 pull-right">
                                <x-form.select name="status" :options="['1' => 'Active', '0' => 'Inactive']" value="1"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered table-hover order-column" id="table_staff">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th style="width:5%"> #</th>
                                        <th> Name</th>
                                        <th> Company</th>
                                        <th> Phone</th>
                                        <th> Email</th>
                                        <th> Last Login</th>
                                    </tr>
                                    </thead>
                                </table>
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
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        var table_staff = $('#table_staff').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            //bFilter: false,
            //bLengthChange: false,
            ajax: {
                'url': '/company/dt/users',
                'type': 'GET',
                'data': function (d) {
                    d.company_id = {{ $company->id }};
                    d.staff = $('#staff').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'users.id', orderable: false, searchable: false},
                {data: 'full_name', name: 'full_name', orderable: true, searchable: false},
                {data: 'name', name: 'companys.name', visible: false},
                {data: 'phone', name: 'users.phone', orderable: false},
                {data: 'email', name: 'users.email', orderable: false},
                {data: 'last_login', name: 'users.last_login'},
                {data: 'firstname', name: 'users.firstname', visible: false},
                {data: 'lastname', name: 'users.lastname', visible: false},
            ],
            order: [
                [1, "asc"]
            ]
        });

        $('select#staff').change(function () {
            if ($('#staff').val() == 'staff')
                table_staff.column(2).visible(false);   // To hide
            else
                table_staff.column(2).visible(true);    // To show
            table_staff.ajax.reload();
        });

        $('#status').change(function () {
            table_staff.ajax.reload();
        });

        @if ($company->subscription > 1)
        table_staff.column(2).visible(true);
        @endif

    </script>
@stop