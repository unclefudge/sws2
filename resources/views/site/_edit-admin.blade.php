{{-- Edit Admin Details --}}
<div class="portlet light" style="display: none;" id="edit_admin">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Admin Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        {!! Form::model($site, ['method' => 'POST', 'action' => ['Site\SiteController@updateAdmin', $site->id]]) !!}
        @if (Auth::user()->allowed2('edit.site.zoho.fields', $site))
            {{--Council Appoval Signed --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('council_approval', $errors) !!}">
                    {!! Form::label('council_approval', 'Council Approval:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('council_approval', ($site->council_approval) ? $site->council_approval->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('council_approval', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Contract Sent --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('contract_sent', $errors) !!}">
                    {!! Form::label('contract_sent', 'Contract Sent:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('contract_sent', ($site->contract_sent) ? $site->contract_sent->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('contract_sent', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Contract Signed --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('contract_signed', $errors) !!}">
                    {!! Form::label('contract_signed', 'Contract Signed:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('contract_signed', ($site->contract_signed) ? $site->contract_signed->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('contract_signed', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Deposit Paid --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('deposit_paid', $errors) !!}">
                    {!! Form::label('deposit_paid', 'Deposit Paid:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('deposit_paid', ($site->deposit_paid) ? $site->deposit_paid->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('deposit_paid', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{--Prac Papers Signed --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('completion_signed', $errors) !!}">
                    {!! Form::label('completion_signed', 'Prac Papers Signed:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('completion_signed', ($site->completion_signed) ? $site->completion_signed->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('completion_signed', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>

            <hr class="field-hr">
            {{-- construction Certificate --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('construction', $errors) !!}">
                    {!! Form::label('construction', 'construction Certificate:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    {{--}}<div class="col-md-6">
                        {!! Form::select('construction', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('construction', $errors) !!}
                    </div>--}}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('construction_rcvd', ($site->construction_rcvd) ? $site->construction_rcvd->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('construction_rcvd', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Home Builder Compensation Fund --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('hbcf', $errors) !!}">
                    {!! Form::label('hbcf', 'Home Builder Compensation Fund:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    {{--}}<div class="col-md-6">
                        {!! Form::select('hbcf', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('hbcf', $errors) !!}
                    </div>--}}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('hbcf_start', ($site->hbcf_start) ? $site->hbcf_start->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('hbcf_start', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Engineering Certificate --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('engineering', $errors) !!}">
                    {!! Form::label('engineering', 'Engineering Certificate:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('engineering', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('engineering', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Holidays Added --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('holidays_added', $errors) !!}">
                    {!! Form::label('holidays_added', 'Holidays Added:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('holidays_added', ['' => 'Selecti option', 'No' => 'No', 'Yes' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('holidays_added', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- OSD --}}
            <div class="row">
                <div class="form-group {!! fieldHasError('osd', $errors) !!}">
                    {!! Form::label('osd', 'OSD:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('osd', ['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('osd', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- FW--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('sw', $errors) !!}">
                    {!! Form::label('sw', 'SW:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('sw', ['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('sw', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- GAL--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('gal', $errors) !!}">
                    {!! Form::label('gal', 'GAL:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('gal', ['' => 'Select option', 'No' => 'No', 'Yes' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('gal', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Consultant--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('consultant_name', $errors) !!}">
                    {!! Form::label('consultant_name', 'Consultant', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::text('consultant_name', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('consultant_name', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Project Coodinator--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('project_mgr', $errors) !!}">
                    {!! Form::label('project_mgr', 'Project Coodinator', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::select('project_mgr', $site->company->projectManagersSelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                        {!! fieldErrorMessage('project_mgr', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Estimator--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('estimator_fc', $errors) !!}">
                    {!! Form::label('estimator_fc', 'Estimator FC', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        {!! Form::text('estimator_fc', null, ['class' => 'form-control']) !!}
                        {!! fieldErrorMessage('estimator_fc', $errors) !!}
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Jobstart Estimate--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('jobstart_estimate', $errors) !!}">
                    {!! Form::label('jobstart_estimate', 'Start Estimate:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('jobstart_estimate', ($site->jobstart_estimate) ? $site->jobstart_estimate->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('jobstart_estimate', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Forecast Completion--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('forecast_completion', $errors) !!}">
                    {!! Form::label('forecast_completion', 'Completion Deadline:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('forecast_completion', ($site->forecast_completion) ? $site->forecast_completion->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('forecast_completion', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>

            <hr class="field-hr">
            {{-- Client Occupation--}}
            <div class="row">
                <div class="form-group {!! fieldHasError('client_occupation', $errors) !!}">
                    {!! Form::label('client_occupation', 'Client Occupation:', ['class' => 'col-md-6 control-label font-yellow']) !!}
                    <div class="col-md-6">
                        <div class="input-group date date-picker">
                            {!! Form::text('client_occupation', ($site->client_occupation) ? $site->client_occupation->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                            'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                            <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                            {!! fieldErrorMessage('client_occupation', $errors) !!}
                        </div>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
        @endif
        {{-- After Care--}}
        <div class="row">
            <div class="form-group {!! fieldHasError('aftercare', $errors) !!}">
                {!! Form::label('aftercare', 'After Care:', ['class' => 'col-md-6 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('aftercare', ['' => 'Select option', 'No' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                    {!! fieldErrorMessage('aftercare', $errors) !!}
                </div>
            </div>
        </div>
        <br>
        <div class="form-actions right">
            <button class="btn default" onclick="cancelForm(event, 'admin')">Cancel</button>
            <button type="submit" class="btn green"> Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>