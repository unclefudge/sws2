@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-users"></i> Missing Company Information</h1>
    </div>
@stop

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
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th> Expiry / Last Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                @if ($company->missingInfo() && !preg_match('/cc-/', strtolower($company->name)))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                        <td>{!! $company->missingInfo() !!}</td>
                                        <td>{!! $company->updated_at->format('d/m/Y')!!}</td>
                                    </tr>
                                @endif
                                @if ($company->missingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                                    @foreach($company->missingDocs() as $type => $name)
                                        <?php $doc = $company->expiredCompanyDoc($type) ?>
                                        <tr>
                                            <td>
                                                @if ($doc != 'N/A')
                                                <div class="text-center"><a href="/company/{{ $company->id }}/doc/{{ $doc->id }}/edit"><i class="fa fa-file-text-o"></i></a></div>
                                                    @else
                                                    <div class="text-center"><a href="/company/{{ $company->id }}/doct"><i class="fa fa-search"></i></a></div>
                                                @endif
                                            </td>
                                            <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                            <td>{{ $name }}</td>
                                            <td>{!! ($doc != 'N/A') ? $doc->expiry->format('d/m/Y') : 'never' !!} </td>
                                        </tr>
                                    @endforeach
                                @endif
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