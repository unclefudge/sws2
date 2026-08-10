{{-- Edit Con Factors --}}
<div class="portlet light" style="display: none;" id="edit_confactors">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contributing Factors</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController::class, 'updateConfactors'], $incident->id) }}" class="horizontal-form">
            @csrf

            {{-- Absent / Failed Defences --}}
            <div class="row">
                <div class="form-group">
                    <div class="col-md-12">
                        <x-form.select name="response_125[]" id="response_125" label="Absent / Failed Defences:" :options="$qConFactorDefences->optionsArray()" :value="$qConFactorDefences->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"
                                       style="width:100%"/>
                    </div>
                </div>
            </div>
            <div id="field_response_147">
                <br>
                <div class="row">
                    <label for="response_147" class="col-md-2 control-label">Other:</label>
                    <div class="col-md-10">
                        <x-form.input name="response_147" :value="$qConFactorDefences->responseOther('site_incidents', $incident->id, 147)"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Individual / Team Actions --}}
            <div class="row">
                <div class="form-group">
                    <div class="col-md-12">
                        <x-form.select name="response_148[]" id="response_148" label="Individual / Team Actions:" :options="$qConFactorITactions->optionsArray()" :value="$qConFactorITactions->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable"
                                       style="width:100%"/>
                    </div>
                </div>
            </div>
            <div id="field_response_166">
                <br>
                <div class="row">
                    <label for="response_166" class="col-md-2 control-label">Other:</label>
                    <div class="col-md-10">
                        <x-form.input name="response_166" :value="$qConFactorITactions->responseOther('site_incidents', $incident->id, 166)"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Workplace Conditions --}}
            <div class="row">
                <div class="form-group">
                    <div class="col-md-12">
                        <x-form.select name="response_167[]" id="response_167" label="Task / Environment Workplace Conditions:" :options="$qConFactorWorkplace->optionsArray()" :value="$qConFactorWorkplace->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple
                                       title="Check all applicable" style="width:100%"/>
                    </div>
                </div>
            </div>
            <div id="field_response_191">
                <br>
                <div class="row">
                    <label for="response_191" class="col-md-2 control-label">Other:</label>
                    <div class="col-md-10">
                        <x-form.input name="response_191" :value="$qConFactorWorkplace->responseOther('site_incidents', $incident->id, 191)"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Human Factors --}}
            <div class="row">
                <div class="form-group">
                    <div class="col-md-12">
                        <x-form.select name="response_192[]" id="response_192" label="Task / Environment Conditions - Human Factors:" :options="$qConFactorHuman->optionsArray()" :value="$qConFactorHuman->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple
                                       title="Check all applicable" style="width:100%"/>
                    </div>
                </div>
            </div>
            <div id="field_response_218">
                <br>
                <div class="row">
                    <label for="response_218" class="col-md-2 control-label">Other:</label>
                    <div class="col-md-10">
                        <x-form.input name="response_218" :value="$qConFactorHuman->responseOther('site_incidents', $incident->id, 218)"/>
                    </div>
                </div>
            </div>

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'confactors')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
