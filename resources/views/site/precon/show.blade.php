@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/precon">Pre Con Review</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Pre Con Review</span>
                            <span class="caption-helper">ID: {{ $cc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form id="site-precon-form" method="POST" action="{{ action([\App\Http\Controllers\Site\SitePreConController::class, 'update'], $cc->id) }}" class="horizontal-form">
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
                                        {{-- Pre Con Review Stage --}}
                                        @php
                                            $reviewStage = ((int) $cc->status === -1 || (int) $cc->site?->status === -2) ? 'Disabled' : 'Active';
                                        @endphp

                                        <h2 style="margin: 0; padding-right: 20px">
                                            <span class="pull-right font-red hidden-sm hidden-xs">{{ strtoupper($reviewStage) }}</span>
                                            <span class="text-center font-red visible-sm visible-xs">{{ strtoupper($reviewStage) }}</span>
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
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>Lodgement Type</b></div>
                                                <div class="col-md-6">{{ $cc->site->lodgement_type ?? '-' }}</div>
                                            </div>
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>Plans by Others</b></div>
                                                <div class="col-md-6">{{ $cc->site->plans_by_others ?? '-' }}</div>
                                            </div>
                                            <div class="row" style="margin-bottom: 4px">
                                                <div class="col-md-6 text-right"><b>Ausgrid Quote Req?</b></div>
                                                <div class="col-md-6">{{ $cc->site->ausgrid_quote_required ?? '-' }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <br>
                                <h4>Engineering</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                @php
                                    $site = $cc->site;
                                @endphp
                                <div class="row">
                                    @foreach ([
                                        ['Floor Joists', 'Draftsperson FJ', $site?->fj_draftsperson, 'FJ Start Date', $site?->fj_start_date, 'FJ Completed Date', $site?->fj_received_date, $site?->fj_scheduled_received_date],
                                        ['Concrete', 'Engineer', $site?->concrete_engineer, 'Request Sent', $site?->concrete_sent_date, 'Design Rcvd', $site?->concrete_received_date, $site?->concrete_scheduled_received_date],
                                    ] as [$section, $personLabel, $person, $startLabel, $start, $receivedLabel, $actual, $scheduled])
                                        <div class="col-md-6">
                                            <h5>{{ $section }}</h5>
                                            @if ($person || $start || $actual || $scheduled)
                                                <p><strong>{{ $personLabel }}</strong> {{ $person ?? '-' }}</p>
                                                <p><strong>{{ $startLabel }}</strong> {{ $start?->format('d/m/Y') ?? '-' }}</p>
                                                <p><strong>{{ $receivedLabel }}</strong>
                                                    @if ($actual)
                                                        {{ $actual->format('d/m/Y') }}
                                                    @elseif ($scheduled)
                                                        <span class="font-grey-silver">{{ $scheduled->format('d/m/Y') }} (scheduled)</span>
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <br>
                                <h4>Project Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div style="white-space: pre-wrap">{{ $site?->project_notes ?? '-' }}</div>
                                {{--}}<br>
                                <x-form.textarea name="notes" id="notes" label="Notes" :value="$cc->notes"/>--}}
                            </form>
                            <livewire:misc.attachments context="site-precon" :context-id="$cc->id"/>
                        </div>
                        <div class="row">
                            {{-- Assigned Supervisor --}}
                            <div class="col-md-5"></div>
                        </div>

                        {{-- Pre Con Review Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.pre-con.items :review-id="$cc->id"/>
                            </div>
                        </div>
                        <br>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:misc.actions table="site_pre_con" :table-id="$cc->id" :allow-add="Auth::user()->allowed2('edit.site.precon', $cc)"/>
                            </div>
                        </div>

                        {{-- Assigned Tasks --}}
                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/precon" class="btn default">Back</a>

                            @php
                                $siteDisabled = (int) $cc->site?->status === -2;
                            @endphp

                            @if (!$siteDisabled)
                                @if (!$cc->master && Auth::user()->allowed2('edit.site.precon', $cc) && in_array((int) $cc->status, [2, -1], true))
                                    <button type="button" class="btn green" onclick="updateCcStatus(1)">Make Active</button>
                                @endif

                                @if (!$cc->master && Auth::user()->allowed2('del.site.precon', $cc) && in_array((int) $cc->status, [1, 2], true))
                                    <button type="button" class="btn red" onclick="updateCcStatus(-1)">Disable</button>
                                @endif
                            @endif
                            @if (Auth::user()->allowed2('edit.site.precon', $cc))
                                <button id="submit" type="submit" form="site-precon-form" name="save" class="btn green">Save</button>
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

        function updateCcStatus(status) {
            $.ajax({
                url: '/site/precon/{{ $cc->id }}/update',
                type: 'PATCH',
                data: {status: status},
                success: function () {
                    window.location.href = '/site/precon/{{ $cc->id }}';
                },
                error: function () {
                    alert('Failed to update Pre Con Review status');
                }
            });
        }
    </script>
@stop
