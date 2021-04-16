@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create Notification</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($asb, ['method' => 'PATCH', 'action' => ['Site\SiteAsbestosController@update', $asb->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        {!! Form::hidden('site_id', $asb->site_id) !!}
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        {!! Form::hidden('amount_over', '0', ['id' => 'amount_over']) !!}
                        <div class="form-body">

                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $asb->site->name }}</h2>
                                    {{ $asb->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$asb->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">CLOSED</h2>
                                    @endif
                                    <b>Site No:</b> {{ $asb->site->code }}<br>
                                    <b>Supervisor(s):</b> {{ $asb->site->supervisorsSBC() }}<br>
                                </div>
                            </div>
                            <hr>

                            {{-- Amount --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('amount', $errors) !!}">
                                        {!! Form::label('amount', 'Amount to be removed (m2)', ['class' => 'control-label']) !!}
                                        <input type="text" class="form-control" value="{{ (old('amount')) ? old('amount') : $asb->amount }}" id="amount" name="amount" onkeypress="return isNumber(event)">
                                        {!! fieldErrorMessage('amount', $errors) !!}
                                    </div>
                                    <div class="note note-warning" style="display: none;" id="amount_note">
                                        <p>Volumes over 10m2 are classed as licensed asbestos removal.</p>
                                        <ul>
                                            <li><b>5 calendar days notice to SafeWork is required.</b></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('friable', $errors) !!}">
                                        {!! Form::label('friable', 'Asbestos Class', ['class' => 'control-label']) !!}
                                        {!! Form::select('friable', ['' => 'Select class', '1' => 'Class A (Friable)', '0' => 'Class B (Non-Friable)'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('friable', $errors) !!}
                                    </div>
                                    <div class="note note-warning" style="display: none;" id="friable_note">
                                        <p><b>NOTE:</b> Cape Cod does not hold the Licence Class required to handle this type of Asbestos</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Type --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                        {!! Form::select('type', ['' => 'Select type', 'Asbestos Cement Sheets/Products' => 'Asbestos Cement Sheets/Products',
                                        'Vinyl floor covering' => 'Vinyl floor covering', 'other' => 'Other'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6" style="display: none" id="type_other_div">
                                    <div class="form-group {!! fieldHasError('type_other', $errors) !!}">
                                        {!! Form::label('type_other', 'Other type', ['class' => 'control-label']) !!}
                                        {!! Form::text('type_other', null, ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                        {!! fieldErrorMessage('type_other', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Specific Location of Asbestos', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('location', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Dates - Open Hours --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('from', $errors) !!}">
                                        {!! Form::label('from', 'Proposed dates of asbestos removal work', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-start-date="0d">
                                            {!! Form::text('date_from', $asb->date_from->format('d/m/Y'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('date_to',  $asb->date_to->format('d/m/Y'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                        </div>
                                        {!! fieldErrorMessage('from', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('hours_from', $errors) !!} {!! fieldHasError('open_to', $errors) !!}">
                                        {!! Form::label('hours_from', 'Operating hours of the site', ['class' => 'control-label']) !!}
                                        <div class="input-group">
                                            {!! Form::text('hours_from', null, ['class' => 'form-control timepicker timepicker-no-seconds']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('hours_to', null, ['class' => 'form-control timepicker timepicker-no-seconds']) !!}
                                        </div>
                                        {!! fieldErrorMessage('hours_from', $errors) !!}
                                        {!! fieldErrorMessage('hours_to', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Non Friable Extra Fields --}}
                            <div id="non_friable_fields" style="display: none">
                                <h3><br>Cape Cod to perform Asbestos Removal</h3>
                                <hr>
                                {{-- Workers --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('workers', $errors) !!}">
                                            {!! Form::label('workers', 'Number of workers involved in the asbestos removal work', ['class' => 'control-label']) !!}
                                            <input type="text" class="form-control" value="{{ (old('workers')) ? old('workers') : $asb->workers }}" id="workers" name="workers" onkeypress="return isNumber(event)"/>
                                            {!! fieldErrorMessage('workers', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="note note-warning">
                                            <p><b>NOTE:</b> All workers involved in the removal of Asbestos MUST have successfully completed relevant competency unit.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Protective Equipment --}}
                                <div class="row">
                                    <div class="col-md-12 {!! fieldHasError('equip', $errors) !!}">
                                        Personal Protective Equipment to be used &nbsp; &nbsp; <i>(Check all that apply)</i>
                                        {!! fieldErrorMessage('equip', $errors) !!}</div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Protective coveralls
                                                    <input type="checkbox" value="equip_overalls" name="equip[]" {{ ($asb->equip_overalls) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Protective gloves
                                                    {!! Form::checkbox('equip[]', 'equip_gloves') !!}
                                                    <input type="checkbox" value="equip_gloves" name="equip[]" {{ ($asb->equip_gloves) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> P2 Mask
                                                    {!! Form::checkbox('equip[]','') !!}
                                                    <input type="checkbox" value="equip_mask" name="equip[]" {{ ($asb->equip_mask) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> 1/2 face respirator
                                                    <input type="checkbox" value="equip_half_face" name="equip[]" {{ ($asb->equip_half_face) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Full face air supplied
                                                    <input type="checkbox" value="equip_full_face" name="equip[]" {{ ($asb->equip_full_face) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Other
                                                    <input type="checkbox" value="equip_other" name="equip[]" {{ (old('equip_other') || $asb->equip_other)  ? 'checked="checked" ' : '' }} onClick='checkbox_equipOther(this)'>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="{{ ($asb->equip_other || old('equip_other')) ? '' : 'display: none' }}" id="equip_other_div">
                                        <div class="form-group {!! fieldHasError('equip_other', $errors) !!}">
                                            {!! Form::label('equip_other', 'Other Equipment', ['class' => 'control-label']) !!}
                                            {!! Form::text('equip_other', null, ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                            {!! fieldErrorMessage('equip_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isolate Methods --}}
                                <div class="row">
                                    <div class="col-md-12 {!! fieldHasError('method', $errors) !!}">
                                        Methods used to isolate / enclose the removal area &nbsp; &nbsp; <i>(Check all that apply)</i>
                                        {!! fieldErrorMessage('method', $errors) !!}
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Fencing
                                                    <input type="checkbox" value="method_fencing" name="method[]" {{ ($asb->method_fencing) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Signage
                                                    <input type="checkbox" value="method_signage" name="method[]" {{ ($asb->method_signage) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Water
                                                    <input type="checkbox" value="method_water" name="method[]" {{ ($asb->method_water) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> PVA
                                                    <input type="checkbox" value="method_pva" name="method[]" {{ ($asb->method_pva) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Barriers
                                                    <input type="checkbox" value="method_barriers" name="method[]" {{ ($asb->method_barriers) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> 200 μm plastic
                                                    <input type="checkbox" value="method_plastic" name="method[]" {{ ($asb->method_plastic) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Class H asbestos vacuum cleaners
                                                    <input type="checkbox" value="method_vacuum" name="method[]" {{ ($asb->method_vacuum) ? 'checked="checked" ' : '' }}>
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Other
                                                    <input type="checkbox" value="method_other" name="method[]" {{ (old('method_other') || $asb->method_other)  ? 'checked="checked" ' : '' }} onClick='checkbox_methodOther(this)'>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="{{ ($asb->method_other || old('method_other')) ? '' : 'display: none' }}" id="method_other_div">
                                        <div class="form-group {!! fieldHasError('method_other', $errors) !!}">
                                            {!! Form::label('method_other', 'Other Method', ['class' => 'control-label']) !!}
                                            {!! Form::text('method_other', null, ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                            {!! fieldErrorMessage('method_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isolation Entent --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('isolation', $errors) !!}">
                                            {!! Form::label('isolation', 'Extent of isolation / encapsulation (how will these methods be used)', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('isolation', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('isolation', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Reviewed Asbestos Register --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        {!! Form::label('register', 'Have you reviewed the applicable Asbestos Register to confirm the location of identified asbestos and conducted a site assessment to plan for the removal work?', ['class' => 'control-label']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('register', $errors) !!}">
                                            {!! Form::select('register', ['' => 'Select option', '1' => 'Yes', '0' => 'No', 'N/A' => 'An Asbestos Register is not available for this site'],
                                                 null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('register', $errors) !!}
                                        </div>
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
                                        {!! Form::label('swms', 'Have you confirmed a Safe Work Method Statement relevant to the asbestos removal work has been developed by the applicable workers?', ['class' => 'control-label']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('swms', $errors) !!}">
                                            {!! Form::select('swms', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('swms', $errors) !!}
                                        </div>
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
                                            {!! Form::label('inspection', 'Do you acknowledge that a clearance certificate* must be received prior to normal use of the area?', ['class' => 'control-label']) !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('inspection', $errors) !!}">
                                                {!! Form::select('inspection', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('inspection', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="alert alert-danger" style="display: none;" id="inspection_note">
                                                <p><b>Refer to WHS & HR Manager; Licensed Asbestos Removal Work is not to commence.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Supervisor --}}
                                    <div class="row" style="padding-top: 10px">
                                        <div class="col-md-3">
                                            {!! Form::label('supervisor_id', 'Asbestos Supervisor', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_id', ['' => 'Select supervisor', '5' => 'Dean Beringer', '7' => 'Gary Klomp', '13' => 'John Walton'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_id', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/asbestos/{{ $asb->id }}" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {

        /* Select2 */
        $("#site_id").select2({
            placeholder: "Select Site",
        });

        displayFields();

        function displayFields() {
            // Amount
            if ($("#amount").val() > 9) {
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
            $("#non_friable_fields").hide();
            if ($("#friable").val() == '1')
                $("#friable_note").show();
            if ($("#friable").val() == '0')
                $("#non_friable_fields").show();

            // Checkbox Equip
            //alert($("#equip").val());
            if ($('[name="equip[]"]').eq(5).is(':checked') || $old('equip_other')) {  // Equip other
                $("#equip_other_div").show()
            } else {
                $("#equip_other_div").hide();
            }
            if ($('[name="method[]"]').eq(7).is(':checked') || $old('method_other')) {  // Equip method
                $("#equip_other_div").show()
            } else {
                $("#equip_other_div").hide();
            }
            $("#type").val() == 'other' ? $("#type_other_div").show() : $("#type_other_div").hide(); // Type
            $("#register").val() == '0' ? $("#register_note").show() : $("#register_note").hide(); // Register
            $("#swms").val() == '0' ? $("#swms_note").show() : $("#swms_note").hide(); // SWMS
            $("#inspection").val() == '0' ? $("#inspection_note").show() : $("#inspection_note").hide();  // Inspection
        }

        // On Change Amount
        $("#amount").keyup(function () {
            displayFields();
        });

        // On Change Class 'Friable'
        $("#friable").change(function () {
            displayFields();
        });

        // On Change Type
        $("#type").change(function () {
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
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
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

