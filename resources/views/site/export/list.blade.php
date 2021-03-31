@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-download"></i> Export Site Data</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Export Site Data</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Export Data List</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle btn-icon-only btn-default fullscreen" href="javascript:;"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th> Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><a href="/site/export/plan"> Planner Export </a></td>
                            </tr>
                            <tr>
                                <td><a href="/site/export/start"> Job Start Export </a></td>
                            </tr>
                            <tr>
                                <td><a href="/site/export/completion"> Practical Completion Export </a></td>
                            </tr>
                            <tr>
                                <td><a href="/manage/report/site_inspections"> Site Inspection Reports (Electrical/Plumbing) </a></td>
                            </tr>
                            <tr>
                                <td><a href="/site/export/qa"> Quality Assurance Export </a></td>
                            </tr>
                            @if (Auth::user()->hasPermission2('view.company.doc.gen') || Auth::user()->hasPermission2('view.company.doc.lic') || Auth::user()->hasPermission2('view.company.doc.ics'))
                                <tr>
                                    <td><a href="/company/doc/export"> Company Documents Export </a></td>
                                </tr>
                            @endif
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