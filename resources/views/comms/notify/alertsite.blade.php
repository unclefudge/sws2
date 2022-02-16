@extends('layout-basic')
@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">Alert Message</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @foreach ($site->notify() as $notify)
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>{!! $notify->name !!}</h3>
                                    <p style="line-height: 1.5">{!! nl2br($notify->info) !!}</p>
                                </div>
                            </div>
                            <hr class="field-hr">
                            @if (!$notify->isOpenedBy(Auth::user()))
                                {!! $notify->markOpenedBy(Auth::user()) !!}
                            @endif
                        @endforeach

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a href="{{ $intended_url }}" class="btn green btn-lg"> Ok</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop