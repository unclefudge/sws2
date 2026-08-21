@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Weekly Planner</span></li>
    </ul>
@stop

@section('content')
    <livewire:planner.weekly-planner :date="$date" :supervisor-id="(string)$supervisor_id" :site-id="$site_id ? (int)$site_id : null" :site-start="$site_start" :supervisors="$supervisors" :preview="$preview"/>
@stop
