@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Site Incidents</span></li>
    </ul>
    @stop

    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        @if ($progress->count())
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <span class="caption-subject bold uppercase font-green-haze"> In Progress</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover order-column" id="table_progress">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th> Incident Date</th>
                                    <th> Site</th>
                                    <th> Reported by</th>
                                    <th> Reported date</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($progress as $p)
                                    <tr>
                                        <td width="5%">
                                            <div class="text-center"><a href="/site/incident/{{ $p->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td width="15%">{{ $p->date->format('d/m/Y') }}</td>
                                        <td>{{ ($p->site) ? $p->site->name : '' }}</td>
                                        <td>{{ $p->createdBy->name }}</td>
                                        <td width="15%">{{ $p->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase font-green-haze"> Site Incidents</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->allowed2('add.site.incident'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/incident/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.accident', Auth::user()->company_id) && (Auth::user()->company->parent_company && Auth::user()->permissionLevel('view.site.accident', Auth::user()->company->reportsTo()->id)))
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::select('site_group', ['0' => 'All Sites', Auth::user()->company_id => Auth::user()->company->name,
                                    Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name], null, ['class' => 'form-control bs-select', 'id' => 'site_group']) !!}
                                </div>
                            </div>
                        @else
                            {!! Form::hidden('site_group', '') !!}
                        @endif

                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Open</option>
                                    <option value="0">Resolved</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> ID</th>
                                <th> Date</th>
                                <th> Resolved</th>
                                <th> Site</th>
                                <th> Supervisor</th>
                                <th> Description</th>
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">

    var status = $('#status').val();

    var table_list = $('#table_list').DataTable({
        pageLength: 100,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('site/incident/dt/incidents') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_group = $('#site_group').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            {data: 'view', name: 'view', orderable: false, searchable: false},
            {data: 'id', name: 'site_incidents.id', orderable: false, searchable: false},
            {data: 'nicedate', name: 'site_incidents.date'},
            {data: 'nicedate2', name: 'site_incidents.resolved_at', visible: false,},
            {data: 'site_name', name: 'site_incidents.site_name'},
            {data: 'site_supervisor', name: 'site_incidents.site_supervisor', orderable: false, searchable: false},
            {data: 'description', name: 'description', orderable: false},
        ],
        order: [
            [2, "desc"]
        ]
    });

    $('select#site_group').change(function () {
        table_list.ajax.reload();
    });

    $('select#status').change(function () {
        if ($('#status').val() == 0)
            table_list.column('3').visible(true);
        else
            table_list.column('3').visible(false);
        table_list.ajax.reload();
    });
</script>
@stop