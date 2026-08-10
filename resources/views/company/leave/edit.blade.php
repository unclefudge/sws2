@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/leave">Company leave</a><i class="fa fa-circle"></i></li>
        <li><span>Edit leave</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Leave </span>
                            <span class="caption-helper"> - {{ $leave->company->name_alias }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanyLeaveController::class, 'update'], $leave->id) }}" class="horizontal-form">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="company_name" label="Company" :value="$leave->company->name_alias" readonly/>
                                        <input type="hidden" name="company_id" value="{{ $leave->company_id }}">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="from" class="control-label">Leave From</label>
                                            <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy">
                                                @if ($leave->from->lt(Carbon\Carbon::now()))
                                                    <input type="text" name="from" id="from" class="form-control" value="{{ $leave->from->format('d/m/Y') }}" readonly disabled style="background:#FFF">
                                                    <span class="input-group-addon"> to </span>
                                                    <input type="text" name="to" id="to" class="form-control" value="{{ $leave->to->format('d/m/Y') }}" readonly disabled style="background:#FFF">
                                                @else
                                                    <input type="text" name="from" id="from" class="form-control" value="{{ old('from', $leave->from->format('d/m/Y')) }}" readonly style="background:#FFF">
                                                    <span class="input-group-addon"> to </span>
                                                    <input type="text" name="to" id="to" class="form-control" value="{{ old('to', $leave->to->format('d/m/Y')) }}" readonly style="background:#FFF">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="form-section"></h3>
                                <!-- Notes -->
                                <div class="row">
                                    <div class="col-md-12">
                                        @if ($leave->from->lt(Carbon\Carbon::now()))
                                            <x-form.textarea name="notes" label="Notes" rows="2" :value="$leave->notes" help="For internal use only" readonly/>
                                        @else
                                            <x-form.textarea name="notes" label="Notes" rows="2" :value="$leave->notes" help="For internal use only"/>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="/company/leave" class="btn default"> Back</a>
                                    @if (($leave->from->gt(Carbon\Carbon::now())))
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
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
@stop

