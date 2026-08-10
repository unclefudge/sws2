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
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentTransferController::class, 'transferItem'], $item->id) }}" class="horizontal-form">
                            @csrf
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
                                        <x-form.select name="qty" label="Quantity">
                                            @for ($i = 1; $i <= $item->qty; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </x-form.select>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="type" label="Transfer to" :options="['' => 'Select action', 'store' => 'Store', 'site' => 'Site', 'super' => 'Supervisor', 'user' => 'Onsite User', 'other' => 'Other location', 'dispose' => 'Dispose']" :value="$item->type"/>
                                    </div>
                                    <div class="col-md-8">
                                        {{-- Site --}}
                                        <div style="{{ $errors->has('site_id') ? '' : 'display:none' }}" id="site-div">
                                            <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%">{!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}</x-form.select>
                                        </div>
                                        {{-- Supervisor --}}
                                        <div style="{{ $errors->has('super') ? '' : 'display:none' }}" id="super-div">
                                            <x-form.select name="super" label="Supervisor" style="width:100%">
                                                @foreach (Auth::user()->company->reportsTo()->supervisors()->sortBy('name') as $super)
                                                    <option value="{{ $super->name }}">{{ $super->name }}</option>
                                                @endforeach
                                            </x-form.select>
                                        </div>
                                        {{-- Onsite User --}}
                                        <div style="{{ $errors->has('user') ? '' : 'display:none' }}" id="user-div">
                                            <x-form.select name="user" label="Onsite User" plugin="select2" style="width:100%">
                                                @foreach (Auth::user()->company->reportsTo()->onsiteUsers('1')->sortBy('name') as $onsiteuser)
                                                    <option value="{{ $onsiteuser->name }}">{{ $onsiteuser->name }} ({{ $onsiteuser->company->name }})</option>
                                                @endforeach
                                            </x-form.select>
                                        </div>
                                        {{-- Other --}}
                                        <div style="{{ $errors->has('other') ? '' : 'display:none' }}" id="other-div">
                                            <x-form.select name="other" label="Specify Other Location" :options="\App\Models\Misc\Equipment\EquipmentLocationOther::where('status', 1)->pluck('name', 'name')->toArray()" :value="$item->other"/>
                                        </div>
                                        {{-- Disposal --}}
                                        <div style="{{ $errors->has('reason') ? '' : 'display:none' }}" id="dispose-div">
                                            <x-form.input name="reason" label="Reason for disposal" :value="$item->reason"/>
                                        </div>
                                    </div>
                                </div>
                                @if (Auth::user()->isCC())
                                    <div class="row" style="{{ $errors->has('site_id') ? '' : 'display:none' }}" id="assign-div">
                                        <div class="col-md-4">
                                            <x-form.select name="assign" label="Assign task to (optional)" :options="Auth::user()->company->usersSelect('prompt', 1)" :value="$item->assign" plugin="select2" style="width:100%"/>
                                        </div>
                                        <div class="col-md-3 ">
                                            <x-form.datepicker name="due_at" label="Due Date" :value="nextWorkDate(\Carbon\Carbon::today(), '+', 3)->format('d/m/Y')" start-date="+0d" clear-button wrapper-class="input-medium" readonly/>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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