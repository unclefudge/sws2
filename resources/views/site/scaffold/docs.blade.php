@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
            <li><a href="/site/scaffold/handover">Scaffold Handover Certificate</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Certificate</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Scaffold Handover Certificate</span>
                            <span class="caption-helper">ID: {{ $report->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteScaffoldHandoverController@documents', $report->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                            <input type="hidden" name="report_id" id="report_id" value="{{ $report->id }}">
                            <input type="hidden" name="site_id" id="site_id" value="{{ $report->site_id }}">

                            @include('form-error')

                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-4 mt-step-col first done">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create certificate</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col active">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                        <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col last">
                                        <div class="mt-step-number bg-white font-grey">3</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                                        <div class="mt-step-content font-grey-cascade">Certificate sign off</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-2"><b>Site</b></div>
                                <div class="col-md-10">{{ $report->site->code }}-{{ $report->site->name }}</div>
                                </div>
                            <br>


                            <!-- Multi File upload -->
                            <div id="multifile-div">
                                <h4>Photos / Documents</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                @if(Auth::user()->allowed2('add.site.scaffold.handover'))
                                    <div class="note note-warning">
                                        Multiple documents/photos/images can be uploaded with this scaffold certificate.
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
                                @else
                                    <div class="row">
                                        <div class="col-md-12">You don't have permission to upload photos
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <hr>
                            <div class="pull-right" style="min-height: 50px">
                                <a href="/site/scaffold/handover" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('add.site.scaffold.handover'))
                                    <button type="submit" name="save" class="btn blue"> Next Step</button>
                                @endif
                            </div>
                            <br><br>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Bootstrap Fileinput */
        $("#multifile").fileinput({
            uploadUrl: "/site/scaffold/handover/upload/", // server upload action
            uploadAsync: true,
            allowedFileExtensions: ["image"],
            allowedFileTypes: ["image"],
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
                "report_id": report_id,
                "category": 'setup',
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
            data.form.append("report_id", $("#report_id").val());
        });
    });
</script>
@stop

