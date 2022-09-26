@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/form">Forms</a><i class="fa fa-circle"></i></li>
        <li><a href="/form/template">Form Templates</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Form Template </span>
                            <span class="caption-helper"> - ID: {{ $template->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($template, ['method' => 'PATCH', 'action' => ['Misc\Form\FormTemplateController@update', $template->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('Description', $errors) !!}">
                                        {!! Form::label('description', 'Description', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
                                        {!! fieldErrorMessage('description', $errors) !!}
                                    </div>
                                </div>
                            </div>





                            <div class="form-actions right">
                                <a href="/form/template" class="btn default"> Back</a>
                                <button type="submit" name="save" value="save" class="btn green">Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $template->displayUpdatedBy() !!}
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>
    $(document).ready(function () {

    });

</script>
@stop