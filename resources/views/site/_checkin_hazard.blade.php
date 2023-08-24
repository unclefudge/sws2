<div id="unsafe-site">
    <hr>
    <h4 class="font-green-haze">Hazard Details</h4>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {!! fieldHasError('location', $errors) !!}">
                {!! Form::label('location', 'Location of hazard (eg. bathroom, first floor addition, kitchen, backyard)', ['class' => 'control-label']) !!}
                {!! Form::text('location', null, ['class' => 'form-control']) !!}
                {!! fieldErrorMessage('location', $errors) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {!! fieldHasError('rating', $errors) !!}">
                {!! Form::label('rating', 'Risk Rating', ['class' => 'control-label']) !!}
                {!! Form::select('rating', ['' => 'Select rating', '1' => "Low", '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                {!! fieldErrorMessage('rating', $errors) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {!! fieldHasError('reason', $errors) !!}">
                {!! Form::label('reason', 'What is the hazard / safety issue?', ['class' => 'control-label']) !!}
                {!! Form::textarea('reason', null, ['rows' => '3', 'class' => 'form-control']) !!}
                {!! fieldErrorMessage('reason', $errors) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {!! fieldHasError('action', $errors) !!}">
                {!! Form::label('action', 'What action/s (if any) have you taken to resolve the issue?', ['class' => 'control-label']) !!}
                {!! Form::textarea('action', null, ['rows' => '3', 'class' => 'form-control']) !!}
                {!! fieldErrorMessage('action', $errors) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h5>Upload Photos/Video of issue</h5>
            <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2 col-xs-4 text-center">
            <div class="form-group">
                {!! Form::checkbox('action_required', '1', null,
                 ['class' => 'make-switch', 'data-size' => 'small',
                 'data-on-text'=>'Yes', 'data-on-color'=>'success',
                 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
            </div>
        </div>
        <div class="col-sm-10 col-xs-8">
            Does {{ $worksite->company->name }} need to take any action?
        </div>
    </div>
</div>