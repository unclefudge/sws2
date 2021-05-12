@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
            <li><a href="/site/inspection/plumbing">Plumbing Inspection Reports</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Plumbing Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                        <div class="actions">
                            @if($report->status == '0')
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/plumbing/{{ $report->id }}/report" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteInspectionPlumbingController@update', $report->id], 'class' => 'horizontal-form']) !!}

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

                            {{-- Inspection DetaiLs --}}
                            <h4 class="font-green-haze">Inspection Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            {{--Water Pressure / Hammer--}}
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Water Pressure</div>
                                <div class="col-xs-3">{{ $report->pressure }} kpa</div>
                                <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">500kpa Water Pressure Reduction Value Recommend</div>
                                <div class="col-xs-5 visible-sm visible-xs">500kpa Water Pressure Reduction Value Recommend</div>
                                <div class="col-xs-2">{{ ($report->pressure_reduction) ? 'Yes' : 'No' }}</div>
                            </div>
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Water Hammer</div>
                                <div class="col-xs-10">{{ $report->hammer }} &nbsp; &nbsp; &nbsp; (Refer to Water Hammer comments below)</div>
                            </div>
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Existing Hot Water Type</div>
                                <div class="col-xs-3">{{ $report->hotwater_type }}</div>
                                <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">Will pipes in roof hot water need to be lowerd?</div>
                                <div class="col-xs-5 visible-sm visible-xs">Will pipes in roof hot water need to be lowerd?</div>
                                <div class="col-xs-2">{{ ($report->hotwater_lowered) ? 'Yes' : 'No' }}</div>
                            </div>
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Fuel Type</div>
                                <div class="col-xs-10">{{ $report->fuel_type }}</div>
                            </div>


                            {{--  Gas  Meter / Pipes --}}
                            <h4 class="font-green-haze">Gas</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Gas Meter Position OK?</div>
                                <div class="col-xs-3">{{ $report->gas_position }}</div>
                                <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">Are gas pipes able to be tapped into?</div>
                                <div class="col-xs-5 visible-sm visible-xs">Are gas pipes able to be tapped into?</div>
                                <div class="col-xs-2">{{ ($report->gas_lines) ? 'Yes' : 'No' }}</div>
                            </div>
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-xs-2">Gas Pipe</div>
                                <div class="col-xs-10">{{ $report->gas_pipes }}</div>
                            </div>
                            {{-- Gas Notes --}}
                            <div class="row" style="padding: 5px 0px">
                                <div class="col-md-12">Gas Notes</div>
                            </div>
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->gas_notes) !!}</div>
                            </div>


                            {{-- Existing Plumbing --}}
                            <br>
                            <h4 class="font-green-haze">Condition of existing plumbing</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            The existing plumbing was found to be:
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->existing) !!}</div>
                            </div>

                            <!-- Comments -->
                            @if ($report->notes)
                                <br>
                                <h4 class="font-green-haze">Additional notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->notes) !!}</div>
                                </div>
                            @endif

                            {{-- Water Pressure --}}
                            <br>
                            <h4 class="font-green-haze">Water Pressure</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            Water pressure higher than 500KPA will void the warranty on all mixer sets; it is our recommendation that you have fitted a pressure limiting valve at the metre to avoid possible problems:
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->pressure_notes) !!}</div>
                            </div>
                            @if ($report->pressure_cost)
                                <br>
                                <hr style="margin: 0px"><span style="float: right;">at a cost of <b>${{ $report->pressure_cost }}</b> Incl GST</span>
                            @endif


                            {{-- Water Hammer --}}
                            <br>
                            <h4 class="font-green-haze">Water Hammer</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->hammer_notes) !!}</div>
                            </div>

                            {{-- Sewer --}}
                            <h4 class="font-green-haze">Sewer</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            Upon closer inspection of the sewer diagram that we have obtained from the Water Board:
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->sewer_notes) !!}</div>
                            </div>
                            <br>
                            <hr style="margin: 0px">
                            <div class="row" style="text-align: right;">
                                <div class="col-md-12">
                                    Cost estimate <b>${{ $report->sewer_cost }}</b> (incl GST)<br>
                                    Allowance in your tender document is <b>${{ $report->sewer_allowance }}</b> (incl GST)<br>
                                    Meaning you may incur extra costs of <b>${{ $report->sewer_extra }}</b> (incl GST)
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="text-align: center"><h6>PRICE TO BE CONFIRMED AT TIME OF CONSTRUCTION AND DOES NOT INCLUDE BUILDERS MARGIN</h6><br></div>
                            </div>


                            {{-- Stormwater --}}
                            <h4 class="font-green-haze">Stormwater</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            Upon closer examination of your current stormwater system:
                            <div class="row">
                                <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                <div class="col-md-11">{!! nl2br($report->stormwater_notes) !!}</div>
                            </div>
                            <br>
                            <hr style="margin: 0px">
                            <div class="row" style="text-align: right;">
                                <div class="col-md-12">
                                    Cost estimate <b>${{ $report->stormwater_cost }}</b> (incl GST)<br>
                                    Allowance in your tender document is <b>${{ $report->stormwater_allowance }}</b> (incl GST)<br>
                                    Meaning you may incur extra costs of <b>${{ $report->stormwater_extra }}</b> (incl GST)
                                </div>
                            </div>


                            {{-- Stormwater Detention --}}
                            @if ($report->stormwater_detention_type)
                                <h4 class="font-green-haze">Onsite Stormwater Detention</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{ $report->stormwater_detention_type }}:
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-1 hidden-sm hidden-xs">&nbsp;</div>
                                    <div class="col-md-11">{!! nl2br($report->stormwater_detention_notes) !!}</div>
                                </div>
                            @endif

                            {{-- Note --}}
                            <br>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Please note that these remain best estimate until the final position and depth of services are located. Final estimates will be relayed to you at that time for your approval. <br><br>Thank you for your acknowledgment of the above and we will do our best to
                                        keep all costs to a minimum.</h6>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->allowed2('edit.site.inspection', $report))
                            <div class="form-actions right">
                                <a href="/site/inspection/plumbing" class="btn default"> Back</a>
                            </div>
                            {!! Form::close() !!}
                        @endif
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

