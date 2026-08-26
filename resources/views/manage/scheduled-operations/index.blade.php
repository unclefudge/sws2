@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        <li><span>Scheduled Operations</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <livewire:manage.scheduled-operations.dashboard/>
    </div>
@stop
