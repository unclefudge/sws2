@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Company Privacy Policy</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Company Privacy Policy</span>
                        </div>
                        <div class="actions">
                            <div class="btn-group">
                                <a class="btn green btn-outline btn-circle btn-sm" href="javascript:;" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"> Send Request to Sign &nbsp; <i class="fa fa-angle-down"></i></a>
                                <ul class="dropdown-menu pull-right">
                                    <li><a href="/manage/report/company_privacy_send/new">Send To New Companies Only</a></li>
                                    <li><a href="/manage/report/company_privacy_send/all">Re-send To All Outstanding Companies</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Status</th>
                                <th> User</th>
                                <th width="7%"> Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                <?php $todo = \App\Models\Comms\Todo::where('type', 'company privacy')->where('type_id', $company->id)->where('status', '1')->first(); ?>
                                @if (!preg_match('/cc-/', strtolower($company->name)))
                                    <tr>
                                        <td>
                                            <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                        </td>
                                        <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                        <td>
                                            @if ($company->activeCompanyDoc(12))
                                                <a href="{{ $company->activeCompanyDoc(12)->attachment_url }}"> <i class="fa fa-2x fa-check green" style="color: #26C281"></i> </a>
                                            @elseif ($todo)
                                                Request Sent
                                            @else
                                                <i class="fa fa-2x fa-times font-red"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($company->activeCompanyDoc(12))
                                                {{ $company->activeCompanyDoc(12)->createdBy->name }}
                                            @elseif ($todo)
                                                {{ $todo->assignedToBySBC() }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($company->activeCompanyDoc(12))
                                                {{ $company->activeCompanyDoc(12)->created_at->format('d/m/Y') }}
                                            @elseif ($todo)
                                                {{ $todo->created_at->format('d/m/Y') }}
                                            @endif
                                        </td>
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop