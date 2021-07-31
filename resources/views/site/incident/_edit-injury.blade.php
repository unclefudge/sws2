{{-- Edit Injury Details --}}
<div class="portlet light" style="display: none;" id="edit_injury">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Injury Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@updateInjury', $incident->id], 'class' => 'horizontal-form']) !!}
        @if ($incident->status != 0)
            <div class="row">
                {!! Form::label('treatment', 'Treatment:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('treatment', $qTreatment->optionsArray(), $qTreatment->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'treatment[]', 'id' => 'treatment']) !!}
                    {!! fieldErrorMessage('treatment', $errors) !!}
                </div>
            </div>
            <hr class="field-hr">
            <div id="field_treatment_other">
                <div class="row">
                    <div class="form-group {!! fieldHasError('treatment_other', $errors) !!}">
                        {!! Form::label('treatment_other', 'Treatment Other:', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::text('treatment_other', old('treatment_other', $qTreatment->responseOther('site_incidents', $incident->id, 20)), ['class' => 'form-control']) !!}
                            {!! fieldErrorMessage('treatment_other', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            </div>
            <div class="row">
                <div class="form-group {!! fieldHasError('injured_part', $errors) !!}">
                    {!! Form::label('inured_part', 'Part(s) Injured:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('inured_part', $qInjuredPart->optionsArray(), $qInjuredPart->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width-min' => '200px',  'name' => 'injured_part[]', 'id' => 'injured_part']) !!}
                        {!! fieldErrorMessage('inured_part', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div id="field_injured_part_other">
                <div class="row">
                    <div class="form-group {!! fieldHasError('injured_part_other', $errors) !!}">
                        {!! Form::label('injured_part_other', 'Part Other:', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::text('injured_part_other', old('injured_part_other', $qInjuredPart->responseOther('site_incidents', $incident->id, 49)), ['class' => 'form-control']) !!}
                            {!! fieldErrorMessage('injured_part_other', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            </div>
            <div>
                <div class="row">
                    <div class="form-group {!! fieldHasError('injured_nature', $errors) !!}">
                        {!! Form::label('injured_nature', 'Nature of Injury', ['class' => 'col-md-12 control-label']) !!}
                        <div class="col-md-12">
                            {!! Form::select('injured_nature', $qInjuredNature->optionsArray(), $qInjuredNature->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_nature[]', 'id' => 'injured_nature']) !!}
                            {!! fieldErrorMessage('injured_nature', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div>
                <div class="row">
                    <div class="form-group {!! fieldHasError('injured_mechanism', $errors) !!}">
                        {!! Form::label('injured_mechanism', 'Mechanism of Injury:', ['class' => 'col-md-12 control-label']) !!}
                        <div class="col-md-12">
                            {!! Form::select('injured_mechanism', $qInjuredMechanism->optionsArray(), $qInjuredMechanism->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_mechanism[]', 'id' => 'injured_mechanism']) !!}
                            {!! fieldErrorMessage('injured_mechanism', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('injured_agency', $errors) !!}">
                    {!! Form::label('injured_agency', 'Agency of Injury:', ['class' => 'col-md-12 control-label']) !!}
                    <div class="col-md-12">
                        {!! Form::select('injured_agency', $qInjuredAgency->optionsArray(), $qInjuredAgency->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable',  'name' => 'injured_agency[]', 'id' => 'injured_agency']) !!}
                        {!! fieldErrorMessage('injured_agency', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
        @endif

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'injury')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
