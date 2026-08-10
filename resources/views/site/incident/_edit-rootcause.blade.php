{{-- Edit Root Cause --}}
<div class="portlet light" style="display: none;" id="edit_rootcause">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Root Cause - Organisation Factors</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController::class, 'updateRootcause'], $incident->id) }}" class="horizontal-form">
            @csrf

            {{-- Root Cause --}}
            <div class="row">
                <div class="col-md-12">
                    <x-form.select name="response_219[]" id="response_219" label="Root Cause:" :options="$qRootCause->optionsArray()" :value="$qRootCause->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple title="Check all applicable" style="width:100%"/>
                </div>
            </div>
            <hr class="field-hr">

            @foreach ($qRootCause->optionsArray() as $id => $label)
                <div id="field_response_{{ $id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <x-form.input :name="'response_'.$id" :label="$label" :value="$qRootCause->responseOther('site_incidents', $incident->id, $id)"/>
                        </div>
                    </div>
                </div>
            @endforeach


            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'rootcause')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
