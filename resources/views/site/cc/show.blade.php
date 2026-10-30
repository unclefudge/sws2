@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/cc">CC Requirements</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> CC Requirements</span>
                            <span class="caption-helper">ID: {{ $cc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form id="site-cc-form" method="POST" action="{{ action([\App\Http\Controllers\Site\SiteCcController::class, 'update'], $cc->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="site_id" value="{{ $cc->site_id }}">

                                @include('form-error')
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-6">
                                        <h4>Site Details</h4>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- CC Stage --}}
                                        @php
                                            $ccStage = $cc->calculateStage();
                                        @endphp

                                        <h2 style="margin: 0; padding-right: 20px">
                                            <span class="pull-right font-red hidden-sm hidden-xs">{{ strtoupper($ccStage) }}</span>
                                            <span class="text-center font-red visible-sm visible-xs">{{ strtoupper($ccStage) }}</span>
                                        </h2>
                                    </div>
                                </div>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-8">
                                        @if ($cc->site)
                                            <b><a href="/site/{{ $cc->site_id }}">{{ $cc->site->name }}</a></b><br>
                                            {{ $cc->site->full_address }}<br>
                                            <b>Supervisor:</b> {{ ($cc->site->supervisor_id) ? $cc->site->supervisor->name : 'none'}}<br>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if ($cc->site)
                                            <div class="row">
                                                <div class="col-md-6" style="text-align: right">
                                                    Prac Completion<br>
                                                    Completion Pack Sent<br>
                                                    Final Photos Rcvd<br>
                                                </div>
                                                <div class="col-md-6">
                                                    {!! ($cc->site->completion_signed) ? $cc->site->completion_signed->format('d/m/Y') : '-' !!}<br>
                                                    {{ ($cc->site->cp_sent_client) ? $cc->site->cp_sent_client->format('d/m/Y') : '-'}}<br>
                                                    {{ $cc->final_photos_rcvd?->format('d/m/Y') ?? '-' }}<br>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <br>
                                {{-- Under Review + assign to super --}}
                                <h4>CC Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Assigned Supervisor --}}
                                    <div class="col-md-4">
                                        @if ($cc->status && Auth::user()->allowed2('sig.site.cc', $cc))
                                            <x-form.select name="super_id" label="CC Supervisor" plugin="select2" style="width:100%">
                                                <option value=""></option>
                                                @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                    <option value="{{ $super->id }}" {{ ($super->id == $cc->super_id) ? 'selected' : '' }}>{{ $super->name }}</option>
                                                @endforeach
                                            </x-form.select>
                                        @else
                                            <x-form.input name="assigned_super_text" label="CC Supervisor" :value="$cc->super_id ? $cc->supervisor->name : '-'" readonly/>
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="portal_fee_paid" id="portal_fee_paid" label="Portal fee paid" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$cc->portal_fee_paid"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="wbo_waiting" id="wbo_waiting" label="Waiting on WBO" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$cc->wbo_waiting"/>
                                    </div>
                                    {{-- CC Requested --}}
                                    <div class="col-md-2">
                                        <x-form.datepicker name="cc_requested" label="CC Requested" :value="$cc->cc_requested?->format('d/m/Y')" format="dd/mm/yyyy" clear-button/>
                                    </div>
                                    <div class="col-md-2">
                                        {{-- CC Received --}}
                                        <x-form.input name="cc_received_display" label="CC Received (Zoho)" :value="$cc->site?->construction_rcvd?->format('d/m/Y')" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <x-form.textarea name="notes" id="notes" label="Notes" :value="$cc->notes"/>
                                    </div>

                                    <div class="col-md-2">
                                        <x-form.datepicker name="final_photos_rcvd" label="Final Photos Rcvd" :value="$cc->final_photos_rcvd?->format('d/m/Y')" format="dd/mm/yyyy" clear-button/>
                                    </div>


                                </div>
                                {{-- Certifiers Details --}}
                                <h4>Certifiers Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="certifier" label="Certifier" :value="old('certifier', $cc->certifier)"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="certifier_phone" label="Certifier Phone" :value="old('certifier_phone', $cc->certifier_phone)"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="pca_name" label="PCA Name" :value="old('pca_name', $cc->pca_name)"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="pca_email" label="PCA Email" :value="old('pca_email', $cc->pca_email)"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="inspections_email" label="Inspections Email" :value="old('inspections_email', $cc->inspections_email)"/>
                                    </div>
                                </div>
                            </form>
                            <livewire:misc.attachments context="site-cc" :context-id="$cc->id"/>
                        </div>
                        <div class="row">
                            {{-- Assigned Supervisor --}}
                            <div class="col-md-5"></div>
                        </div>

                        {{-- CC Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.cc.items :cc-id="$cc->id"/>
                            </div>
                        </div>
                        <br>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:misc.actions table="site_cc" :table-id="$cc->id" :allow-add="Auth::user()->allowed2('edit.site.cc', $cc)"/>
                            </div>
                        </div>

                        {{-- Assigned Tasks --}}
                        

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/cc" class="btn default">Back</a>

                            @php
                                $siteDisabled = (int) $cc->site?->status === -2;
                            @endphp

                            @if (!$siteDisabled)
                                @if (!$cc->master && Auth::user()->allowed2('edit.site.cc', $cc) && in_array((int) $cc->status, [2, -1], true))
                                    <button type="button" class="btn green" onclick="updateCcStatus(1)">Make Active</button>
                                @endif

                                @if (!$cc->master && Auth::user()->allowed2('del.site.cc', $cc) && in_array((int) $cc->status, [1, 2], true))
                                    <button type="button" class="btn red" onclick="updateCcStatus(-1)">Disable</button>
                                @endif
                            @endif
                            @if (Auth::user()->allowed2('edit.site.cc', $cc))
                                <button id="submit" type="submit" form="site-cc-form" name="save" class="btn green">Save</button>
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
            /* Select2 */
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});

        });

        function updateCcStatus(status) {
            $.ajax({
                url: '/site/cc/{{ $cc->id }}/update',
                type: 'PATCH',
                data: {status: status},
                success: function () {
                    window.location.href = '/site/cc/{{ $cc->id }}';
                },
                error: function () {
                    alert('Failed to update CC status');
                }
            });
        }
    </script>
@stop
