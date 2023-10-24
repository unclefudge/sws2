@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Missing Company Information on Planner</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Missing Company Information on Planner (expired 7 days+)</span>
                        </div>
                        <div class="actions">
                            {{--}}<a href="/manage/report/missing_company_info_planner_csv" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> Download CSV</a>--}}
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_list">
                            {{--}}<thead>
                            <tr class="mytable-header">
                                <th> Name</th>
                                <th> Missing Info / Document</th>
                                <th> Expired / Last Updated</th>
                                <th> Next on Planner</th>
                            </tr>
                            </thead>--}}
                            <tbody>
                            <?php $today = \Carbon\Carbon::today(); $weekago = \Carbon\Carbon::today()->subWeeks(1); ?>
                            @foreach($companies as $company)
                                    <?php $planner_date = $company->nextDateOnPlanner(); ?>
                                @if (!preg_match('/cc-/', strtolower($company->name)) && ($company->missingInfo() || $company->isMissingDocs()))
                                    <tr style="background: #f0f6fa !important">
                                        <td>
                                            <b>{{ $company->name }}</b> <span class="pull-right">Next on Planner in {!! $planner_date->longAbsoluteDiffForHumans() !!}</span>
                                            {!! ($company->nickname) ? "&nbsp; &nbsp; <span class='font-grey-cascade'>$company->nickname</span>" : '' !!}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {!! ($company->missingInfo()) ? $company->missingInfo()."<br>" : '' !!}
                                            @foreach ($company->missingDocs() as $type => $name)
                                                    <?php $doc = $company->expiredCompanyDoc($type) ?>
                                                @if ($doc && ($doc == 'N/A' || $doc->expiry->lt($weekago)))
                                                    <span style="width: 100px; display: inline-block"> {!! ($doc != 'N/A' && $doc->expiry) ?  $doc->expiry->longAbsoluteDiffForHumans() : 'never' !!}</span>
                                                    <span>
                                                        @if ($doc != 'N/A')
                                                            <a href="/company/{{ $company->id }}/doc/{{ $doc->id }}/edit">{{ $name }}</a>
                                                        @else
                                                            <a href="/company/{{ $company->id }}/doc">{{ $name }}</a>
                                                        @endif
                                                    </span><br>
                                                @endif
                                            @endforeach

                                        </td>
                                        {{--}}<td>{!! $company->updated_at->diffForHumans() !!}</td>
                                        <td>Next Planned: {!! $planner_date->longAbsoluteDiffForHumans() !!}</td>--}}
                                    </tr>
                                    @if (false && $company->isMissingDocs() && !preg_match('/cc-/', strtolower($company->name)))
                                        @foreach($company->missingDocs() as $type => $name)
                                                <?php $doc = $company->expiredCompanyDoc($type) ?>
                                            @if ($doc && ($doc == 'N/A' || $doc->expiry->lt($weekago)))
                                                <tr>
                                                    <td>
                                                        @if ($doc != 'N/A')
                                                            <div class="text-center"><a href="/company/{{ $company->id }}/doc/{{ $doc->id }}/edit"><i class="fa fa-file-text-o"></i></a></div>
                                                        @else
                                                            <div class="text-center"><a href="/company/{{ $company->id }}/doc"><i class="fa fa-search"></i></a></div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $company->name }} {!! ($company->nickname) ? "<span class='font-grey-cascade'><br>$company->nickname</span>" : '' !!}</td>
                                                    <td>{{ $name }}</td>
                                                    <td>
                                                        @if ($doc != 'N/A' && $doc->expiry)
                                                            {!! $doc->expiry->diffForHumans() !!}
                                                        @else
                                                            never
                                                        @endif
                                                    </td>
                                                    <td>{!! $planner_date->longAbsoluteDiffForHumans($today) !!}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
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