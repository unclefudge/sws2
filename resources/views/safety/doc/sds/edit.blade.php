@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span></span></li>
        <li><a href="/safety/doc/sds">Safety Data Sheets</a><i class="fa fa-circle"></i></li>
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
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Edit SDS </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($sds, ['method' => 'PATCH', 'action' => ['Safety\SdsController@update', $sds->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            {{-- Name + Category --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('categories', $errors) !!}">
                                        {!! Form::label('categories', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('categories', App\Models\Safety\SafetyDocCategory::sdsCats('all'), null, ['class' => 'form-control select2', 'multiple', 'required', 'title' => 'Check all applicable categories',  'name' => 'categories[]', 'id' => 'categories']) !!}
                                        {!! fieldErrorMessage('categories', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Manufacturer + Date --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('manufacturer', $errors) !!}">
                                        {!! Form::label('manufacturer', 'Manufacturer', ['class' => 'control-label']) !!}
                                        {!! Form::text('manufacturer', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('manufacturer', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                        {!! Form::label('date', 'Date', ['class' => 'control-label']) !!}
                                        <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-reset>
                                            <input type="text" value="{{ ($sds->date) ? $sds->date->format('d/m/Y') : '' }}" class="form-control" style="background:#FFF" id="date" name="date">
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('date', $errors) !!}
                                    </div>

                                </div>
                            </div>

                            {{-- Application --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('application', $errors) !!}">
                                        {!! Form::label('application', 'Application:', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('application', null, ['rows' => 3, 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('application', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            {{-- Hazardous + Dangerous --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('hazardous', $errors) !!}">
                                        {!! Form::label('hazardous', 'Hazardous', ['class' => 'control-label']) !!}
                                        {!! Form::select('hazardous', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('hazardous', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('dangerous', $errors) !!}">
                                        {!! Form::label('dangerous', 'Dangerous', ['class' => 'control-label']) !!}
                                        {!! Form::select('dangerous', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('dangerous', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- SingleFile Upload --}} {{--}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                        <label class="control-label">Select File</label>
                                        <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singlefile', $errors) !!}
                                    </div>
                                </div>
                            </div> --}}

                            @if ($sds->attachment)
                                <div class="row" id="attachment_div">
                                    <div class="col-md-12">
                                            <b>SDS File</b><br> <a href="{{ $sds->attachment_url }}">{{ $sds->attachment }} </a>
                                        @if ($sds->status && $sds->attachment)
                                            &nbsp; &nbsp;<i class="fa fa-times font-red" id="delete"></i><br><br>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($sds->status)
                                <div class="row" id="uploadfile_div" style="@if ($sds->status && $sds->attachment) display:none @endif">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                            <label class="control-label">Select File</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            {!! fieldErrorMessage('singlefile', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif


                            <div class="form-actions right">
                                <a href="/safety/doc/sds" class="btn default"> Back</a>
                                <button type="submit" class="btn green">Save</button>
                            </div>
                        </div>
                    </div> <!--/form-body-->
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $sds->displayUpdatedBy() !!}
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
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
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Bootstrap Fileinput */
        $("#singlefile").fileinput({
            showUpload: false,
            allowedFileExtensions: ["pdf"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn btn-danger",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn btn-info",
        });

        $("#change_file").click(function () {
            $('#attachment-div').hide();
            $('#uploadfile-div').show();
        });

        $("#delete").click(function (e) {
            e.preventDefault();
            $('#delete_attachment').val(1);
            $('#uploadfile_div').show();
            $('#attachment_div').hide();
        });
    });

</script>
@stop