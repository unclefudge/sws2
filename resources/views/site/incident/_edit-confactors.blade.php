{{-- Edit Con Factors --}}
<div class="portlet light" style="display: none;" id="edit_confactors">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contributing Factors</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentAnalysisController@updateConfactors', $incident->id], 'class' => 'horizontal-form']) !!}

        {{-- Absent / Failed Defences --}}
        <div class="row">
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::label("response_125", 'Absent / Failed Defences:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_125', $qConFactorDefences->optionsArray(), $qConFactorDefences->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_125[]', 'id' => 'response_125']) !!}
                </div>
            </div>
        </div>
        <div id="field_response_147">
            <br>
            <div class="row">
                <div class="form-group {!! fieldHasError('response_147', $errors) !!}">
                    {!! Form::label('response_147', 'Other:', ['class' => 'col-md-2 control-label']) !!}
                    <div class="col-md-10">
                        {!! Form::text('response_147', old('response_147', $qConFactorDefences->responseOther('site_incidents', $incident->id, 147)), ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('response_147', $errors) !!}
                    </div>
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Individual / Team Actions --}}
        <div class="row">
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::label("response_148", 'Individual / Team Actions:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_148', $qConFactorITactions->optionsArray(), $qConFactorITactions->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_148[]', 'id' => 'response_148']) !!}
                </div>
            </div>
        </div>
        <div id="field_response_166">
            <br>
            <div class="row">
                <div class="form-group {!! fieldHasError('response_166', $errors) !!}">
                    {!! Form::label('response_166', 'Other:', ['class' => 'col-md-2 control-label']) !!}
                    <div class="col-md-10">
                        {!! Form::text('response_166', old('response_166', $qConFactorITactions->responseOther('site_incidents', $incident->id, 166)), ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('response_166', $errors) !!}
                    </div>
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Workplace Conditions --}}
        <div class="row">
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::label("response_167", 'Task / Environment Workplace Conditions:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_167', $qConFactorWorkplace->optionsArray(), $qConFactorWorkplace->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_167[]', 'id' => 'response_167']) !!}
                </div>
            </div>
        </div>
        <div id="field_response_191">
            <br>
            <div class="row">
                <div class="form-group {!! fieldHasError('response_191', $errors) !!}">
                    {!! Form::label('response_191', 'Other:', ['class' => 'col-md-2 control-label']) !!}
                    <div class="col-md-10">
                        {!! Form::text('response_191', old('response_191', $qConFactorWorkplace->responseOther('site_incidents', $incident->id, 191)), ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('response_191', $errors) !!}
                    </div>
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{-- Human Factors --}}
        <div class="row">
            <div class="form-group">
                <div class="col-md-12">
                    {!! Form::label("response_192", 'Task / Environment Conditions - Human Factors:', ['class' => 'control-label']) !!}
                    {!! Form::select('response_192', $qConFactorHuman->optionsArray(), $qConFactorHuman->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'width' => '100%', 'name' => 'response_192[]', 'id' => 'response_192']) !!}
                </div>
            </div>
        </div>
        <div id="field_response_218">
            <br>
            <div class="row">
                <div class="form-group {!! fieldHasError('response_218', $errors) !!}">
                    {!! Form::label('response_218', 'Other:', ['class' => 'col-md-2 control-label']) !!}
                    <div class="col-md-10">
                        {!! Form::text('response_218', old('response_218', $qConFactorHuman->responseOther('site_incidents', $incident->id, 218)), ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('response_218', $errors) !!}
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'confactors')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
