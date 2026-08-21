@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Site Roster</span></li>
    </ul>
@stop

@section('content')
    <livewire:planner.attendance-planner :date="$date" :site-id="$site_id ? (int)$site_id : null" :supervisor-id="$supervisor_id ? (string)$supervisor_id : null" :site-start="$site_start" :preview="$preview"/>
@stop
