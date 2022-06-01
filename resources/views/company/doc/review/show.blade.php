@inject('CompanyDocCategory', 'App\Models\Company\CompanyDocCategory')
@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/company/doc/standard/review">Standard Details Review</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-dark bold uppercase"> Edit Standard Details</span>
                            <span class="caption-helper"> ID: {{ $doc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($doc, ['method' => 'PATCH', 'action' => ['Company\CompanyDocReviewController@update',$doc->id], 'class' => 'horizontal-form', 'files' => true, 'id' => 'doc_form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-9">
                                    @if ($doc->status == 2)
                                        <h2 style="margin: 0 0"><span class="label label-warning">Pending Approval</span></h2><br><br>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    @if(!$doc->status)
                                        <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Inactive</h3>
                                    @endif
                                </div>
                            </div>

                            <h3>{{ $doc->name }}</h3>
                            <hr class="field-hr">

                            <div class="row">
                                <div class="col-md-8">
                                    {{-- Stage --}}
                                    <h4 class="font-green-haze">Status</h4>
                                    <hr class="field-hr">
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-3"><b>Stage:</b></div>
                                        <div class="col-md-9">{{ $doc->stage }}</div>
                                        <div class="col-md-3"><b>Assigned To:</b></div>
                                        <div class="col-md-9">{{ $doc->assignedToSBC() }}</div>
                                    </div>
                                    <br>

                                    {{-- Review Process --}}
                                    <h4 class="font-green-haze">Review Process</h4>
                                    <hr class="field-hr">
                                    <div class="row">
                                        <div class="col-md-3"><b>Current version:</b></div>
                                        <div class="col-md-9">
                                            <a href="{{  $doc->current_doc_url }}" target="_blank"> {{ ($doc->current_doc) ? $doc->current_doc : $doc->original_doc }} </a>
                                            @if (!$doc->current_doc)
                                                <span class="font-red"> &nbsp; (Original Standard)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('approve_version', $errors) !!}">
                                                {!! Form::label('approve_version', 'Do you approval the current version', ['class' => 'control-label']) !!}
                                                {!! Form::select('approve_version', ['' => 'Select option', '0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'approve_version']) !!}
                                                {!! fieldErrorMessage('approve_version', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h4 class="font-green-haze">Files</h4>
                                    <hr class="field-hr">
                                    <i class="fa fa-file-pdf-o"></i> &nbsp; <a href="{{ $doc->original_doc_url }}" target="_blank"> Original Standard </a>
                                </div>
                            </div>

                            <div id="file-upload">
                                {{-- SingleFile Upload --}}
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                            <label class="control-label">Please uploaded a document with the required changes</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            {!! fieldErrorMessage('singlefile', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/company/doc/standard/review" class="btn default"> Back</a>
                                <button id="approve_button" type="submit" name="approve" class="btn green"> Approve</button>
                                <button id="save_button" type="submit" name="save" class="btn green"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $doc->displayUpdatedBy() !!}
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
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
    $(document).ready(function () {
        /* Select2 */
        $("#lic_type").select2({placeholder: "Select one or more", width: '100%'});

        function display_fields() {
            var approve_version = $("#approve_version").val();

            $('#file-upload').hide();
            $('#approve_button').hide();
            $('#save_button').hide();

            // Approved
            if ($("#approve_version").val() == '1') {
                $('#file-upload').hide();
                $('#approve_button').show();
            }

            // Not Approved
            if ($("#approve_version").val() == '0') {
                $('#file-upload').show();

                if ($("#single_file").val() != '')
                    $('#save_button').show();
            }
        }


        display_fields();

        $("#approve_version").change(function () {
            display_fields();
        });


        /* Bootstrap Fileinput */
        $("#singlefile").fileinput({
            showUpload: false,
            allowedFileExtensions: ["pdf", "jpg", "jpeg", "png", "gif"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn btn-danger",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn btn-info",
        });

        /* Bootstrap Fileinput */
        $("#singleimage").fileinput({
            showUpload: false,
            allowedFileExtensions: ["pdf", "jpg", "png", "gif"],
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
            //$('#singlefile-div').show();
            if ($("#category_id").val() == 7 || $("#category_id").val() == 9 || $("#category_id").val() == 10) { // 7 Contractors Lic, 9 Other Lic, 10 Builders Lic
                $('#singleimage-div').show();
                $('#filetype').val('image');
            } else {
                $('#singlefile-div').show();
                $('#filetype').val('pdf');
            }
            $('#but_upload').show();
            $('#but_save').hide();
        });


    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

</script>
@stop