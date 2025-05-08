@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Equipment Allocation</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <style>
        .rowHighlight {
            color: #fff;
            background-color: #666 !important;
        }
    </style>
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase font-green-haze"> Equipment Allocation</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->allowed2('add.equipment'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/equipment/other-location" data-original-title="Stocktake">Other Locations</a>
                            @endif
                            @if (Auth::user()->hasPermission2('view.equipment.stocktake'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/equipment/stocktake/0" data-original-title="Stocktake">Stocktake</a>
                            @endif
                            <a class="btn btn-circle green btn-outline btn-sm" href="/equipment/inventory" data-original-title="Inventory">Inventory</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Current Equipment Transfers</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list2">
                            <thead>
                            <tr class="mytable-header">
                                <th width="10%"> Date</th>
                                <th> Items</th>
                                <th> From</th>
                                <th> To</th>
                                <th> Assigned To / By</th>
                                <th width="10%"> Action</th>
                            </tr>
                            </thead>
                        </table>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Current Equipment Allocation <a href="/equipment/0/transfer-bulk" class="btn dark pull-right" id="btn-multiple" style="margin-top: 0px">Bulk Equipment Transfer</a></h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::select('category_id', \App\Models\Misc\Equipment\EquipmentCategory::where('parent', 0)->orderBy('name')->pluck('name', 'id')->toArray(), 1, ['class' => 'form-control bs-select', 'id' => 'category_id']) !!} </div>
                        </div>
                        <br>


                        {{-- General Equipment --}}
                        <table class="table table-bordered order-column" id="table-1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"></th>
                                <th> Item Name</th>
                                <th width="5%"> Qty</th>
                                <th width="10%"></th>
                            </tr>
                            </thead>
                            @foreach (\App\Models\Misc\Equipment\Equipment::where('category_id', 1)->where('status', 1)->orderBy('name')->get() as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locations()->sortBy('name') as $location)
                                        <?php $item = $location->equipmentItem($equip->id); ?>
                                    @if (!$location->notes)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$location->inTransit())
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        </table>

                        {{-- Scaffold Equipment --}}
                        <table class="table table-bordered order-column" id="table-2">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"></th>
                                <th width="90">Photo</th>
                                <th> Item Name</th>
                                <th width="5%"> Qty</th>
                                <th width="10%"></th>
                            </tr>
                            </thead>
                            @foreach (\App\Models\Misc\Equipment\Equipment::where('category_id', 2)->where('status', 1)->orderBy('name')->get() as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>
                                        @if ($equip->attachment && file_exists(public_path($equip->attachmentUrl)))
                                            <a href="{{ $equip->attachmentUrl }}" class="html5lightbox " title="{{ $equip->name }}" data-lityXXX>
                                                <img src="{{ $equip->attachmentUrl }}?{{rand(1, 32000)}}" width="90" class="thumbnail img-responsive img-thumbnail"></a>
                                        @endif
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locations()->sortBy('name') as $location)
                                        <?php $item = $location->equipmentItem($equip->id); ?>
                                    @if (!$location->notes)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$location->inTransit())
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        </table>

                        {{-- Bulk Hardware Equipment --}}
                        <table class="table table-bordered order-column" id="table-19">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"></th>
                                <th width="90">Photo</th>
                                <th> Item Name</th>
                                <th width="5%"> Qty</th>
                                <th width="10%"></th>
                            </tr>
                            </thead>
                            @foreach (\App\Models\Misc\Equipment\Equipment::where('category_id', 19)->where('status', 1)->orderBy('name')->get() as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>
                                        @if ($equip->attachment && file_exists(public_path($equip->attachmentUrl)))
                                            <a href="{{ $equip->attachmentUrl }}" class="html5lightbox " title="{{ $equip->name }}" data-lityXXX>
                                                <img src="{{ $equip->attachmentUrl }}?{{rand(1, 32000)}}" width="90" class="thumbnail img-responsive img-thumbnail"></a>
                                        @endif
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locations()->sortBy('name') as $location)
                                        <?php $item = $location->equipmentItem($equip->id); ?>
                                    @if (!$location->notes)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$location->inTransit())
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        </table>

                        {{-- Materials Equipment --}}
                        <table class="table table-bordered order-column" id="table-3">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"></th>
                                <th> Item Name</th>
                                <th width="7%"> Length</th>
                                <th width="5%"> Qty</th>
                                <th width="10%"></th>
                            </tr>
                            </thead>
                            <?php $materials_cats = \App\Models\Misc\Equipment\EquipmentCategory::where('parent', 3)->where('status', 1)->pluck('id')->toArray() ?>
                            @foreach (\App\Models\Misc\Equipment\EquipmentCategory::where('parent', 3)->where('status', 1)->orderBy('name')->get() as $cat)
                                {{-- Sub Categories --}}
                                <tr id="equipc-{{ $cat->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closedc-{{ $cat->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="openedc-{{ $cat->id}}"></i>
                                    </td>
                                    <td>{{ $cat->name }}</td>
                                    <td></td>
                                    <td></td>
                                    <td>&nbsp;</td>
                                </tr>
                                    <?php
                                    $equip_ids = \App\Models\Misc\Equipment\Equipment::where('category_id', $cat->id)->where('status', 1)->pluck('id')->toArray();
                                    $location_ids = \App\Models\Misc\Equipment\EquipmentLocationItem::whereIn('equipment_id', $equip_ids)->pluck('location_id')->toArray();
                                    $locations = array_unique($location_ids);
                                    rsort($locations);
                                    ?>
                                @foreach ($locations as $loc_id)
                                    {{-- Location of items --}}
                                        <?php $location = \App\Models\Misc\Equipment\EquipmentLocation::findOrFail($loc_id); ?>
                                    @if (!$location->notes)
                                        <tr class="locationc-{{ $cat->id}}" style="display: none; background-color: #ccc" id="locations-{{ $equip->id}}-loc">
                                            <td></td>
                                            <td colspan="4"><b>{{ $location->name }}</b></td>
                                        </tr>

                                        {{-- Items at Location --}}
                                        @foreach ($location->items->whereIn('equipment_id', $equip_ids)->sortBy('item_name') as $item)
                                            <tr class="locationc-{{ $cat->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                                <td></td>
                                                <td>{{ $item->equipment->name }}</td>
                                                <td>{{ ($item->equipment->length) ? $item->equipment->length : 'N/A' }}</td>
                                                <td>{{ ($item) ? $item->qty : 0 }}</td>
                                                <td>
                                                    @if (!$location->inTransit())
                                                        <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endforeach
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
        $(document).ready(function () {
            var status = $('#status').val();

            showAllocation();

            function showAllocation() {
                var num = $('#category_id').val();
                $('#table-1').hide();
                $('#table-2').hide();
                $('#table-3').hide();
                $('#table-19').hide();
                $('#table-' + num).show();
            }

            $('#category_id').change(function () {
                showAllocation();
            });

            $('.fa-plus-circle').click(function () {
                var split = this.id.split("-");
                var type = split[0];
                var id = split[1];

                if (type[type.length - 1] != 'c') {
                    $('#closed-' + id).hide();
                    $('#opened-' + id).show();
                    $(".location-" + id).show();
                    $("#equip-" + id).addClass('rowHighlight');
                } else {
                    $('#closedc-' + id).hide();
                    $('#openedc-' + id).show();
                    $(".locationc-" + id).show();
                    $("#equipc-" + id).addClass('rowHighlight');
                }
            });

            $('.fa-minus-circle').click(function () {
                var split = this.id.split("-");
                var type = split[0];
                var id = split[1];

                if (type[type.length - 1] != 'c') {
                    $('#closed-' + id).show();
                    $('#opened-' + id).hide();
                    $(".location-" + id).hide();
                    $("#equip-" + id).removeClass('rowHighlight');
                } else {
                    $('#closedc-' + id).show();
                    $('#openedc-' + id).hide();
                    $(".locationc-" + id).hide();
                    $("#equipc-" + id).removeClass('rowHighlight');
                }

            });


            var table_list2 = $('#table_list2').DataTable({
                pageLength: 100,
                processing: true,
                serverSide: true,
                searching: false,
                paging: false,
                info: false,
                ajax: {
                    'url': '{!! url('equipment/dt/transfers') !!}',
                    'type': 'GET',
                },
                columns: [
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'items', name: 'items'},
                    {data: 'from', name: 'from'},
                    {data: 'to', name: 'to'},
                    {data: 'assigned_to', name: 'assigned_to'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [
                    [0, "asc"],
                ]
            });

            /*
             var table_list = $('#table_list').DataTable({
             pageLength: 100,
             processing: true,
             serverSide: true,
             ajax: {
             'url': '{!! url('equipment/dt/allocation') !!}',
         'type': 'GET',
         'data': function (d) {
         d.site_id = $('#site_id').val();
         }
         },
         columns: [
         {data: 'view', name: 'view', orderable: false, searchable: false},
         {data: 'catname', name: 'equipment_categories.name'},
         {data: 'itemname', name: 'equipment.name'},
         {data: 'qty', name: 'qty'},
         {data: 'code', name: 'sites.code'},
         {data: 'suburb', name: 'sites.suburb'},
         {data: 'sitename', name: 'sites.name'},
         {data: 'other', name: 'equipment_location.other'},
         {data: 'action', name: 'action', orderable: false, searchable: false},
         ],
         order: [
         [1, "asc"], [2, "asc"], [4, "asc"], [3, "desc"]
         ]
         }); */
        });
    </script>

    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop