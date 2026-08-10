@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/inventory">Inventory</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>View</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Equipment Item </span>
                            <span class="caption-helper"> - ID: {{ $equip->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <x-form.hidden name="item_id" :value="$equip->id"/>
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $equip->name }}</h2>
                                    {!! nl2br($equip->notes) !!}
                                </div>
                                <div class="col-md-5">
                                    @if (!$equip->status)
                                        <h3 style="margin-top: 0px" class="font-red">DELETED {{ $equip->updated_at->format('d/m/Y') }}</h3>
                                    @endif
                                </div>
                            </div>

                            {{-- Allocation --}}
                            <h3 class="form-section">Allocation:
                                <small>Total: {{ $equip->total }}</small>
                            </h3>
                            <table class="table table-striped table-bordered table-hover order-column" id="table_location">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> Qty</th>
                                    <th style="width:7%"> Site</th>
                                    <th> Suburb</th>
                                    <th> Name</th>
                                    <th> Other</th>
                                </tr>
                                </thead>
                            </table>

                            {{-- Missing --}}
                            {{--}}
                            @if (count($equip->lost))
                                <h3 class="form-section">Missing:
                                    <small>Total: {!! count($equip->lost) !!}</small>
                                </h3>
                                <table class="table table-striped table-bordered table-hover order-column" id="table_lost">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th style="width:5%"> Qty</th>
                                        <th style="width:7%"> Site</th>
                                        <th> Suburb</th>
                                        <th> Name</th>
                                        <th> Other</th>
                                    </tr>
                                    </thead>
                                </table>
                            @endif
                            --}}

                            {{-- History --}}
                            <h3 class="form-section">History</h3>
                            <table class="table table-striped table-bordered table-hover order-column" id="table_history">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> Date</th>
                                    <th> Action</th>
                                    <th> By Whom</th>
                                </tr>
                                </thead>
                            </table>

                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $equip->displayUpdatedBy() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>
    $(document).ready(function () {
        $("#action").change(function () {
            $('#purchase-div').hide();
            $('#dispose-div').hide();
            $('#dispose-reason').hide();
            $('#delete-div').hide();

            if ($("#action").val() == 'P') {
                $('#purchase-div').show();
            }
            if ($("#action").val() == 'D') {
                $('#dispose-div').show();
                $('#dispose-reason').show();
            }
            if ($("#action").val() == 'X') {
                $('#delete-div').show();
            }
        });

        var table_location = $('#table_location').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('equipment/dt/allocation') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.equipment_id = "{{ $equip->id }}";
                }
            },
            columns: [
                {data: 'qty', name: 'qty'},
                {data: 'code', name: 'sites.code'},
                {data: 'suburb', name: 'sites.suburb'},
                {data: 'sitename', name: 'sites.name'},
                {data: 'other', name: 'equipment_location.other'},
            ],
            order: [
                [0, "desc"]
            ]
        });

        var table_history = $('#table_history').DataTable({
            pageLength: 10,
            processing: true,
            serverSide: true,
            ajax: {
                'url': '{!! url('equipment/dt/log') !!}',
                'type': 'GET',
                'data': function (d) {
                    d.equipment_id = "{{ $equip->id }}";
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'notes', name: 'notes'},
                {data: 'created_by', name: 'created_by'},
            ],
            order: [
                [0, "desc"]
            ]
        });

        /*
         var table_lost = $('#table_lost').DataTable({
         pageLength: 10,
         processing: true,
         serverSide: true,
         ajax: {
         'url': '{!! url('equipment/dt/missing') !!}',
         'type': 'GET',
         'data': function (d) {
         d.equipment_id = "{{ $equip->id }}";
         }
         },
         columns: [
         {data: 'qty', name: 'qty'},
         {data: 'code', name: 'sites.code'},
         {data: 'suburb', name: 'sites.suburb'},
         {data: 'sitename', name: 'sites.name'},
         {data: 'other', name: 'equipment_location.other'},
         ],
         order: [
         [0, "desc"]
         ]
         }); */

    });
</script>
@stop