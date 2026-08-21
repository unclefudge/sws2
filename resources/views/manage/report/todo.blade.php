@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>ToDo Tasks</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size:14px;
        font-weight:600;
        color:#333 !important;
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject font-green-haze bold uppercase">ToDo Tasks</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <livewire:manage.report.todo-tasks/>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop
