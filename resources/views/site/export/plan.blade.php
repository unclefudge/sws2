@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.export'))
            <li><a href="/site/export">Export</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Planner</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Planner Export</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\Planner\SitePlannerExportController::class, 'sitePDF']) }}" class="horizontal-form">
                            @csrf
                            <div class="row" style="padding-bottom: 5px">
                                <div class="col-md-3">
                                    <x-form.datepicker name="date" label="Date From" :value="$date" format="dd-mm-yyyy" readonly/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.input name="weeks" label="Weeks to Export" value="2"/>
                                </div>
                            </div>
                            <hr style="margin: 5px 0px 15px 0px">
                            <div class="row">
                                <div class="col-md-3"><h4>Export Planner by Site (Client)</h4></div>
                                <div class="col-md-6">
                                    <x-form.select name="site_id_client[]" :options="Auth::user()->authSitesSelect('view.site', [1, 2], 'ALL')" id="site_id_client" plugin="select2" multiple style="width:100%"/>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn green" name="export_site_client" value="true"> View PDF</button>
                                </div>
                            </div>
                            <br>
                            @if (Auth::user()->hasAnyRole2('mgt-general-manager|con-construction-manager|web-admin') || (Auth::user()->isAreaSupervisor()))
                                <div class="row">
                                    <div class="col-md-3"><h4>Export Planner by Site</h4></div>
                                    <div class="col-md-6">
                                        <x-form.select name="site_id[]" :options="Auth::user()->authSitesSelect('view.site.export', [1, 2], 'ALL')" id="site_id" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn green" name="export_site" value="true"> View PDF</button>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3"><h4>Export Planner by Company</h4></div>
                                    <div class="col-md-6">
                                        <x-form.select name="company_id[]" :options="Auth::user()->company->companiesSelect('all')" id="company_id" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn green" name="export_company" value="true"> View PDF</button>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3"><h4>Export Planner by Supervisor</h4></div>
                                    <div class="col-md-6">
                                        <x-form.select name="supervisor_id[]" :options="Auth::user()->company->supervisorsSelect()" id="supervisor_id" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn green" name="export_supervisor" value="true"> View PDF</button>
                                    </div>
                                </div>
                                <br>
                            @endif
                            <div class="form-actions right">
                                <a href="{{ URL::previous() }}" class="btn default"> Back</a>
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
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "All Sites", width: '100%'});
            $("#site_id_client").select2({placeholder: "All Sites", width: '100%'});
            $("#company_id").select2({placeholder: "All Companies", width: '100%'});
            $("#supervisor_id").select2({placeholder: "All Supervisors", width: '100%'});

        });

        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop
