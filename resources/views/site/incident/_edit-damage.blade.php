{{-- Edit Damage Details --}}
<div class="portlet light" style="display: none;" id="edit_damage">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Damage Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@updateDamage', $incident->id], 'class' => 'horizontal-form']) !!}

        <div class="row">
            <div class="form-group {!! fieldHasError('damage', $errors) !!}">
                {!! Form::label('damage', 'Damage Details:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('damage', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('damage', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="form-group {!! fieldHasError('damage_cost', $errors) !!}">
                {!! Form::label('damage_cost', 'Repair Cost:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::text('damage_cost', null, ['class' => 'form-control']) !!}
                    {!! fieldErrorMessage('damage_cost', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="form-group {!! fieldHasError('damage_repair', $errors) !!}">
                {!! Form::label('damage_repair', 'Repair Details:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::textarea('damage_repair', null, ['rows' => '3', 'class' => 'form-control']) !!}
                    {!! fieldErrorMessage('damage_repair', $errors) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'damage')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
