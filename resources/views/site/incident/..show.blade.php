@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Incident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Incident Report</span>
                            <span class="caption-helper"> ID: {{ $incident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($incident, ['method' => 'PATCH', 'action' => ['Site\Incident\SiteIncidentController@update', $incident->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                    {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('edit.site.incident', $incident) && false)
                                        {!! Form::select('site_id', Auth::user()->company->sitesSelect('prompt'), $incident->site_id, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    @else
                                        {!! Form::text('site_name', $incident->site->name, ['class' => 'form-control', 'disabled']) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::label('site_address', 'Address', ['class' => 'control-label']) !!}
                                    {!! Form::text('site_address', $incident->site->full_address, ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                    {!! Form::label('supervisor', 'Supervisor', ['class' => 'control-label']) !!}
                                    {!! Form::text('supervisor', $incident->supervisor, ['class' => 'form-control',
                                    (Auth::user()->allowed2('del.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('supervisor', $errors) !!}
                                </div>
                            </div>
                        </div>


                        <h4 class="font-green-haze">Notification Details</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                        {{-- Date + Reported by --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                    {!! Form::label('date', 'Date / Time of Incident', ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('date', $incident->date->format('d/m/Y - H:i'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                        </div>
                                        {!! fieldErrorMessage('date', $errors) !!}
                                    @else
                                        {!! Form::text('date', $incident->created_at->format('d/m/Y - H:i'), ['class' => 'form-control', 'disabled']) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('reported_by', 'Reported by', ['class' => 'control-label']) !!}
                                    {!! Form::text('reported_by', $incident->createdBy->fullname, ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::label('reported_at', 'Reported date', ['class' => 'control-label']) !!}
                                    {!! Form::text('reported_by', $incident->created_at->format('d/m/Y'), ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Incident Type --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                    <?php $qType = App\Models\Misc\FormQuestion::find(1) ?>
                                    {!! Form::label('type', $qType->name, ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                                        {!! Form::select('type', $qType->optionsArray(), $qType->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'type[]', 'multiple', 'id' => 'type']) !!}
                                        {!! fieldErrorMessage('type', $errors) !!}
                                    @else
                                        {!! Form::text('type_text', $qType->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly', 'id' => 'type_text']) !!}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- What occurred --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('describe', $errors) !!}">
                                    {!! Form::label('describe','Describe what occurred', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('describe', null, ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
                                    {!! fieldErrorMessage('describe', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Immediate Actions --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {!! fieldHasError('actions_taken', $errors) !!}">
                                    {!! Form::label('actions_taken','Immediate actions taken ', ['class' => 'control-label']) !!}
                                    {!! Form::textarea('actions_taken', null, ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
                                    {!! fieldErrorMessage('actions_taken', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Injury Details --}}
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
                                        @if (Auth::user()->allowed2('del.site.incident', $incident))
                                            {!! Form::select('treatment', $qTreatment->optionsArray(), $qTreatment->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'treatment[]', 'multiple', 'id' => 'treatment']) !!}
                                            {!! fieldErrorMessage('treatment', $errors) !!}
                                        @else
                                            {!! Form::text('treatment_text', $qTreatment->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6" id="field_treatment_other">
                                    <div class="form-group {!! fieldHasError('treatment_other', $errors) !!}">
                                        {!! Form::label('treatment_other', 'Other Treatment', ['class' => 'control-label']) !!}
                                        {!! Form::text('treatment_other', $qTreatment->responseOther('site_incidents', $incident->id, 20), ['class' => 'form-control']) !!}
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
                                        @if (Auth::user()->allowed2('del.site.incident', $incident))
                                            {!! Form::select('injured_part', $qInjuredPart->optionsArray(), $qInjuredPart->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_part[]', 'multiple', 'id' => 'injured_part']) !!}
                                            {!! fieldErrorMessage('injured_part', $errors) !!}
                                        @else
                                            {!! Form::text('injured_part_text', $qInjuredPart->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6" id="field_injured_part_other">
                                    <div class="form-group {!! fieldHasError('injured_part_other', $errors) !!}">
                                        {!! Form::label('injured_part_other', 'Other Body Part', ['class' => 'control-label']) !!}
                                        {!! Form::text('injured_part_other', $qInjuredPart->responseOther('site_incidents', $incident->id, 49), ['class' => 'form-control', (Auth::user()->allowed2('del.site.incident', $incident)) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('injured_part_other', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Nature of Injury --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('injured_nature', $errors) !!}">
                                        <?php $qInjuredNature = App\Models\Misc\FormQuestion::find(50) ?>
                                        {!! Form::label('injured_nature', $qInjuredNature->name, ['class' => 'control-label']) !!}
                                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                            {!! Form::select('injured_nature', $qInjuredNature->optionsArray(), $qInjuredNature->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_nature[]', 'id' => 'injured_nature']) !!}
                                            {!! fieldErrorMessage('injured_nature', $errors) !!}
                                        @else
                                            {!! Form::text('injured_part_text', $qInjuredNature->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Mechanism of Injury --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('injured_mechanism', $errors) !!}">
                                        <?php $qInjuredMechanism = App\Models\Misc\FormQuestion::find(69) ?>
                                        {!! Form::label('injured_mechanism', $qInjuredMechanism->name, ['class' => 'control-label']) !!}
                                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                            {!! Form::select('injured_mechanism', $qInjuredMechanism->optionsArray(), $qInjuredMechanism->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_mechanism[]', 'id' => 'injured_mechanism']) !!}
                                            {!! fieldErrorMessage('injured_mechanism', $errors) !!}
                                        @else
                                            {!! Form::text('injured_part_text', $qInjuredMechanism->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Agency of Injury --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('injured_agency', $errors) !!}">
                                        <?php $qInjuredAgency = App\Models\Misc\FormQuestion::find(92) ?>
                                        {!! Form::label('injured_agency', $qInjuredAgency->name, ['class' => 'control-label']) !!}
                                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                            {!! Form::select('injured_agency', $qInjuredAgency->optionsArray(), $qInjuredAgency->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_agency[]', 'id' => 'injured_agency']) !!}
                                            {!! fieldErrorMessage('injured_agency', $errors) !!}
                                        @else
                                            {!! Form::text('injured_agency_text', $qInjuredAgency->responsesCSV('site_incidents', $incident->id), ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                                        {!! Form::text('damage', null, ['class' => 'form-control', (Auth::user()->allowed2('del.site.incident', $incident)) ? '' : 'readonly' ]) !!}
                                        {!! fieldErrorMessage('damage', $errors) !!}
                                    </div>
                                </div>
                                @if (Auth::user()->allowed2('del.site.incident', $incident))
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

                            @if (Auth::user()->allowed2('del.site.incident', $incident))
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


                        {{-- Persons Involved --}}
                        <h4>Person(s) Involved <span class="pull-right" style="margin-top: -10px;"><a class="btn btn-circle green btn-outline btn-sm" href="/site/incident/{{ $incident->id }}/people/create" data-original-title="Add">Add</a></span></h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_people">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="20%"> Involvement Type</th>
                                <th> Name</th>
                                <th> Contact</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($incident->people->sortBy('name') as $person)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/site/incident/{{ $incident->id }}/people/{{ $person->id  }}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $person->typeName }}</td>
                                    <td>{{ $person->name }}</td>
                                    <td>{{ $person->contact }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $incident->displayUpdatedBy() !!}
        </div>
    </div>

    @stop <!-- END Content -->


@section('page-level-plugins-head')
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
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

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
            var treatment = $("#treatment").select2("val");
            var injured_part = $("#injured_part").select2("val");

            // Type
            if ($("#type_text").val())
                var types = $("#type_text").val().split(', ');
            else
                var types = $("#type").select2("val");

            $("#injury_details").hide();
            $("#damage_details").hide();
            $("#field_treatment_other").hide();
            $("#field_injured_part_other").hide();

            // Show relevant fields
            if (types != null && (types.includes('2') || types.includes('Injury / illness'))) $("#injury_details").show();
            if (types != null && (types.includes('3') || types.includes('Damage'))) $("#damage_details").show();
            if (treatment != null && treatment.includes('20')) $("#field_treatment_other").show(); // Other treatment
            if (injured_part != null && injured_part.includes('49')) $("#field_injured_part_other").show(); // Other part
        }

    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });
</script>
@stop

