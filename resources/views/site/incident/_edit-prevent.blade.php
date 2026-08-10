{{-- Edit Preventive --}}
<div class="portlet light" style="display: none;" id="edit_prevent">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Actions to Prevent Reoccurence</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController::class, 'updatePrevent'], $incident->id) }}" class="horizontal-form">
            @csrf

            {{-- Absent / Failed Defences --}}
            <div class="row">
                <label for="response_236" class="col-md-2 control-label">Preventive Strategies:</label>
                <div class="col-md-10">
                    <x-form.select name="response_236[]" id="response_236" :options="$qPreventive->optionsArray()" :value="$qPreventive->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable" style="width:100%"/>
                </div>
            </div>
            <hr class="field-hr">

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'prevent')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
