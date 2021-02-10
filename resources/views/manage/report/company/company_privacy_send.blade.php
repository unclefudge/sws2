@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
            <li><a href="/manage/report/company_privacy">Company Privacy Policy</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Request Sent</span></li>

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
                            <span class="caption-subject bold uppercase font-green-haze"> Company Privacy Policy - Request Sent</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="alert alert-info">
                            <b>A ToDo Task has been sent to the following companies / users requesting them to read + sign Cape Cod's Privacy Policy</b><br>
                        </div>
                        @if ($sent_to_company)
                            <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                                <thead>
                                <tr class="mytable-header">
                                    <th> Company</th>
                                    <th> Assigned To</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sent_to_company as $cid => $company)
                                    <tr>
                                        <td>{!! $company !!}</td>
                                        <td>{!! $sent_to_user[$cid] !!}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <b class="font-red">No requests were sent!</b><br><br>Every company has either already signed or has an outstanding task requesting them to sign.
                        @endif
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