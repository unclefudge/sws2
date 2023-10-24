@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Maintenance Requests Without Client Appointment</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Maintenance Requests Without Client Appointment</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Reported</th>
                                <th> Site</th>
                                <th> Name</th>
                                <th width="15%"> Task Owner</th>
                                <th width="10%"> Updated</th>
                                <th> Last Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($mains as $main)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/site/maintenance/{{ $main->id }}">M{{ $main->code }}</a></div>
                                    </td>
                                    <td>{{ $main->reported->format('d/m/Y') }}</td>
                                    <td>{{ $main->site->code }}</td>
                                    <td>{{ $main->site->name }}</td>
                                    <td>{{ ($main->taskOwner) ? $main->taskOwner->name : '-' }}</td>
                                    <td>{{ $main->updated_at->format('d/m/Y') }}</td>
                                    <td>{{ $main->lastActionNote() }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <br>
                        <h4 class="bold uppercase font-green-haze">Maintenance Requests Without Client Contacted</h4>
                        <hr>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Reported</th>
                                <th> Site</th>
                                <th> Name</th>
                                <th width="15%"> Task Owner</th>
                                <th width="10%"> Updated</th>
                                <th> Last Note</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($mains2 as $main)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/site/maintenance/{{ $main->id }}">M{{ $main->code }}</a></div>
                                    </td>
                                    <td>{{ $main->reported->format('d/m/Y') }}</td>
                                    <td>{{ $main->site->code }}</td>
                                    <td>{{ $main->site->name }}</td>
                                    <td>{{ ($main->taskOwner) ? $main->taskOwner->name : '-' }}</td>
                                    <td>{{ $main->updated_at->format('d/m/Y') }}</td>
                                    <td>{{ $main->lastActionNote() }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <a href="/manage/report" class="btn default pull-right">Back</a><br><br>
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