@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        <li><span>Inventory</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Equipment Inventory</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->allowed2('add.equipment'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/equipment/create" data-original-title="Add">Add</a>
                            @endif
                            @if (Auth::user()->hasPermission2('del.equipment'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/equipment/writeoff" data-original-title="Add">Write-Off</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            {{-- {!! Form::select('category_id', \App\Models\Misc\Equipment\EquipmentCategory::where('parent', 0)->orderBy('name')->pluck('name', 'id')->toArray(), 1, ['class' => 'form-control bs-select', 'id' => 'category_id']) !!} --}}
                            {!! Form::select('category_id', ['1' => 'General', '3' => 'Materials', '19' => 'Materials (Bulk Hardware)', '2' => 'Scaffold'], 1, ['class' => 'form-control bs-select', 'id' => 'category_id']) !!}
                        </div>

                    </div>
                    <br>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Category</th>
                                <th> Item Name</th>
                                <th width="10%"> Length</th>
                                <th width="10%"> Available</th>
                                <th width="10%"> Required Min</th>
                                <th width="10%"> Last Ordered</th>
                                <th width="10%"> Missing</th>
                                <th width="10%"> Puchased</th>
                                <th width="10%"> Disposed</th>
                                <th width="10%"> Action</th>
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
            'url': '{!! url('equipment/dt/inventory') !!}',
            'type': 'GET',
            'data': function (d) {
                d.category_id = $('#category_id').val();
            }
        },
        columns: [
            {data: 'id', name: 'id', orderable: false, searchable: false},
            {data: 'catname', name: 'equipment_categories.name'}, // 1
            {data: 'name', name: 'name'},     // 2
            {data: 'length', name: 'length'}, // 3
            {data: 'total', name: 'total'},   // 4
            {data: 'min_stock', name: 'min_stock'},   // 5
            {data: 'purchased_last', name: 'purchased_last', searchable: false},   // 6
            {data: 'lost', name: 'lost'},     // 7
            {data: 'purchased', name: 'purchased'},  // 8
            {data: 'disposed', name: 'disposed'}, // 9
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [1, "asc"], [2, "asc"]
        ]
    });

    updateCols();

    function updateCols() {
        if ($("#category_id").val() == 3) { // Materials
            table_list.column(1).visible(true);  // Category
            table_list.column(3).visible(true);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(false);  // Min Req
            table_list.column(6).visible(false);  // Last Ordered
            table_list.column(7).visible(false);  // Missing
            table_list.column(8).visible(false);  // Purchased
            table_list.column(9).visible(false);  // Disposed
        } else if ($("#category_id").val() == 19) { // Bulk Hardware
            table_list.column(1).visible(false);  // Category
            table_list.column(3).visible(true);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(true);  // Min Req
            table_list.column(6).visible(true);  // Last Ordered
            table_list.column(7).visible(false);  // Missing
            table_list.column(8).visible(false);  // Purchased
            table_list.column(9).visible(false);  // Disposed
        } else {
            table_list.column(1).visible(false);  // Category
            table_list.column(3).visible(false);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(false);  // Min Req
            table_list.column(6).visible(false);  // Last Ordered
            table_list.column(7).visible(true);  // Missing
            table_list.column(8).visible(true);  // Purchased
            table_list.column(9).visible(true);  // Disposed
        }
        table_list.ajax.reload();
    }

    function updateCols2() {
        if ($("#category_id").val() == 3) {
            table_list.column(3).visible(true);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(false);  // Missing
            table_list.column(6).visible(false);  // Purchased
            table_list.column(7).visible(false);  // Disposed
        } else if ($("#category_id").val() == 35) {
            table_list.column(3).visible(true);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(false);  // Missing
            table_list.column(6).visible(false);  // Purchased
            table_list.column(7).visible(false);  // Disposed
        } else {
            table_list.column(3).visible(false);  // Length
            table_list.column(4).visible(true);  // Available
            table_list.column(5).visible(true);  // Missing
            table_list.column(6).visible(true);  // Purchased
            table_list.column(7).visible(true);  // Disposed
        }
        table_list.ajax.reload();
    }
    $('select#category_id').change(function () {
        updateCols();
    });
</script>
@stop