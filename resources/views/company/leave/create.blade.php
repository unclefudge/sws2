@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        <li><a href="/company/leave">Company leave</a><i class="fa fa-circle"></i></li>
        <li><span>Create new leave</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New Company Leave</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanyLeaveController::class, 'store']) }}" class="horizontal-form">
                            @csrf

                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.select name="company_id" label="Company" :options="Auth::user()->company->companiesSelect('prompt')"/>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="from" class="control-label">Leave From</label>
                                            <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy">
                                                <input type="text" name="from" id="from" class="form-control" value="{{ old('from') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon"> to </span>
                                                <input type="text" name="to" id="to" class="form-control" value="{{ old('to') }}" readonly style="background:#FFF">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="form-section"></h3>
                                <!-- Notes -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes" rows="2" help="For internal use only"/>
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="/company/leave" class="btn default"> Back</a>
                                    <button type="submit" class="btn green">Save</button>
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

