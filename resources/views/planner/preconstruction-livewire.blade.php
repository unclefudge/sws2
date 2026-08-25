@extends('layout')

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css">
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Pre-construction Planner</span></li>
    </ul>
@stop

@section('content')
    <livewire:planner.site-planner :date="$date" :site-id="$site_id ? (int)$site_id : null" :supervisor-id="$supervisor_id ? (string)$supervisor_id : null" :site-start="$site_start" :preview="$preview" planner-mode="preconstruction"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop
