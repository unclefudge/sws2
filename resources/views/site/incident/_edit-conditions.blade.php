{{-- Edit Conditions --}}
<div class="portlet light" style="display: none;" id="edit_conditions">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Conditions</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController::class, 'updateConditions'], $incident->id) }}" class="horizontal-form">
            @csrf

            {{-- Conditions --}}
            <div class="row">
                <div class="col-md-12">
                    <x-form.select name="response_113[]" id="response_113" label="Conditions:" :options="$qConditions->optionsArray()" :value="$qConditions->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable" style="width:100%"/>
                </div>
            </div>
            <hr class="field-hr">

            @foreach ($qConditions->optionsArray() as $id => $label)
                <div id="field_response_{{ $id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <x-form.input :name="'response_'.$id" :label="$label" :value="$qConditions->responseOther('site_incidents', $incident->id, $id)"/>
                        </div>
                    </div>
                </div>
            @endforeach

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'conditions')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
