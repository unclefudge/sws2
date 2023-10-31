@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/incident/{{ $incident->id }}">Incident</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Incident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        @include('site/incident/_header')

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Incident Report</span>
                            <span class="caption-helper"> ID: {{ $incident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($incident, ['action' => ['Site\Incident\SiteIncidentController@lodge', $incident->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                            <input type="hidden" name="incident_id" id="incident_id" value="{{ $incident->id }}">
                            @include('form-error')


                            @if(Auth::user()->allowed2('add.site.incident'))
                                <h4>Photos / Documents</h4>
                                <hr>
                                <div class="note note-warning">
                                    Please upload any photos / documents related to the incident. Include photos of:
                                    <ul>
                                        <li>Scene / area of the incident</li>
                                        <li>Any damage occured to property / equipment as result of incident</li>
                                    </ul>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-12">You don't have permission to upload photos
                                    </div>
                                </div>
                            @endif
                        </div>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/incident/{{ $incident->id }}" class="btn default"> Back</a>
                            <button type="submit" class="btn green" name="add_docs"> Save</button>
                        </div>
                        <br><br>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        // Get a reference to the file input element
        const inputElement = document.querySelector('input[type="file"]');

        // Create a FilePond instance
        const pond = FilePond.create(inputElement);
        FilePond.setOptions({
            server: {
                url: '/file/upload',
                fetch: null,
                revert: null,
                headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
            },
            allowMultiple: true,
        });

        $(document).ready(function () {
            /* Bootstrap Fileinput */
            $("#multifile").fileinput({
                uploadUrl: "/site/incident/upload/", // server upload action
                uploadAsync: true,
                //allowedFileExtensions: ["image"],
                //allowedFileTypes: ["image"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn red",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn dark",
                uploadIcon: "<i class=\"fa fa-upload\"></i> ",
                uploadExtraData: {
                    "incident_id": incident_id,
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
                data.form.append("incident_id", $("#incident_id").val());
            });
        });
    </script>
@stop

