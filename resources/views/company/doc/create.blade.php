@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/{{ $company->id }}/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Upload</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Company Header --}}
        @include('company/_header')

        {{-- Compliance Documents --}}
        @if (count($company->missingDocs()))
            <div class="row">
                @include('company/_compliance-docs')
            </div>
        @endif

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
                        {!! Form::model('companydoc', ['action' => ['Company\CompanyDocController@store', $company->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')
                        {!! Form::hidden('create', 'true') !!}
                        {!! Form::hidden('filetype', 'pdf', ['id' => 'filetype']) !!}

                        <div class="alert alert-danger alert-dismissable" style="display: none;" id="multifile-error">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            <i class="fa fa-warning"></i><strong> Error(s) have occured</strong>
                            <ul>
                                <li>Before you can upload multiple files you are required to select Category</li>
                            </ul>
                        </div>

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Doc type --}}
                                    <div class="form-group {!! fieldHasError('category_id', $errors) !!}" id="category_id_form">
                                        {!! Form::label('category_id', 'Document type', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id',Auth::user()->companyDocTypeSelect('add', $company, '-SS-PTC'),
                                             $category_id, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('category_id', $errors) !!}
                                    </div>
                                    {{-- Doc Sub-Category --}}
                                    <div class="form-group {!! fieldHasError('subcategory_id', $errors) !!}" id="fields_subcategory">
                                        {!! Form::label('subcategory_id', 'Sub Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('subcategory_id', \App\Models\Company\CompanyDocCategory::find(22)->subcategorySelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('subcategory_id', $errors) !!}
                                    </div>
                                    {{-- Name --}}
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}" style="display: none" id="fields_name">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                    </div>
                                    {{-- Policy --}}
                                    <div class="form-group {!! fieldHasError('ref_no', $errors) !!}" style="display: none" id="fields_policy">
                                        {!! Form::label('ref_no', 'Policy No', ['class' => 'control-label']) !!}
                                        {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('ref_no', $errors) !!}
                                    </div>
                                    {{-- Insurer --}}
                                    <div class="form-group {!! fieldHasError('ref_name', $errors) !!}" style="display: none" id="fields_insurer">
                                        {!! Form::label('ref_name', 'Insurer', ['class' => 'control-label']) !!}
                                        {!! Form::text('ref_name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('ref_name', $errors) !!}
                                    </div>
                                    {{-- Category --}}
                                    <div class="form-group {!! fieldHasError('ref_type', $errors) !!}" style="display: none" id="fields_category">
                                        {!! Form::label('ref_type', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('ref_type', $company->workersCompCategorySelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('ref_type', $errors) !!}
                                    </div>
                                    {{-- Lic No --}}
                                    <div class="form-group {!! fieldHasError('lic_no', $errors) !!}" style="display: none" id="fields_lic_no">
                                        {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('lic_no', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('lic_no', $errors) !!}
                                    </div>
                                    {{-- Lic Class --}}
                                    <div class="form-group {!! fieldHasError('lic_type', $errors) !!}" style="display: none; width:100%" id="fields_lic_class">
                                        {!! Form::label('lic_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        <select id="lic_type" name="lic_type[]" class="form-control select2" width="100%" multiple>
                                            {!! $company->contractorLicenceOptions((old('lic_type') ? old('lic_type') : [])) !!}
                                        </select>
                                        {!! fieldErrorMessage('lic_type', $errors) !!}
                                    </div>
                                    {{-- Supervisor of CL --}}
                                    <div style="display: none" id="fields_supervisors">
                                        <div class="form-group {!! fieldHasError('supervisor_no', $errors) !!}" id="fields_supervisor_no">
                                            {!! Form::label('supervisor_no', 'How many Supervisors are required to cover the above class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_no', ['' => 'Please specify', '1' => '1', '2' => '2', '3' => '3'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('supervisor_id', $errors) !!}" style="display: none" id="fields_supervisor_id">
                                            {!! Form::label('supervisor_id', 'Supervisor of all class(s) on licence', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_id', $company->staffSelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_id', $errors) !!}
                                        </div>
                                        <div style="display: none" id="fields_supervisor_id2">
                                            {{-- Supervisor 1 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id1', $errors) !!}">
                                                {!! Form::label('supervisor_id1', 'Supervisor 1', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id1', $company->staffSelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id1', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type1', $errors) !!}">
                                                {!! Form::label('lic_type1', 'Supervisor 1 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type1" name="lic_type1[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes"></select>
                                                {!! fieldErrorMessage('lic_type1', $errors) !!}
                                            </div>

                                            {{-- Supervisor 2 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id2', $errors) !!}">
                                                {!! Form::label('supervisor_id2', 'Supervisor 2', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id2', $company->staffSelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id2', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type2', $errors) !!}">
                                                {!! Form::label('lic_type2', 'Supervisor 2 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type2" name="lic_type2[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes"></select>
                                                {!! fieldErrorMessage('lic_type2', $errors) !!}
                                            </div>
                                        </div>

                                        {{-- Supervisor 3 --}}
                                        <div style="display: none" id="fields_supervisor_id3">
                                            <div class="form-group {!! fieldHasError('supervisor_id3', $errors) !!}">
                                                {!! Form::label('supervisor_id3', 'Supervisor 3', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id3', $company->staffSelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id3', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type3', $errors) !!}">
                                                {!! Form::label('lic_type3', 'Supervisor 3 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type3" name="lic_type3[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes"></select>
                                                {!! fieldErrorMessage('lic_type3', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Asbestos Class --}}
                                    <div class="form-group {!! fieldHasError('asb_type', $errors) !!}" style="display: none" id="fields_asb_class">
                                        {!! Form::label('asb_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('asb_type', ['' => 'Select class', 'A' => 'Class A', 'B' => 'Class B'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('asb_type', $errors) !!}
                                    </div>
                                    {{-- Expiry --}}
                                    <div class="form-group {!! fieldHasError('expiry', $errors) !!}" style="display: none" id="fields_expiry">
                                        {!! Form::label('expiry', 'Expiry', ['class' => 'control-label', 'id' => 'expiry_label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('expiry', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy", 'readonly']) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        {!! fieldErrorMessage('expiry', $errors) !!}
                                    </div>
                                    {{-- Test Expire Type --}}
                                    <div class="form-group {!! fieldHasError('tag_type', $errors) !!}" style="display: none" id="fields_tag_type">
                                        @if ($company->id == 3)
                                            {!! Form::label('tag_type', 'Expiry', ['class' => 'control-label']) !!}
                                            {!! Form::select('tag_type', ['3' => '3 month (site)', '12' => '12 month (office)'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('tag_type', $errors) !!}
                                        @else
                                            {!! Form::hidden('tag_type', '3') !!}
                                        @endif
                                    </div>
                                    {{-- Test date --}}
                                    <div class="form-group {!! fieldHasError('tag_date', $errors) !!}" style="display: none" id="fields_tag_date">
                                        {!! Form::label('tag_date', 'Date of Testing', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker">
                                            {!! Form::text('tag_date', '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                        </div>
                                        @if ($company->id != 3)
                                            <span class="help-block">Expires 3 months from date of testing</span>
                                        @endif
                                        {!! fieldErrorMessage('tag_date', $errors) !!}
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
                                <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                <button type="submit" name="save" value="save" class="btn green" id="upload" style="display: none;">Upload</button>
                            </div>
                        </div>

                        <!-- Multi File upload -->
                        <div id="multifile-div" style="display: none">
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
        $("#lic_type").select2({placeholder: "Select one or more", width: '100%'});
        $("#lic_type1").select2({placeholder: "Select one or more", width: '100%'});
        $("#lic_type2").select2({placeholder: "Select one or more", width: '100%'});

        function display_fields() {
            var cat = $("#category_id").val();

            $('#name').val('');
            $('#fields_policy').hide();
            $('#fields_insurer').hide();
            $('#fields_category').hide();
            $('#fields_subcategory').hide();
            $('#fields_lic_no').hide();
            $('#fields_lic_class').hide();
            $('#fields_supervisor').hide();
            $('#fields_supervisor_id').hide();
            $('#fields_supervisor_id2').hide();
            $('#fields_supervisor_id3').hide();
            $('#fields_asb_class').hide();
            $('#fields_expiry').hide();
            $('#fields_tag_type').hide();
            $('#fields_tag_date').hide();
            $('#fields_notes').hide();
            $('#singlefile-div').hide();
            $('#singleimage-div').hide();
            $('#upload').hide();


            if (cat != '') {
                if (cat == 6 || cat == 7 || cat == 9 || cat == 10) { // 6 Test Tag,  7 Contractors Lic, 9 Other Lic, 10 Builders Lic
                    $('#singleimage-div').show();
                    $('#filetype').val('image');
                } else {
                    $('#singlefile-div').show();
                    $('#filetype').val('pdf');
                }
                //$("#singlefile").fileinput('allowedFileExtensions', ["pdf"]);*/
                $('#fields_expiry').show();
                $('#fields_notes').show();
                $('#upload').show();
            }

            if (cat < 9) {
                $('#name').val($("#category_id option:selected").text());
                $('#fields_name').hide();
            } else // Other Licence + everything else
                $('#fields_name').show();

            if (cat == 1 || cat == 2 || cat == 3) {  // PL, WC & SA
                $('#fields_policy').show();
                $('#fields_insurer').show();
            }
            if (cat == 2 || cat == 3) // WC & SA
                $('#fields_category').show();

            if (cat == 6) { // Test & Tag
                $('#fields_tag_type').show();
                $('#fields_tag_date').show();
                $('#fields_expiry').hide();
            } else {
                $('#fields_tag_date').hide();
                $('#fields_expiry').show();
            }
            if (cat == 7) { // CL
                $('#fields_lic_no').show();
                $('#fields_lic_class').show();
                $('#fields_supervisors').show();

                if ($("#supervisor_no").val() == 1)
                    $('#fields_supervisor_id').show();
                if ($("#supervisor_no").val() > 1)
                    $('#fields_supervisor_id2').show();
                if ($("#supervisor_no").val() > 2)
                    $('#fields_supervisor_id3').show();

                var lic_types = {};
                $("#lic_type option:selected").each(function () {
                    var val = $(this).val();
                    if (val !== '')
                        lic_types[val] = $(this).text();
                });

                $("#lic_type1").empty();
                $("#lic_type2").empty();
                $("#lic_type3").empty();
                $.each(lic_types, function (index, value) {
                    $("#lic_type1").append('<option value="' + index + '">' + value + '</option>');
                    $("#lic_type2").append('<option value="' + index + '">' + value + '</option>');
                    $("#lic_type3").append('<option value="' + index + '">' + value + '</option>');
                });
            }

            if (cat == 8)  // Asbestos
                $('#fields_asb_class').show();

            if (cat == 22) {  // Standard Details
                $('#fields_subcategory').show();
                $('#expiry_label').html('Renewal');
            } else {
                $('#expiry_label').html('Expiry');
            }
        }

        display_fields();
        // On Change determine if Category fields are valid for multi file upload
        $("#category_id").change(function () {
            display_fields();
        });

        $("#lic_type").change(function () {
            display_fields();
        });

        $("#supervisor_no").change(function () {
            display_fields();
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