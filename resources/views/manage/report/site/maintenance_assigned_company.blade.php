@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Maintenance Assigned Companies</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <form method="POST" action="{{ action([\App\Http\Controllers\Misc\ReportController::class, 'maintenanceAssignedCompanyPDF']) }}" class="horizontal-form">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Maintenance Assigned Companies</span>
                            </div>
                            <div class="actions">
                                <button type="submit" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</button>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <x-form.select name="supervisor" label="Task Owner" :options="['all' => 'All supervisors'] + Auth::user()->company->reportsTo()->supervisorsSelect() + ['2023' => 'Jason Habib']"/>
                                </div>
                                <div class="col-md-4">
                                    <x-form.select name="assigned_to" label="Assigned Company" :options="$assignedList" value="all"/>
                                </div>
                            </div>
                            <br><br>


                            <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th style="width:10%"> Reported</th>
                                    <th> Site</th>
                                    <th> Task Owner</th>
                                    <th> Assigned Company</th>
                                    <th style="width:10%"> Updated</th>
                                </tr>
                                </thead>
                            </table>


                            <hr>
                            <a href="/manage/report" class="btn default pull-right">Back</a><br><br>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- loading Spinner -->
    <div style="background-color: #FFF; padding: 20px; display: none" id="spinner">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {
            //$('#view_pdf').click(function (e) {
            $('form').submit(function (e) {
                // custom handling here
                if (true) {
                    //$('#spinner').show();
                    return true;
                }


                swal({
                    title: 'Unable to view PDF',
                    text: 'You must select a <b>Site</b> or <b>Company</b>',
                    html: true,
                });
                e.preventDefault();

            });
        });


        var status1 = $('#status1').val();
        var table1 = $('#table1').DataTable({
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('/manage/report/maintenance_assigned_company/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.supervisor = $('#supervisor').val();
                    d.assigned_to = $('#assigned_to').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'reported_date', name: 'm.reported'},
                //{data: 'site_id', name: 's.code'},
                {data: 'sitename', name: 's.name', orderable: false},
                {data: 'super_id', name: 'm.super_id'},
                {data: 'assigned_to', name: 'm.assigned_to'},
                {data: 'last_updated', name: 'last_updated', orderable: false, searchable: false},
                //{data: 'companyname', name: 'c.name', visible: false},
            ],
            order: [
                [1, "desc"]
            ]
        });

        $('select#assigned_to').change(function () {
            table1.ajax.reload();
        });

        $('select#supervisor').change(function () {
            table1.ajax.reload();
        });
    </script>
@stop