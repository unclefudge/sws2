@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Trades</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size:14px;
        font-weight:600;
        color:#333 !important;
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <livewire:planner.trade-list/>
            </div>
        </div>
    </div>
@stop
