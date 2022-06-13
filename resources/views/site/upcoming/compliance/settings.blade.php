@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.upcoming.compliance'))
            <li><a href="/manage/report/upcoming_compliance">Upcoming Jobs Compliance Data</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Upcoming Jobs Compliance Data Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteUpcomingSettings', ['method' => 'POST', 'action' => ['Site\SiteUpcomingComplianceController@updateSettings'], 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <div class="form-body">
                            <?php
                            //$colours = ['col2-blue-3B67BD', 'col2-green-0A9A5B', 'col2-yellow-FFEC00', 'col2-orange-F77402', 'col2-red-ED0F17', 'col2-purple-A32AA2']
                            //$fields = ['cc' => "CC", 'fc_plans' => "FC Plans", 'fc_struct' => 'FC Structural'];
                            $colours = ['col-blue-C5D1EC', 'col-green-B5E2CD', 'col-yellow-FFFAAE', 'col-orange-FDD7B1', 'col-red-FBB6B9', 'col-purple-E4BFE4'];
                            $fields = ['opt' => 'Stage Options'];
                            ?>

                            @foreach ($fields as $field => $title)
                                <h3>{{ $title }}</h3>
                                <hr class="field-hr">
                                <?php $recs = $settings->where('field', $field) ?>
                                @foreach ($settings->where('field', $field)->sortBy('order') as $setting)
                                    <div class="row">
                                        <div class="col-md-1"><span class="pull-right" style="margin-top: 5px"> {{ $setting->order }}. &nbsp; </span></div>
                                        <div class="col-md-2">
                                            <div class="form-group {!! fieldHasError("$field-$setting->id", $errors) !!}">
                                                {!! Form::text("$field-$setting->id", $setting->name, ['class' => 'form-control', 'id' => "$field-$setting->id"]) !!}
                                                {!! fieldErrorMessage("$field-$setting->id", $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            @foreach ($colours as $colour)
                                                <?php $opacity = ($colour == $setting->colour) ? '1' : '0.15' ?>
                                                <span class="hoverDiv" style="padding: 3px" id="{{$field}}_{{$setting->id}}_{{$colour}}_s"><img src="/img/{{$colour}}.png" style="opacity: {{$opacity}};" id="{{$field}}_{{$setting->id}}_{{$colour}}_i"></span>
                                            @endforeach
                                            <a href="/manage/report/upcoming_compliance/settings/del/{{ $setting->id }}" style="margin-left: 30px"><i class="fa fa-times font-red"></i> </a>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="hidden" name="{{$field}}-{{$setting->id}}-colour" id="{{$field}}-{{$setting->id}}-colour" value="{{$setting->colour}}">
                                        </div>
                                    </div>
                                    @if ($loop->last)
                                        <br>
                                    @else
                                        <hr style="padding: 0px; margin: 10px 0px 10px 0px;">
                                    @endif
                                @endforeach
                            @endforeach

                            <div class="row" style="{{ ($errors->has('add_field_name')) ? 'display: none' : '' }}">
                                <div class="col-md-12">
                                    <button class="btn blue" id="btn-add-item">Add another option</button>
                                </div>
                            </div>

                            {{-- Additiona field --}}
                            <div style="{{ ($errors->has('add_field_name')) ? '' : 'display: none' }}" id="add-items">
                                <input type="hidden" name="add_field" id="add_field" value="{{ ($errors->has('add_field_name')) ? 1 : 0 }}">

                                <div class="row">
                                    <div class="col-md-1"><span class="pull-right" style="margin-top: 5px"> {{ count($settings)+1 }}. &nbsp; </span></div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('add_field_name', $errors) !!}">
                                            {!! Form::text('add_field_name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('add_field_name', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @foreach ($colours as $colour)
                                            <span class="hoverDiv" style="padding: 3px" id="add_field_{{$colour}}_s"><img src="/img/{{$colour}}.png" style="opacity: 0.2" id="add_field_{{$colour}}_i"></span>
                                        @endforeach
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="add-field-colour" id="add-field-colour" value="">
                                    </div>
                                </div>
                            </div>
                            <br>
                                <h3>Email list</h3>
                                <hr class="field-hr">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('email_list', $errors) !!}">
                                            {!! Form::label('email_list', 'Email List', ['class' => 'control-label']) !!}
                                            {!! Form::select('email_list', ['' => 'Select user(s)'] + Auth::user()->company->staffSelect('select', '1'), $email_list, ['class' => 'form-control select2', 'name' => 'email_list[]', 'id'  => 'email_list', 'title' => 'Select one or more users', 'multiple']) !!}
                                            {!! fieldErrorMessage('email_list', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                            {{-- CC --}}
                            {{--}}
                        <h3>CC</h3>
                        <hr class="field-hr">

                        @foreach ($cc as $setting)
                            <div class="row">
                                <div class="col-md-2">
                                    {!! Form::text("cc-$setting->id", $setting->name, ['class' => 'form-control', 'id' => "cc-$setting->id"]) !!}
                                </div>
                                <div class="col-md-4">
                                    @foreach ($colours as $colour)
                                        @if ($colour == $setting->colour)
                                            <span class="hoverDiv" style="padding: 3px" id="cc_{{$setting->id}}_{{$colour}}_s"><img src="/img/{{$colour}}.png" id="cc_{{$setting->id}}_{{$colour}}_i"></span>
                                        @else
                                            <span class="hoverDiv" style="padding: 3px" id="cc_{{$setting->id}}_{{$colour}}_s"><img src="/img/{{$colour}}.png" style="opacity: 0.2;" id="cc_{{$setting->id}}_{{$colour}}_i"></span>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="cc-{{$setting->id}}-colour" id="cc-{{$setting->id}}-colour" value="{{$setting->colour}}">
                                </div>
                            </div>
                            @if ($loop->last)
                                <br>
                            @else
                                <hr style="padding: 0px; margin: 10px 0px 10px 0px;">
                            @endif
                        @endforeach
                        --}}

                            <div class="form-actions right">
                                <a href="/manage/report/upcoming_compliance" class="btn default"> Back</a>
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>
    $(document).ready(function () {
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
        $("#btn-add-item").click(function (e) {
            e.preventDefault();
            $("#add-items").show();
            //$(".add-item").show();
            $("#btn-add-item").hide();
            $("#add_field").val(1);
        });
    });
</script>
@stop

