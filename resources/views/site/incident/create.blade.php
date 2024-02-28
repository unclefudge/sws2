@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription)
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Lodge Incident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="note note-warning">
            To be completed by the Primary Contractor AND Construction Supervisor immediately after:
            <ul>
                <li>A lost time injury or</li>
                <li>A incident with the potenital cause serious injury / illness occurs</li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Lodge Incident Report</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('siteIncident', ['action' => 'Site\Incident\SiteIncidentController@store', 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        {{-- Progress Steps --}}
                        <div class="mt-element-step hidden-sm hidden-xs">
                            <div class="row step-thin" id="steps">
                                <div class="col-md-6 mt-step-col first active">
                                    <div class="mt-step-number bg-white font-grey">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Lodge</div>
                                    <div class="mt-step-content font-grey-cascade">Lodge notification</div>
                                </div>
                                <div class="col-md-6 mt-step-col">
                                    <div class="mt-step-number bg-white font-grey">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">People</div>
                                    <div class="mt-step-content font-grey-cascade">Add people involved</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-body">
                            <h4 class="font-green-haze">Site Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Site detail --}}
                            <div class="row">
                                {{-- CC Site --}}
                                <div class="col-md-4 ">
                                    <div class="form-group {!! fieldHasError('site_cc', $errors) !!}">
                                        {!! Form::label('site_cc', 'Did the incident occur on a Cape Cod work site?', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_cc', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select', 'id' => 'site_cc']) !!}
                                        {!! fieldErrorMessage('site_cc', $errors) !!}
                                    </div>
                                </div>
                                {{-- Site ID --}}
                                <div class="col-md-8" id="field_site_id">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                {{-- Site Name --}}
                                <div class="col-md-8" id="field_site_name">
                                    <div class="form-group {!! fieldHasError('site_name', $errors) !!}">
                                        {!! Form::label('site_name', 'Place of incident', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('site_name', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="row">
                                {{-- Location --}}
                                <div class="col-md-6 ">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Location of Incident (be specific)', ['class' => 'control-label']) !!}
                                        {!! Form::text('location', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Notification Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                        {!! Form::label('date', 'Date / Time of Incident', ['class' => 'control-label']) !!}
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('date', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('date', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Type --}}
                                <div class="col-md-6 ">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        <?php $qType = App\Models\Misc\FormQuestion::find(1) ?>
                                        {!! Form::label('type', $qType->name, ['class' => 'control-label']) !!}
                                        {!! Form::select('type', $qType->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'type[]', 'multiple', 'id' => 'type']) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('describe', $errors) !!}">
                                        {!! Form::label('describe', 'Describe what occured', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('describe', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('describe', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            {{-- Preventative Action --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('actions_taken', $errors) !!}">
                                        {!! Form::label('actions_taken', 'Immediate actions taken', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('actions_taken', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('actions_taken', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Photos / Documents</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
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

                            <div id="injury_details">
                                <br>
                                <h4 class="font-green-haze">Injury Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                {{-- Treatment --}}
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group {!! fieldHasError('treatment', $errors) !!}">
                                            <?php $qTreatment = App\Models\Misc\FormQuestion::find(14) ?>
                                            {!! Form::label('treatment', $qTreatment->name, ['class' => 'control-label']) !!}
                                            {!! Form::select('treatment', $qTreatment->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'treatment[]', 'multiple', 'id' => 'treatment']) !!}
                                            {!! fieldErrorMessage('treatment', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="field_treatment_other">
                                        <div class="form-group {!! fieldHasError('treatment_other', $errors) !!}">
                                            {!! Form::label('treatment_other', 'Other Treatment', ['class' => 'control-label']) !!}
                                            {!! Form::text('treatment_other', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('treatment_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Injured Parts --}}
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group {!! fieldHasError('injured_part', $errors) !!}">
                                            <?php $qInjuredPart = App\Models\Misc\FormQuestion::find(21) ?>
                                            {!! Form::label('injured_part', $qInjuredPart->name, ['class' => 'control-label']) !!}
                                            {!! Form::select('injured_part', $qInjuredPart->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_part[]', 'multiple', 'id' => 'injured_part']) !!}
                                            {!! fieldErrorMessage('injured_part', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="field_injured_part_other">
                                        <div class="form-group {!! fieldHasError('injured_part_other', $errors) !!}">
                                            {!! Form::label('injured_part_other', 'Other Body Part', ['class' => 'control-label']) !!}
                                            {!! Form::text('injured_part_other', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('injured_part_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                @if (Auth::user()->hasPermission2('del.site.incident'))
                                    {{-- Nature of Injury --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group {!! fieldHasError('injured_nature', $errors) !!}">
                                                    <?php $qInjuredNature = App\Models\Misc\FormQuestion::find(50) ?>
                                                {!! Form::label('injured_nature', $qInjuredNature->name, ['class' => 'control-label']) !!}
                                                {!! Form::select('injured_nature', $qInjuredNature->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_nature[]', 'id' => 'injured_nature']) !!}
                                                {!! fieldErrorMessage('injured_nature', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Mechanism of Injury --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group {!! fieldHasError('injured_mechanism', $errors) !!}">
                                                    <?php $qInjuredMechanism = App\Models\Misc\FormQuestion::find(69) ?>
                                                {!! Form::label('injured_mechanism', $qInjuredMechanism->name, ['class' => 'control-label']) !!}
                                                {!! Form::select('injured_mechanism', $qInjuredMechanism->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_mechanism[]', 'id' => 'injured_mechanism']) !!}
                                                {!! fieldErrorMessage('injured_mechanism', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Agency of Injury --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group {!! fieldHasError('injured_agency', $errors) !!}">
                                                    <?php $qInjuredAgency = App\Models\Misc\FormQuestion::find(92) ?>
                                                {!! Form::label('injured_agency', $qInjuredAgency->name, ['class' => 'control-label']) !!}
                                                {!! Form::select('injured_agency', $qInjuredAgency->optionsArray(), null, ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_agency[]', 'id' => 'injured_agency']) !!}
                                                {!! fieldErrorMessage('injured_agency', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Damage Details --}}
                            <div id="damage_details">
                                <br>
                                <h4 class="font-green-haze">Damage Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Details --}}
                                    <div class="col-md-9 ">
                                        <div class="form-group {!! fieldHasError('damage', $errors) !!}">
                                            {!! Form::label('damage', 'Property / Equipment Damage Details', ['class' => 'control-label']) !!}
                                            {!! Form::text('damage', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('damage', $errors) !!}
                                        </div>
                                    </div>
                                    @if (Auth::user()->hasPermission2('del.site.incident'))
                                        {{-- Details --}}
                                        <div class="col-md-3 ">
                                            <div class="form-group {!! fieldHasError('damage_cost', $errors) !!}">
                                                {!! Form::label('damage_cost', 'Cost of Repair / Replacement', ['class' => 'control-label']) !!}
                                                {!! Form::text('damage_cost', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('damage_cost', $errors) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if (Auth::user()->hasPermission2('del.site.incident'))
                                    <div class="row">
                                        {{-- Replacement --}}
                                        <div class="col-md-9 ">
                                            <div class="form-group {!! fieldHasError('damage_repair', $errors) !!}">
                                                {!! Form::label('damage_repair', 'Repair / Replacement Details', ['class' => 'control-label']) !!}
                                                {!! Form::textarea('damage_repair', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('damage_repair', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-actions right">
                                <a href="/site/incident" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});
            $("#type").select2({placeholder: "Check all applicable"});
            $("#treatment").select2({placeholder: "Check all applicable"});
            $("#injured_part").select2({placeholder: "Check all applicable"});
            $("#injured_nature").select2({placeholder: "Check all applicable"});
            $("#injured_mechanism").select2({placeholder: "Check all applicable"});
            $("#injured_agency").select2({placeholder: "Check all applicable"});

            updateFields();

            // On Change Site CC
            $("#site_cc").change(function () {
                updateFields();
            });

            // On Change Site ID
            $("#site_id").change(function () {
                updateFields();
            });

            // On Change Type
            $("#type").change(function () {
                updateFields();
            });

            // On Change Treatment
            $("#treatment").change(function () {
                updateFields();
            });

            // On Change Injured Part
            $("#injured_part").change(function () {
                updateFields();
            });

            function updateFields() {
                var site_id = $("#site_id").select2("val");
                var types = $("#type").select2("val");
                var treatment = $("#treatment").select2("val");
                var part = $("#injured_part").select2("val");

                $("#injury_details").hide()
                $("#damage_details").hide()
                $("#field_site_id").hide()
                $("#field_site_name").hide()
                $("#field_treatment_other").hide()
                $("#field_injured_part_other").hide()

                if ($("#site_cc").val() == '1') $("#field_site_id").show() // Site id
                if ($("#site_cc").val() == '0') $("#field_site_name").show() // Site name
                if (types != null && types.includes('2')) $("#injury_details").show()
                if (types != null && types.includes('3')) $("#damage_details").show()
                if (treatment != null && treatment.includes('20')) $("#field_treatment_other").show() // Other treatment
                if (part != null && part.includes('49')) $("#field_injured_part_other").show() // Other part
            }

        });

        // Force datepicker to not be able to select dates after today
        $('.bs-datetime').datetimepicker({
            endDate: new Date(),
            format: 'dd/mm/yyyy hh:ii',
        });

        swal({
            title: "Scene of Incident",
            text: "The incident scene should be preserved and only disturbed to faciliate emergency response and/or to make safe!",
            cancelButtonColor: "#555555",
            confirmButtonColor: "#E7505A",
            confirmButtonText: "Yes, I understand!",
            allowOutsideClick: true,
            html: true,
        });
    </script>
@stop


