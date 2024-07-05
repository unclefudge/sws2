@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Practical Completion</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @if ($progress->count())
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> In Progress</span>
                            </div>
                        </div>

                        <div>
                            <table class="table table-striped table-bordered table-hover order-column" id="under_review">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th style="width:10%"> Created</th>
                                    <th> Site</th>
                                    <th> Site Supervisor</th>
                                    <th style="width:10%"></th>
                                </tr>
                                </thead>
                                @foreach ($progress as $prac)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/site/prac-completion/{{ $prac->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td> {{ $prac->created_at->format('d/m/Y') }}</td>
                                        <td> {{ $prac->site->name }}</td>
                                        <td> {{ $prac->site->supervisor->name }}</td>
                                        <td>
                                            @if(Auth::user()->allowed2('edit.prac.completion', $prac))
                                                <a href="/site/prac-completion/{{ $prac->id }}/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>
                                            @endif
                                            @if(Auth::user()->allowed2('del.prac.completion', $prac))
                                                <button class="btn dark btn-xs sbold uppercase margin-bottom delete-report" data-id="{{ $prac->id }}" data-name="{{ $prac->site->name }}"><i class="fa fa-trash"></i></button>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Practical Completion</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->allowed2('add.site.maintenance'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/prac-completion/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.maintenance', 3) == 99)
                            <input type="hidden" id="supervisor_sel" value="1">
                            <div class="col-md-4">
                                {!! Form::select('supervisor', ['all' => 'All sites', 'signoff' => 'Require Sign Off'] + Auth::user()->company->reportsTo()->supervisorsSelect() + ['2023' => 'Jason Habib'], null, ['class' => 'form-control bs-select', 'id' => 'supervisor']) !!}
                            </div>
                        @else
                            <input type="hidden" id="supervisor_sel" value="0">
                        @endif

                        <div class="col-md-4">
                            {!! Form::select('assigned_to', $assignedList, 'all', ['class' => 'form-control bs-select', 'id' => 'assigned_to']) !!}
                        </div>

                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status1" id="status1" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    {{--}}<option value="4">On Hold</option>--}}
                                    <option value="0">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"> #</th>
                                <th> Site</th>
                                {{--}}<th width="10%"> Client Contacted</th>
                                <th width="10%"> Appointment</th>--}}
                                <th style="width:10%"> Supervisor</th>
                                <th> Assigned Company</th>
                                <th style="width:10%"> Updated</th>
                                <th style="width:10%"></th>
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
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/prac-completion/dt/prac') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.supervisor_sel = $('#supervisor_sel').val();
                    d.supervisor = $('#supervisor').val();
                    d.assigned_to = $('#assigned_to').val();
                    d.status = $('#status1').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                //{data: 'site_id', name: 's.code'},
                {data: 'sitename', name: 's.name', orderable: false},
                //{data: 'contacted_date', name: 'm.client_contacted'},
                //{data: 'appointment_date', name: 'm.client_appointment'},
                {data: 'super_id', name: 'm.super_id'},
                {data: 'assigned_to', name: 'm.assigned_to'},
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

        $('select#assigned_to').change(function () {
            table1.ajax.reload();
        });

        // Warning message for deleting report
        $('.delete-report').click(function (e) {
            e.preventDefault();
            var url = "/site/prac-completion/" + $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The Prac Completion <b>" + name + "</b> will be deleted.<br><br><span class='font-red'><i class='fa fa-warning'></i> You will not be able to undo this action!</span>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted report');
                    },
                }).always(function (data) {
                    location.reload();
                });
            });
        });
    </script>
@stop