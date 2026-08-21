@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Recent</span></li>
    </ul>
@stop

<style>
    .report-table tbody tr > td { background:#fff !important; }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Recent Reports</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <livewire:manage.report.recent-reports/>

                        <div class="form-actions right" style="margin-top:15px">
                            <a href="/manage/report" class="btn default">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
