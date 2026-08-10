{{-- Edit Review --}}
<div class="portlet light" style="display: none;" id="edit_review">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Review</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'updateReview'], $incident->id) }}" class="horizontal-form">
            @csrf

            {{-- Risk Registered --}}
            <div class="row">
                <label for="risk_register" class="col-md-5 control-label">Risk Registered reviewed:</label>
                <div class="col-md-3">
                    <x-form.select name="risk_register" :options="['0' => 'No', '1' => 'Yes']" :value="$incident->risk_register"/>
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
