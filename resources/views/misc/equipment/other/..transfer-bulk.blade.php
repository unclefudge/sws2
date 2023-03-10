@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        <li><span>Bulk Tansfer</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Bulk Transfer </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($location, ['action' => ['Misc\EquipmentTransferController@transferBulkItems', ($location) ? $location->id : 0], 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('location_id', $errors) !!}">
                                        {!! Form::label('location_id', 'Transfer From', ['class' => 'control-label']) !!}
                                        <select id="location_id" name="location_id" class="form-control select2" width="100%">
                                            <option></option>
                                            <optgroup label="Sites"></optgroup>
                                            @foreach ($sites as $id => $name)
                                                <option value={{ $id }} {{ ($location && $location->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                            <optgroup label="Other Locations"></optgroup>
                                            @foreach ($others as $id => $name)
                                                <option value={{ $id }} {{ ($location && $location->id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        {!! fieldErrorMessage('location_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        {!! Form::label('type', 'Transfer to', ['class' => 'control-label']) !!}
                                        {!! Form::select('type', ['' => 'Select action', 'store' => 'Store', 'site' => 'Site', 'super' => 'Supervisor', 'user' => 'Onsite User', 'other' => 'Other location', 'dispose' => 'Dispose'], null, ['class' => 'form-control bs-select', 'id' => 'type']) !!}
                                        {!! fieldErrorMessage('type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    {{-- Site --}}
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}" style="{{ fieldHasError('site_id', $errors) ? '' : 'display:none' }}" id="site-div">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                    {{-- Supervisor --}}
                                    <div class="form-group {!! fieldHasError('other', $errors) !!}" style="{{ fieldHasError('super', $errors) ? '' : 'display:none' }}" id="super-div">
                                        {!! Form::label('super', 'Supervisor', ['class' => 'control-label']) !!}
                                        <select id="super" name="super" class="form-control bs-select" style="width:100%">
                                            @foreach (Auth::user()->company->reportsTo()->supervisors()->sortBy('name') as $super)
                                                <option value="{{ $super->name }}">{{ $super->name }}</option>
                                            @endforeach
                                        </select>
                                        {!! fieldErrorMessage('super', $errors) !!}
                                    </div>
                                    {{-- Onsite User --}}
                                    <div class="form-group {!! fieldHasError('other', $errors) !!}" style="{{ fieldHasError('super', $errors) ? '' : 'display:none' }}" id="user-div">
                                        {!! Form::label('user', 'Onsite User', ['class' => 'control-label']) !!}
                                        <select id="user" name="user" class="form-control select2" style="width:100%">
                                            @foreach (Auth::user()->company->reportsTo()->onsiteUsers('1')->sortBy('name') as $onsiteuser)
                                                <option value="{{ $onsiteuser->name }}">{{ $onsiteuser->name }} ({{ $onsiteuser->company->name }})</option>
                                            @endforeach
                                        </select>
                                        {!! fieldErrorMessage('user', $errors) !!}
                                    </div>
                                    {{-- Other --}}
                                    <div class="form-group {!! fieldHasError('other', $errors) !!}" style="{{ fieldHasError('other', $errors) ? '' : 'display:none' }}" id="other-div">
                                        {!! Form::label('other', 'Specify Other Location', ['class' => 'control-label']) !!}
                                        {!! Form::select('other', \App\Models\Misc\Equipment\EquipmentLocationOther::where('status', 1)->pluck('name', 'name')->toArray(), null, ['class' => 'form-control bs-select', 'id' => 'other']) !!}
                                        {!! fieldErrorMessage('other', $errors) !!}
                                    </div>
                                    {{-- Disposal --}}
                                    <div class="form-group {!! fieldHasError('reason', $errors) !!}" style="{{ fieldHasError('reason', $errors) ? '' : 'display:none' }}" id="dispose-div">
                                        {!! Form::label('reason', 'Reason for disposal', ['class' => 'control-label']) !!}
                                        {!! Form::text('reason', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('reason', $errors) !!}
                                    </div>
                                </div>
                                {{--}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Transfer To', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            <option value="25">CAPE COD STORE</option>
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>--}}
                            </div>
                            @if (Auth::user()->isCC())
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('assign', $errors) !!}" id="assign-div">
                                            {!! Form::label('assign', 'Assign task to (optional)', ['class' => 'control-label']) !!}
                                            {!! Form::select('assign', Auth::user()->company->usersSelect('prompt', 1), null, ['class' => 'form-control select2', 'id' => 'assign', 'width' => '100%']) !!}
                                            {!! fieldErrorMessage('assign', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3 ">
                                        <div class="form-group {!! fieldHasError('due_at', $errors) !!}">
                                            {!! Form::label('due_at', 'Due Date', ['class' => 'control-label']) !!}
                                            <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-start-date="+0d" data-date-reset>
                                                <input type="text" class="form-control" value="{!! nextWorkDate(\Carbon\Carbon::today(), '+', 3)->format('d/m/Y') !!}" readonly style="background:#FFF" id="due_at" name="due_at">
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <h4 class="font-green-haze">Transfer Items</h4>

                            @if ($location)
                                <div id="equipment_list">
                                    {{-- General Equipment --}}
                                    <div class="panel-group accordion scrollable" id="accordion3" style="margin-bottom: 5px">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_1" aria-expanded="true"> General </a>
                                                </h4>
                                            </div>
                                            <div id="collapse_3_1" class="panel-collapse collapse" aria-expanded="true" style="">
                                                <div class="panel-body">
                                                    <table class="table table-striped table-bordered table-hover order-column" id="table-1">
                                                        <thead>
                                                        <tr class="mytable-header">
                                                            <th width="5%"> Qty</th>
                                                            <th> Item Name</th>
                                                            <th width="10%"> Transfer</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @if (count($items))
                                                            @foreach($items->sortBy('item_name') as $loc)
                                                                @if ($loc->equipment->category_id == 1)
                                                                    <tr class="itemrow-" id="itemrow-{{ $loc->id }}">
                                                                        <td>{{ $loc->qty }}</td>
                                                                        <td>{{ $loc->item_name }}</td>
                                                                        <td>
                                                                            <div class="itemactual-" id="itemactual-{{ $loc->id }}">
                                                                                <select id="{{ $loc->id }}-qty" name="{{ $loc->id }}-qty" class="form-control bs-select" width="100%">
                                                                                    @for ($i = 0; $i <= $loc->qty; $i++)
                                                                                        <option value="{{ $i }}" {{ (old("$loc->id-qty") == $i) ? 'selected' : '' }}>{{ $i }}</option>
                                                                                    @endfor
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="2">No items found at current location.</td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Materials Equipment --}}
                                    <div class="panel-group accordion scrollable" id="accordion3" style="margin-bottom: 5px">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_3" aria-expanded="true"> Material </a>
                                                </h4>
                                            </div>
                                            <div id="collapse_3_3" class="panel-collapse collapse" aria-expanded="true" style="">
                                                <div class="panel-body">
                                                    <table class="table table-striped table-bordered table-hover order-column" id="table-3">
                                                        <thead>
                                                        <tr class="mytable-header">
                                                            <th width="5%"> Qty</th>
                                                            <th> Sub-category</th>
                                                            <th> Item Name</th>
                                                            <th width="10%"> Transfer</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @if (count($items))
                                                            <?php
                                                            $sorted = $items->sortBy(function ($item) {
                                                                return $item->item_category_name . '-' . $item->item_name;
                                                            });
                                                            ?>
                                                            @foreach($sorted as $loc)
                                                                @if ($loc->equipment->parent_category == 3)
                                                                    <tr class="itemrow-" id="itemrow-{{ $loc->id }}">
                                                                        <td>{{ $loc->qty }}</td>
                                                                        <td>{{ $loc->equipment->category->name }}</td>
                                                                        <td>{{ $loc->item_name }}</td>
                                                                        <td>
                                                                            <div class="itemactual-" id="itemactual-{{ $loc->id }}">
                                                                                <select id="{{ $loc->id }}-qty" name="{{ $loc->id }}-qty" class="form-control bs-select" width="100%">
                                                                                    @for ($i = 0; $i <= $loc->qty; $i++)
                                                                                        <option value="{{ $i }}" {{ (old("$loc->id-qty") == $i) ? 'selected' : '' }}>{{ $i }}</option>
                                                                                    @endfor
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="2">No items found at current location.</td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Scaffold Equipment --}}
                                    <div class="panel-group accordion scrollable" id="accordion3">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_2" aria-expanded="true"> Scaffold </a>
                                                </h4>
                                            </div>
                                            <div id="collapse_3_2" class="panel-collapse collapse" aria-expanded="true" style="">
                                                <div class="panel-body">
                                                    <table class="table table-striped table-bordered table-hover order-column" id="table-2">
                                                        <thead>
                                                        <tr class="mytable-header">
                                                            <th width="5%"> Qty</th>
                                                            <th> Item Name</th>
                                                            <th width="10%"> Transfer</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @if (count($items))
                                                            @foreach($items->sortBy('item_name') as $loc)
                                                                @if ($loc->equipment->category_id == 2)
                                                                    <tr class="itemrow-" id="itemrow-{{ $loc->id }}">
                                                                        <td>{{ $loc->qty }}</td>
                                                                        <td>{{ $loc->item_name }}</td>
                                                                        <td>
                                                                            <div class="itemactual-" id="itemactual-{{ $loc->id }}">
                                                                                <select id="{{ $loc->id }}-qty" name="{{ $loc->id }}-qty" class="form-control bs-select" width="100%">
                                                                                    @for ($i = 0; $i <= $loc->qty; $i++)
                                                                                        <option value="{{ $i }}" {{ (old("$loc->id-qty") == $i) ? 'selected' : '' }}>{{ $i }}</option>
                                                                                    @endfor
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="2">No items found at current location.</td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                <button type="submit" name="save" class="btn green">Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="loadSpinnerOverlay" id="spinner" style="display: none">
                                <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site"});
        $("#user").select2({placeholder: "Select User", width: '100%'});
        $("#location_id").select2({placeholder: "Select Site"});
        $("#assign").select2({placeholder: "Select User", width: '100%'});


        // Location
        $("#location_id").change(function () {
            $("#table_list").hide();
            $("#btn-add-item").hide();
            $("#spinner").show();
            window.location.href = "/equipment/" + $("#location_id").val() + "/transfer-bulk";
        });

        $("#type").change(function () {
            $('#site-div').hide();
            $('#super-div').hide();
            $('#user-div').hide();
            $('#other-div').hide();
            $('#dispose-div').hide();
            $('#assign-div').hide();

            if ($("#type").val() == 'store') {
                $('#site_id').val(25);
                $('#site_id').trigger('change');
                $('#assign-div').show();
            }

            if ($("#type").val() == 'site') {
                $('#site-div').show();
                $('#assign-div').show();
            }

            if ($("#type").val() == 'super') {
                $('#super-div').show();
                $('#assign-div').show();
            }

            if ($("#type").val() == 'user') {
                $('#user-div').show();
                $('#assign-div').show();
            }

            if ($("#type").val() == 'other') {
                $('#other-div').show();
                $('#assign-div').show();
            }

            if ($("#type").val() == 'dispose')
                $('#dispose-div').show();
        });


        /*
         $("#type").change(function () {
         $('#site-div').hide();
         $('#other-div').hide();
         $('#dispose-div').hide();
         $('#assign-div').hide();

         if ($("#type").val() == 'store') {
         $('#site_id').val(25);
         $('#site_id').trigger('change');
         $('#assign-div').show();
         }

         if ($("#type").val() == 'site') {
         $('#site-div').show();
         $('#assign-div').show();
         }

         if ($("#type").val() == 'other') {
         $('#other-div').show();
         $('#assign-div').show();
         }

         if ($("#type").val() == 'dispose')
         $('#dispose-div').show();
         });*/
    });
</script>
@stop
