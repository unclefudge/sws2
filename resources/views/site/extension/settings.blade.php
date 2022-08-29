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

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Contract Time Extensions Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteExtensionCategories', ['method' => 'POST', 'action' => ['Site\SiteExtensionController@updateSettings',], 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <div class="form-body">

                            <h3>Extend Reasons</h3>
                            <hr class="field-hr">
                            @foreach ($cats as $cat)
                                <div class="row">
                                    <div class="col-md-1"><span class="pull-right" style="margin-top: 5px"> {{ $cat->order }}. &nbsp; </span></div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError("cat-$cat->id", $errors) !!}">
                                            {!! Form::text("cat-$cat->id", $cat->name, ['class' => 'form-control', 'id' => "cat-$cat->id"]) !!}
                                            {!! fieldErrorMessage("cat-$cat->id", $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="/site/extension/settings/del/{{ $cat->id }}" style="margin-left: 30px"><i class="fa fa-times font-red"></i> </a>
                                    </div>
                                </div>
                                @if (!$loop->last)
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                @endif
                            @endforeach

                            <div class="row" style="{{ ($errors->has('add_field_name')) ? 'display: none' : '' }}">
                                <div class="col-md-12">
                                    <button class="btn blue" id="btn-add-item">Add another option</button>
                                </div>
                            </div>

                            {{-- Additiona field --}}
                            <div style="{{ ($errors->has('add_field_name')) ? '' : 'display: none' }}" id="add-items">
                                <input type="hidden" name="add_field" id="add_field" value="{{ ($errors->has('add_field_name')) ? 1 : 0 }}">
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                <div class="row">
                                    <div class="col-md-1"><span class="pull-right" style="margin-top: 5px"> {{ count($cats) +1 }}. &nbsp; </span></div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('add_field_name', $errors) !!}">
                                            {!! Form::text('add_field_name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('add_field_name', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="form-actions right">
                                <a href="/site/supply" class="btn default"> Back</a>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
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

