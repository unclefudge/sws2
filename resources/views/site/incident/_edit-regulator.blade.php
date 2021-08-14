{{-- Edit Regulator --}}
<div class="portlet light" style="display: none;" id="edit_regulator">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Regulator Action Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@updateRegulator', $incident->id], 'class' => 'horizontal-form']) !!}
        {{-- Context --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('notifiable_reason', $errors) !!}">
                {!! Form::label('notifiable_reason', 'Context:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::textarea('notifiable_reason', null, ['rows' => '3', 'class' => 'form-control', 'required']) !!}
                    {!! fieldErrorMessage('notifiable_reason', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Regulator --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('regulator', $errors) !!}">
                {!! Form::label('regulator', 'Regulator:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('regulator', ($incident->regulator) ? $incident->regulator : 'Safework NSW', ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('regulator', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Regulator Ref --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('regulator_ref', $errors) !!}">
                {!! Form::label('regulator_ref', 'Regulator Ref:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('regulator_ref', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('regulator_ref', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Date/Time --}}
        <div class="row">
            {!! Form::label('regulator_date', 'Notified Date:', ['class' => 'col-md-3 control-label']) !!}
            <div class="col-md-9">
                <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                    {!! Form::text('regulator_date', ($incident->regulator_date) ? $incident->regulator_date->format('d/m/Y H:i') : '', ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                    <span class="input-group-addon">
                        <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                </div>
                {!! fieldErrorMessage('regulator_date', $errors) !!}
            </div>
        </div>
        <hr class="field-hr">
        {{-- Inspector --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('inspector', $errors) !!}">
                {!! Form::label('inspector', 'Inspector:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('inspector', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('inspector', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        {{-- Notes --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                {!! Form::label('notes', 'Notes:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                    {!! fieldErrorMessage('notes', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">


        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'regulator')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
