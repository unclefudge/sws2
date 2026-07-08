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
                            <span class="caption-subject font-green-haze bold ">EDIT TALK (v3)</span>
                            <span class="caption-helper">ID: {{ $talk->id }}</span>
                        </div>
                        <div class="actions">
                            <a class="btn blue btn-sm" href="#modal_upload" data-original-title="Add" data-toggle="modal">
                                <i class="fa fa-upload"></i> Upload File
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
                        <input type="hidden" name="draft" id="draft" value="save">
                        {{--}}<input type="hidden" name="overview" id='overview' value="{{ $talk->overview }}">
                        <input type="hidden" name="hazards" id='hazards' value="{{ $talk->hazards }}">
                        <input type="hidden" name="controls" id='controls' value="{{ $talk->controls }}">
                        <input type="hidden" name="further" id='further' value="{{ $talk->further }}">--}}

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

                            @if ($talk->uploadedFiles())
                                <div class="row">
                                    <div class="col-md-12 note note-info">
                                        <h3>Uploaded files</h3>
                                        <p>
                                            Use the buttons below to copy a file link or insert an image/PDF directly into the toolbox talk.
                                        </p>

                                        <div class="row">
                                            @foreach($talk->uploadedFiles() as $file)
                                                <div class="col-md-3 col-sm-4">
                                                    <div class="thumbnail" style="min-height: 230px;">
                                                        @if($file['is_image'])
                                                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" style="height:120px; width:100%; object-fit:cover;">
                                                        @else
                                                            <div class="text-center" style="height:120px; padding-top:35px; background:#f5f5f5;">
                                                                <i class="fa fa-file-pdf-o fa-3x font-red"></i>
                                                            </div>
                                                        @endif

                                                        <div class="caption">
                                                            <strong title="{{ $file['name'] }}">
                                                                {{ \Illuminate\Support\Str::limit($file['name'], 28) }}
                                                            </strong>
                                                            <br><br>
                                                            <button type="button" class="btn btn-xs blue btn-copy-file" data-url="{{ e($file['url']) }}">
                                                                <i class="fa fa-copy"></i> Copy Link
                                                            </button>

                                                            @if($file['is_image'])
                                                                <button type="button" class="btn btn-xs green btn-insert-file" data-type="image" data-url="{{ e($file['url']) }}" data-name="{{ e($file['name']) }}">
                                                                    <i class="fa fa-picture-o"></i> Insert
                                                                </button>
                                                            @elseif(!empty($file['is_pdf']))
                                                                <button type="button" class="btn btn-xs green btn-insert-file" data-type="pdf" data-url="{{ e($file['url']) }}" data-name="{{ e($file['name']) }}">
                                                                    <i class="fa fa-file-pdf-o"></i> Insert PDF
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-12">
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">OVERVIEW</h5></div>
                                    <div><textarea name="overview" id="overview"></textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE HAZARDS?</h5></div>
                                    <div><textarea name="hazards" id="hazards"></textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE CONTROLS / WHAT ACTIONS ARE REQUIRED?</h5></div>
                                    <div><textarea name="controls" id="controls"></textarea></div>
                                    <br>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
                                    <div><textarea name="further" id="further"></textarea></div>
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

    <!-- Upload File Modal -->
    <div id="modal_upload" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content form">
                {!! Form::model($talk, ['method' => 'POST', 'action' => ['Safety\ToolboxTalk3Controller@uploadMedia', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'upload_form']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Upload Image or PDF</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                    <x-form.filepond id="singlefile" name="singlefile" label="Select File" accept="image/*,application/pdf" :multiple="false"/>
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
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.1.1/ckeditor5.css"/>
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type@^1/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview@^4/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/43.1.1/ckeditor5.umd.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        const {
            Bold, ClassicEditor, Essentials, Font, Heading, HorizontalLine,
            ImageBlock, ImageInline, ImageInsertViaUrl, ImageResize, ImageStyle,
            Indent, IndentBlock, Italic, Link, List, MediaEmbed, Paragraph, SourceEditing,
            Table, TableCaption, TableCellProperties, TableColumnResize, TableProperties, TableToolbar,
            Underline,
        } = CKEDITOR;

        const default_toolbar = ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', '|', 'fontColor', 'fontBackgroundColor', '|',
            'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'horizontalLine', 'link', 'insertImage', 'mediaEmbed', 'insertTable', 'sourceEditing'];
        const default_plugins = [Essentials, Bold, Italic, Font, Paragraph, Heading, HorizontalLine, List, Link, Underline, Indent, IndentBlock, SourceEditing,
            Table, TableCaption, TableCellProperties, TableColumnResize, TableProperties, TableToolbar, MediaEmbed,
            ImageBlock, ImageInline, ImageInsertViaUrl, ImageResize, ImageStyle];


        let activeToolboxEditor = null;

        function createToolboxEditor(selector, initialData) {
            ClassicEditor.create(document.querySelector(selector), {
                plugins: default_plugins,
                toolbar: default_toolbar,
                initialData: initialData,

                link: {
                    decorators: {
                        openFilebankLinksInNewTab: {
                            mode: 'automatic',
                            callback: url => {
                                return url && (
                                    url.includes('/filebank/') ||
                                    url.toLowerCase().endsWith('.pdf')
                                );
                            },
                            attributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }
                        }
                    }
                }
            }).then(editor => {
                editor.editing.view.document.on('focus', () => {
                    activeToolboxEditor = editor;
                });
            }).catch(error => {
                console.error(error);
            });
        }

        createToolboxEditor('#overview', @json($talk->overview));
        createToolboxEditor('#hazards', @json($talk->hazards));
        createToolboxEditor('#controls', @json($talk->controls));
        createToolboxEditor('#further', @json($talk->further));
    </script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $('#name-show').on('click', function () {
            $('#name-show').hide();
            $('#name-edit').show();
        });

        $('#active').on('click', function () {
            $('#status').val(1);
            document.getElementById('talk_form').submit();
            //submit_form();
        });

        $('#continue').on('click', function () {
            $('#status').val(1);
            document.getElementById('talk_form').submit();
            //submit_form();
        });

        FilePond.registerPlugin(FilePondPluginFileValidateType, FilePondPluginImagePreview);

        const csrfToken = $('meta[name=token]').attr('value') || $('meta[name=csrf-token]').attr('content');
        const uploadUrl = '/safety/doc/toolbox3/' + $('#talk_id').val() + '/upload';

        FilePond.setOptions({
            credits: false,
            server: {
                process: {
                    url: uploadUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    onload: function (response) {
                        return response;
                    },
                    onerror: function (response) {
                        return response;
                    }
                }
            }
        });

        const toolboxUploadPond = FilePond.create(document.querySelector('#singlefile'), {
            allowMultiple: false,
            instantUpload: false,
            acceptedFileTypes: ['image/*', 'application/pdf'],
            labelIdle: 'Drag & drop an image or PDF or <span class="filepond--label-action">Browse</span>'
        });

        $('#upload_media').on('click', function () {
            if (!toolboxUploadPond.getFiles().length) {
                alert('Please select a file first.');
                return;
            }

            toolboxUploadPond.processFiles();
        });

        toolboxUploadPond.on('processfiles', function () {
            location.reload();
        });

        toolboxUploadPond.on('processfile', function (error) {
            if (error) {
                alert('Upload failed. Please try again.');
            }
        });

        $(document).on('click', '.btn-copy-file', function () {
            const button = $(this);
            const url = button.attr('data-url');

            if (!url) {
                alert('No file URL found.');
                return;
            }

            function copied() {
                button.html('<i class="fa fa-check"></i> Copied!');
                button.removeClass('blue').addClass('green');

                setTimeout(function () {
                    button.html('<i class="fa fa-copy"></i> Copy Link');
                    button.removeClass('green').addClass('blue');
                }, 1500);
            }

            // Modern browser copy
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(copied).catch(function () {
                    fallbackCopy(url, copied);
                });
            } else {
                fallbackCopy(url, copied);
            }
        });

        $(document).on('click', '.btn-insert-file', function () {
            const url = $(this).attr('data-url');
            const name = $(this).attr('data-name') || 'Open file';
            const type = $(this).attr('data-type');

            if (!url) {
                alert('No file URL found.');
                return;
            }

            if (!activeToolboxEditor) {
                alert('Click inside the toolbox talk editor where you want the file first.');
                return;
            }

            activeToolboxEditor.editing.view.focus();

            if (type === 'image') {
                if (activeToolboxEditor.commands.get('insertImage')) {
                    activeToolboxEditor.execute('insertImage', {source: url});
                    return;
                }

                activeToolboxEditor.model.change(writer => {
                    const imageElement = writer.createElement('imageBlock', {
                        src: url
                    });

                    activeToolboxEditor.model.insertContent(
                        imageElement,
                        activeToolboxEditor.model.document.selection
                    );
                });

                return;
            }

            if (type === 'pdf') {
                activeToolboxEditor.model.change(writer => {
                    const paragraph = writer.createElement('paragraph');
                    const linkText = writer.createText('Open PDF: ' + name, {linkHref: url});

                    writer.append(linkText, paragraph);

                    activeToolboxEditor.model.insertContent(paragraph, activeToolboxEditor.model.document.selection);
                });
            }
        });

        function fallbackCopy(text, callback) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';

            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                document.execCommand('copy');
                callback();
            } catch (err) {
                alert('Copy failed. Please copy the link manually.');
            }

            document.body.removeChild(textarea);
        }
    </script>
@stop

