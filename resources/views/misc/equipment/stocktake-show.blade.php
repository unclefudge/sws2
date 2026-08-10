@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/stocktake/0">Stocktake</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Stocktake Event </span>
                            <span class="caption-helper"> - ID: {{ $stock->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $stock->location->name }}</h2>
                                </div>
                                <div class="col-md-5">
                                    <b>Date:</b> {!! $stock->created_at->format('d/m/Y') !!}<br>
                                    <b>Done By:</b> {!! $stock->user->name !!}<br>
                                </div>
                            </div>

                            {{-- Allocation --}}
                            <h3 class="form-section">Items in stock:
                                <small>Total: {!! count($stock->items) !!}</small>
                            </h3>
                            <table class="table table-striped table-bordered table-hover order-column" id="table_location">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th> Name</th>
                                    <th style="width:10%"> Qty Expected</th>
                                    <th style="width:10%"> Qty Actual</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($stock->items as $item)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/equipment/{{ $item->equipment_id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->qty_expect }}</td>
                                        <td>{{ $item->qty_actual }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
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
                {!! $stock->displayUpdatedBy() !!}
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')

@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>
    $(document).ready(function () {

    });
</script>
@stop