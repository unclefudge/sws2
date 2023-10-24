{{-- Edit Details --}}
<div class="portlet light" style="display: none;" id="edit_details">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Details</span>
            @if($incident->status == 2)
                <span class="label label-warning">IN PROGRESS</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($incident, ['method' => 'POST', 'action' => ['Site\Incident\SiteIncidentController@updateDetails', $incident->id], 'class' => 'horizontal-form']) !!}
        {{-- Status --}}
        <div class="row">
            <div class="form-group {!! fieldHasError('status', $errors) !!}">
                {!! Form::label('status', 'Status:', ['class' => 'col-md-3 control-label']) !!}
                <div class="col-md-9">
                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                        {!! Form::select('status', ['1' => 'Open', '9' => 'Resolved', '0' => 'Closed'], null, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                        {!! fieldErrorMessage('status', $errors) !!}
                    @else
                        {!! $incident->status_text !!}
                    @endif
                </div>
            </div>
        </div>

        @if ($incident->status != 0)
            <hr class="field-hr">
            {{-- Risk Potential --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('risk_potential', $errors) !!}">
                    {!! Form::label('risk_potential', 'Risk Potential:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('risk_potential', ['' => 'Select option', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('risk_potential', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Risk Actual --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('risk_actual', $errors) !!}">
                    {!! Form::label('risk_actual', 'Risk Actual:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('risk_actual', ['' => 'Select option', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('risk_actual', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Summary --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('exec_summary', $errors) !!}">
                    {!! Form::label('exec_summary', 'Summary:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('exec_summary', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('exec_summary', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Description --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('exec_describe', $errors) !!}">
                    {!! Form::label('exec_describe', 'Description:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('exec_describe', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('exec_describe', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Actions --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('exec_actions', $errors) !!}">
                    {!! Form::label('exec_actions', 'Corrective Actions:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::textarea('exec_actions', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('exec_actions', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Notifiable --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('notifiable', $errors) !!}">
                    {!! Form::label('notifiable', 'Notifiable:', ['class' => 'col-md-3 control-label']) !!}
                    <div class="col-md-9">
                        {!! Form::select('notifiable', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'notifiable']) !!}
                        {!! fieldErrorMessage('notifiable', $errors) !!}
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'details')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
