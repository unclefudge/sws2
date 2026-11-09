@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/cdc">CDC Requirements</a><i class="fa fa-circle"></i></li>
        <li><span>View items</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> CDC Requirements</span>
                            <span class="caption-helper">ID: {{ $cdc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form id="site-cdc-form" method="POST" action="{{ action([\App\Http\Controllers\Site\SiteCdcController::class, 'update'], $cdc->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="site_id" value="{{ $cdc->site_id }}">

                                @include('form-error')
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-6">
                                        <h4>Site Details</h4>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- CDC Stage --}}
                                        @php
                                            $cdcStage = $cdc->calculateStage();
                                        @endphp

                                        <h2 style="margin: 0; padding-right: 20px">
                                            <span class="pull-right font-red hidden-sm hidden-xs">{{ strtoupper($cdcStage) }}</span>
                                            <span class="text-center font-red visible-sm visible-xs">{{ strtoupper($cdcStage) }}</span>
                                        </h2>
                                    </div>
                                </div>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-8">
                                        @if ($cdc->site)
                                            <b><a href="/site/{{ $cdc->site_id }}">{{ $cdc->site->name }}</a></b><br>
                                            {{ $cdc->site->full_address }}<br>
                                            <b>Supervisor:</b> {{ ($cdc->site->supervisor_id) ? $cdc->site->supervisor->name : 'none'}}<br>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if ($cdc->site)
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>DA-CDC Number</b></div>
                                                <div class="col-md-6">{{ $cdc->site->da_number ?? '-' }}</div>
                                            </div>
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>PAN Number</b></div>
                                                <div class="col-md-6">{{ $cdc->site->pan_number ?? '-' }}</div>
                                            </div>
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>Planning Officer</b></div>
                                                <div class="col-md-6">{{ $cdc->site->planning_officer ?? '-' }}</div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                                <br>
                                {{-- Under Review + assign to super --}}
                                <h4>CDC Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-2">
                                        <x-form.input name="draftsperson_cc_display" label="Draftsperson CDC" :value="$cdc->site?->draftsperson_cc" readonly/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="portal_fee_paid" id="portal_fee_paid" label="Portal fee paid" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$cdc->portal_fee_paid"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="wbo_waiting" id="wbo_waiting" label="Waiting on WBO" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$cdc->wbo_waiting"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.datepicker name="cdc_requested" label="CDC Requested" :value="$cdc->cdc_requested?->format('d/m/Y')" format="dd/mm/yyyy" clear-button/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <x-form.input name="cdc_received_display" label="CDC Received" :value="$cdc->site?->construction_rcvd?->format('d/m/Y')" readonly/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="cc_number_display" label="CDC Number" :value="$cdc->site?->cc_number" readonly/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="cft_number_display" label="CFT Number" :value="$cdc->site?->cft_number" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" id="notes" label="Notes" :value="$cdc->notes"/>
                                    </div>
                                </div>
                                {{-- Certifiers Details --}}
                                <h4>Certifiers Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="certifier" label="Certifier" :value="old('certifier', $cdc->certifier)"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="certifier_phone" label="Certifier Phone" :value="old('certifier_phone', $cdc->certifier_phone)"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="pca_name" label="PCA Name" :value="old('pca_name', $cdc->pca_name)"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="pca_email" label="PCA Email" :value="old('pca_email', $cdc->pca_email)"/>
                                    </div>
                                </div>
                            </form>
                            <livewire:misc.attachments context="site-cdc" :context-id="$cdc->id"/>
                        </div>
                        <div class="row">
                            {{-- Assigned Supervisor --}}
                            <div class="col-md-5"></div>
                        </div>

                        {{-- CDC Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.cdc.items :cdc-id="$cdc->id"/>
                            </div>
                        </div>
                        <br>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:misc.actions table="site_cdc" :table-id="$cdc->id" :allow-add="Auth::user()->allowed2('edit.site.cdc', $cdc)"/>
                            </div>
                        </div>

                        {{-- Assigned Tasks --}}


                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/cdc" class="btn default">Back</a>

                            @php
                                $siteDisabled = (int) $cdc->site?->status === -2;
                            @endphp

                            @if (!$siteDisabled)
                                @if (!$cdc->master && Auth::user()->allowed2('edit.site.cdc', $cdc) && in_array((int) $cdc->status, [2, -1], true))
                                    <button type="button" class="btn green" onclick="updateCdcStatus(1)">Make Active</button>
                                @endif

                                @if (!$cdc->master && Auth::user()->allowed2('del.site.cdc', $cdc) && in_array((int) $cdc->status, [1, 2], true))
                                    <button type="button" class="btn red" onclick="updateCdcStatus(-1)">Disable</button>
                                @endif
                            @endif
                            @if (Auth::user()->allowed2('edit.site.cdc', $cdc))
                                <button id="submit" type="submit" form="site-cdc-form" name="save" class="btn green">Save</button>
                            @endif
                        </div>
                        <br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {


        });

        function updateCdcStatus(status) {
            $.ajax({
                url: '/site/cdc/{{ $cdc->id }}/update',
                type: 'PATCH',
                data: {status: status},
                success: function () {
                    window.location.href = '/site/cdc/{{ $cdc->id }}';
                },
                error: function () {
                    alert('Failed to update CDC status');
                }
            });
        }
    </script>
@stop
