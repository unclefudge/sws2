{{-- Edit Injury Details --}}
<div class="portlet light" style="display: none;" id="edit_injury">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Injury Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'updateInjury'], $incident->id) }}" class="horizontal-form">
            @csrf
            @if ($incident->status != 0)
                <div class="row">
                    <label for="treatment" class="col-md-3 control-label">Treatment:</label>
                    <div class="col-md-9">
                        <x-form.select name="treatment[]" id="treatment" :options="$qTreatment->optionsArray()" :value="$qTreatment->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable" style="width:100%"/>
                    </div>
                </div>
                <hr class="field-hr">
                <div id="field_treatment_other">
                    <div class="row">
                        <label for="treatment_other" class="col-md-3 control-label">Treatment Other:</label>
                        <div class="col-md-9">
                            <x-form.input name="treatment_other" :value="$qTreatment->responseOther('site_incidents', $incident->id, 20)"/>
                        </div>
                    </div>
                    <hr class="field-hr">
                </div>
                <div class="row">
                    <label for="injured_part" class="col-md-3 control-label">Part(s) Injured:</label>
                    <div class="col-md-9">
                        <x-form.select name="injured_part[]" id="injured_part" :options="$qInjuredPart->optionsArray()" :value="$qInjuredPart->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"/>
                    </div>
                </div>
                <hr class="field-hr">
                <div id="field_injured_part_other">
                    <div class="row">
                        <label for="injured_part_other" class="col-md-3 control-label">Part Other:</label>
                        <div class="col-md-9">
                            <x-form.input name="injured_part_other" :value="$qInjuredPart->responseOther('site_incidents', $incident->id, 49)"/>
                        </div>
                    </div>
                    <hr class="field-hr">
                </div>
                <div>
                    <div class="row">
                        <label for="injured_nature" class="col-md-12 control-label">Nature of Injury</label>
                        <div class="col-md-12">
                            <x-form.select name="injured_nature[]" id="injured_nature" :options="$qInjuredNature->optionsArray()" :value="$qInjuredNature->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                <div>
                    <div class="row">
                        <label for="injured_mechanism" class="col-md-12 control-label">Mechanism of Injury:</label>
                        <div class="col-md-12">
                            <x-form.select name="injured_mechanism[]" id="injured_mechanism" :options="$qInjuredMechanism->optionsArray()" :value="$qInjuredMechanism->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="injured_agency" class="col-md-12 control-label">Agency of Injury:</label>
                    <div class="col-md-12">
                        <x-form.select name="injured_agency[]" id="injured_agency" :options="$qInjuredAgency->optionsArray()" :value="$qInjuredAgency->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"/>
                    </div>
                </div>
                <hr class="field-hr">
            @endif

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'injury')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
