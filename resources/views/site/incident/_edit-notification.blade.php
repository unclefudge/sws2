{{-- Edit Notification Details --}}
<div class="portlet light" style="display: none;" id="edit_notification">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Notification Details</span>
            @if($incident->status == 2)
                <span class="label label-warning">IN PROGRESS</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentController::class, 'update'], $incident->id) }}" class="horizontal-form">
            @csrf
            @method('PATCH')
            @if ($incident->status != 0)
                <div class="row">
                    <label for="date" class="col-md-3 control-label">Incident Date:</label>
                    <div class="col-md-9">
                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                            <input type="text" name="date" id="date" class="form-control" value="{{ $incident->date->format('d/m/Y H:i') }}" readonly style="background:#FFF">
                            <span class="input-group-addon">
                        <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                    </span>
                        </div>

                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="site_cc" class="col-md-3 control-label">Cape Cod site:</label>
                    <div class="col-md-9">
                        <x-form.select name="site_cc" :options="['1' => 'Yes', '0' => 'No']" :value="$incident->site_id ? '1' : '0'"/>
                    </div>
                </div>
                <hr class="field-hr">
                <div id="field_site_id">
                    <div class="row">
                        <label for="site_id" class="col-md-3 control-label">Site</label>
                        <div class="col-md-9">
                            <x-form.select name="site_id" plugin="select2" style="width:100%">
                                {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id', $incident->site_id)) !!}
                            </x-form.select>
                        </div>
                    </div>
                    <hr class="field-hr">
                </div>
                <div id="field_site_name">
                    <div class="row">
                        <label for="site_name" class="col-md-3 control-label">Place of incident:</label>
                        <div class="col-md-9">
                            <x-form.input name="site_name" :value="$incident->site_name" required/>
                        </div>
                    </div>
                    <hr class="field-hr">
                </div>
                <div class="row">
                    <label for="location" class="col-md-3 control-label">Location:</label>
                    <div class="col-md-9">
                        <x-form.input name="location" :value="$incident->location" required/>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="type" class="col-md-3 control-label">Incident Type:</label>
                    <div class="col-md-9">
                        <x-form.select name="type[]" id="type" :options="$qType->optionsArray()" :value="$qType->responsesArray('site_incidents', $incident->id)" plugin="select2" multiple required title="Check all applicable" style="width:100%"/>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="describe" class="col-md-3 control-label">What occured:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="describe" rows="3" :value="$incident->describe" required/>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="actions_taken" class="col-md-3 control-label">Actions taken:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="actions_taken" rows="3" :value="$incident->actions_taken" required/>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Supervisor--}}
                <div class="row">
                    <label for="site_supervisor" class="col-md-3 control-label">Supervisor</label>
                    <div class="col-md-9">
                        <x-form.input name="site_supervisor" :value="$incident->site_supervisor"/>
                    </div>
                </div>
            @endif

            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'notification')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
