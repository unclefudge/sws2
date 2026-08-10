{{-- Edit Details --}}
<div class="portlet light" style="display: none;" id="edit_details">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Incident Details</span>
            @if($incident->status == 2)
                <span class="label label-warning">IN PROGRESS</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'updateDetails'], $incident->id) }}" class="horizontal-form">
            @csrf
            {{-- Status --}}
            <div class="row">
                <label for="status" class="col-md-3 control-label">Status:</label>
                <div class="col-md-9">
                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                        <x-form.select name="status" :options="['1' => 'Open', '9' => 'Resolved', '0' => 'Closed']" :value="$incident->status"/>
                    @else
                        {!! $incident->status_text !!}
                    @endif
                </div>
            </div>

            @if ($incident->status != 0)
                <hr class="field-hr">
                {{-- Risk Potential --}}
                <div class="row">
                    <label for="risk_potential" class="col-md-3 control-label">Risk Potential:</label>
                    <div class="col-md-9">
                        <x-form.select name="risk_potential" :options="['' => 'Select option', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme']" :value="$incident->risk_potential"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Risk Actual --}}
                <div class="row">
                    <label for="risk_actual" class="col-md-3 control-label">Risk Actual:</label>
                    <div class="col-md-9">
                        <x-form.select name="risk_actual" :options="['' => 'Select option', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme']" :value="$incident->risk_actual"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Summary --}}
                <div class="row">
                    <label for="exec_summary" class="col-md-3 control-label">Summary:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="exec_summary" rows="3" :value="$incident->exec_summary"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Description --}}
                <div class="row">
                    <label for="exec_describe" class="col-md-3 control-label">Description:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="exec_describe" rows="3" :value="$incident->exec_describe"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Actions --}}
                <div class="row">
                    <label for="exec_actions" class="col-md-3 control-label">Corrective Actions:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="exec_actions" rows="3" :value="$incident->exec_actions"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Notifiable --}}
                <div class="row">
                    <label for="notifiable" class="col-md-3 control-label">Notifiable:</label>
                    <div class="col-md-9">
                        <x-form.select name="notifiable" :options="['0' => 'No', '1' => 'Yes']" :value="$incident->notifiable"/>
                    </div>
                </div>
            @endif

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'details')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
