{{-- Add Notes --}}
<div class="portlet light" style="display: none;" id="add_notes">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notes</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model('action', ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@addNote', $incident->id], 'class' => 'horizontal-form']) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group {!! fieldHasError('action', $errors) !!}">
                    {!! Form::label('action', 'Description:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('action', null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => 'enter note description']) !!}
                    {!! fieldErrorMessage('action', $errors) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'notes')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
