@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Equipment Allocation</span></li>
    </ul>
@stop

@section('content')
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
                                <th style="width:10%"> Date</th>
                                <th> Items</th>
                                <th> From</th>
                                <th> To</th>
                                <th> Assigned To / By</th>
                                <th style="width:10%"> Action</th>
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
                                <x-form.select name="category_id" :options="$categoryOptions" value="1"/> </div>
                        </div>
                        <br>


                        {{-- General Equipment --}}
                        <table class="table table-bordered order-column" id="table-1">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"></th>
                                <th> Item Name</th>
                                <th style="width:5%"> Qty</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                            @foreach ($allocationGeneral as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->allocation_total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locationItems->filter(fn ($item) => $item->location && !$item->location->notes)->sortBy(fn ($item) => $item->location->name4) as $item)
                                    @php($location = $item->location)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$inTransitLocationIds->has($location->id))
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                            @endforeach
                        </table>

                        {{-- Scaffold Equipment --}}
                        <table class="table table-bordered order-column" id="table-2">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"></th>
                                <th style="width:90px">Photo</th>
                                <th> Item Name</th>
                                <th style="width:5%"> Qty</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                            @foreach ($allocationScaffold as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>
                                        @if ($equip->attachment_url_cached)
                                            <a href="{{ $equip->attachment_url_cached }}" class="html5lightbox " title="{{ $equip->name }}" data-lityXXX>
                                                <img src="{{ $equip->attachment_url_cached }}?{{rand(1, 32000)}}" width="90" class="thumbnail img-responsive img-thumbnail"></a>
                                        @endif
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->allocation_total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locationItems->filter(fn ($item) => $item->location && !$item->location->notes)->sortBy(fn ($item) => $item->location->name4) as $item)
                                    @php($location = $item->location)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$inTransitLocationIds->has($location->id))
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                            @endforeach
                        </table>

                        {{-- Bulk Hardware Equipment --}}
                        <table class="table table-bordered order-column" id="table-19">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"></th>
                                <th style="width:90px">Photo</th>
                                <th> Item Name</th>
                                <th style="width:5%"> Qty</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                            @foreach ($allocationBulkHardware as $equip)
                                <tr id="equip-{{ $equip->id }}">
                                    <td style="text-align: center">
                                        <i class="fa fa-plus-circle" style="color: #32c5d2;" id="closed-{{ $equip->id}}"></i>
                                        <i class="fa fa-minus-circle" style="color: #e7505a; display: none" id="opened-{{ $equip->id}}"></i>
                                    </td>
                                    <td>
                                        @if ($equip->attachment_url_cached)
                                            <a href="{{ $equip->attachment_url_cached }}" class="html5lightbox " title="{{ $equip->name }}" data-lityXXX>
                                                <img src="{{ $equip->attachment_url_cached }}?{{rand(1, 32000)}}" width="90" class="thumbnail img-responsive img-thumbnail"></a>
                                        @endif
                                    </td>
                                    <td>{{ $equip->name }}</td>
                                    <td>{{ $equip->allocation_total }}</td>
                                    <td>&nbsp;</td>
                                </tr>
                                @foreach ($equip->locationItems->filter(fn ($item) => $item->location && !$item->location->notes)->sortBy(fn ($item) => $item->location->name4) as $item)
                                    @php($location = $item->location)
                                        <tr class="location-{{ $equip->id}}" style="display: none; background-color: #fbfcfd" id="locations-{{ $equip->id}}-{{ $item->id }}">
                                            <td></td>
                                            <td></td>
                                            <td>{{ $location->name4 }}</td>
                                            <td>{{ ($item) ? $item->qty : 0 }}</td>
                                            <td>
                                                @if (!$inTransitLocationIds->has($location->id))
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                            @endforeach
                        </table>

                        {{-- Materials Equipment --}}
                        <table class="table table-bordered order-column" id="table-3">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width:5%"></th>
                                <th> Item Name</th>
                                <th style="width:7%"> Length</th>
                                <th style="width:5%"> Qty</th>
                                <th style="width:10%"></th>
                            </tr>
                            </thead>
                            @foreach ($materialCategories as $cat)
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
                                @foreach ($materialLocations[$cat->id] ?? collect() as $locationGroup)
                                    @php($location = $locationGroup['location'])
                                    <tr class="locationc-{{ $cat->id}}" style="display: none; background-color: #ccc" id="location-category-{{ $cat->id }}-{{ $location->id }}">
                                        <td></td>
                                        <td colspan="4"><b>{{ $location->name }}</b></td>
                                    </tr>
                                    @foreach ($locationGroup['items'] as $item)
                                        <tr class="locationc-{{ $cat->id}}" style="display: none; background-color: #fbfcfd" id="location-item-{{ $item->id }}">
                                            <td></td>
                                            <td>{{ $item->equipment->name }}</td>
                                            <td>{{ $item->equipment->length ?: 'N/A' }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                @if (!$inTransitLocationIds->has($location->id))
                                                    <a href="/equipment/{{ $item->id }}/transfer" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Transfer</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
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