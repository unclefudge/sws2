@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Company SWMS</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Company SWMS</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                <a class="btn btn-circle red btn-outline btn-sm" href="/manage/report/company_swms/email-all" data-original-title="Add">Email All to Review Current SWMS</a>
                                <a class="btn btn-circle green btn-outline btn-sm" href="/manage/report/company_swms/email-outofdate" data-original-title="Add">Email Out of Date & None</a>
                                <a class="btn btn-circle green btn-outline btn-sm" href="/manage/report/company_swms/settings" data-original-title="Add">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        @if ($email_sent > 0)
                            <div class="col-md-12 note note-warning">
                                <p>{{ $email_sent }} emails have been sent</p>
                            </div>
                        @endif
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Name</th>
                                <th> Trades</th>
                                <th> SWMS</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $twoyearago = \Carbon\Carbon::now()->subYears(2)->toDateTimeString(); ?>
                            @foreach($companies as $company)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/company/{{ $company->id }}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                    <td>{{ $company->tradesSkilledInSBC() }}</td>
                                    <td>
                                        @if (in_array($company->id, $excluded_companies))
                                            <span class="font-red">Not required</span>
                                        @else
                                            @foreach ($company->wmsdocs as $doc)
                                                @if ($doc->status == 1)
                                                        <?php $name = $doc->name; ?>
                                                    @if ($doc->updated_at < $twoyearago)
                                                            <?php $name .= ' <span class="badge badge-danger badge-roundless">Out of Date</span>'; ?>
                                                    @endif
                                                    <a href="/safety/doc/wms/{{$doc->id}}">{!! $name !!}</a><br>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop