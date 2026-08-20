@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/notification">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
    </ul>
@stop


@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Asbestos Notification</span>
                            <span class="caption-helper"> ID: {{ $asb->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteAsbestosController::class, 'updateExtra'], $asb->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <h2 style="margin-top: 0px">{{ $asb->site->name }}</h2>
                                        {{ $asb->site->fulladdress }}
                                    </div>
                                    <div class="col-md-5">
                                        @if (!$asb->status)
                                            <h2 class="font-red pull-right" style="margin-top: 0px">CLOSED</h2>
                                        @endif
                                        <b>Job #:</b> {{ $asb->site->code }}<br>
                                        <b>Supervisor:</b> {{ $asb->site->supervisorName }}<br>
                                    </div>
                                </div>
                                <hr>

                                {{-- Client + Supervisor Details --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- Client name + phone--}}
                                        <h4 class="font-green-haze">Individual (Client) Details</h4>
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Name:</b></div>
                                            <div class="col-md-8">{{ $asb->client_name }}</div>
                                            <div class="col-md-4"><b>Phone:</b></div>
                                            <div class="col-md-8">{{ $asb->client_phone }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Supervisor name + phone--}}
                                        <h4 class="font-green-haze">Contact Person (Supervisor) Details</h4>
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Name:</b></div>
                                            <div class="col-md-8">{{ ($asb->supervisor) ? $asb->supervisor->name : '' }}</div>
                                            <div class="col-md-4"><b>Phone:</b></div>
                                            <div class="col-md-8">{{ $asb->super_phone }}</div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                {{-- Site Details--}}
                                <h4 class="font-green-haze">Site Details</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Site Name:</b></div>
                                            <div class="col-md-8">{{ $asb->site->name }}</div>
                                        </div>
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Address:</b></div>
                                            <div class="col-md-8">{{ $asb->site->fulladdress }}</div>
                                        </div>
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Workplace type:</b></div>
                                            <div class="col-md-8">{{ $asb->workplace }}</div>
                                        </div>
                                        {{-- Open Hours --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Opening hours: </b></div>
                                            <div class="col-md-8">{{ $asb->hours_from }} to {{ $asb->hours_to }}</div>
                                        </div>
                                        {{-- Start --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Proposed start:</b></div>
                                            <div class="col-md-8">{{ $asb->date_from->format('d/m/Y') }}</div>
                                        </div>
                                        {{-- Finish --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Proposed finish:</b></div>
                                            <div class="col-md-8">{{ $asb->date_to->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Workers --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Number of workers: </b></div>
                                            <div class="col-md-8">{{ $asb->workers }}</div>
                                        </div>
                                        {{-- Coal / Mining --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Coal/mining workplace: </b></div>
                                            <div class="col-md-8">{{ ($asb->coalmine) ? 'Yes' : 'No' }}</div>
                                        </div>
                                        {{-- Planner ID --}}
                                        @if ($asb->plan_id && $asb->planner)
                                            <div class="row" style="line-height: 2">
                                                <div class="col-md-12"><br><span class="font-red">Linked to Planner Task: {!! ($asb->planner) ? $asb->planner->from->format('d/m/Y') : '' !!} </span></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <br>

                                <h4 class="font-green-haze">Asbestos Identification &nbsp;
                                    <small>(Applicable to Friable / Asbestos in soils)</small>
                                </h4>
                                {{-- Hydiene Report --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Hygienist report:</b></div>
                                    <div class="col-md-10">{!! ($asb->hygiene) ? "Yes &nbsp; ($asb->hygiene_report)" : 'No' !!}</div>
                                </div>
                                <br>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Assessor Contact Details</h5>
                                        <hr style="padding: 0px; margin: 0px">
                                        {{-- Assessor Name --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Name:</b></div>
                                            <div class="col-md-8">{{ $asb->assessor_name }}</div>
                                        </div>

                                        {{-- Assessor Phone --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Phone:</b></div>
                                            <div class="col-md-8">{{ $asb->assessor_phone }}</div>
                                        </div>

                                        {{-- Assessor --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Licence No.:</b></div>
                                            <div class="col-md-8">{{ $asb->assessor_lic }}</div>
                                        </div>

                                        {{-- Assessor --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>Department:</b></div>
                                            <div class="col-md-8">{{ $asb->assessor_dept }}</div>
                                        </div>

                                        {{-- Report --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-4"><b>State:</b></div>
                                            <div class="col-md-8">{{ $asb->assessor_state }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Testing Laboratory</h5>
                                        <hr style="padding: 0px; margin: 0px">
                                    </div>
                                </div>
                                <br>


                                <h4 class="font-green-haze">Asbestos Details &nbsp;
                                    <small>(Type / Location / Amount)</small>
                                </h4>
                                {{-- Class--}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Asbestos Class:</b></div>
                                    <div class="col-md-10">{{ ($asb->friable) ? 'Class A (Friable)' : 'Class B (Non-Friable)' }}</div>
                                </div>
                                {{-- Amount --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Amount:</b></div>
                                    <div class="col-md-10">{{ $asb->amount }} (m2)</div>
                                </div>
                                {{-- Location --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Location:</b></div>
                                    <div class="col-md-10">{{ $asb->location }}</div>
                                </div>
                                {{-- Type --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Type:</b></div>
                                    <div class="col-md-10">{{ $asb->type }}</div>
                                </div>
                                {{-- Removalist --}}
                                <div class="row" style="line-height: 2">
                                    <div class="col-md-2"><b>Licensed Asbestos Removalist:</b></div>
                                    <div class="col-md-10">{{ $asb->removalist_name }}</div>
                                </div>


                                {{-- Asbestos Removal --}}
                                @if(!$asb->friable)
                                    <br><h4 class="font-green-haze">Protective Equipment and Isolation / Encapsulation</h4>
                                    <div class="row" style="line-height: 2">
                                        {{-- Equipment --}}
                                        <div class="col-md-2"><b>Personal equipment: </b></div>
                                        <div class="col-md-10">{!! $asb->equipment('SBC') !!}</div>
                                    </div>
                                    <div class="row" style="line-height: 2">
                                        {{-- Methods --}}
                                        <div class="col-md-2"><b>Isolate / Enclose: </b></div>
                                        <div class="col-md-10">{!! $asb->methods('SBC') !!}</div>
                                    </div>
                                    <div class="row" style="line-height: 2">
                                        {{-- Extent --}}
                                        <div class="col-md-2"><b>Extent of isolation: </b></div>
                                        <div class="col-md-10">{{ $asb->isolation }}</div>
                                    </div>
                                @endif


                                {{-- Additional Details --}}
                                <br><h4 class="font-green-haze">Additional Infomation</h4>
                                @if(!$asb->friable)
                                    {{-- Register --}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-2"><b>Asbestos Register: </b></div>
                                        <div class="col-md-10">{{ ($asb->register) ? 'Yes - Asbestos Register was reviewed' : 'An Asbestos Register was not available for this site' }}</div>
                                    </div>
                                @endif
                                <br>
                                @if (Auth::user()->allowed2('edit.site.asbestos', $asb))
                                    @if(!$asb->friable)
                                        <div class="row">
                                            {{-- Safe Work Notification --}}
                                            <div class="col-md-3">
                                                    <?php
                                                    $safe_at = ($asb->safework_at) ? $asb->safework_at->format('d/m/Y') : '';
                                                    $lodged = "Lodged"; //($asb->safework == 2 && $asb->safework_at) ? "Lodged - $safe_at" : 'Lodged';
                                                    $accept = "Accepted"; //($asb->safework == 1 && $asb->safework_at) ? "Accepted - $safe_at" : 'Accepted';
                                                    ?>
                                                <x-form.select name="safework" label="Safe Work Notification" :options="['' => 'Not lodged', '2' => $lodged, '1' => $accept]" :value="$asb->safework"/>
                                            </div>
                                            {{-- Safe Work Ref# --}}
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="safework_ref" class="control-label">Safe Work Reference</label>
                                                    <input type="text" name="safework_ref" id="safework_ref" class="form-control" value="{{ old('safework_ref', $asb->safework_ref ?? '') }}">
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            {{-- Supervisor Form --}}
                                            <div class="col-md-3">
                                                <x-form.datepicker name="supervisor_at" label="Supervisor form sent" :value="$asb->supervisor_at ? $asb->supervisor_at->format('d/m/Y') : ''" format="dd/mm/yyyy" placeholder="choose date" wrapper-class="input-medium" clear-button readonly/>
                                            </div>
                                            {{-- Neighbour Form --}}
                                            <div class="col-md-3">
                                                <x-form.datepicker name="neighbours_at" label="Neighbours form sent" :value="$asb->neighbours_at ? $asb->neighbours_at->format('d/m/Y') : ''" format="dd/mm/yyyy" placeholder="choose date" wrapper-class="input-medium" clear-button readonly/>
                                            </div>
                                        </div>
                                        <br>
                                    @endif


                                    <div class="row">
                                        {{-- Removal Date --}}
                                        <div class="col-md-3">
                                            <x-form.datepicker name="removal_at" label="Removal Date" :value="$asb->removal_at ? $asb->removal_at->format('d/m/Y') : ''" format="dd/mm/yyyy" placeholder="choose date" wrapper-class="input-medium" clear-button readonly/>
                                        </div>
                                        {{-- Register Updated Date --}}
                                        <div class="col-md-3">
                                            <x-form.datepicker name="reg_updated_at" label="Register Updated" :value="$asb->reg_updated_at ? $asb->reg_updated_at->format('d/m/Y') : ''" format="dd/mm/yyyy" placeholder="choose date" wrapper-class="input-medium" clear-button readonly/>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn blue" style="margin-top: 25px"> Save</button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </form>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:misc.actions table="site_asbestos" :table-id="$asb->id" :allow-add="(int) $asb->status === 1 && Auth::user()->allowed2('edit.site.asbestos', $asb)"/>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/asbestos/notification" class="btn default"> Back</a>
                            @if(Auth::user()->allowed2('del.site.asbestos', $asb))
                                @if ($asb->status)
                                    @if(Auth::user()->allowed2('edit.site.asbestos', $asb))
                                        <a href="/site/asbestos/notification/{{ $asb->id }}/edit" class="btn green"> Edit Notification</a>
                                    @endif
                                    <a href="/site/asbestos/notification/{{ $asb->id }}/status/0" class="btn red"> Close Notification</a>
                                @else
                                    <a href="/site/asbestos/notification/{{ $asb->id }}/status/1" class="btn green"> Re-open Notification</a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
@stop

