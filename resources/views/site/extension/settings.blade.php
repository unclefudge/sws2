@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/extension">Contract Time Extensions</a><i class="fa fa-circle"></i></li>
        <li><span>Settings</span></li>
    </ul>
@stop

@section('page-level-plugins-head')
    <link href="/css/sws-settings.css" rel="stylesheet" type="text/css"/>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Contract Time Extensions Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <livewire:site.extension.settings/>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

