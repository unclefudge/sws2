@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Company Contact Info</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Company Contact Info</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Trades</th>
                                <th> Primary</th>
                                <th> Phone</th>
                                <th> Email</th>
                                <th> Users</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    <td><div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div></td>
                                    <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                    {{--}}<td><b>{{ (preg_match('/[0-9]/', $company->category)) ? $companyTypes::name($company->category) : $company->tradesSkilledInSBC() }}</b> {{ $company->tradesSkilledInSBC() }}</td>--}}
                                    <td>{{ $company->tradesSkilledInSBC() }}</td>
                                    <td>{{ ($company->primary_user) ? $company->primary_contact()->fullname : '-' }}</td>
                                    <td>{{ ($company->primary_user && $company->primary_contact()->phone) ? $company->primary_contact()->phone : $company->phone }}</td>
                                    <td>{{ ($company->primary_user && $company->primary_contact()->email) ? $company->primary_contact()->email : $company->email }}</td>
                                    <td>{{ $company->staffSBC() }}</td>
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