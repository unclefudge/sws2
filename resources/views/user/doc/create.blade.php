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
                        <form method="POST" action="{{ action([\App\Http\Controllers\User\UserDocController::class, 'store'], ['uid' => $user->id]) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')
                            <x-form.hidden name="create" value="true"/>
                            <x-form.hidden name="filetype" value="pdf"/>
                            <x-form.hidden name="name" value=""/>

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- Doc type --}}
                                        <div id="category_id_form">
                                            <x-form.select name="category_id" label="Document type" :options="Auth::user()->userDocTypeSelect('add', $user, 'prompt')"/>
                                        </div>
                                        {{-- Name --}}
                                        <div style="display: none" id="fields_name">
                                            <x-form.input name="ref_name" label="Name"/>
                                        </div>
                                        {{-- Lic No --}}
                                        <div style="display: none" id="fields_lic_no">
                                            <x-form.input name="lic_no" label="Licence No."/>
                                        </div>
                                        {{-- Drivers Lic Class --}}
                                        <div style="display: none" id="fields_driver_class">
                                            <x-form.select name="drivers_type[]" label="Class(s)" plugin="select2" multiple style="width:100%">
                                                {!! $user->driversLicenceOptions() !!}
                                            </x-form.select>
                                        </div>
                                        {{-- Contractor Lic Class --}}
                                        <div style="display: none" id="fields_cl_class">
                                            <x-form.select name="cl_type[]" label="Class(s)" plugin="select2" multiple style="width:100%">
                                                {!! $user->contractorLicenceOptions() !!}
                                            </x-form.select>
                                            @if ($user->requiredContractorLicencesSBC())
                                                <br><span class="note note-warning" style="width:100%">Company nominated supervisor for classes: {{ $user->requiredContractorLicencesSBC() }}</span>
                                            @endif
                                        </div>
                                        {{-- Supervisor Lic Class --}}
                                        <div style="display: none" id="fields_super_class">
                                            <x-form.select name="super_type[]" label="Class(s)" plugin="select2" multiple style="width:100%">
                                                {!! $user->contractorLicenceOptions() !!}
                                            </x-form.select>
                                            @if ($user->requiredContractorLicencesSBC())
                                                <br><span class="note note-warning" style="width:100%">Company nominated supervisor for classes: {{ $user->requiredContractorLicencesSBC() }}</span>
                                            @endif
                                        </div>
                                        {{-- Asbestos Class --}}
                                        <div style="display: none" id="fields_asb_class">
                                            <x-form.select name="asb_type" label="Class(s)" :options="['' => 'Select class', 'A' => 'Class A (Friable)', 'B' => 'Class B (Non-Friable)']"/>
                                        </div>
                                        {{-- State --}}
                                        <div style="display: none" id="fields_state">
                                            <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                        </div>
                                        {{-- Issued --}}
                                        <div style="display: none" id="fields_issued">
                                            <x-form.datepicker name="issued" label="Issued Date" format="dd/mm/yyyy"/>
                                        </div>
                                        {{-- Expiry --}}
                                        <div style="display: none" id="fields_expiry">
                                            <x-form.datepicker name="expiry" label="Expiry" format="dd/mm/yyyy"/>
                                        </div>
                                        {{-- Notes --}}
                                        <div style="display: none" id="fields_notes">
                                            <x-form.textarea name="notes" label="Notes" rows="3"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Single File -->
                                        <div class="form-group {{ $errors->has('singlefile') ? 'has-error' : '' }}" style="display: none" id="singlefile-div">
                                            <label class="control-label">Select File</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            <x-form.error name="singlefile"/>
                                        </div>

                                        <!-- Single Image File -->
                                        <div class="form-group {{ $errors->has('singleimage') ? 'has-error' : '' }}" style="display: none" id="singleimage-div">
                                            <label class="control-label">Select File / Photo</label>
                                            <input id="singleimage" name="singleimage" type="file" class="file-loading">
                                            <x-form.error name="singleimage"/>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-actions right">
                                    <a href="/user/{{ $user->id }}/doc" class="btn default"> Back</a>
                                    <button type="submit" name="save" value="save" class="btn green" id="upload" style="display: none;">Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
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