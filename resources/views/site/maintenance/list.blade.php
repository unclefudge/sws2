@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Maintenance Register</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        @if ($under_review)
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Under Review</span>
                            </div>
                        </div>

                        <div>
                            <table class="table table-striped table-bordered table-hover order-column" id="under_review">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th width="10%"> Reported</th>
                                    <th> Site</th>
                                    <th> Site Supervisor</th>
                                    <th width="10%"> Updated</th>
                                    <th width="5%"></th>
                                </tr>
                                </thead>
                                @foreach ($under_review as $rec)
                                    <?php $main = App\Models\Site\SiteMaintenance::find($rec->id) ?>
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/site/maintenance/{{ $rec->id }}">M{{$rec->code}}</a></div>
                                        </td>
                                        <td> {{ $rec->created_date }}</td>
                                        <td> {{ $rec->sitename }}</td>
                                        <td> {{ $rec->supervisor }}</td>
                                        <td> {{ ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if(Auth::user()->allowed2('edit.site.maintenance', $main))
                                                <a href="/site/maintenance/{{ $rec->id }}/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
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
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Maintenance Register</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->allowed2('add.site.maintenance'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/maintenance/category" data-original-title="Add">Categories</a>
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/maintenance/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.maintenance', 3) == 99)
                            <input type="hidden" id="supervisor_sel" value="1">
                            <div class="col-md-4">
                                {!! Form::select('supervisor', ['all' => 'All sites'] + Auth::user()->company->reportsTo()->supervisorsSelect() + ['2023' => 'Jason Habib'], null, ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}
                            </div>
                        @else
                            <input type="hidden" id="supervisor_sel" value="0">
                        @endif
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status1" id="status1" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="4">On Hold</option>
                                    <option value="0">Completed</option>
                                    <option value="-1">Declined</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="10%"> Reported</th>
                                <th> Site</th>
                                <th width="10%"> Client Contacted</th>
                                <th width="10%"> Appointment</th>
                                <th> Supervisor</th>
                                <th width="10%"> Updated</th>
                                {{--}}<th width="10%"> Completed</th>--}}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    var status1 = $('#status1').val();
    var table1 = $('#table1').DataTable({
        pageLength: 25,
        processing: true,
        serverSide: true,
        ajax: {
            'url': '{!! url('site/maintenance/dt/maintenance') !!}',
            'type': 'GET',
            'data': function (d) {
                d.supervisor_sel = $('#supervisor_sel').val();
                d.supervisor = $('#supervisor').val();
                d.status = $('#status1').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'reported_date', name: 'm.reported'},
            //{data: 'site_id', name: 's.code'},
            {data: 'sitename', name: 's.name', orderable: false},
            {data: 'contacted_date', name: 'm.client_contacted'},
            {data: 'appointment_date', name: 'm.client_appointment'},
            {data: 'super_id', name: 'm.super_id'},
            {data: 'last_updated', name: 'last_updated', orderable: false, searchable: false},
            //{data: 'completed', name: 'completed', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [1, "desc"]
        ]
    });

    $('select#status1').change(function () {
        table1.ajax.reload();
    });

    $('select#supervisor').change(function () {
        table1.ajax.reload();
    });
</script>
@stop