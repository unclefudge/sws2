@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Create Document</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create Site Plan </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('sitedoc', ['action' => 'Site\SiteDocController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')
                        {!! Form::hidden('create', 'true') !!}
                        {!! Form::hidden('company_id', Auth::user()->company_id) !!}
                        {!! Form::hidden('type', 'PLAN', ['class' => 'form-control', 'id' => 'type']) !!}

                        <div class="alert alert-danger alert-dismissable" style="display: none;" id="multifile-error">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            <i class="fa fa-warning"></i><strong> Error(s) have occured</strong>
                            <ul>
                                <li>Before you can upload multiple files both the Site + Type fields are required</li>
                            </ul>
                        </div>

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}" id="site_id_form">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        @if (!Auth::user()->company->subscription && Auth::user()->company->parent_company == '3')
                                            {!! Form::select('site_id', Auth::user()->company->reportsTo()->sitesSelect('prompt', '1'), $site_id, ['class' => 'form-control select2']) !!}
                                        @else
                                            {!! Form::select('site_id', Auth::user()->company->sitesSelect('prompt'), $site_id, ['class' => 'form-control select2']) !!}
                                        @endif
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <div class="form-group">
                                        {!! Form::label('files', 'Files', ['class' => 'control-label']) !!}
                                        {!! Form::select('files', ['single' => 'Single File', 'multi' => 'Multiple Files'],
                                             'single', ['class' => 'form-control bs-select']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Multi File upload -->
                            <div id="multifile-div">
                                <div class="note note-warning">
                                    When uploading multiple documents please note the actual filename of the document will also be used as the name or 'title' of the document.
                                    <ul>
                                        <li>Once you have selected your files upload them by clicking
                                            <button class="btn dark btn-outline btn-xs" href="javascript:;"><i class="fa fa-upload"></i> Upload</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Select Files</label>
                                            <input id="multifile" name="multifile[]" type="file" multiple class="file-loading">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Single File upload -->
                            <div id="singlefile-div" style="display: none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                            <label class="control-label">Select File</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            {!! fieldErrorMessage('singlefile', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <h3 class="form-section"></h3>
                                <!-- Notes -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                            {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('notes', '', ['rows' => '2', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('notes', $errors) !!}
                                            <span class="help-block"> For internal use only </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <button type="submit" name="back" value="back" class="btn default"> Back</button>
                                <button type="submit" name="save" value="save" class="btn green" id="save" style="display: none;">Save</button>
                            </div>
                        </div>
                    </div> <!--/form-body-->
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <!--<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>-->
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({
            placeholder: "Select Site",
        });

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

        /* Bootstrap Fileinput */
        $("#multifile").fileinput({
            uploadUrl: "/site/doc/upload/", // server upload action
            uploadAsync: true,
            allowedFileExtensions: ["pdf"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn red",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn dark",
            uploadIcon: "<i class=\"fa fa-upload\"></i> ",
            uploadExtraData: {
                "site_id": site_id,
                "type": type,
            },
            layoutTemplates: {
                main1: '<div class="input-group {class}">\n' +
                '   {caption}\n' +
                '   <div class="input-group-btn">\n' +
                '       {remove}\n' +
                '       {upload}\n' +
                '       {browse}\n' +
                '   </div>\n' +
                '</div>\n' +
                '<div class="kv-upload-progress hide" style="margin-top:10px"></div>\n' +
                '{preview}\n'
            },
        });

        $('#multifile').on('filepreupload', function (event, data, previewId, index, jqXHR) {
            data.form.append("site_id", $("#site_id").val());
            data.form.append("type", $("#type").val());
        });

        // Toggle between Single + Multi file upload inputs
        $("#files").change(function () {
            $('#singlefile-div').toggle();
            $('#multifile-div').toggle();

            // If Multi verify Site + Type fields are completed
            if ($("#files").val() == 'multi') {
                $('#singlefile-div').hide();
                $('#save').hide();
                $("#typeform").removeClass('has-error');
                $("#site_id_form").removeClass('has-error');
                if ($("#type").val() == '')
                    $("#type_form").addClass('has-error');
                if ($("#site_id").val() == '')
                    $("#site_id_form").addClass('has-error');
                if ($("#site_id").val() == '' || $("#type").val() == '') {
                    $('#multifile-div').hide();
                    $('#multifile-error').show();
                }
            } else {
                $('#singlefile-div').show();
                $('#save').show();
                $('#multifile-div').hide();
                $('#multifile-error').hide();
            }
        });


        // On load verify Site ID + Type fields are set otherwise hide multi upload
        if ($("#files").val() == 'multi' && ($("#site_id").val() == '' || $("#type").val() == '')) {
            $('#multifile-div').hide();
        }
        // On load verify File upload type and show right div
        if ($("#files").val() == 'single') {
            $('#save').show();
            $('#singlefile-div').show();
            $('#multifile-div').hide();
        }


        // On Change determine if Site ID + Type fields are valid for multi file upload
        $("#site_id").change(function () {
            if ($("#files").val() == 'multi') {
                if ($("#site_id").val() == '') {
                    $("#site_id_form").addClass('has-error');
                    if ($("#files").val() == 'multi') {
                        $('#multifile-div').hide();
                        $('#multifile-error').show();
                    }
                } else {
                    $("#site_id_form").removeClass('has-error');
                    if ($("#type").val() != '') {
                        $('#multifile-div').show();
                        $('#multifile-error').hide();
                    }
                }
            }
        });

        // On Change determine if Site ID + Type fields are valid for multi file upload
        $("#type").change(function () {
            if ($("#files").val() == 'multi') {
                if ($("#type").val() == '') {
                    $("type_form").addClass('has-error');
                    $('#multifile-div').hide();
                    $('#multifile-error').show();
                } else {
                    $("#type_form").removeClass('has-error');
                    if ($("#site_id").val() != '') {
                        $('#multifile-div').show();
                        $('#multifile-error').hide();
                    }
                }
            }
        });

    });

</script>
@stop