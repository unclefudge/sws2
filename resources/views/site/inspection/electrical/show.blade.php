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
                            @if(in_array($report->status, ['0', '3']))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/inspection/electrical/{{ $report->id }}/report" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteInspectionElectricalController::class, 'signoff'], $report->id) }}" class="horizontal-form">
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
                                        <div class="col-md-10">
                                            Client contact was made: &nbsp; {{ ($report->client_contacted) ? 'Yes' : 'No' }}<br>
                                            Client electricty bill required: {{ $report->clientbill }}<br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-6">Ausgrid pre-construction?</div>
                                        <div class="col-md-6">{{ $report->ausgrid }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-6">Non-Ausgrid pre-construction?</div>
                                        <div class="col-md-6">{{ $report->nonausgrid }}</div>
                                    </div>
                                </div>
                            </div>
                            @if ($report->nonausgrid_weeks)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row" style="padding: 5px;">
                                            <div class="col-md-6">Weeks in advance for this work:</div>
                                            <div class="col-md-6">{{ $report->nonausgrid_weeks }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <hr>


                            <h4 class="font-green-haze">Admin Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 "> {!! ($report->info) ? nl2br($report->info) : 'none' !!}</div>
                            </div>

                            {{-- Existing --}}
                            @if ($report->existing)
                                <h4 class="font-green-haze">Condition of existing wiring</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>The existing wiring was found to be:</b><br>
                                <div>{!! nl2br($report->existing) !!}</div>
                                <br>
                            @endif

                            {{-- Required --}}
                            @if ($report->required || $report->required_cost)
                                <h4 class="font-green-haze">Required work to meet compliance</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council:</b><br>
                                <div>{!! nl2br($report->required) !!}</div>
                                @if ($report->required_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->required_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Recommended Cost --}}
                            @if ($report->recommend || $report->recommend_cost)
                                <h4 class="font-green-haze">Recommended works</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <b>Work not essential but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same:</b><br>
                                <div>{!! nl2br($report->recommend) !!}</div>
                                @if ($report->recommend_cost)
                                    <br>
                                    <hr style="margin: 0px">
                                    <div class="row" style="text-align: right;">
                                        <div class="col-md-12"><b> at a cost of ${{ $report->recommend_cost }} Incl GST</b></div>
                                    </div>
                                @endif
                            @endif

                            {{-- Additional --}}
                            @if ($report->notes)
                                <h4 class="font-green-haze">Client Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div>{!! nl2br($report->notes) !!}</div>
                            @endif

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <livewire:misc.actions table="site_inspection_electrical" :table-id="$report->id"/>
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
                                            {!! \App\User::find($report->supervisor_sign_by)->full_name !!}, &nbsp;{{ $report->supervisor_sign_at->format('d/m/Y') }}
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
                                            {!! \App\User::find($report->manager_sign_by)->full_name !!}, &nbsp;{{ $report->manager_sign_at->format('d/m/Y') }}
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
                        </div>

                        @if(Auth::user()->allowed2('edit.site.inspection', $report))
                            <div class="form-actions right">
                                <a href="/site/inspection/electrical" class="btn default"> Back</a>
                                @if($report->status == 3 && Auth::user()->allowed2('edit.site.inspection', $report))
                                    <button type="submit" class="btn green"> Save</button>
                                    
                                @elseif (!$report->status && Auth::user()->allowed2('sig.site.inspection', $report))
                                    <a href="/site/inspection/electrical/{{ $report->id }}/status/1" class="btn green"> Re-open Report</a>
                                @endif
                            </div>
                            
                        @endif
                        </form>
                        <livewire:misc.attachments context="site-inspection-electrical" :context-id="$report->id"/>
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
