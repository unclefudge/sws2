@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/maintenance">Maintenance</a><i class="fa fa-circle"></i></li>
        <li><span>View Request</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Maintenance Request</span>
                            <span class="caption-helper">ID: {{ $main->code }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($main, ['action' => ['Site\SiteMaintenanceController@photos', $main->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                            <input type="hidden" name="main_id" id="main_id" value="{{ $main->id }}">
                            <input type="hidden" name="site_id" id="site_id" value="{{ $main->site_id }}">
                            @include('form-error')

                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-4 mt-step-col first done">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create request</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col active">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                        <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col last">
                                        <div class="mt-step-number bg-white font-grey">3</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                        <div class="mt-step-content font-grey-cascade">Assign supervisor</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-5">
                                    <p><h4>Site Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @if ($main->site) <b>{{ $main->site->name }} (#{{ $main->site->code }})</b> @endif<br>
                                    @if ($main->site) {{ $main->site->full_address }}<br> @endif
                                    {{--@if ($main->site && $main->site->client_phone) {{ $main->site->client_phone }} ({{ $main->site->client_phone_desc }})  @endif --}}
                                    <br>
                                    @if ($main->completed)<b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br> @endif
                                    @if ($main->supervisor)<b>Supervisor:</b> {{ $main->supervisor }} @endif
                                    </p>
                                </div>
                                <div class="col-md-1"></div>

                                <div class="col-md-6">
                                    <p><h4>Client Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @if ($main->contact_name) <b>{{ $main->contact_name }}</b><br> @endif
                                    @if ($main->contact_phone) {{ $main->contact_phone }}<br> @endif
                                    @if ($main->contact_email) {{ $main->contact_email }}<br> @endif
                                    </p>
                                </div>
                            </div>


                            <!-- Multi File upload -->
                            <div id="multifile-div">
                                <h4>Photos</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                @if(Auth::user()->allowed2('add.site.maintenance'))
                                    <div class="note note-warning">
                                        Multiple documents/photos/images can be uploaded with this maintenance request.
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
                                <a href="/site/maintenance" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('add.site.maintenance'))
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
        /* Select2 */
        $("#company_id").select2({placeholder: "Select Company", width: '100%'});
        $("#assign").select2({placeholder: "Select User", width: '100%'});
        $("#super_id").select2({placeholder: "Select Supervisor", width: "100%"});

        $("#assign_to").change(function () {
            $('#super-div').hide();
            $('#company-div').hide();

            if ($("#assign_to").val() == 'super') {
                $('#super-div').show();
            }

            if ($("#assign_to").val() == 'company') {
                $('#company-div').show();
            }
        });

        $("#status").change(function () {
            //updateFields()
        });

        $("#more").click(function (e) {
            e.preventDefault();
            $('#more').hide();
            $('#more_items').show();
        });

        //updateFields();

        function updateInfo() {
            $('#super-div').hide();
            $('#company-div').hide();

            if ($("#assign_to").val() == 'super') {
                $('#super-div').show();
            }

            if ($("#assign_to").val() == 'company') {
                $('#company-div').show();
            }
        }

        /* Bootstrap Fileinput */
        $("#multifile").fileinput({
            uploadUrl: "/site/maintenance/upload/", // server upload action
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
                "site_id": site_id,
                "main_id": main_id,
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
            data.form.append("main_id", $("#main_id").val());
        });
    });
</script>
@stop

