@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
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
    @php
        $itemsTotal = $qa->items->count();
        $itemsDone = $qa->items->where('status', '!=', 0)->count();
    @endphp

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Quality Assurance Report</span>
                            <span class="caption-helper">ID: {{ $qa->id }}</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div class="page-content-inner">
                            @if ($qa->status && $itemsTotal === $itemsDone)
                                <div class="col-md-12 note note-warning">
                                    <p>All items have been completed and report requires <button type="button" class="btn btn-xs btn-outline dark disabled">Sign Off</button> at the bottom</p>
                                </div>
                            @endif

                            <div class="row hidden-sm hidden-xs">
                                <div class="col-xs-7"><img src="/img/logo-capecod2-med.png"></div>
                                <div class="col-xs-5"><p>JOB NAME: {{ $qa->site?->name }}<br>ADDRESS: {{ $qa->site?->full_address }}</p></div>
                            </div>

                            <div class="row" style="padding-top:10px">
                                <div class="col-xs-12">
                                    <br>
                                    <h2 style="margin:0">
                                        <b>{{ $qa->name }}</b>

                                        @if ($qa->master)
                                            <span class="pull-right font-red hidden-sm hidden-xs">TEMPLATE</span>
                                            <span class="text-center font-red visible-sm visible-xs">TEMPLATE</span>
                                        @elseif ((int)$qa->status === -1)
                                            <span class="pull-right font-red hidden-sm hidden-xs">NOT REQUIRED</span>
                                            <span class="text-center font-red visible-sm visible-xs">NOT REQUIRED</span>
                                        @elseif ((int)$qa->status === 0)
                                            <span class="pull-right font-red hidden-sm hidden-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                        @elseif ((int)$qa->status === 4)
                                            <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                            <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                        @elseif ((int)$qa->status === 5)
                                            <span class="pull-right font-red hidden-sm hidden-xs">OWNERS WORKS</span>
                                            <span class="text-center font-red visible-sm visible-xs">OWNERS WORKS</span>
                                        @endif
                                    </h2>
                                </div>

                                <div class="col-xs-12"><p>Item Tasks: {{ $qa->tasksSBC() }}</p></div>
                            </div>

                            {{-- Page-specific component --}}
                            <livewire:site.qa.items :qa-id="$qa->id"/>

                            {{-- Reusable component --}}
                            @if (!$qa->master)
                                <livewire:misc.actions table="site_qa" :table-id="$qa->id" :allow-add="(int)$qa->status === 1 && Auth::user()->allowed2('edit.site.qa', $qa)"/>
                                <hr>
                            @endif

                            {{-- Page-specific workflow/sign-off component --}}
                            <livewire:site.qa.workflow :qa-id="$qa->id"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
