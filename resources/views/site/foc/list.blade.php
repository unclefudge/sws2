@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>FOC Requirements</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> FOC Requirements</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/foc/settings"
                                   data-original-title="Settings">Settings</a>
                            @endif
                            @if(Auth::user()->allowed2('add.site.foc'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/foc/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.foc', 3) == 99)
                            <div class="col-md-4">
                                <x-form.select name="supervisor" id="supervisor" :options="['all' => 'All sites'] + Auth::user()->company->reportsTo()->supervisorsSelect()"/>
                            </div>
                        @endif

                        <div class="col-md-2 pull-right">
                            <x-form.select name="stage1" id="stage1" :options="$stageOptions" :value="$selectedStage"/>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%">#</th>
                                <th>Site</th>
                                <th style="width:10%">Supervisor</th>
                                <th style="width:10%">Prac Completed</th>
                                <th style="width:12%">FOC Requested</th>
                                <th style="width:12%">FOC Received</th>
                                <th style="width:10%">Updated</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
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
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var table1 = $('#table1').DataTable({
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('site/foc/dt/foc') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.supervisor = $('#supervisor').val();
                    d.stage = $('#stage1').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'sitename', name: 's.name', orderable: true},
                {data: 'super_id', name: 'm.super_id'},
                {data: 'prac_completed', name: 's.completion_signed'},
                {data: 'foc_requested_date', name: 'm.foc_requested'},
                {data: 'foc_received', name: 's.oc_rcvd_date'},
                {data: 'last_updated', name: 'last_updated', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [1, "desc"]
            ]
        });

        function applyStageColumns(stage) {
            var showSuper = true;
            var showPrac = true;
            var showFocRequested = false;
            var showFocReceived = true;

            if (stage === 'Upcoming') {
                showSuper = false;
                showPrac = false;
                showFocRequested = false;
                showFocReceived = false;
            } else if (stage === 'Jobs in Const') {
                showPrac = false;
                showFocRequested = false;
                showFocReceived = false;
            } else if (stage === "Prac'd Jobs") {
                showPrac = true;
                showFocRequested = false;
                showFocReceived = false;
            } else if (stage === 'FOC Booked') {
                showPrac = true;
                showFocRequested = true;
                showFocReceived = false;
            }

            table1.column(2).visible(showSuper, false);
            table1.column(3).visible(showPrac, false);
            table1.column(4).visible(showFocRequested, false);
            table1.column(5).visible(showFocReceived, false);
            table1.columns.adjust();
        }

        applyStageColumns($('#stage1').val());

        $('select#stage1').change(function () {
            applyStageColumns($(this).val());
            table1.ajax.reload();
        });

        $('select#supervisor').change(function () {
            table1.ajax.reload();
        });

        $('select#assigned_to').change(function () {
            table1.ajax.reload();
        });

        // Warning message for deleting report
        $(document).on('click', '.delete-report', function (e) {
            e.preventDefault();
            var url = "/site/foc/" + $(this).data('id');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "The FOC <b>" + name + "</b> will be moved to Disabled.",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, disable it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('FOC moved to Disabled');
                    },
                }).always(function (data) {
                    location.reload();
                });
            });
        });
    </script>
@stop
