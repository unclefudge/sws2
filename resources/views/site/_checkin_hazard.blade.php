<div id="unsafe-site">
    <hr>
    <h4 class="font-green-haze">Hazard Details</h4>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {{ $errors->has('location') ? 'has-error' : '' }}">
                <label for="location" class="control-label">Location of hazard (eg. bathroom, first floor addition, kitchen, backyard)</label>
                <x-form.input name="location"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {{ $errors->has('rating') ? 'has-error' : '' }}">
                <label for="rating" class="control-label">Risk Rating</label>
                <x-form.select name="rating" :options="['' => 'Select rating', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme']" :value="old('rating')" plugin="bs-select"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {{ $errors->has('reason') ? 'has-error' : '' }}">
                <label for="reason" class="control-label">What is the hazard / safety issue?</label>
                <x-form.textarea name="reason" rows="3"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group {{ $errors->has('action') ? 'has-error' : '' }}">
                <label for="action" class="control-label">What action/s (if any) have you taken to resolve the issue?</label>
                <x-form.textarea name="action" rows="3"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h5>Upload Photos/Video of issue</h5>
            <x-form.filepond name="filepond[]" multiple/><br><br>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2 col-xs-4 text-center">
            <div class="form-group">
                <input type="checkbox" name="action_required" id="action_required" value="1" class="make-switch" data-size="small" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" {{ old('action_required') ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-sm-10 col-xs-8">
            Does {{ $worksite->company->name }} need to take any action?
        </div>
    </div>
</div>