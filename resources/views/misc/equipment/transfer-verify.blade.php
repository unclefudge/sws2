@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/inventory">Inventory</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Tansfer</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Transfer Items </span>
                            <span class="caption-helper"> - ID: {{ $location->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentTransferController::class, 'confirmTransfer'], $location->id) }}" class="horizontal-form">
                            @csrf

                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <h2 style="margin-top: 0px">{{ $location->name }}</h2>
                                    </div>
                                    <div class="col-md-5">
                                        <b>From:</b> {{ $from }}<br>
                                        <b>To:</b> {{ $to }}<br>
                                    </div>
                                </div>
                                <hr>
                                <h4 class="font-green-haze">Transfer Details</h4>

                                <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th> Equipment</th>
                                        <th style="width:10%"> Qty</th>
                                    </tr>
                                    </thead>
                                    @foreach ($location->items as $item)
                                        <tr>
                                            <td>{{ $item->item_name }}</td>
                                            <td>
                                                <select id="{{ $item->id }}-qty" name="{{ $item->id }}-qty" class="form-control bs-select" width="100%">
                                                    @for ($i = 0; $i <= $item->qty; $i++)
                                                        <option value="{{ $i }}" {!! ($item->qty == $i) ? 'selected' : '' !!}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>

                                <div class="form-actions right">
                                    <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                    <button type="submit" name="save" class="btn green">Save</button>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});
            $("#assign").select2({placeholder: "Select User"});

            $("#type").change(function () {
                $('#site-div').hide();
                $('#other-div').hide();
                $('#dispose-div').hide();

                if ($("#type").val() == 'store') {
                    $('#site_id').val(25);
                    $('#site_id').trigger('change');
                }

                if ($("#type").val() == 'site')
                    $('#site-div').show();

                if ($("#type").val() == 'other')
                    $('#other-div').show();

                if ($("#type").val() == 'dispose')
                    $('#dispose-div').show();
            });
        });
    </script>
@stop