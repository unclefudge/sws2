@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Hazard Register.</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Hazard Register</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasPermission2('add.site.hazard'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/hazard/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.hazard', Auth::user()->company_id) && (Auth::user()->company->parent_company && Auth::user()->permissionLevel('view.site.hazard', Auth::user()->company->reportsTo()->id)))
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
                                    <option value="9">Resolved</option>
                                    <option value="0">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="5%"> ID</th>
                                <th width="10%"> Date</th>
                                <th width="10%"> Resolved</th>
                                <th> Site</th>
                                <th> Initiated By</th>
                                <th> Supervisor</th>
                                <th> Safety Concern</th>
                                <th>Location</th>
                                <th>Source</th>
                                <th width="5%"> Rating</th>
                                @if (Auth::user()->hasAnyRole2("web-admin|mgt-general-manager|whs-manager"))
                                    <th width="2%"></th>
                                @endif
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
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        var status = $('#status').val();

        var table_list = $('#table_list').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('/site/hazard/dt/hazards') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.site_group = $('#site_group').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'view', name: 'view', orderable: false, searchable: false},
                {data: 'id', name: 'site_hazards.id', orderable: false, searchable: false},
                {data: 'nicedate', name: 'site_hazards.created_at'},
                {data: 'nicedate2', name: 'site_hazards.resolved_at', visible: false,},
                {data: 'name', name: 'sites.name', orderable: false},
                {data: 'fullname', name: 'fullname', orderable: false, searchable: false},
                {data: 'supervisor', name: 'supervisor', orderable: false, searchable: false},
                {data: 'reason', name: 'site_hazards.reason', orderable: false},
                {data: 'location', name: 'site_hazards.location', orderable: false},
                {data: 'source', name: 'site_hazards.source', orderable: false},
                {data: 'rating', name: 'rating'},
                    @if (Auth::user()->hasAnyRole2("web-admin|mgt-general-manager|whs-manager"))
                {
                    data: 'action', name: 'action', orderable: false, searchable: false
                },
                @endif
            ],
            order: [
                [2, "desc"]
            ]
        });

        table_list.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this record!<br><b>" + name + "</b>",
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
                        toastr.error('Deleted document');
                    },
                }).always(function (data) {
                    $('#table_list').DataTable().draw(false);
                });
            });
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

    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop