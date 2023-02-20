@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Site Supervisor Tasks</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Site Supervisor Tasks</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <b>The following tasks have been created due to your response on Site Checkin - {{ $site->name }}</b><br><br>
                        <ul>
                            @foreach ($todo_created as $todo_id => $text)
                                <li><a href="/todo/{{$todo_id}}">{{ $text }}</a></li>
                            @endforeach
                        </ul>
                        <br><br>
                        <a class="btn default" href="/dashboard">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop

