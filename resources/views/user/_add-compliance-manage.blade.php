{{-- Edit Compliance Manaement --}}
<div class="portlet light" style="display: none;" id="add_compliance">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Compliance Management</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'storeCompliance'], $user->id) }}" class="horizontal-form">
            @csrf
        {{-- Hidden Required Doc Fields --}}
        @foreach ($overrideTypes::userSelect() as $type => $name)
            <?php $cat = substr($type, 2) ?>
            @if (is_numeric($cat))
                <x-form.hidden :name="'ot_'.$type" :value="$user->requiresUserDoc($cat, 'system') ? 1 : 0"/>
            @endif
        @endforeach

        {{-- Add New Override --}}
        <div class="row">
            <div class="form-group">
                <label for="compliance_type" class="col-md-3 control-label">Override Type:</label>
                <div class="col-md-9">
                    <x-form.select name="compliance_type" :options="$overrideTypes::userSelect()"/>
                    <x-form.error name="duplicate_override"/>
                </div>
            </div>
        </div>
        <div style="display: none" id="add_compliance_fields">
            {{-- Required --}}
            <div id="add_compliance_required">
                <hr class="field-hr">
                <div class="row">
                    <div class="form-group">
                        <label for="required" class="col-md-3 control-label">Required:</label>
                        <div class="col-md-9">
                            <x-form.select name="required" :options="['0' => 'No', '1' => 'Yes']"/>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <span class="help-block"> By default this document <span id="creq_yes"><b>IS</b></span><span id="creq_not">is <b>NOT</b></span> <b>REQUIRED</b> for this user to be compliant</span>
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            <hr class="field-hr">
            <div class="row">
                <div class="form-group">
                    <label for="reason" class="col-md-3 control-label">Reason:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="reason" rows="2" required/>
                    </div>
                </div>
            </div>

            {{-- Expiry --}}
            <hr class="field-hr">
            <div class="row">
                <div class="form-group">
                    <label for="expiry" class="col-md-3 control-label">Expiry:</label>
                    <div class="col-md-9">
                        <x-form.datepicker name="expiry" placeholder="Leave blank to never expire" readonly/>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'compliance')">Cancel</button>
            <button type="submit" class="btn green" id="save_compliance" style="display: none"> Save</button>
        </div>
        </form>
    </div>
</div>