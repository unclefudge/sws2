@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasPermission2('view.company.doc.gen') || Auth::user()->hasPermission2('view.company.doc.lic') || Auth::user()->hasPermission2('view.company.doc.whs') || Auth::user()->hasPermission2('view.company.doc.ics'))
            <li><a href="/company/doc">Company Documents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Export</span></li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Company Documents Export</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanyExportController::class, 'docsPDF']) }}" class="horizontal-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="from" class="control-label">Expiry From</label>
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy">
                                            <input type="text" name="from" id="from" class="form-control" value="{{ old('from', \Carbon\Carbon::today()->format('d/m/Y')) }}" readonly style="background:#FFF">
                                            <span class="input-group-addon"> to </span>
                                            <input type="text" name="to" id="to" class="form-control" value="{{ old('to', \Carbon\Carbon::today()->addDays(14)->format('d/m/Y')) }}" readonly style="background:#FFF">
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <x-form.select name="category_id" label="Category" :options="Auth::user()->companyDocTypeSelect('view', 'all')"/>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <x-form.select name="status" id="site_id" label="Status" :options="['' => 'All status', '1' => 'Approved', '2' => 'Pending Approval', '3' => 'Rejected']"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.select name="for_company_id" id="company_id" label="Company" :options="Auth::user()->company->companiesSelect('all')"/>
                                </div>
                            </div>
                            <br>
                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> View PDF</button>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop