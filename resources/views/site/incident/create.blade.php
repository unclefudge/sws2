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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'store']) }}" class="horizontal-form">
                            @csrf
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
                                        <x-form.select name="site_cc" label="Did the incident occur on a Cape Cod work site?" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No']"/>
                                    </div>
                                    {{-- Site ID --}}
                                    <div class="col-md-8" id="field_site_id">
                                        <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </x-form.select>
                                    </div>
                                    {{-- Site Name --}}
                                    <div class="col-md-8" id="field_site_name">
                                        <x-form.input name="site_name" label="Place of incident"/>
                                    </div>
                                </div>

                                {{-- Location --}}
                                <div class="row">
                                    {{-- Location --}}
                                    <div class="col-md-6 ">
                                        <x-form.input name="location" label="Location of Incident (be specific)"/>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Notification Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date" class="control-label">Date / Time of Incident</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" name="date" id="date" class="form-control" value="{{ old('date') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Type --}}
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <?php $qType = App\Models\Misc\FormQuestion::find(1) ?>
                                            <label for="type" class="control-label">{{ $qType->name }}</label>
                                            <x-form.select name="type[]" id="type" :options="$qType->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
                                        </div>
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="describe" label="Describe what occured" rows="3"/>
                                    </div>
                                </div>
                                {{-- Preventative Action --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="actions_taken" label="Immediate actions taken" rows="3"/>
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
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>

                                <div id="injury_details">
                                    <br>
                                    <h4 class="font-green-haze">Injury Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                    {{-- Treatment --}}
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <?php $qTreatment = App\Models\Misc\FormQuestion::find(14) ?>
                                                <label for="treatment" class="control-label">{{ $qTreatment->name }}</label>
                                                <x-form.select name="treatment[]" id="treatment" :options="$qTreatment->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="field_treatment_other">
                                            <x-form.input name="treatment_other" label="Other Treatment"/>
                                        </div>
                                    </div>

                                    {{-- Injured Parts --}}
                                    <div class="row">
                                        <div class="col-md-6 ">
                                            <div class="form-group">
                                                <?php $qInjuredPart = App\Models\Misc\FormQuestion::find(21) ?>
                                                <label for="injured_part" class="control-label">{{ $qInjuredPart->name }}</label>
                                                <x-form.select name="injured_part[]" id="injured_part" :options="$qInjuredPart->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="field_injured_part_other">
                                            <x-form.input name="injured_part_other" label="Other Body Part"/>
                                        </div>
                                    </div>

                                    @if (Auth::user()->hasPermission2('del.site.incident'))
                                        {{-- Nature of Injury --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                        <?php $qInjuredNature = App\Models\Misc\FormQuestion::find(50) ?>
                                                    <label for="injured_nature" class="control-label">{{ $qInjuredNature->name }}</label>
                                                    <x-form.select name="injured_nature[]" id="injured_nature" :options="$qInjuredNature->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Mechanism of Injury --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                        <?php $qInjuredMechanism = App\Models\Misc\FormQuestion::find(69) ?>
                                                    <label for="injured_mechanism" class="control-label">{{ $qInjuredMechanism->name }}</label>
                                                    <x-form.select name="injured_mechanism[]" id="injured_mechanism" :options="$qInjuredMechanism->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Agency of Injury --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                        <?php $qInjuredAgency = App\Models\Misc\FormQuestion::find(92) ?>
                                                    <label for="injured_agency" class="control-label">{{ $qInjuredAgency->name }}</label>
                                                    <x-form.select name="injured_agency[]" id="injured_agency" :options="$qInjuredAgency->optionsArray()" plugin="select2" multiple title="Check all applicable"/>
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
                                            <x-form.input name="damage" label="Property / Equipment Damage Details"/>
                                        </div>
                                        @if (Auth::user()->hasPermission2('del.site.incident'))
                                            {{-- Details --}}
                                            <div class="col-md-3 ">
                                                <x-form.input name="damage_cost" label="Cost of Repair / Replacement"/>
                                            </div>
                                        @endif
                                    </div>

                                    @if (Auth::user()->hasPermission2('del.site.incident'))
                                        <div class="row">
                                            {{-- Replacement --}}
                                            <div class="col-md-9 ">
                                                <x-form.textarea name="damage_repair" label="Repair / Replacement Details" rows="3"/>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/incident" class="btn default"> Back</a>
                                    <button type="submit" class="btn green" id="submit"> Save</button>
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


