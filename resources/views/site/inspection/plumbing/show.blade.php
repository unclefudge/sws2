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
                            @if(in_array($report->status, ['0', '3']))
                                <a class="btn btn-circle green btn-outline btn-sm"
                                   href="/site/inspection/plumbing/{{ $report->id }}/report" target="_blank"
                                   data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteInspectionPlumbingController::class, 'signoff'], $report->id) }}" class="horizontal-form">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6"><h3 style="margin: 0px"> {{ $report->site->name }}</h3></div>
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
                                            @if($report->status == '3')
                                                <span class="pull-right font-red hidden-sm hidden-xs">PENDING</span>
                                                <span class="text-center font-red visible-sm visible-xs">PENDING</span>
                                            @endif
                                            @if($report->status == '4')
                                                <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
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
                                            <div class="col-md-8">{{ ($report->assignedTo) ? $report->assignedTo->name : '' }}
                                                <br>Licence No. {{ $report->inspected_lic }}</div>
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
                                            <div class="col-md-10">Client contact was made:
                                                &nbsp; {{ ($report->client_contacted) ? 'Yes' : 'No' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <hr>

                                <h4 class="font-green-haze">Admin Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 ">{!! ($report->info) ? nl2br($report->info) : 'none' !!}</div>
                                </div>

                                {{-- Attachments --}}
                                <br>
                                <livewire:misc.attachments context="site-inspection-plumbing" :context-id="$report->id"/>


                                {{-- Inspection DetaiLs --}}
                                <h4 class="font-green-haze">Inspection Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                {{--Water Pressure / Hammer--}}
                                <div class="row" style="padding: 5px 0px">
                                    <div class="col-xs-2">Water Pressure</div>
                                    <div class="col-xs-3">{{ $report->pressure }} kpa</div>
                                    <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">500kpa Water
                                        Pressure Reduction Value Recommend
                                    </div>
                                    <div class="col-xs-5 visible-sm visible-xs">500kpa Water Pressure Reduction Value
                                        Recommend
                                    </div>
                                    <div class="col-xs-2">{{ ($report->pressure_reduction) ? 'Yes' : 'No' }}</div>
                                </div>
                                <div class="row" style="padding: 5px 0px">
                                    <div class="col-xs-2">Water Hammer</div>
                                    <div class="col-xs-10">{{ $report->hammer }} &nbsp; &nbsp; &nbsp; (Refer to Water Hammer
                                        comments below)
                                    </div>
                                </div>
                                <div class="row" style="padding: 5px 0px">
                                    <div class="col-xs-2">Existing Hot Water Type</div>
                                    <div class="col-xs-3">{{ $report->hotwater_type }}</div>
                                    <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">Will pipes in roof
                                        hot water need to be lowerd?
                                    </div>
                                    <div class="col-xs-5 visible-sm visible-xs">Will pipes in roof hot water need to be
                                        lowerd?
                                    </div>
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
                                    <div class="col-xs-5 hidden-sm hidden-xs" style="text-align: right">Are gas pipes able
                                        to be tapped into?
                                    </div>
                                    <div class="col-xs-5 visible-sm visible-xs">Are gas pipes able to be tapped into?</div>
                                    <div class="col-xs-2">{{ ($report->gas_lines) ? 'Yes' : 'No' }}</div>
                                </div>
                                <div class="row" style="padding: 5px 0px">
                                    <div class="col-xs-2">Gas Pipe</div>
                                    <div class="col-xs-10">{{ $report->gas_pipes }}</div>
                                </div>
                                {{-- Gas Notes --}}
                                <div style="padding: 5px 0px"><b>Gas Notes</b><br>
                                    {!! nl2br($report->gas_notes) !!}
                                </div>


                                {{-- Existing Plumbing --}}
                                <br>
                                <h4 class="font-green-haze">Condition of existing plumbing</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>The existing plumbing was found to be:</b><br>
                                <div>{!! nl2br($report->existing) !!}</div>

                                {{-- Comments --}}
                                @if ($report->notes)
                                    <br>
                                    <h4 class="font-green-haze">Client notes</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div>{!! nl2br($report->notes) !!}</div>
                                @endif

                                {{-- Water Pressure --}}
                                <br>
                                <h4 class="font-green-haze">Water Pressure</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>Water pressure higher than 500KPA will void the warranty on all mixer sets; it is our
                                    recommendation that you have fitted a pressure limiting valve at the metre to avoid possible
                                    problems:</b><br>
                                <div>{!! nl2br($report->pressure_notes) !!}</div>
                                @if ($report->pressure_cost)
                                    <br>
                                    <hr style="margin: 0px"><span
                                            style="float: right;">at a cost of <b>${{ $report->pressure_cost }}</b> Incl GST</span>
                                @endif


                                {{-- Water Hammer --}}
                                <br>
                                <h4 class="font-green-haze">Water Hammer</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div>{!! nl2br($report->hammer_notes) !!}</div>

                                {{-- Sewer --}}
                                <h4 class="font-green-haze">Sewer</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>Upon closer inspection of the sewer diagram that we have obtained from the Water Board:</b><br>
                                <div>{!! nl2br($report->sewer_notes) !!}</div>
                                <br>
                                <hr style="margin: 0px">
                                <div class="row" style="text-align: right;">
                                    <div class="col-md-12">
                                        Cost estimate <b>${{ $report->sewer_cost }}</b> (incl GST)<br>
                                        Allowance in your tender document is <b>${{ $report->sewer_allowance }}</b> (incl
                                        GST)<br>
                                        Meaning you may incur extra costs of <b>${{ $report->sewer_extra }}</b> (incl GST)
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="text-align: center"><h6>PRICE TO BE CONFIRMED AT TIME OF
                                            CONSTRUCTION AND DOES NOT INCLUDE BUILDERS MARGIN</h6><br></div>
                                </div>


                                {{-- Stormwater --}}
                                <h4 class="font-green-haze">Stormwater</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>Upon closer examination of your current stormwater system:</b><br>
                                <div>{!! nl2br($report->stormwater_notes) !!}</div>
                                <br>
                                <hr style="margin: 0px">
                                <div class="row" style="text-align: right;">
                                    <div class="col-md-12">
                                        Cost estimate <b>${{ $report->stormwater_cost }}</b> (incl GST)<br>
                                        Allowance in your tender document is <b>${{ $report->stormwater_allowance }}</b>
                                        (incl GST)<br>
                                        Meaning you may incur extra costs of <b>${{ $report->stormwater_extra }}</b> (incl
                                        GST)
                                    </div>
                                </div>


                                {{-- Stormwater Detention --}}
                                @if ($report->stormwater_detention_type)
                                    <h4 class="font-green-haze">Onsite Stormwater Detention</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <b>{{ $report->stormwater_detention_type }}:</b><br>
                                    <div>{!! nl2br($report->stormwater_detention_notes) !!}</div>
                                @endif

                                {{-- Note --}}
                                <br>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Please note that these remain best estimate until the final position and depth
                                            of services are located. Final estimates will be relayed to you at that time for
                                            your approval. <br><br>Thank you for your acknowledgment of the above and we
                                            will do our best to
                                            keep all costs to a minimum.</h6>
                                    </div>
                                </div>
                                <br>

                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <livewire:misc.actions table="site_inspection_plumbing" :table-id="$report->id"/>
                                    </div>
                                </div>

                                {{-- Sign Off --}}
                                <br>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5><b>PROCESS VARIATION AND SIGN OFF ON TASK</b></h5>
                                        <p>The above report have been reviewed by the following people.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3 text-right">Admin Officer:</div>
                                    <div class="col-sm-9">
                                        <div class="col-md-6">
                                            @if ($report->supervisor_sign_by)
                                                {!! \App\User::find($report->supervisor_sign_by)->full_name !!},
                                                &nbsp;{{ $report->supervisor_sign_at->format('d/m/Y') }}
                                            @elseif($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report) && (Auth::user()->id == 464 || Auth::user()->hasAnyRole2('web-admin|mgt-general-manager|con-administrator')))
                                                {{-- Brianna --}}
                                                <x-form.select name="supervisor_sign_by" :options="['' => 'Do you approve this inspection report', 'n' => 'No', 'y' => 'Yes']"/>
                                            @else
                                                <span class="font-red">Pending Sign Off</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3 text-right">Technical Manager:</div>
                                    <div class="col-sm-9">
                                        <div class="col-md-6">
                                            @if ($report->manager_sign_by)
                                                {!! \App\User::find($report->manager_sign_by)->full_name !!},
                                                &nbsp;{{ $report->manager_sign_at->format('d/m/Y') }}
                                            @elseif($report->status == 3 && $report->supervisor_sign_by && Auth::user()->allowed2('edit.site.inspection', $report) && Auth::user()->hasAnyRole2('con-construction-manager|gen-technical-manager|web-admin|mgt-general-manager'))
                                                <x-form.select name="manager_sign_by" :options="['' => 'Do you approve this inspection report', 'n' => 'No', 'y' => 'Yes']"/>
                                            @else
                                                <span class="font-red">Pending Sign Off</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Admin update with Client or Not --}}
                                @if ($report->manager_sign_by)
                                    <div class="row">
                                        <div class="col-sm-3 text-right">Report Sent to Client:</div>
                                        <div class="col-sm-9">
                                            <div class="col-md-6">
                                                {{-- Alethea --}}
                                                @if($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report) && (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager|con-administrator') || Auth::user()->id == 464 ))
                                                    <x-form.select name="sent2_client" :options="['n' => 'No', 'y' => 'Yes']"/>
                                                @endif
                                                @if($report->status == 0)
                                                    Yes
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-actions right">
                                    <a href="/site/inspection/plumbing" class="btn default"> Back</a>
                                    @if($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report))
                                        <button type="submit" class="btn green"> Save</button>

                                    @elseif (!$report->status && Auth::user()->allowed2('sig.site.inspection', $report))
                                        <a href="/site/inspection/plumbing/{{ $report->id }}/status/3" class="btn blue"> Mark Not With Client</a>
                                        <a href="/site/inspection/plumbing/{{ $report->id }}/status/1" class="btn green">Re-open Report</a>
                                    @endif
                                </div>
                            </form>
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

@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
@stop
