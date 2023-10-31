@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Company Planned Tasks</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Company Planned Tasks</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Last Task date</th>
                                <th> Next Task Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                @if (!preg_match('/cc-/', strtolower($company->name)))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                        <td>{{ ($company->lastDateOnPlanner()) ? $company->lastDateOnPlanner()->diffForHumans() : '-' }}</td>
                                        <td>{{ ($company->nextDateOnPlanner()) ? $company->nextDateOnPlanner()->diffForHumans() : '-' }}</td>
                                    </tr>
                                @endif
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop