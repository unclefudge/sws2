@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-building"></i> Site Profile</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li>
            <a href="/">Home</a><i class="fa fa-circle"></i>
        </li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li>
                <a href="/site">Sites</a><i class="fa fa-circle"></i>
            </li>
        @endif
        <li>
            <span>Profile</span>
        </li>
    </ul>
    @stop

    @section('content')
            <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <div class="profile">
            <div class="tabbable-line tabbable-full-width">
                <ul class="nav nav-tabs">
                    <li class="{{ $tabs['0'] == 'profile' ? 'active' : '' }}">
                        <a href="#tab_profile" data-toggle="tab"> Profile </a>
                    </li>
                    @if (Auth::user()->allowed2('edit.site', $site))
                        <li class="{{ $tabs['0'] == 'settings' ? 'active' : '' }}">
                            <a href="#tab_settings" data-toggle="tab"> Settings </a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">
                    <!-- tab main -->
                    @include('site._tab-profile')
                            <!-- tab settings -->
                    @include('site._tab-settings')
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $site->displayUpdatedBy() !!}
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css">
@stop

@section('page-level-styles-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
    <script src="/assets/global/plugins/gmaps/gmaps.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop