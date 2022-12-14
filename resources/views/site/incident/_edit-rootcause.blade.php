{{-- Edit Root Cause --}}
<div class="portlet light" style="display: none;" id="edit_rootcause">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Root Cause - Organisation Factors</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentAnalysisController@updateRootcause', $incident->id], 'class' => 'horizontal-form']) !!}

        {{-- Root Cause --}}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group {!! fieldHasError('response_219', $errors) !!}">
                    {!! Form::label('response_219', 'Root Cause:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_219', $qRootCause->optionsArray(), $qRootCause->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_219[]', 'id' => 'response_219']) !!}
                    {!! fieldErrorMessage('response_219', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        @foreach ($qRootCause->optionsArray() as $id => $label)
            <div id="field_response_{{ $id }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {!! fieldHasError("response_$id", $errors) !!}">
                            {!! Form::label("response_$id", "$label", ['class' => 'control-label']) !!}
                            {!! Form::text("response_$id", old("response_$id", $qRootCause->responseOther('site_incidents', $incident->id, $id)), ['class' => 'form-control']) !!}
                            {!! fieldErrorMessage("response_$id", $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach


        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'rootcause')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
