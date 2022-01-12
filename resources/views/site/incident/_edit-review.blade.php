{{-- Edit Review --}}
<div class="portlet light" style="display: none;" id="edit_review">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Review</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model('action', ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@updateReview', $incident->id], 'class' => 'horizontal-form']) !!}

        {{-- Risk Registered --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('risk_register', $errors) !!}">
                {!! Form::label('risk_register', 'Risk Registered reviewed:', ['class' => 'col-md-5 control-label']) !!}
                <div class="col-md-3">
                    {!! Form::select('risk_register', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                    {!! fieldErrorMessage('risk_register', $errors) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'review')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
