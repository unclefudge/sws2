@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Missing Company Information</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Missing Company Information</span>
                        </div>
                        <div class="actions">
                            <a href="/manage/report/missing_company_info_csv" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> Download CSV</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h4>Missing Company Info</h4>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width: 5%"> #</th>
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th style="width: 10%"> Expiry / Last Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($missing_info as $row)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                                    <td>{!! $row['data'] !!}</td>
                                    <td>{!! $row['date']!!}</td>
                                </tr>
                            </tbody>
                            @endforeach
                        </table>

                        <h4>Contractors Licence, Workers Compensation, Sickness & Accident, Public Liability, Privacy Policy</h4>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width: 5%"> #</th>
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th style="width: 10%"> Expiry / Last Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($expired_docs1 as $row)
                                <tr>
                                    <td>
                                        @if ($row['date'] != 'never')
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                                        @else
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                                        @endif
                                    </td>
                                    <td>{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                                    <td>{!! $row['data'] !!}</td>
                                    <td>{!! $row['date']!!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>


                        <h4>Subcontractors Statement, Period Trade Contract</h4>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width: 5%"> #</th>
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th style="width: 10%"> Expiry / Last Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($expired_docs2 as $row)
                                <tr>
                                    <td>
                                        @if ($row['date'] != 'never')
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                                        @else
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                                        @endif
                                    </td>
                                    <td>{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                                    <td>{!! $row['data'] !!}</td>
                                    <td>{!! $row['date']!!}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <h4>Electrical Test & Tagging</h4>
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th style="width: 5%"> #</th>
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th style="width: 10%"> Expiry / Last Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($expired_docs3 as $row)
                                <tr>
                                    <td>
                                        @if ($row['date'] != 'never')
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                                        @else
                                            <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                                        @endif
                                    </td>
                                    <td>{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                                    <td>{!! $row['data'] !!}</td>
                                    <td>{!! $row['date']!!}</td>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop