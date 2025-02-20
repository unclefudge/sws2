@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
            <li><a href="/manage/report/company_swms">Company SWMS</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Settings</span></li>
    </ul>
@stop

@section('content')

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Company SWMS Settings</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/asbestos/register/create" data-original-title="Add">Settings</a>
                            @endif
                        </div>
                    </div>
                    {!! Form::model('CompanySWMSSettings', ['method' => 'POST', 'action' => ['Misc\ReportUserCompanyController@companySWMSSettingsUpdate'], 'class' => 'horizontal-form', 'files' => true]) !!}

                    @include('form-error')
                    <div class="portlet-body">
                        <h3>Companies not required to have SWMS</h3>
                        <hr class="field-hr">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('excluded_companies', $errors) !!}">
                                    {!! Form::label('excluded_companies', 'Companies', ['class' => 'control-label']) !!}
                                    {!! Form::select('excluded_companies',$company_list, $excluded_companies, ['class' => 'form-control select2', 'name' => 'excluded_companies[]', 'id'  => 'excluded_companies', 'title' => 'Select one or more companies', 'multiple']) !!}
                                    {!! fieldErrorMessage('excluded_companies', $errors) !!}
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn green pull-right" style="margin-left: 10px"> Save</button>
                        <a href="/manage/report/company_swms" class="btn default pull-right">Back</a>
                        <br><br>
                    </div>
                    {!! Form::close() !!}
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $("#excluded_companies").select2({placeholder: "Select one or more", width: '100%'});
        });
    </script>
@stop