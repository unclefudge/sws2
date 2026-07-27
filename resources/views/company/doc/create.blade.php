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
                        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyDocController::class, 'store'], ['cid' => $company->id]) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')
                            <x-form.hidden name="create" value="true"/>
                            <x-form.hidden name="filetype" value="pdf"/>

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
                                        <div id="category_id_form">
                                            <x-form.select name="category_id" label="Document type" :options="Auth::user()->companyDocTypeSelect('add', $company, '-SS-PTC')" :value="$category_id"/>
                                        </div>
                                        {{-- Doc Sub-Category --}}
                                        <div id="fields_subcategory">
                                            <x-form.select name="subcategory_id" label="Sub Category" :options="\App\Models\Company\CompanyDocCategory::find(22)->subcategorySelect('prompt')"/>
                                        </div>
                                        {{-- Name --}}
                                        <div style="display: none" id="fields_name">
                                            <x-form.input name="name" label="Name"/>
                                        </div>
                                        {{-- Policy --}}
                                        <div style="display: none" id="fields_policy">
                                            <x-form.input name="ref_no" label="Policy No"/>
                                        </div>
                                        {{-- Insurer --}}
                                        <div style="display: none" id="fields_insurer">
                                            <x-form.input name="ref_name" label="Insurer"/>
                                        </div>
                                        {{-- Category --}}
                                        <div style="display: none" id="fields_category">
                                            <x-form.select name="ref_type" label="Category" :options="$company->workersCompCategorySelect('prompt')"/>
                                        </div>
                                        {{-- Lic No --}}
                                        <div style="display: none" id="fields_lic_no">
                                            <x-form.input name="lic_no" label="Licence No."/>
                                        </div>
                                        {{-- Lic Class --}}
                                        <div class="form-group {{ $errors->has('lic_type') ? 'has-error' : '' }}" style="display: none; width:100%" id="fields_lic_class">
                                            <label for="lic_type" class="control-label">Class(s)</label>
                                            <select id="lic_type" name="lic_type[]" class="form-control select2" style="width:100%" multiple>
                                                {!! $company->contractorLicenceOptions((old('lic_type') ? old('lic_type') : [])) !!}
                                            </select>
                                            <x-form.error name="lic_type"/>
                                        </div>
                                        {{-- Supervisor of CL --}}
                                        <div style="display: none" id="fields_supervisors">
                                            <div id="fields_supervisor_no">
                                                <x-form.select name="supervisor_no" label="How many Supervisors are required to cover the above class(s)" :options="['' => 'Please specify', '1' => '1', '2' => '2', '3' => '3']"/>
                                            </div>
                                            <div style="display: none" id="fields_supervisor_id">
                                                <x-form.select name="supervisor_id" label="Supervisor of all class(s) on licence" :options="$company->staffSelect('prompt')"/>
                                            </div>
                                            <div style="display: none" id="fields_supervisor_id2">
                                                {{-- Supervisor 1 --}}
                                                <x-form.select name="supervisor_id1" label="Supervisor 1" :options="$company->staffSelect('prompt')"/>
                                                <x-form.select name="lic_type1[]" label="Supervisor 1 is ONLY responsible for class(s)" :options="[]" plugin="select2" style="width:100%" multiple placeholder="Select one or more classes"/>

                                                {{-- Supervisor 2 --}}
                                                <x-form.select name="supervisor_id2" label="Supervisor 2" :options="$company->staffSelect('prompt')"/>
                                                <x-form.select name="lic_type2[]" label="Supervisor 2 is ONLY responsible for class(s)" :options="[]" plugin="select2" style="width:100%" multiple placeholder="Select one or more classes"/>
                                            </div>

                                            {{-- Supervisor 3 --}}
                                            <div style="display: none" id="fields_supervisor_id3">
                                                <x-form.select name="supervisor_id3" label="Supervisor 3" :options="$company->staffSelect('prompt')"/>
                                                <x-form.select name="lic_type3[]" label="Supervisor 3 is ONLY responsible for class(s)" :options="[]" plugin="select2" style="width:100%" multiple placeholder="Select one or more classes"/>
                                            </div>
                                        </div>
                                        {{-- Asbestos Class --}}
                                        <div style="display: none" id="fields_asb_class">
                                            <x-form.select name="asb_type" label="Class(s)" :options="['' => 'Select class', 'A' => 'Class A', 'B' => 'Class B']"/>
                                        </div>
                                        {{-- Expiry --}}
                                        <div style="display: none" id="fields_expiry">
                                            <label for="expiry" class="control-label" id="expiry_label">Expiry</label>
                                            <x-form.datepicker name="expiry" value="" readonly clear-button/>
                                            <x-form.error name="expiry"/>
                                        </div>
                                        {{-- Test Expire Type --}}
                                        <div style="display: none" id="fields_tag_type">
                                            @if ($company->id == 3)
                                                <x-form.select name="tag_type" label="Expiry" :options="['3' => '3 month (site)', '12' => '12 month (office)']"/>
                                            @else
                                                <x-form.hidden name="tag_type" value="3"/>
                                            @endif
                                        </div>
                                        {{-- Test date --}}
                                        <div style="display: none" id="fields_tag_date">
                                            <x-form.datepicker name="tag_date" label="Date of Testing" value="" clear-button/>
                                            @if ($company->id != 3)
                                                <span class="help-block">Expires 3 months from date of testing</span>
                                            @endif
                                        </div>
                                        {{-- Notes --}}
                                        <div style="display: none" id="fields_notes">
                                            <x-form.textarea name="notes" label="Notes" rows="3"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Single File --}}
                                        <div class="form-group {{ $errors->has('singlefile') ? 'has-error' : '' }}" style="display: none" id="singlefile-div">
                                            <label class="control-label">Select File (PDF)</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            <x-form.error name="singlefile"/>
                                        </div>

                                        {{-- Single Image File --}}
                                        <div class="form-group {{ $errors->has('singleimage') ? 'has-error' : '' }}" style="display: none" id="singleimage-div">
                                            <label class="control-label">Select File / Photo</label>
                                            <input id="singleimage" name="singleimage" type="file" class="file-loading">
                                            <x-form.error name="singleimage"/>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-actions right">
                                    <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                    <button type="submit" name="save" value="save" class="btn green" id="upload" style="display: none;">Upload</button>
                                </div>
                            </div>

                            {{-- Multi File upload --}}
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
                    </div>
                    </form>
                </div>
            </div>
        </div>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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
                    // All uploads can now be images 7/4/25
                    $('#singleimage-div').show();
                    $('#filetype').val('image');
                    /*if (cat == 6 || cat == 7 || cat == 9 || cat == 10) { // 6 Test Tag,  7 Contractors Lic, 9 Other Lic, 10 Builders Lic
                        $('#singleimage-div').show();
                        $('#filetype').val('image');
                    } else {
                        $('#singlefile-div').show();
                        $('#filetype').val('pdf');
                    }*/
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