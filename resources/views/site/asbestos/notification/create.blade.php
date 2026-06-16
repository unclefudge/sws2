@inject('ozstates', 'App\Http\Utilities\OzStates')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/notification">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Asbestos Notification</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteAsbestosController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                            <input type="hidden" name="amount_over" value="{{ old('amount_over', '0') }}" id="amount_over">
                            <div class="form-body">
                                {{-- Site --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </x-form.select>
                                    </div>
                                </div>

                                {{-- Client / Super Details --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- Individual Details --}}
                                        <h4>Individual (Client) Details</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-form.input name="client_name" label="Primary Contact"/>
                                            </div>
                                            <div class="col-md-6">
                                                <x-form.input name="client_phone" label="Phone"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Supervisor Details --}}
                                        <h4>Contact Person (Supervisor) Details</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-form.select name="supervisor_id" label="Supervisor" :options="Auth::user()->company->supervisorsSelect()" title="Select supervisor"/>
                                            </div>
                                            <div class="col-md-6">
                                                <x-form.input name="super_phone" label="Phone"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                {{-- Site Details --}}
                                <h4>Site Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-2">
                                        <x-form.input name="site_code" label="Job #" readonly/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="site_name" label="Site Name" readonly/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-form.input name="site_address" label="Site Address" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <x-form.select name="workplace" label="Workplace Type" :options="['' => 'Select type', 'Residental' => 'Residental', 'Factory' => 'Factory', 'Office' => 'Office']"/>
                                    </div>

                                    {{-- Dates - Open Hours --}}
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('hours_from') ? 'has-error' : '' }} {{ $errors->has('hours_to') ? 'has-error' : '' }}">
                                            <label for="hours_from" class="control-label">Operating hours of the site</label>
                                            <div class="input-group">
                                                <input type="text" name="hours_from" value="{{ old('hours_from', '7:00 AM') }}" id="hours_from" class="form-control timepicker timepicker-no-seconds">
                                                <span class="input-group-addon"> to </span>
                                                <input type="text" name="hours_to" value="{{ old('hours_to', '3:30 PM') }}" id="hours_to" class="form-control timepicker timepicker-no-seconds">
                                            </div>
                                            <x-form.error name="hours_from"/>
                                            <x-form.error name="hours_to"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('date_from') ? 'has-error' : '' }}">
                                            <label for="date_from" class="control-label">Proposed dates of asbestos removal work</label>
                                            <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy">
                                                <input type="text" name="date_from" value="{{ old('date_from', '') }}" id="date_from" class="form-control" readonly style="background:#FFF">
                                                <span class="input-group-addon"> to </span>
                                                <input type="text" name="date_to" value="{{ old('date_to', '') }}" id="date_to" class="form-control" readonly style="background:#FFF">
                                            </div>
                                            <x-form.error name="date_from"/>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                {{-- Asbestos Details --}}
                                <h4>Asbestos Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                {{-- Amount --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="amount" label="Amount to be removed (m2)" onkeydown="return isNumber(event)"/>
                                        <div class=" note note-warning" style="display: none;" id="amount_note">
                                            <p>Volumes over 10m2 are classed as licensed asbestos removal.</p>
                                            <ul>
                                                <li><b>5 calendar days notice to SafeWork is required.</b></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <x-form.select name="friable" label="Asbestos Class" :options="['' => 'Select class', '1' => 'Class A (Friable)', '0' => 'Class B (Non-Friable)']"/>
                                        <div class="note note-warning" style="display: none;" id="friable_note">
                                            <p><b>NOTE:</b> Cape Cod does not hold the Licence Class required to handle this type of Asbestos</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Type --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.select name="type" label="Type" :options="['' => 'Select type', 'Asbestos Cement Sheets/Products' => 'Asbestos Cement Sheets/Products', 'Vinyl floor covering' => 'Vinyl floor covering', 'other' => 'Other']"/>
                                    </div>
                                    <div class="col-md-6" style="display: none" id="type_other_div">
                                        <x-form.input name="type_other" label="Other type" placeholder="Please specify other"/>
                                    </div>
                                </div>

                                {{-- Location --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="location" label="Specific Location of Asbestos" rows="3"/>
                                    </div>
                                </div>

                                <div id="non_friable_removal" style="display: none">
                                    <h3><br>Asbestos Removal</h3>
                                    <hr>
                                    {{-- Asbestos Removalist --}}
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.select name="removalist" label="Licensed Asbestos Removalist" :options="Auth::user()->company->asbestosRemovalSelect()"/>
                                        </div>
                                        <div class="col-md-6" id="removalist_name_div" style="display: none">
                                            <x-form.input name="removalist_name" label="Name of Removalist"/>
                                        </div>
                                    </div>
                                </div>


                                {{-- Non Friable Extra Fields --}}
                                <div id="non_friable_fields" style="display: none">
                                    {{-- Workers --}}
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.input name="workers" label="Number of workers involved in the asbestos removal work" onkeydown="return isNumber(event)"/>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="note note-warning">
                                                <p><b>NOTE:</b> All workers involved in the removal of Asbestos MUST have successfully completed relevant competency unit.</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Coal Mine --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="coalmine" label="Is this a coal or mining workplace" :options="['0' => 'No', '1' => 'Yes']"/>
                                        </div>
                                    </div>

                                    {{-- Asbestos Identification --}}
                                    <h4>Asbestos Identification
                                        <small>(Applicable to Friable / Asbestos in soils)</small>
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="hygiene" label="Is a hygienist report available" :options="['0' => 'No', '1' => 'Yes']"/>
                                        </div>
                                        <div class="col-md-3" id="hygiene_report_div">
                                            <x-form.select name="hygiene_report" label="Report type" :options="['' => 'Select type', 'Online Attachment' => 'Online Attachment', 'Email' => 'Email', 'Faxed' => 'Faxed', 'Post' => 'Post', 'By Hand' => 'By Hand']"/>
                                        </div>
                                    </div>

                                    {{-- Protective Equipment --}}
                                    <h4>Personal Protective Equipment &nbsp;
                                        <small>(Check all that apply)</small>
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12 {{ $errors->has('equip') ? 'has-error' : '' }}">
                                            <x-form.error name="equip"/>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline"> Protective coveralls
                                                        <input type="checkbox" name="equip[]" value="equip_overalls" @checked(in_array('equip_overalls', old('equip', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Protective gloves
                                                        <input type="checkbox" name="equip[]" value="equip_gloves" @checked(in_array('equip_gloves', old('equip', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> P2 Mask
                                                        <input type="checkbox" name="equip[]" value="equip_mask" @checked(in_array('equip_mask', old('equip', [])))>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline"> 1/2 face respirator
                                                        <input type="checkbox" name="equip[]" value="equip_half_face" @checked(in_array('equip_half_face', old('equip', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Full face air supplied
                                                        <input type="checkbox" name="equip[]" value="equip_full_face" @checked(in_array('equip_full_face', old('equip', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Other
                                                        <input type="checkbox" name="equip[]" value="equip_other" @checked(in_array('equip_other', old('equip', []))) onclick="checkbox_equipOther(this)">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="display: none;" id="equip_other_div">
                                            <x-form.input name="equip_other" label="Other Equipment" placeholder="Please specify other"/>
                                        </div>
                                    </div>

                                    {{-- Isolate Methods --}}
                                    <h4>Methods used to isolate / enclose the removal area &nbsp;
                                        <small>(Check all that apply)</small>
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12 {{ $errors->has('method') ? 'has-error' : '' }}">
                                            <x-form.error name="method"/>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline"> Fencing
                                                        <input type="checkbox" name="method[]" value="method_fencing" @checked(in_array('method_fencing', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Signage
                                                        <input type="checkbox" name="method[]" value="method_signage" @checked(in_array('method_signage', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Water
                                                        <input type="checkbox" name="method[]" value="method_water" @checked(in_array('method_water', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> PVA
                                                        <input type="checkbox" name="method[]" value="method_pva" @checked(in_array('method_pva', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline"> Barriers
                                                        <input type="checkbox" name="method[]" value="method_barriers" @checked(in_array('method_barriers', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> 200 μm plastic
                                                        <input type="checkbox" name="method[]" value="method_plastic" @checked(in_array('method_plastic', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Class H asbestos vacuum cleaners
                                                        <input type="checkbox" name="method[]" value="method_vacuum" @checked(in_array('method_vacuum', old('method', [])))>
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-checkbox mt-checkbox-outline"> Other
                                                        <input type="checkbox" name="method[]" value="method_other" @checked(in_array('method_other', old('method', []))) onclick="checkbox_methodOther(this)">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="display: none;" id="method_other_div">
                                            <x-form.input name="method_other" label="Other Method" placeholder="Please specify other"/>
                                        </div>
                                    </div>

                                    {{-- Isolation Entent --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="isolation" label="Extent of isolation / encapsulation (how will these methods be used)" rows="3"/>
                                        </div>
                                    </div>
                                </div>

                                <div id="non_friable_fields_part2" style="display: none">
                                    {{-- Reviewed Asbestos Register --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="register" class="control-label">Have you reviewed the applicable Asbestos Register to confirm the location of identified asbestos and conducted a site assessment to plan for the removal work?</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-form.select name="register" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No', 'N/A' => 'An Asbestos Register is not available for this site']"/>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="alert alert-danger" style="display: none;" id="register_note">
                                                <p><b>You must review the Asbestos Register relevant to the site</b></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- SWMS --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="swms" class="control-label">Have you confirmed a Safe Work Method Statement relevant to the asbestos removal work has been developed by the applicable workers?</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="swms" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No']"/>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="alert alert-danger" style="display: none;" id="swms_note">
                                                <p><b>Work involving asbestos is high risk. A SWMS must be in place for this work to take place.</b></p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Over 10m2 Removal Fields --}}
                                    <div id="amount_fields" style="display: none">
                                        <h3><br>Licensed Asbestos Removal (10m2)</h3>

                                        {{-- Inspection Certificate --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="note note-warning">
                                                    <p><b>Note:</b> A Clearance Inspection is legally required of the Asbestos Removal Area to verify that the area is safe for normal use. Following
                                                        inspection, a Clearance Insection Certificate MUST be obtained PRIOR to the Abestos Removal Area being reoccupied.
                                                        This must be conducted by an independant compentent person. Cape Cod enlists the services of Leon Carnevale to conduct clearance inspection and
                                                        action subsequent asbestos clearance certificate.</p>
                                                </div>
                                                <label for="inspection" class="control-label">Do you acknowledge that a clearance certificate must be received prior to normal use of the area?</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-form.select name="inspection" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No']"/>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="alert alert-danger" style="display: none;" id="inspection_note">
                                                    <p><b>Refer to WHS & HR Manager; Licensed Asbestos Removal Work is not to commence.</b></p>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Supervisor --}}
                                        {{-- Asbestos Assessment --}}
                                        <h4>Asbestos Assessment</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        {{-- Assessor Contact --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-form.select name="assessor_name" label="Assessor Name" :options="['' => 'Select option', 'Mark Spindler' => 'Mark Spindler', 'Moayad Khateib' => 'Moayad Khateib', 'Tony Gabriel' => 'Tony Gabriel']"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="assessor_phone" label="Assessor Phone"/>
                                            </div>
                                            <div class="col-md-4">
                                                <x-form.select name="assessor_cert" label="Assessor Qualification"
                                                               :options="['' => 'Select option', 'Competent person (VET Course)' => 'Competent person (VET Course)', 'Competent person (Tertiary qualification)' => 'Competent person (Tertiary qualification)', 'Licensed Asbestos Assessor' => 'Licensed Asbestos Assessor']"/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-form.input name="assessor_lic" label="Licence No."/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="assessor_dept" label="Department of Issue"/>
                                            </div>
                                            <div class="col-md-2">
                                                <x-form.select name="assessor_state" label="State" :options="$ozstates::all()" value="NSW"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br><br>
                                <div class="form-actions right">
                                    <a href="/site/asbestos/notification" class="btn default"> Back</a>
                                    <button type="submit" class="btn green"> Save</button>
                                </div>

                            </div> <!-- /Form body -->
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({
                placeholder: "Select Site",
            });

            displayFields();

            function displayFields() {
                var site_id = $("#site_id").select2("val");
                if (site_id != '') {
                    $.ajax({
                        url: '/site/data/details/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $("#site_name").val(data.name);
                            $("#site_code").val(data.code);
                            $("#site_address").val(data.address + ', ' + data.suburb + ' ' + data.state + ' ' + data.postcode);
                            $("#client_name").val(data.client_phone_desc);
                            $("#client_phone").val(data.client_phone);
                        },
                    })

                    $.ajax({
                        url: '/site/data/super/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $("#supervisor_id").val(data.id);
                            $("#supervisor_id").selectpicker('refresh');
                            $("#super_phone").val(data.phone);
                        },
                    })
                }
                // Amount
                if ($("#amount").val() > 9.999) {
                    $("#amount_note").show();
                    $("#amount_fields").show();
                    $("#amount_over").val('1');
                } else {
                    $("#amount_note").hide();
                    $("#amount_fields").hide();
                    $("#amount_over").val('0');
                }
                // Class 'Friable'
                $("#friable_note").hide();
                $("#non_friable_removal").hide();
                $("#non_friable_fields").hide();
                $("#non_friable_fields_part2").hide();
                if ($("#friable").val() == '1')
                    $("#friable_note").show();
                if ($("#friable").val() == '0')
                    $("#non_friable_removal").show();

                // Removalist
                $("#removalist_name_div").hide();
                if ($("#removalist").val() == '3') {
                    $("#removalist_name").val('Cape Cod Australia Pty Ltd AD205686');
                    $("#non_friable_fields").show();
                    $("#non_friable_fields_part2").show();
                }
                if ($("#removalist").val() == '507') {
                    $("#removalist_name").val('Asbestos Away');
                    $("#assessor_name").val('Tony Gabriel').change();
                    $("#non_friable_fields_part2").show();
                }
                if ($("#removalist").val() == '511') {
                    $("#removalist_name").val('SafeStrip 1st Pty Ltd');
                    $("#assessor_name").val('Moayad Khateib').change();
                    $("#non_friable_fields_part2").show();
                }
                if ($("#removalist").val() == 'other') {
                    $("#non_friable_fields_part2").show();
                    $("#removalist_name_div").show();
                }

                // Hygiene Report
                $("#hygiene_report_div").hide();
                if ($("#hygiene").val() == '1')
                    $("#hygiene_report_div").show();

                // Checkbox Other Equip + Method
                $('[name="equip[]"]').eq(5).is(':checked') ? $("#equip_other_div").show() : $("#equip_other_div").hide(); // Equip other
                $('[name="method[]"]').eq(7).is(':checked') ? $("#method_other_div").show() : $("#method_other_div").hide(); // Method other

                $("#type").val() == 'other' ? $("#type_other_div").show() : $("#type_other_div").hide(); // Type
                $("#register").val() == '0' ? $("#register_note").show() : $("#register_note").hide(); // Register
                $("#swms").val() == '0' ? $("#swms_note").show() : $("#swms_note").hide(); // SWMS
                $("#inspection").val() == '0' ? $("#inspection_note").show() : $("#inspection_note").hide();  // Inspection
            }

            // On Change Site ID
            $("#site_id").change(function () {
                displayFields();
            });

            // On Change Supervisor
            $("#supervisor_id").change(function () {
                $.ajax({
                    url: '/user/data/details/' + $("#supervisor_id").val(),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $("#super_phone").val(data.phone);
                    },
                })
            });

            // On Change Amount
            $("#amount").keyup(function () {
                displayFields();
            });

            // On Change Friable
            $("#friable").change(function () {
                displayFields();
            });

            // On Change Removalist
            $("#removalist").change(function () {
                $("#removalist_name").val('');
                if ($("#removalist").val() == '511')
                    $("#assessor_name").val('Moayad Khateib ').change();
                else
                    $("#assessor_name").val('').change();
                displayFields();
            });


            // On Change Type
            $("#type").change(function () {
                displayFields();
            });

            // On Change Hygiene Report
            $("#hygiene").change(function () {
                displayFields();
            });

            // On Change Equip
            $("#equip").click(function () {
                displayFields();
            });

            // On Change Register
            $("#register").change(function () {
                displayFields();
            });

            // On Change SWMS
            $("#swms").change(function () {
                displayFields();
            });

            // On Change Inspection
            $("#inspection").change(function () {
                displayFields();
            });

            // On Change Assessor
            $("#assessor_name").change(function () {
                if ($("#assessor_name").val() == 'Mark Spindler') {
                    $("#assessor_phone").val('0417 064 161');
                    $("#assessor_lic").val('');
                    $("#assessor_state").val('NSW');
                } else if ($("#assessor_name").val() == 'Moayad Khateib') {
                    $("#assessor_phone").val('0406 754 194');
                    $("#assessor_lic").val('');
                    $("#assessor_state").val('NSW');
                } else if ($("#assessor_name").val() == 'Tony Gabriel') {
                    $("#assessor_phone").val('0405 317 807');
                    $("#assessor_lic").val('');
                    $("#assessor_state").val('NSW');
                } else {
                    $("#assessor_phone").val('');
                    $("#assessor_lic").val('');
                    $("#assessor_dept").val('');
                    $("#assessor_state").val('');
                }
            });
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode == 190) // decimal .
                return true;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }

        function checkbox_equipOther(el) {
            if (el.checked)
                document.getElementById('equip_other_div').style.display = 'block'
            else {
                document.getElementById('equip_other_div').style.display = 'none';
                $("#equip_other").val('');
            }
        }

        function checkbox_methodOther(el) {
            if (el.checked)
                document.getElementById('method_other_div').style.display = 'block'
            else {
                document.getElementById('method_other_div').style.display = 'none';
                $("#method_other").val('');
            }
        }

    </script>
@stop
