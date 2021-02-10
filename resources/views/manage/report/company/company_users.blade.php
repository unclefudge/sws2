@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Company Staff</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Company Staff</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Users</th>
                                <th> Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($all_companies as $company)
                                @if ($company->staff->count() == 0)
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{!! $company->name_both !!}</td>
                                        <td><span class="font-red">No users</span></td>
                                        <td>{!! $company->updated_at->format('d/m/Y')!!}</td>
                                    </tr>
                                @endif
                            @endforeach
                            @foreach($user_companies as $company)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>
                                        {!! $company->name !!}
                                        @if (!$company->sec)
                                            <span class="label label-warning"> No Security </span> &nbsp;
                                        @endif
                                        @if (!$company->pu && $company->id != 3 && $company->id != 3)
                                            <span class="label label-info"> No Primary Contact </span>
                                        @endif
                                    </td>
                                    <td>{{ $company->users }}</td>
                                    <td>{!! $company->updated_at !!}</td>
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