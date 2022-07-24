@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Plumbing Inspection</span></li>
    </ul>
    @stop

    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        {{-- To Be Assigned --}}
        @if (Auth::user()->isCC() && $non_assigned->count())
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> To Be Assigned</span>
                            </div>
                        </div>

                        <div>
                            <table class="table table-striped table-bordered table-hover order-column" id="under_review">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"> #</th>
                                    <th width="10%"> Created</th>
                                    <th> Name</th>
                                    <th width="5%"></th>
                                </tr>
                                </thead>
                                @foreach ($non_assigned as $report)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/site/inspection/plumbing/{{ $report->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td> {{ $report->created_at->format('d/m/Y') }}</td>
                                        <td> {{ $report->site->name }}</td>
                                        <td>
                                            @if(Auth::user()->allowed2('edit.site.inspection', $report))
                                                <a href="/site/inspection/plumbing/{{ $report->id }}/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>
                                            @endif
                                            @if(Auth::user()->allowed2('del.site.inspection', $report))
                                                <button class="btn dark btn-xs sbold uppercase margin-bottom delete-report" data-id="{{ $report->id }}" data-name="{{ $report->site->name }}"><i class="fa fa-trash"></i></button>
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

        {{-- Under Review --}}
        @if (Auth::user()->isCC() && $under_review->count())
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
                                    <th width="10%"> Created</th>
                                    <th> Name</th>
                                    <th width="10%"></th>
                                </tr>
                                </thead>
                                @foreach ($under_review as $report)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/site/inspection/plumbing/{{ $report->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td> {{ $report->created_at->format('d/m/Y') }}</td>
                                        <td> {{ $report->site->name }}</td>
                                        <td>
                                            @if(Auth::user()->allowed2('edit.site.inspection', $report))
                                                <a href="/site/inspection/plumbing/{{ $report->id }}/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>
                                            @endif
                                                @if(Auth::user()->allowed2('del.site.inspection', $report))
                                                    <button class="btn dark btn-xs sbold uppercase margin-bottom delete-report" data-id="{{ $report->id }}" data-name="{{ $report->site->name }}"><i class="fa fa-trash"></i></button>
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

        {{-- Reports --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Plumbing Inspection Reports</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->allowed2('add.site.inspection'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/plumbing/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="10%"> Created</th>
                                <th> Name</th>
                                <th width="10%"> Assigned</th>
                                <th> Assigned to</th>
                                <th width="10%"> Client Contacted</th>
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
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        var status = $('#status').val();

        var table1 = $('#table1').DataTable({
            pageLength: 100,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/inspection/plumbing/dt/list') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'view', name: 'view', orderable: false, searchable: false},
                {data: 'nicedate', name: 'site_inspection_plumbing.created_at'},
                {data: 'sitename', name: 'sites.name'},
                {data: 'assigned_date', name: 'site_inspection_plumbing.assigned_at'},
                {data: 'assigned_to', name: 'assigned_to', orderable: false, searchable: false},
                {data: 'client_date', name: 'site_inspection_plumbing.client_contacted'},
            ],
            order: [
                [2, "desc"]
            ]
        });

        $('select#status').change(function () {
            table1.ajax.reload();
        });

        // Warning message for deleting report
        $('.delete-report').click(function (e) {
            e.preventDefault();
            var url = "/site/inspection/plumbing/" + $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The report <b>" + name + "</b> will be deleted.<br><br><span class='font-red'><i class='fa fa-warning'></i> You will not be able to undo this action!</span>",
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

    });

</script>
@stop