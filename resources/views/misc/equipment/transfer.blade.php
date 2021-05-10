@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Transfer Item </span>
                            <span class="caption-helper"> - ID: {{ $item->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($item, ['action' => ['Misc\EquipmentTransferController@transferItem', $item->id], 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $item->equipment->name }}</h2>
                                    {!! nl2br($item->equipment->notes) !!}
                                </div>
                                <div class="col-md-5">
                                    <b>Location:</b> {!! ($item->location->site_id) ? $item->location->site->suburb.' ('.$item->location->site->name.')' : $item->location->other !!}<br>
                                    <b>Quantity:</b> {{ $item->qty }}<br>
                                </div>
                            </div>
                            <hr>
                            <h4 class="font-green-haze">Transfer Details</h4>

                            <div class="row">
                                <div class="col-md-2" id="qty-div">
                                    <div class="form-group">
                                        {!! Form::label('qty', 'Quantity', ['class' => 'control-label']) !!}
                                        <select id="transfer_qty" name="qty" class="form-control bs-select" width="100%">
                                            @for ($i = 1; $i <= $item->qty; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        {!! Form::label('type', 'Transfer to', ['class' => 'control-label']) !!}
                                        {!! Form::select('type', ['' => 'Select action', 'store' => 'Store', 'site' => 'Site', 'super' => 'Supervisor', 'user' => 'Onsite User', 'other' => 'Other location', 'dispose' => 'Dispose'], null, ['class' => 'form-control bs-select', 'id' => 'type']) !!}
                                        {!! fieldErrorMessage('type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-8">
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
                            </div>
                            @if (Auth::user()->isCC())
                                <div class="row" style="{{ fieldHasError('site_id', $errors) ? '' : 'display:none' }}" id="assign-div">
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('assign', $errors) !!}">
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

                            @if ($item->equipment->parent_category == 3 && $item->location && $item->location->id == 1)
                                <div id="materials_note" class="note note-warning">
                                    <p><b>Please Note:</b> Any Materials transferred from the Store to any Site are considered to be 'consumed' and therefore the quantity will be removed from the Store + logged but won't appear on the 'transfer' site as inventory.</p>
                                </div>
                            @endif
                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                <button type="submit" name="save" class="btn green">Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
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
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site"});
        $("#user").select2({placeholder: "Select User", width: '100%'});
        $("#assign").select2({placeholder: "Select User", width: '100%'});

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
    });
</script>
@stop