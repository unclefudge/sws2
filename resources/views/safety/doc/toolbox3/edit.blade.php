@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-life-ring"></i> Toolbox Talks</h1>
    </div>
@stop
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/toolbox3">Toolbox Talks</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Talk</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        <div class="mt-element-step hidden-sm hidden-xs">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first done">
                    <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                    <div class="mt-step-content font-grey-cascade">Create Talk</div>
                </div>
                <div class="col-md-3 mt-step-col active">
                    <div class="mt-step-number bg-white font-grey">2</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                    <div class="mt-step-content font-grey-cascade">Add content</div>
                </div>
                <div class="col-md-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">3</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Users</div>
                    <div class="mt-step-content font-grey-cascade">Assign Users</div>
                </div>
                <div class="col-md-3 mt-step-col last">
                    <div class="mt-step-number bg-white font-grey">4</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Archive</div>
                    <div class="mt-step-content font-grey-cascade">Talk completed</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Edit Talk</span>
                            <span class="caption-helper">ID: {{ $talk->id }}</span>
                        </div>
                        <div class="actions">
                            <a class="btn blue btn-sm" href="#modal_upload" data-original-title="Add" data-toggle="modal">
                                <i class="fa fa-upload"></i> Upload Image
                            </a>

                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($talk, ['method' => 'PATCH', 'action' => ['Safety\ToolboxTalk3Controller@update', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}

                        @include('form-error')

                        <input type="hidden" name="talk_id" id='talk_id' value="{{ $talk->id }}">
                        <input type="hidden" name="version" value="{{ $talk->version }}">
                        <input type="hidden" name="toolbox_type" value="none">
                        <input type="hidden" name="for_company_id" value="{{ Auth::user()->company_id }}">
                        <input type="hidden" name="status" id="status" value="2">
                        <input type="hidden" name="overview" id='overview' value="{{ $talk->overview }}">
                        <input type="hidden" name="hazards" id='hazards' value="{{ $talk->hazards }}">
                        <input type="hidden" name="controls" id='controls' value="{{ $talk->controls }}">
                        <input type="hidden" name="further" id='further' value="{{ $talk->further }}">

                        <div class="form-body">
                            <div class="row">
                                @if($talk->master)
                                    <div class="col-md-12">
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Template</h3>
                                    </div>
                                @endif
                            </div>
                            <div class="row hoverDiv" style="padding: 0px; min-height: 0px">
                                <div class="col-md-9" id="name-show">
                                    <h1 style="margin: 0 0 2px 0">{{ $talk->name }}
                                        <small class="font-grey-silver" style="vertical-align: text-top"> &nbsp; <i class="fa fa-pencil"></i></small>
                                    </h1>
                                </div>
                                <div class="col-md-9" id="name-edit" style="display: none">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name of Toolbox Talk', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $talk->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3 text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa"><span class="font-grey-salsa">version {{ $talk->version }} </span>
                                </div>
                            </div>
                            <hr style="margin: 2px 0 15px 0">

                            @if ($talk->uploadedFilesURL())
                                <div class="row">
                                    <div class="col-md-12 note note-warning">
                                        <h3>List of uploaded files</h3>
                                        <p>To use any of the following files in your Toolbox Talk you'll need to:</p>
                                        <ol>
                                            <li>Copy to link to Clipboard by highlighting file and using CTL + C to copy</li>
                                            <li>Click insert media button <i class="fa fa-picture-o"></i></li>
                                            <li>Paste to URL field using CTL + V to paste</li>
                                        </ol>

                                        <br>
                                        @foreach($talk->uploadedFilesURL() as $file)
                                            <b>{{ $file }}</b><br>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-12">
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">OVERVIEW</h5></div>
                                    <div><textarea name="ck_overview" id="ck_overview" rows="20" cols="80">{{ $talk->overview }}</textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE HAZARDS?</h5></div>
                                    <div><textarea name="ck_hazards" id="ck_hazards" rows="20" cols="80">{{ $talk->hazards }}</textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE CONTROLS / WHAT ACTIONS ARE REQUIRED?</h5>
                                    </div>
                                    <div><textarea name="ck_controls" id="ck_controls" rows="20" cols="80">{{ $talk->controls }}</textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
                                    <textarea name="ck_further" id="ck_further" rows="20" cols="80">{{ $talk->further }}</textarea>
                                </div>
                            </div>
                            <br>

                            <div class="form-actions right">
                                <a href="/safety/doc/toolbox3" class="btn default"> Back</a>
                                <button type="submit" class="btn dark"> Save Draft</button>
                                @if(!$talk->master)
                                    <a data-original-title="Assign Users" data-toggle="modal" href="#modal_final">
                                        <button type="button" class="btn green" id="final"> Assign Users</button>
                                    </a>
                                @else
                                    <button type="button" class="btn green" data-dismiss="modal" id="active">Make Active</button>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Users Modal -->
    <div id="modal_final" class="modal fade bs-modal-sm" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Assign Users</b></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center">You are about leave DRAFT mode and begin to assign USERS.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> You will no longer be able to modify this talk anymore.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn green" data-dismiss="modal" id="continue">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Photo Modal -->
    <div id="modal_upload" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content form">
                {!! Form::model($talk, ['method' => 'POST', 'action' => ['Safety\ToolboxTalk3Controller@uploadMedia', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'upload_form']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Upload Photo</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                    <label class="control-label">Select File</label>
                                    <input id="singlefile" name="singlefile" type="file" accept="image/*" class="file-loading">
                                    {!! fieldErrorMessage('singlefile', $errors) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn green" data-dismiss="modal" id="upload_media">Upload</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <script src="https://cdn.ckeditor.com/4.7.0/standard-all/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            header: $('meta[name="_token"]').attr('content')
        })

        CKEDITOR.replace('ck_overview', {
            customConfig: '/js/libs/ckeditor/customConfig.js',
        });

        CKEDITOR.replace('ck_hazards', {
            customConfig: '/js/libs/ckeditor/customConfig.js',
        });

        CKEDITOR.replace('ck_controls', {
            customConfig: '/js/libs/ckeditor/customConfig.js',
        });

        CKEDITOR.replace('ck_further', {
            customConfig: '/js/libs/ckeditor/customConfig.js',
            height: 200, // Make the editing area bigger than default.
            /*toolbar: [
             { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
             { name: 'styles', items: [ 'Styles', 'Format' ] },
             { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ] },
             { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
             { name: 'links', items: [ 'Link', 'Unlink' ] },
             { name: 'insert', items: [ 'Image', 'EmbedSemantic', 'Table' ] },
             { name: 'tools', items: [ 'Maximize' ] },
             { name: 'editing', items: [ 'Scayt' ] },
             { name: 'document', items: [ 'Source' ] }
             ],
             customConfig: '',
             extraPlugins: 'autoembed,embedsemantic,image2,',//uploadimage,uploadfile',
             removePlugins: 'image', // Remove the default image plugin because image2, which offers captions for images*/
        });


        $('#name-show').on('click', function () {
            $('#name-show').hide();
            $('#name-edit').show();
        });

        $('#talk_form').on('submit', function (e) {
            e.preventDefault(e);
            submit_form();
        });

        $('#active').on('click', function () {
            $('#status').val(1);
            submit_form();
        });

        $('#continue').on('click', function () {
            $('#status').val(1);
            submit_form();
        });

        $('#upload_media').on('click', function () {
            $('#overview').val(CKEDITOR.instances.ck_overview.getData());
            $('#hazards').val(CKEDITOR.instances.ck_hazards.getData());
            $('#controls').val(CKEDITOR.instances.ck_controls.getData());
            $('#further').val(CKEDITOR.instances.ck_further.getData());
            $.ajax({
                type: "POST",
                url: '/safety/doc/toolbox3/' + $('#talk_id').val(),
                data: $("#talk_form").serialize(),
                dataType: 'json',
                success: function (data) {
                    document.getElementById('upload_form').submit();
                },
                error: function (data) {
                    alert('Failed to upload media talk id:' + $('#talk_id').val());
                }
            })
        });

        function submit_form() {
            $('#overview').val(CKEDITOR.instances.ck_overview.getData());
            $('#hazards').val(CKEDITOR.instances.ck_hazards.getData());
            $('#controls').val(CKEDITOR.instances.ck_controls.getData());
            $('#further').val(CKEDITOR.instances.ck_further.getData());
            $.ajax({
                type: "POST",
                url: '/safety/doc/toolbox3/' + $('#talk_id').val(),
                data: $("#talk_form").serialize(),
                dataType: 'json',
                success: function (data) {
                    window.location = "/safety/doc/toolbox3/" + $('#talk_id').val() + '/edit';
                },
                error: function (data) {
                    ///alert('Failed to save Toolbox talk id:' + $('#talk_id').val());
                    swal({
                        title: 'Failed to save Toolbox',
                        text: "<b>We apologise but we were unable to save your Toolbox Talk (id:" + $('#talk_id').val() + ")<br><br>Please try again but if the problem persists let us know.</b>",
                        html: true
                    });
                }
            })
        }

        /* Bootstrap Fileinput */
        $("#singlefile").fileinput({
            showUpload: false,
            previewFileType: "image",
            //allowedFileExtensions: ['gif', 'jpg', 'png'],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn btn-danger",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn btn-info",
        });
    </script>
@stop

