{{-- Edit Conditions --}}
<div class="portlet light" style="display: none;" id="edit_conditions">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Conditions</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentAnalysisController@updateConditions', $incident->id], 'class' => 'horizontal-form']) !!}

        {{-- Conditions --}}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group {!! fieldHasError('response_113', $errors) !!}">
                    {!! Form::label('response_113', 'Conditions:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_113', $qConditions->optionsArray(), $qConditions->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'required', 'width' => '100%', 'name' => 'response_113[]', 'id' => 'response_113']) !!}
                    {!! fieldErrorMessage('response_113', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        @foreach ($qConditions->optionsArray() as $id => $label)
            <div id="field_response_{{ $id }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {!! fieldHasError("response_$id", $errors) !!}">
                            {!! Form::label("response_$id", "$label", ['class' => 'control-label']) !!}
                            {!! Form::text("response_$id", old("response_$id", $qConditions->responseOther('site_incidents', $incident->id, $id)), ['class' => 'form-control']) !!}
                            {!! fieldErrorMessage("response_$id", $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'conditions')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
