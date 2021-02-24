@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Sites</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Management</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->allowed2('add.site'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    @if (Auth::user()->company->subscription)
                                        <option value="-1">Upcoming</option>
                                        <option value="0">Completed</option>
                                        <option value="2">Maintenance</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                @if (Auth::user()->company->subscription ||  Auth::user()->company_id == '96')
                                    <th width="5%"> #</th>
                                @endif
                                <th width="5%"> No.</th>
                                <th> Suburb</th>
                                <th> Name</th>
                                {{-- CapeCod + JonSpin --}}
                                @if (Auth::user()->isCC() ||  Auth::user()->company_id == '96')
                                    <th width="15%"> Phone</th> @endif
                                <th> Address</th>
                                <th> Supervisor</th>
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
            'url': '{!! url('site/dt/sites') !!}',
            'type': 'GET',
            'data': function (d) {
                d.status = $('#status').val();
            }
        },
        columns: [
                @if (Auth::user()->company->subscription ||  Auth::user()->company_id == '96'){data: 'id', name: 'sites.id', orderable: false, searchable: false}, @endif
            {
                data: 'code', name: 'code'
            },
            {data: 'suburb', name: 'suburb'},
            {data: 'name', name: 'name'},
                @if (Auth::user()->isCC() ||  Auth::user()->company_id == '96') {data: 'client_phone', name: 'client_phone'}, @endif
            {
                data: 'address', name: 'address'
            },
            {data: 'supervisor', name: 'supervisor'},
        ],
        order: [
                @if (Auth::user()->isCC() ||  Auth::user()->company_id == '96') [2, "asc"], [3, 'asc']  @else [1, "asc"], [2, 'asc'] @endif
        ]
    });

    $('select#site_group').change(function () {
        if ($('#site_group').val() == 0 || $('#site_group').val() == {{ Auth::user()->company_id}}) {
            var newOptions = {"Active": "1", "Upcoming": "-1", "Completed": "0", "Maintenance": "2"};

            var $el = $("#status");
            $el.empty(); // remove old options
            $.each(newOptions, function (key, value) {
                $el.append($("<option></option>").attr("value", value).text(key));
            });
            $('#status').selectpicker('refresh');
        } else {
            $('#status').children('option:not(:first)').remove();
            $('#status').selectpicker('refresh');

            //$('#status').remove();
        }

        table_list.ajax.reload();
    });
    $('select#status').change(function () {
        table_list.ajax.reload();
    });
</script>
@stop