@inject('ozstates', 'App\Http\Utilities\OzStates')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
            <li><a href="/user/{{ $user->id}}/doc">Documents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Upload</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Users Header --}}
        @include('user/_header')


        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase"> Upload Documents</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('userdoc', ['action' => ['User\UserDocController@store', $user->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')
                        {!! Form::hidden('create', 'true') !!}
                        {!! Form::hidden('filetype', 'pdf', ['id' => 'filetype']) !!}
                        {!! Form::hidden('name', '', ['id' => 'name']) !!}

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Doc type --}}
                                    <div class="form-group {!! fieldHasError('category_id', $errors) !!}" id="category_id_form">
                                        {!! Form::label('category_id', 'Document type', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id',Auth::user()->userDocTypeSelect('add', $user, 'prompt'), null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('category_id', $errors) !!}
                                    </div>
                                    {{-- Name --}}
                                    <div class="form-group {!! fieldHasError('ref_name', $errors) !!}" style="display: none" id="fields_name">
                                        {!! Form::label('ref_name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('ref_name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('ref_name', $errors) !!}
                                    </div>
                                    {{-- Lic No --}}
                                    <div class="form-group {!! fieldHasError('lic_no', $errors) !!}" style="display: none" id="fields_lic_no">
                                        {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('lic_no', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('lic_no', $errors) !!}
                                    </div>
                                    {{-- Drivers Lic Class --}}
                                    <div class="form-group {!! fieldHasError('drivers_type', $errors) !!}" style="display: none" id="fields_driver_class">
                                        {!! Form::label('drivers_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        <select id="drivers_type" name="drivers_type[]" class="form-control select2" width="100%" multiple>
                                            {!! $user->driversLicenceOptions() !!}
                                        </select>
                                        {!! fieldErrorMessage('drivers_type', $errors) !!}
                                    </div>
                                    {{-- Contractor Lic Class --}}
                                    <div class="form-group {!! fieldHasError('cl_type', $errors) !!}" style="display: none" id="fields_cl_class">
                                        {!! Form::label('cl_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        <select id="cl_type" name="cl_type[]" class="form-control select2" width="100%" multiple>
                                            {!! $user->contractorLicenceOptions() !!}
                                        </select>
                                        {!! fieldErrorMessage('cl_type', $errors) !!}
                                        @if ($user->requiredContractorLicencesSBC())
                                            <br><span class="note note-warning" style="width:100%">Company nominated supervisor for classes: {{ $user->requiredContractorLicencesSBC() }}</span>
                                        @endif
                                    </div>
                                    {{-- Supervisor Lic Class --}}
                                    <div class="form-group {!! fieldHasError('super_type', $errors) !!}" style="display: none" id="fields_super_class">
                                        {!! Form::label('super_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        <select id="super_type" name="super_type[]" class="form-control select2" width="100%" multiple>
                                            {!! $user->contractorLicenceOptions() !!}
                                        </select>
                                        {!! fieldErrorMessage('super_type', $errors) !!}
                                        @if ($user->requiredContractorLicencesSBC())
                                            <br><span class="note note-warning" style="width:100%">Company nominated supervisor for classes: {{ $user->requiredContractorLicencesSBC() }}</span>
                                        @endif
                                    </div>
                                    {{-- Asbestos Class --}}
                                    <div class="form-group {!! fieldHasError('asb_type', $errors) !!}" style="display: none" id="fields_asb_class">
                                        {!! Form::label('asb_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('asb_type', ['' => 'Select class', 'A' => 'Class A (Friable)', 'B' => 'Class B (Non-Friable)'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('asb_type', $errors) !!}
                                    </div>
                                    {{-- State --}}
                                    <div class="form-group {!! fieldHasError('state', $errors) !!}" style="display: none" id="fields_state">
                                        {!! Form::label('state', 'State', ['class' => 'control-label']) !!}
                                        {!! Form::select('state', $ozstates::all(), 'NSW', ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('state', $errors) !!}
                                    </div>
                                    {{-- Issued --}}
                                    <div class="form-group {!! fieldHasError('issued', $errors) !!}" style="display: none" id="fields_issued">
                                        {!! Form::label('issued', 'Issued Date', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('issued', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('issued', $errors) !!}
                                    </div>
                                    {{-- Expiry --}}
                                    <div class="form-group {!! fieldHasError('expiry', $errors) !!}" style="display: none" id="fields_expiry">
                                        {!! Form::label('expiry', 'Expiry', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('expiry', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('expiry', $errors) !!}
                                    </div>
                                    {{-- Notes --}}
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}" style="display: none" id="fields_notes">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Single File -->
                                    <div class="form-group {!! fieldHasError('singlefile', $errors) !!}" style="display: none" id="singlefile-div">
                                        <label class="control-label">Select File</label>
                                        <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singlefile', $errors) !!}
                                    </div>

                                    <!-- Single Image File -->
                                    <div class="form-group {!! fieldHasError('singleimage', $errors) !!}" style="display: none" id="singleimage-div">
                                        <label class="control-label">Select File / Photo</label>
                                        <input id="singleimage" name="singleimage" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singleimage', $errors) !!}
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="/user/{{ $user->id }}/doc" class="btn default"> Back</a>
                                <button type="submit" name="save" value="save" class="btn green" id="upload" style="display: none;">Upload</button>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <!--<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>-->
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });


    $(document).ready(function () {
        /* Select2 */
        $("#drivers_class").select2({placeholder: "Select one or more", width: '100%'});
        $("#cl_type").select2({placeholder: "Select one or more", width: '100%'});

        function display_fields() {
            var cat = $("#category_id").val();

            $('#name').val($("#category_id option:selected").text());
            $('#fields_name').hide();
            $('#fields_lic_no').hide();
            $('#fields_driver_class').hide();
            $('#fields_cl_class').hide();
            $('#fields_super_class').hide();
            $('#fields_asb_class').hide();
            $('#fields_state').hide();
            $('#fields_expiry').hide();
            $('#fields_issued').hide();
            $('#fields_notes').hide();
            $('#singlefile-div').hide();
            $('#singleimage-div').hide();
            $('#upload').hide();


            if (cat != '') {
                if (cat == 1 || cat == 2 || cat == 3 || cat == 4) { // 1 WhiteCard, 2 Drivers Lic, 3 Contractors Lic, 4 Supervisor Liv
                    $('#singleimage-div').show();
                    $('#filetype').val('image');
                } else {
                    $('#singlefile-div').show();
                    $('#filetype').val('pdf');
                }
                $('#fields_notes').show();
                $('#upload').show();
            }

            if (cat < 6 || cat == 9 || cat == 10) {
                $('#fields_name').hide();
                $('#ref_name').val('');
            } else // Other Licence + everything else
                $('#fields_name').show();


            // Show Expiry or Date field
            if (cat == 2 || cat == 3)  // Drivers, CL
                $('#fields_expiry').show();
            else if (cat != '')
                $('#fields_issued').show();

            if (cat == 2) { // Drivers
                $('#fields_lic_no').show();
                $('#fields_driver_class').show();
                $('#fields_state').show();
            }

            if (cat == 3) { // CL
                $('#fields_lic_no').show();
                $('#fields_cl_class').show();
            }

            if (cat == 4) { // Supervisor Lic
                $('#fields_lic_no').show();
                $('#fields_super_class').show();
            }

            if (cat == 9)  // Asbestos
                $('#fields_asb_class').show();
        }

        display_fields();
        // On Change determine if Category fields are valid for multi file upload
        $("#category_id").change(function () {
            display_fields();
        });

        /* Bootstrap Fileinput */
        $("#singlefile").fileinput({
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


    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

</script>
@stop