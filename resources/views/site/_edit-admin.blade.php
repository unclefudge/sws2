{{-- Edit Admin Details --}}
<div class="portlet light" style="display: none;" id="edit_admin">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Admin Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'updateAdmin'], $site->id) }}">
            @csrf
            @if (Auth::user()->allowed2('edit.site.zoho.fields', $site))
                {{--Council Appoval Signed --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('council_approval') ? 'has-error' : '' }}">
                        <label for="council_approval" class="col-md-6 control-label font-yellow">Council Approval:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="council_approval" :value="($site->council_approval) ? $site->council_approval->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Contract Sent --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('contract_sent') ? 'has-error' : '' }}">
                        <label for="contract_sent" class="col-md-6 control-label font-yellow">Contract Sent:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="contract_sent" :value="($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Contract Signed --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('contract_signed') ? 'has-error' : '' }}">
                        <label for="contract_signed" class="col-md-6 control-label font-yellow">Contract Signed:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="contract_signed" :value="($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Deposit Paid --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('deposit_paid') ? 'has-error' : '' }}">
                        <label for="deposit_paid" class="col-md-6 control-label font-yellow">Deposit Paid:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="deposit_paid" :value="($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{--Prac Papers Signed --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('completion_signed') ? 'has-error' : '' }}">
                        <label for="completion_signed" class="col-md-6 control-label font-yellow">Prac Papers Signed:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="completion_signed" :value="($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>

                <hr class="field-hr">
                {{-- construction Certificate --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('construction') ? 'has-error' : '' }}">
                        <label for="construction" class="col-md-6 control-label font-yellow">construction Certificate:</label>
                        {{--}}<div class="col-md-6">
                            <x-form.select name="construction" :options="['0' => 'No', '1' => 'Yes']" :value="old('construction', $site->construction)" plugin="bs-select"/>
                        </div>--}}
                        <div class="col-md-6">
                            <x-form.datepicker name="construction_rcvd" :value="($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Home Builder Compensation Fund --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('hbcf') ? 'has-error' : '' }}">
                        <label for="hbcf" class="col-md-6 control-label font-yellow">Home Builder Compensation Fund:</label>
                        {{--}}<div class="col-md-6">
                            <x-form.select name="hbcf" :options="['0' => 'No', '1' => 'Yes']" :value="old('hbcf', $site->hbcf)" plugin="bs-select"/>
                        </div>--}}
                        <div class="col-md-6">
                            <x-form.datepicker name="hbcf_start" :value="($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Engineering Certificate --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('engineering') ? 'has-error' : '' }}">
                        <label for="engineering" class="col-md-6 control-label font-yellow">Engineering Certificate:</label>
                        <div class="col-md-6">
                            <x-form.select name="engineering" :options="['0' => 'No', '1' => 'Yes']" :value="$site->engineering"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Holidays Added --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('holidays_added') ? 'has-error' : '' }}">
                        <label for="holidays_added" class="col-md-6 control-label font-yellow">Holidays Added:</label>
                        <div class="col-md-6">
                            <x-form.select name="holidays_added" :options="['' => 'Selecti option', 'No' => 'No', 'Yes' => 'Yes']" :value="$site->holidays_added"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- OSD --}}
                <div class="row">
                    <div class="form-group {{ $errors->has('osd') ? 'has-error' : '' }}">
                        <label for="osd" class="col-md-6 control-label font-yellow">OSD:</label>
                        <div class="col-md-6">
                            <x-form.select name="osd" :options="['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes']" :value="$site->osd"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- FW--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('sw') ? 'has-error' : '' }}">
                        <label for="sw" class="col-md-6 control-label font-yellow">SW:</label>
                        <div class="col-md-6">
                            <x-form.select name="sw" :options="['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes']" :value="$site->sw"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- GAL--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('gal') ? 'has-error' : '' }}">
                        <label for="gal" class="col-md-6 control-label font-yellow">GAL:</label>
                        <div class="col-md-6">
                            <x-form.select name="gal" :options="['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes']" :value="$site->gal"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Consultant--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('consultant_name') ? 'has-error' : '' }}">
                        <label for="consultant_name" class="col-md-6 control-label font-yellow">Consultant</label>
                        <div class="col-md-6">
                            <x-form.input name="consultant_name" :value="$site->consultant_name"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Project Coodinator--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('project_mgr') ? 'has-error' : '' }}">
                        <label for="project_mgr" class="col-md-6 control-label font-yellow">Project Coodinator</label>
                        <div class="col-md-6">
                            <x-form.select name="project_mgr" :options="$site->company->projectManagersSelect('prompt')" :value="$site->project_mgr"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Estimator--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('estimator_fc') ? 'has-error' : '' }}">
                        <label for="estimator_fc" class="col-md-6 control-label font-yellow">Estimator FC</label>
                        <div class="col-md-6">
                            <x-form.input name="estimator_fc" :value="$site->estimator_fc"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Jobstart Estimate--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('jobstart_estimate') ? 'has-error' : '' }}">
                        <label for="jobstart_estimate" class="col-md-6 control-label font-yellow">Start Estimate:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="jobstart_estimate" :value="($site->jobstart_estimate) ? $site->jobstart_estimate->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">

                {{-- Forecast Completion--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('forecast_completion') ? 'has-error' : '' }}">
                        <label for="forecast_completion" class="col-md-6 control-label font-yellow">Completion Deadline:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="forecast_completion" :value="($site->forecast_completion) ? $site->forecast_completion->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Client Occupation--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('client_occupation') ? 'has-error' : '' }}">
                        <label for="client_occupation" class="col-md-6 control-label font-yellow">Client Occupation:</label>
                        <div class="col-md-6">
                            <x-form.datepicker name="client_occupation" :value="($site->client_occupation) ? $site->client_occupation->format('d/m/Y') : ''"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            @endif
            {{-- After Care--}}
            <div class="row">
                <div class="form-group {{ $errors->has('aftercare') ? 'has-error' : '' }}">
                    <label for="aftercare" class="col-md-6 control-label">After Care:</label>
                    <div class="col-md-6">
                        <x-form.select name="aftercare" :options="['' => 'Select option', 'No' => 'No']" :value="$site->aftercare"/>
                    </div>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'admin')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>