@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            <li><a href="/site/upcoming/compliance">Upcoming Jobs</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Settings</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Upcoming Jobs Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteUpcomingSettings', ['method' => 'POST', 'action' => ['Site\SiteUpcomingComplianceController@updateSettings'], 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <div class="form-body">
                            <ul class="nav nav-tabs ">
                                <li class="">
                                    <a href="/site/upcoming/compliance/settings/stages"> Stage Options </a>
                                </li>
                                <li class="">
                                    <a href="/site/upcoming/compliance/settings/steel"> STEEL Options </a>
                                </li>
                                <li class="active">
                                    <a> Additional Sites </a>
                                </li>
                            </ul>

                            {{-- Additional Sites --}}
                            <h3>Additional Sites (manually)</h3>
                            <hr class="field-hr">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('email_list', $errors) !!}">
                                        {!! Form::label('special_sites', 'Sites', ['class' => 'control-label']) !!}
                                        {!! Form::select('special_sites', Auth::user()->company->sitesSelect(), $special_sites, ['class' => 'form-control select2', 'name' => 'special_sites[]', 'id'  => 'special_sites', 'title' => 'Select one or more sites', 'multiple']) !!}
                                        {!! fieldErrorMessage('special_sites', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/site/upcoming/compliance" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $("#special_sites").select2({placeholder: "Select one or more", width: '100%'});
            $("#email_list").select2({placeholder: "Select one or more", width: '100%'});

            $(".hoverDiv").click(function (e) {
                //var colours = ['col2-blue-3B67BD', 'col2-green-0A9A5B', 'col2-yellow-FFEC00', 'col2-orange-F77402', 'col2-red-ED0F17', 'col2-purple-A32AA2']
                var colours = ['col-blue-C5D1EC', 'col-green-B5E2CD', 'col-yellow-FFFAAE', 'col-orange-FDD7B1', 'col-red-FBB6B9', 'col-purple-E4BFE4'];
                var event_id = e.target.id.split('_');
                //alert(e.target.id);
                var field = event_id[0];
                var field_id = event_id[1];
                var colour = event_id[2];
                //alert("f:" + field + " id:" + field_id + " c:" + colour);


                // Update colour
                $("#" + field + "-" + field_id + "-colour").val(colour);
                // Grey out all the colour options
                for (var i = 0; i < colours.length; i++)
                    $("#" + field + "_" + field_id + "_" + colours[i] + "_i").css('opacity', 0.15);
                // Highlight selected colour
                for (var i = 0; i < colours.length; i++) {
                    console.log(colours[i] + " - " + colour);
                    if (colours[i] == colour)
                        $("#" + field + "_" + field_id + "_" + colour + "_i").css('opacity', 1);
                }
            });

            // Add extra items
            $(".btn-add-item").click(function (e) {
                e.preventDefault();
                var event_id = e.target.id.split('-');
                var field = event_id[0];
                //alert(field);
                $("#" + field + "-add-items").show();
                $("#" + field + "-add_btn").hide();
                $("#" + field + "-addfield").val(1);
            });
        });
    </script>
@stop

