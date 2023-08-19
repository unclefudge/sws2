@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Open Electrical / Plumbing Inspection Reports</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Open Electrical / Plumbing Inspection Reports</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Electrical Reports</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <td style="width:60px">Created</td>
                                <td style="width:200px">Site</td>
                                <td style="width:120px">Assigned</td>
                                <td>Assigned to</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($electrical as $report)
                                <tr>
                                    <td>{{ $report->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $report->site->name }}</td>
                                    <td>{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                                    <td>{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <h3>Plumbing Reports</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <td style="width:60px">Created</td>
                                <td style="width:200px">Site</td>
                                <td style="width:120px">Assigned</td>
                                <td>Assigned to</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($plumbing as $report)
                                <tr>
                                    <td>{{ $report->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $report->site->name }}</td>
                                    <td>{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                                    <td>{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop