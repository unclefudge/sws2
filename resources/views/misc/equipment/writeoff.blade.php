@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/intentory">Inventory</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Write-off</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Equipment Write Off </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentController::class, 'writeoffItems']) }}" class="horizontal-form">
                            @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">Missing Equipment</h2>
                                </div>

                            </div>
                            <table class="table table-striped table-bordered table-hover order-column" id="table_location">
                                <thead>
                                <tr class="mytable-header">
                                    <th style="width:5%"> #</th>
                                    <th style="width:10%"> Date</th>
                                    <th> Name</th>
                                    <th style="width:10%"> Quantity</th>
                                    <th style="width:10%"> Write Off</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if (count($missing))
                                    @foreach($missing as $item)
                                        <tr>
                                            <td>
                                                <div class="text-center"><a href="/equipment/{{ $item->equipment_id }}"><i class="fa fa-search"></i></a></div>
                                            </td>
                                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>
                                                <div class="text-center">
                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        <input type="checkbox" value="{{ $item->id }}" name="writeoff[]"/>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5"> There are currently no missing items</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>

                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                @if (count($missing))
                                    <button type="submit" class="btn green">Save</button>
                                @endif
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
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