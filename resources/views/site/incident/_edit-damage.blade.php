{{-- Edit Damage Details --}}
<div class="portlet light" style="display: none;" id="edit_damage">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Damage Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'updateDamage'], $incident->id) }}" class="horizontal-form">
            @csrf

            <div class="row">
                <label for="damage" class="col-md-3 control-label">Damage Details:</label>
                <div class="col-md-9">
                    <x-form.input name="damage" :value="$incident->damage"/>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <label for="damage_cost" class="col-md-3 control-label">Repair Cost:</label>
                <div class="col-md-9">
                    <x-form.input name="damage_cost" :value="$incident->damage_cost"/>
                </div>
            </div>
            <hr class="field-hr">
            <div class="row">
                <label for="damage_repair" class="col-md-3 control-label">Repair Details:</label>
                <div class="col-md-9">
                    <x-form.textarea name="damage_repair" rows="3" :value="$incident->damage_repair"/>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'damage')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
