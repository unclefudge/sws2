@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Quality Assurance</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- var_dump(session()->all()) --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Upcoming QA Reports</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/qa" data-original-title="QA">Current QA Reports</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.qa', 3) == 99)
                            <input type="hidden" id="supervisor_sel" value="1">
                            <div class="col-md-4">
                                {!! Form::select('supervisor', ['all' => 'All sites'] + Auth::user()->company->reportsTo()->supervisorsSelect(), null, ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}
                            </div>
                        @else
                            <input type="hidden" id="supervisor_sel" value="0">
                        @endif

                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Site</th>
                                <th> Name</th>
                                <th> Supervisor</th>
                                <th width="10%"> Updated</th>
                                <th width="10%"> Completed</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
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
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var status1 = $('#status1').val();
        var table1 = $('#table1').DataTable({
            pageLength: 20,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/qa/dt/qa_upcoming') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.supervisor = $('#supervisor').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'sitename', name: 's.name'},
                {data: 'name', name: 'q.name'},
                {data: 'supervisor', name: 'supervisor', orderable: false, searchable: false},
                {data: 'updated_at', name: 'q.updated_at'},
                {data: 'completed', name: 'completed', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "asc"]
            ]
        });
    </script>
@stop