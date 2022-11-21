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
                            <span class="caption-subject bold uppercase font-green-haze"> Site List</span>
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->permissionLevel('view.site.list', Auth::user()->company_id) && (Auth::user()->company->parent_company && Auth::user()->permissionLevel('view.site.list', Auth::user()->company->reportsTo()->id)))
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::select('site_group', ['0' => 'All Sites', Auth::user()->company_id => Auth::user()->company->name,
                                    Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name], null, ['class' => 'form-control bs-select', 'id' => 'site_group']) !!}
                                </div>
                            </div>
                        @endif
                        <div class="col-md-2 pull-right">
                            <div class="form-group">
                                <select name="status" id="status" class="form-control bs-select">
                                    <option value="1" selected>Active</option>
                                    @if (Auth::user()->hasAnyRole2('mgt-general-manager|con-construction-manager|whs-manager|web-admin'))
                                        <option value="-1">Upcoming</option>
                                        @endif
                                        @if (Auth::user()->hasAnyRole2('mgt-general-manager|con-construction-manager|whs-manager|web-admin|con-supervisor') || Auth::user()->company_id == 29) <!-- GBT -->
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
                                {{--}}<th width="10%"> Job #</th>--}}
                                {{--}}<th> Suburb</th>--}}
                                <th> Site</th>
                                {{-- CapeCod + JonSpin --}}
                                @if (Auth::user()->isCC() ||  in_array(Auth::user()->company_id, [96, 29])) {{-- CC, JonSpin Building,  GBT Carpentry--}}
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
            'url': '{!! url('site/dt/sitelist') !!}',
            'type': 'GET',
            'data': function (d) {
                d.site_group = $('#site_group').val();
                d.status = $('#status').val();
                ;
            }
        },
        columns: [
            //{data: 'code', name: 'code'},
            //{data: 'suburb', name: 'suburb'},
            {data: 'name', name: 'name'},
                @if (Auth::user()->isCC() ||  in_array(Auth::user()->company_id, [96, 29])) {data: 'client_phone', name: 'client_phone'}, @endif
            {
                data: 'address', name: 'address'
            },
            {data: 'supervisor', name: 'supervisor'},
        ],
        order: [
            [0, "asc"], [1, 'asc']
        ]
    });

    $('select#site_group').change(function () {
        if ($('#site_group').val() == 0 || $('#site_group').val() == {{ Auth::user()->company_id}}) {
            var newOptions = {"Active": "1", "Upcoming": "-1", "Completed": "0"};

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