{{-- Add Review --}}
<div class="portlet light" style="display: none;" id="add_review">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Review</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'addReview'], $incident->id) }}" class="horizontal-form">
            @csrf

            <div class="row">
                <div class="col-md-12">Assign a user to review the Incident Report<br><br></div>
            </div>
            {{-- User Id --}}
            <div class="row">
                <label for="assign_review" class="col-md-3 control-label">Assign to:</label>
                <div class="col-md-9">
                    <x-form.select name="assign_review" :options="['' => 'Select user'] + Auth::user()->company->usersSelect('select', '1')" plugin="select2"/>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <label for="review_role" class="col-md-3 control-label">Role:</label>
                <div class="col-md-9">
                    <x-form.select name="review_role" :options="['' => 'Select role', 'Involved Person' => 'Involved Person', 'Supervisor' => 'Supervisor', 'Manager' => 'Manager', 'Executive' => 'Executive', 'WHS Representative' => 'WHS Representative', 'Other' => 'Other']"/>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'review')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
