{{-- Edit Preventive --}}
<div class="portlet light" style="display: none;" id="edit_prevent">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Actions to Prevent Reoccurence</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentAnalysisController@updatePrevent', $incident->id], 'class' => 'horizontal-form']) !!}

        {{-- Absent / Failed Defences --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('response_236', $errors) !!}">
                {!! Form::label('response_236', 'Preventive Strategies:', ['class' => 'col-md-2 control-label']) !!}
                <div class="col-md-10">
                    {!! Form::select('response_236', $qPreventive->optionsArray(), $qPreventive->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_236[]', 'id' => 'response_236']) !!}
                    {!! fieldErrorMessage('response_236', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'prevent')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
