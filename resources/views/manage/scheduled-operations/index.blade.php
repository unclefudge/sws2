@extends('layout')

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

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

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop
