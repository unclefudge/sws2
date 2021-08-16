{{-- Edit Notification Details --}}
<div class="portlet light" style="display: none;" id="edit_notification">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notification Details</span>
            @if($incident->status == 2)
                <span class="label label-warning">IN PROGRESS</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'PATCH', 'action' => ['Site\Incident\SiteIncidentController@update', $incident->id], 'class' => 'horizontal-form']) !!}
        @if ($incident->status != 0)
            <div class="row">
                {!! Form::label('date', 'Incident Date:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                        {!! Form::text('date', $incident->date->format('d/m/Y H:i'), ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                        <span class="input-group-addon">
                        <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                    </div>
                    {!! fieldErrorMessage('date', $errors) !!}
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('site_cc', $errors) !!}">
                    {!! Form::label('site_cc', 'Cape Cod site:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('site_cc', ['1' => 'Yes', '0' => 'No'], ($incident->site_id) ? '1' : '0', ['class' => 'form-control bs-select', 'id' => 'site_cc']) !!}
                        {!! fieldErrorMessage('site_cc', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div id="field_site_id">
                <div class="row">
                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                        {!! Form::label('site_id', 'Site', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id', $incident->site_id)) !!}
                            </select>
                            {!! fieldErrorMessage('site_id', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            </div>
            <div id="field_site_name">
                <div class="row">
                    <div class="form-group {!! fieldHasError('site_name', $errors) !!}">
                        {!! Form::label('site_name', 'Place of incident:', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::text('site_name', null, ['class' => 'form-control', 'required']) !!}
                            {!! fieldErrorMessage('site_name', $errors) !!}
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            </div>
            <div class="row">
                <div class="form-group {!! fieldHasError('site_supervisor', $errors) !!}">
                    {!! Form::label('location', 'Location:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('location', null, ['class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('location', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('type', $errors) !!}">
                    {!! Form::label('type', 'Incident Type:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('type', $qType->optionsArray(), $qType->responsesArray('site_incidents', $incident->id), ['class' => 'form-control select2 ', 'multiple', 'title' => 'Check all applicable', 'required', 'width' => '100%', 'name' => 'type[]', 'id' => 'type']) !!}
                        {!! fieldErrorMessage('type', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('describe', $errors) !!}">
                    {!! Form::label('describe', 'What occured:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('describe', null, ['rows' => '3', 'class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('describe', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <div class="form-group {!! fieldHasError('actions_taken', $errors) !!}">
                    {!! Form::label('actions_taken', 'Actions taken:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('actions_taken', null, ['rows' => '3', 'class' => 'form-control', 'required']) !!}
                        {!! fieldErrorMessage('actions_taken', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Supervisor--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('site_supervisor', $errors) !!}">
                    {!! Form::label('site_supervisor', 'Supervisor', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::text('site_supervisor', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('site_supervisor', $errors) !!}
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'notification')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
