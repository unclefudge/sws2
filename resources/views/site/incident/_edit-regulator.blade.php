{{-- Edit Regulator --}}
<div class="portlet light" style="display: none;" id="edit_regulator">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Regulator Action Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'updateRegulator'], $incident->id) }}" class="horizontal-form">
            @csrf
            {{-- Context --}}
            <div class="row">
                <label for="notifiable_reason" class="col-md-3 control-label">Context:</label>
                <div class="col-md-9">
                    <x-form.textarea name="notifiable_reason" rows="3" :value="$incident->notifiable_reason" required/>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Regulator --}}
            <div class="row">
                <label for="regulator" class="col-md-3 control-label">Regulator:</label>
                <div class="col-md-9">
                    <x-form.input name="regulator" :value="$incident->regulator ?: 'Safework NSW'"/>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Regulator Ref --}}
            <div class="row">
                <label for="regulator_ref" class="col-md-3 control-label">Regulator Ref:</label>
                <div class="col-md-9">
                    <x-form.input name="regulator_ref" :value="$incident->regulator_ref"/>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Date/Time --}}
            <div class="row">
                <label for="regulator_date" class="col-md-3 control-label">Notified Date:</label>
                <div class="col-md-9">
                    <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                        <input type="text" name="regulator_date" id="regulator_date" class="form-control" value="{{ $incident->regulator_date ? $incident->regulator_date->format('d/m/Y H:i') : '' }}" readonly style="background:#FFF">
                        <span class="input-group-addon">
                        <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                    </div>

                </div>
            </div>
            <hr class="field-hr">
            {{-- Inspector --}}
            <div class="row">
                <label for="inspector" class="col-md-3 control-label">Inspector:</label>
                <div class="col-md-9">
                    <x-form.input name="inspector" :value="$incident->inspector"/>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Notes --}}
            <div class="row">
                <label for="notes" class="col-md-3 control-label">Notes:</label>
                <div class="col-md-9">
                    <x-form.textarea name="notes" rows="3" :value="$incident->notes"/>
                </div>
            </div>
            <hr class="field-hr">


            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'regulator')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
