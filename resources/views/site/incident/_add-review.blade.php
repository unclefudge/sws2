{{-- Add Review --}}
<div class="portlet light" style="display: none;" id="add_review">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Review</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model('action', ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@addReview', $incident->id], 'class' => 'horizontal-form']) !!}

        <div class="row">
            <div class="col-md-12">Assign a user to review the Incident Report<br><br></div>
            </div>
        {{-- User Id --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('assign_review', $errors) !!}">
                {!! Form::label('assign_review', 'Assign to:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('assign_review', ['' => 'Select user'] + Auth::user()->company->usersSelect('select', '1'),
                          null, ['class' => 'form-control select2', 'name' => 'assign_review', 'id'  => 'assign_review',]) !!}
                    {!! fieldErrorMessage('assign_review', $errors) !!}
                </div>
            </div>
        </div>
        <hr class="field-hr">
        <div class="row">
            <div class="form-group {!! fieldHasError('review_role', $errors) !!}">
                {!! Form::label('review_role', 'Role:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    {!! Form::select('review_role', ['' => 'Select role', 'Involved Person' => 'Involved Person', 'Supervisor' => 'Supervisor', 'Manager' => 'Manager', 'Executive' => 'Executive', 'WHS Representative' => 'WHS Representative', 'Other' => 'Other'],
                          null, ['class' => 'form-control bs-select', 'name' => 'review_role', 'id'  => 'review_role',]) !!}
                    {!! fieldErrorMessage('review_role', $errors) !!}
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
