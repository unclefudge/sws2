@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-download"></i> Job Start Export</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.export'))
            <li><a href="/site/export">Export</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Job Start</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Job Start Export</span>
                        </div>
                        <div class="actions">
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen" style="margin: 3px"></a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SitePlannerExport', ['action' => 'Site\Planner\SitePlannerExportController@jobstartPDF', 'class' => 'horizontal-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('email_list', $errors) !!}">
                                    {!! Form::label('email_list', 'Email List', ['class' => 'control-label']) !!}
                                    {{--}}{!! Form::text('email_list', 'scott@capecod.com.au; nadia@capecod.com.au; nicole@capecod.com.au; kirstie@capecod.com.au; abarden@capecod.com.au; robert@capecod.com.au; kylie@capecod.com.au; alethea@capecod.com.au; julien@capecod.com.au; george@capecod.com.au', ['class' => 'form-control']) !!}
                                    --}}{!! Form::text('email_list', implode("; ", Auth::user()->company->notificationsUsersEmailType('site.jobstartexport')), ['class' => 'form-control']) !!}

                                    {!! fieldErrorMessage('email_list', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn dark" name="view_pdf" value="true"> View PDF</button>
                                <button type="submit" class="btn green" name="email_pdf" value="true"> Email PDF</button>
                            </div>
                        </div>
                        <br>
                        <div class="form-actions right">
                            <a href="/site/export" class="btn default"> Back</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $('.date-picker').datepicker({
        autoclose: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop