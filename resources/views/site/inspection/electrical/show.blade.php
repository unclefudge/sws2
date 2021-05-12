@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
            <li><a href="/site/inspection/electrical">Electrical Inspection Report</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Electrical Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                        <div class="actions">
                            @if($report->status == '0')
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/electrical/{{ $report->id }}/report" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteInspectionElectricalController@update', $report->id], 'class' => 'horizontal-form']) !!}

                            <div class="row">
                                <div class="col-md-6"><h3 style="margin: 0px"> {{ $report->site->code }} - {{ $report->site->name }}</h3></div>
                                <div class="col-md-6">
                                    <h2 style="margin: 0px; padding-right: 20px">
                                        @if($report->status == '0')
                                            <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</small></span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                        @endif
                                        @if($report->status == '1')
                                            <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                            <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                        @endif
                                    </h2>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Job details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Inspection --}}
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Date</b></div>
                                        <div class="col-md-8">{{ ($report->inspected_at) ?  $report->inspected_at->format('d/m/Y g:i a') : '' }}</div>
                                    </div>
                                    <div class="row" style="padding: 0px 5px;">
                                        <div class="col-md-4">Inspection carried out by</div>
                                        <div class="col-md-8">{{ ($report->assignedTo) ? $report->assignedTo->name : '' }}<br>Licence No. {{ $report->inspected_lic }}</div>
                                    </div>
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Signature</b></div>
                                        <div class="col-md-8">{{ $report->inspected_name }}</div>
                                    </div>
                                </div>
                                {{-- Client --}}
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-2"><b>Client</b></div>
                                        <div class="col-md-10">{{ $report->client_name }}</div>
                                    </div>
                                    <div class="row" style="padding: 0px 5px;">
                                        <div class="col-md-2 hidden-sm hidden-xs">&nbsp;</div>
                                        <div class="col-md-10">{{ $report->client_address }}<br><br></div>
                                    </div>
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-2 hidden-sm hidden-xs">&nbsp;</div>
                                        <div class="col-md-10">Client contact was made: &nbsp; {{ ($report->client_contacted) ? 'Yes' : 'No' }}</div>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            {{-- Existing --}}
                            @if ($report->existing)
                                <h4 class="font-green-haze">Condition of existing wiring</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                The existing wiring was found to be:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->existing) !!}</div>
                                </div>
                                <br>
                            @endif

                            {{-- Required --}}
                            @if ($report->required || $report->required_cost)
                                <h4 class="font-green-haze">Required work to meet compliance</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->required) !!}</div>
                                </div>
                                @if ($report->required_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->required_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Required --}}
                            @if ($report->recommend || $report->recommend_cost)
                                <h4 class="font-green-haze">Recommended works</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                Work not essential but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same:
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->recommend) !!}</div>
                                </div>
                                @if ($report->required_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->recommend_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Additional --}}
                            @if ($report->notes)
                                <h4 class="font-green-haze">Additional Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->notes) !!}</div>
                                </div>
                            @endif
                        </div>

                        @if(Auth::user()->allowed2('edit.site.inspection', $report))
                            <div class="form-actions right">
                                <a href="/site/inspection/electrical" class="btn default"> Back</a>
                            </div>
                            {!! Form::close() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $report->displayUpdatedBy() !!}
        </div>
    </div>

    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<!--<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>-->
<script src="/js/libs/moment.min.js" type="text/javascript"></script>
@stop

