@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            <li><a href="/site/upcoming/compliance">Upcoming Jobs</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>PDF</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Upcoming Jobs</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteUpcomingSettingsPDF', ['action' => 'Site\SiteUpcomingComplianceController@createPDF', 'class' => 'horizontal-form']) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('email_list', $errors) !!}">
                                    {!! Form::label('email_list', 'Email List', ['class' => 'control-label']) !!}
                                    {!! Form::select('email_list', ['' => 'Select user(s)'] + Auth::user()->company->staffSelect('select', '1'), $email_list, ['class' => 'form-control select2', 'name' => 'email_list[]', 'id'  => 'email_list', 'title' => 'Select one or more users', 'multiple']) !!}
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
                            <a href="/site/upcoming/compliance" class="btn default"> Back</a>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#email_list").select2({placeholder: "Select one or more", width: '100%'});
    });
    $('.date-picker').datepicker({
        autoclose: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop