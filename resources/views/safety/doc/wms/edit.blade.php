@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/wms">SWMS</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Statement</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <livewire:safety.wms.editor :doc-id="$doc->id"/>
    </div>
@stop
