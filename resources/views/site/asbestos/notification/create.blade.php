@inject('ozstates', 'App\Http\Utilities\OzStates')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/notification">Asbestos Notifications</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Asbestos Notification</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteAsbestos', ['action' => 'Site\SiteAsbestosController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        {!! Form::hidden('amount_over', '0', ['id' => 'amount_over']) !!}
                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Client / Super Details --}}
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Individual Details --}}
                                    <h4>Individual (Client) Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('client_name', $errors) !!}">
                                                {!! Form::label('client_name', 'Primary Contact', ['class' => 'control-label']) !!}
                                                {!! Form::text('client_name', old('client_name'), ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('client_name', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('client_phone', $errors) !!}">
                                                {!! Form::label('client_phone', 'Phone', ['class' => 'control-label']) !!}
                                                {!! Form::text('client_phone', old('client_phone'), ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('client_phone', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Supervisor Details --}}
                                    <h4>Contact Person (Supervisor) Details</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('supervisor_id', $errors) !!}">
                                                {!! Form::label('supervisor_id', 'Supervisor', ['class' => 'control-label']) !!}
                                                {{--}}{!! Form::select('supervisor_id', ['' => 'Select supervisor', '5' => 'Dean Beringer', '7' => 'Gary Klomp', '13' => 'John Walton'], null, ['class' => 'form-control bs-select']) !!}--}}
                                                {!! Form::select('supervisor_id',
                                                Auth::user()->company->supervisorsSelect(), null, ['class' => 'form-control bs-select', 'name' => 'supervisor_id', 'id' => 'supervisor_id', 'title' => 'Select supervisor',]) !!}
                                                {!! fieldErrorMessage('supervisor_id', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-6 {!! fieldHasError('super_phone', $errors) !!}">
                                            <div class="form-group">
                                                {!! Form::label('super_phone', 'Phone', ['class' => 'control-label']) !!}
                                                {!! Form::text('super_phone', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('super_phone', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            {{-- Site Details --}}
                            <h4>Site Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('site_code', 'Job #', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_code', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('site_name', 'Site Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_name', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('site_address', 'Site Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_address', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('workplace', $errors) !!}">
                                        {!! Form::label('workplace', 'Workplace Type', ['class' => 'control-label']) !!}
                                        {!! Form::select('workplace', ['' => 'Select type', 'Residental' => 'Residental',
                                        'Factory' => 'Factory', 'Office' => 'Office'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('workplace', $errors) !!}
                                    </div>
                                </div>

                                {{-- Dates - Open Hours --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('hours_from', $errors) !!} {!! fieldHasError('open_to', $errors) !!}">
                                        {!! Form::label('hours_from', 'Operating hours of the site', ['class' => 'control-label']) !!}
                                        <div class="input-group">
                                            {!! Form::text('hours_from', '7:00 AM', ['class' => 'form-control timepicker timepicker-no-seconds']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('hours_to', '3:30 PM', ['class' => 'form-control timepicker timepicker-no-seconds']) !!}
                                        </div>
                                        {!! fieldErrorMessage('hours_from', $errors) !!}
                                        {!! fieldErrorMessage('hours_to', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('date_from', $errors) !!}">
                                        {!! Form::label('date_from', 'Proposed dates of asbestos removal work', ['class' => 'control-label']) !!}
                                        <div class="input-group date date-picker input-daterange" data-date-format="dd/mm/yyyy" data-date-start-date="0d">
                                            {!! Form::text('date_from', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon"> to </span>
                                            {!! Form::text('date_to', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                        </div>
                                        {!! fieldErrorMessage('date_from', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <br>

                            {{-- Asbestos Details --}}
                            <h4>Asbestos Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Amount --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('amount', $errors) !!}">
                                        {!! Form::label('amount', 'Amount to be removed (m2)', ['class' => 'control-label']) !!}
                                        <input type="text" class="form-control" value="{{ old('amount') }}" id="amount" name="amount"> {{--}} onkeydown="return isNumber(event)">--}}
                                        {!! fieldErrorMessage('amount', $errors) !!}
                                    </div>
                                    <div class="note note-warning" style="display: none;" id="amount_note">
                                        <p>Volumes over 10m2 are classed as licensed asbestos removal.</p>
                                        <ul>
                                            <li><b>5 calendar days notice to SafeWork is required.</b></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('friable', $errors) !!}">
                                        {!! Form::label('friable', 'Asbestos Class', ['class' => 'control-label']) !!}
                                        {!! Form::select('friable', ['' => 'Select class', '1' => 'Class A (Friable)', '0' => 'Class B (Non-Friable)'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('friable', $errors) !!}
                                    </div>
                                    <div class="note note-warning" style="display: none;" id="friable_note">
                                        <p><b>NOTE:</b> Cape Cod does not hold the Licence Class required to handle this type of Asbestos</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Type --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                        {!! Form::select('type', ['' => 'Select type', 'Asbestos Cement Sheets/Products' => 'Asbestos Cement Sheets/Products',
                                        'Vinyl floor covering' => 'Vinyl floor covering', 'other' => 'Other'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6" style="display: none" id="type_other_div">
                                    <div class="form-group {!! fieldHasError('type_other', $errors) !!}">
                                        {!! Form::label('type_other', 'Other type', ['class' => 'control-label']) !!}
                                        {!! Form::text('type_other', null, ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                        {!! fieldErrorMessage('type_other', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Specific Location of Asbestos', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('location', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div id="non_friable_removal" style="display: none">
                                <h3><br>Asbestos Removal</h3>
                                <hr>
                                {{-- Asbestos Removalist --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('removalist', $errors) !!}">
                                            {!! Form::label('removalist', 'Licensed Asbestos Removalist', ['class' => 'control-label']) !!}
                                            {!! Form::select('removalist', ['' => 'Select removalist', '385' => 'Handy 1st Pty Ltd AD2122895', '3' => 'Cape Cod Australia Pty Ltd AD205686', 'other' => 'Other'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('removalist', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="removalist_name_div" style="display: none">
                                        <div class="form-group {!! fieldHasError('removalist_name', $errors) !!}">
                                            {!! Form::label('removalist_name', 'Name of Removalist', ['class' => 'control-label']) !!}
                                            {!! Form::text('removalist_name', null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('removalist_name', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            {{-- Non Friable Extra Fields --}}
                            <div id="non_friable_fields" style="display: none">
                                {{-- Workers --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('workers', $errors) !!}">
                                            {!! Form::label('workers', 'Number of workers involved in the asbestos removal work', ['class' => 'control-label']) !!}
                                            <input type="text" class="form-control" value="{{ old('workers') }}" id="workers" name="workers" onkeydown="return isNumber(event)"/>
                                            {!! fieldErrorMessage('workers', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="note note-warning">
                                            <p><b>NOTE:</b> All workers involved in the removal of Asbestos MUST have successfully completed relevant competency unit.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Coal Mine --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('coalmine', $errors) !!}">
                                            {!! Form::label('coalmine', 'Is this a coal or mining workplace', ['class' => 'control-label']) !!}
                                            {!! Form::select('coalmine', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('coalmine', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Asbestos Identification --}}
                                <h4>Asbestos Identification
                                    <small>(Applicable to Friable / Asbestos in soils)</small>
                                </h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('hygiene', $errors) !!}">
                                            {!! Form::label('hygiene', 'Is a hygienist report available', ['class' => 'control-label']) !!}
                                            {!! Form::select('hygiene', ['0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('hygiene', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="hygiene_report_div">
                                        <div class="form-group {!! fieldHasError('hygiene_report', $errors) !!}">
                                            {!! Form::label('hygiene_report', 'Report type', ['class' => 'control-label']) !!}
                                            {!! Form::select('hygiene_report', ['' => 'Select type', 'Online Attachment' => 'Online Attachment', 'Email' => 'Email',
                                            'Faxed' => 'Faxed', 'Post' => 'Post', 'By Hand' => 'By Hand'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('hygiene_report', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Protective Equipment --}}
                                <h4>Personal Protective Equipment &nbsp;
                                    <small>(Check all that apply)</small>
                                </h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 {!! fieldHasError('equip', $errors) !!}">
                                        {!! fieldErrorMessage('equip', $errors) !!}
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Protective coveralls
                                                    {!! Form::checkbox('equip[]', 'equip_overalls') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Protective gloves
                                                    {!! Form::checkbox('equip[]', 'equip_gloves') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> P2 Mask
                                                    {!! Form::checkbox('equip[]','equip_mask') !!}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> 1/2 face respirator
                                                    {!! Form::checkbox('equip[]', 'equip_half_face') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Full face air supplied
                                                    {!! Form::checkbox('equip[]', 'equip_full_face') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Other
                                                    {!! Form::checkbox('equip[]', 'equip_other', false, ['onClick' => 'checkbox_equipOther(this)']) !!}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="display: none;" id="equip_other_div">
                                        <div class="form-group {!! fieldHasError('equip_other', $errors) !!}">
                                            {!! Form::label('equip_other', 'Other Equipment', ['class' => 'control-label']) !!}
                                            {!! Form::text('equip_other', '', ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                            {!! fieldErrorMessage('equip_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isolate Methods --}}
                                <h4>Methods used to isolate / enclose the removal area &nbsp;
                                    <small>(Check all that apply)</small>
                                </h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 {!! fieldHasError('method', $errors) !!}">
                                        {!! fieldErrorMessage('method', $errors) !!}
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Fencing
                                                    {!! Form::checkbox('method[]', 'method_fencing') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Signage
                                                    {!! Form::checkbox('method[]', 'method_signage') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Water
                                                    {!! Form::checkbox('method[]', 'method_water') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> PVA
                                                    {!! Form::checkbox('method[]', 'method_pva') !!}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline"> Barriers
                                                    {!! Form::checkbox('method[]', 'method_barriers') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> 200 μm plastic
                                                    {!! Form::checkbox('method[]', 'method_plastic') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Class H asbestos vacuum cleaners
                                                    {!! Form::checkbox('method[]', 'method_vacuum') !!}
                                                    <span></span>
                                                </label>
                                                <label class="mt-checkbox mt-checkbox-outline"> Other
                                                    {!! Form::checkbox('method[]', 'method_other', false, ['onClick' => 'checkbox_methodOther(this)']) !!}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="display: none;" id="method_other_div">
                                        <div class="form-group {!! fieldHasError('method_other', $errors) !!}">
                                            {!! Form::label('method_other', 'Other Method', ['class' => 'control-label']) !!}
                                            {!! Form::text('method_other', '', ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                            {!! fieldErrorMessage('method_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Isolation Entent --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('isolation', $errors) !!}">
                                            {!! Form::label('isolation', 'Extent of isolation / encapsulation (how will these methods be used)', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('isolation', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('isolation', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="non_friable_fields_part2" style="display: none">
                                {{-- Reviewed Asbestos Register --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        {!! Form::label('register', 'Have you reviewed the applicable Asbestos Register to confirm the location of identified asbestos and conducted a site assessment to plan for the removal work?', ['class' => 'control-label']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('register', $errors) !!}">
                                            {!! Form::select('register', ['' => 'Select option', '1' => 'Yes', '0' => 'No', 'N/A' => 'An Asbestos Register is not available for this site'],
                                                 null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('register', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="alert alert-danger" style="display: none;" id="register_note">
                                            <p><b>You must review the Asbestos Register relevant to the site</b></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- SWMS --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        {!! Form::label('swms', 'Have you confirmed a Safe Work Method Statement relevant to the asbestos removal work has been developed by the applicable workers?', ['class' => 'control-label']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('swms', $errors) !!}">
                                            {!! Form::select('swms', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('swms', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="alert alert-danger" style="display: none;" id="swms_note">
                                            <p><b>Work involving asbestos is high risk. A SWMS must be in place for this work to take place.</b></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Over 10m2 Removal Fields --}}
                                <div id="amount_fields" style="display: none">
                                    <h3><br>Licensed Asbestos Removal (10m2)</h3>

                                    {{-- Inspection Certificate --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="note note-warning">
                                                <p><b>Note:</b> A Clearance Inspection is legally required of the Asbestos Removal Area to verify that the area is safe for normal use. Following
                                                    inspection, a Clearance Insection Certificate MUST be obtained PRIOR to the Abestos Removal Area being reoccupied.
                                                    This must be conducted by an independant compentent person. Cape Cod enlists the services of Leon Carnevale to conduct clearance inspection and
                                                    action subsequent asbestos clearance certificate.</p>
                                            </div>
                                            {!! Form::label('inspection', 'Do you acknowledge that a clearance certificate* must be received prior to normal use of the area?', ['class' => 'control-label']) !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('inspection', $errors) !!}">
                                                {!! Form::select('inspection', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('inspection', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="alert alert-danger" style="display: none;" id="inspection_note">
                                                <p><b>Refer to WHS & HR Manager; Licensed Asbestos Removal Work is not to commence.</b></p>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Supervisor --}}
                                    {{--}}
                                    <div class="row" style="padding-top: 10px">
                                        <div class="col-md-3">
                                            {!! Form::label('supervisor_id', 'Asbestos Supervisor', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_id', ['' => 'Select supervisor', '5' => 'Dean Beringer', '7' => 'Gary Klomp', '13' => 'John Walton'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_id', $errors) !!}
                                        </div>
                                    </div>--}}

                                    {{-- Asbestos Assessment --}}
                                    <h4>Asbestos Assessment</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    {{-- Assessor Contact --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('assessor_name', $errors) !!}">
                                                {!! Form::label('assessor_name', 'Assessor Name', ['class' => 'control-label']) !!}
                                                {!! Form::select('assessor_name', ['' => 'Select option', 'Ray Ager' => 'Ray Ager', 'Mark Spindler' => 'Mark Spindler'], null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('assessor_name', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('assessor_phone', $errors) !!}">
                                                {!! Form::label('assessor_phone', 'Assessor Phone', ['class' => 'control-label']) !!}
                                                {!! Form::text('assessor_phone', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('assessor_phone', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {!! fieldHasError('assessor_cert', $errors) !!}">
                                                {!! Form::label('assessor_cert', 'Assessor Qualification', ['class' => 'control-label']) !!}
                                                {!! Form::select('assessor_cert', ['' => 'Select option', 'Competent person (VET Course)' => 'Competent person (VET Course)',
                                                'Competent person (Tertiary qualification)' => 'Competent person (Tertiary qualification)',
                                                'Licensed Asbestos Assessor' => 'Licensed Asbestos Assessor'], null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('assessor_cert', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('assessor_lic', $errors) !!}">
                                                {!! Form::label('assessor_lic', 'Licence No.', ['class' => 'control-label']) !!}
                                                {!! Form::text('assessor_lic', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('assessor_name', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('assessor_dept', $errors) !!}">
                                                {!! Form::label('assessor_dept', 'Department of Issue', ['class' => 'control-label']) !!}
                                                {!! Form::text('assessor_dept', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('assessor_dept', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group {!! fieldHasError('assessor_state', $errors) !!}">
                                                {!! Form::label('assessor_state', 'State', ['class' => 'control-label']) !!}
                                                {!! Form::select('assessor_state', $ozstates::all(), 'NSW', ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('assessor_state', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/asbestos/notification" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({
                placeholder: "Select Site",
            });

            displayFields();

            function displayFields() {
                var site_id = $("#site_id").select2("val");
                if (site_id != '') {
                    $.ajax({
                        url: '/site/data/details/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $("#site_name").val(data.name);
                            $("#site_code").val(data.code);
                            $("#site_address").val(data.address + ', ' + data.suburb + ' ' + data.state + ' ' + data.postcode);
                            $("#client_name").val(data.client_phone_desc);
                            $("#client_phone").val(data.client_phone);
                        },
                    })

                    $.ajax({
                        url: '/site/data/super/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $("#supervisor_id").val(data.id);
                            $("#supervisor_id").selectpicker('refresh');
                            $("#super_phone").val(data.phone);
                        },
                    })
                }
                // Amount
                if ($("#amount").val() > 9.999) {
                    $("#amount_note").show();
                    $("#amount_fields").show();
                    $("#amount_over").val('1');
                } else {
                    $("#amount_note").hide();
                    $("#amount_fields").hide();
                    $("#amount_over").val('0');
                }
                // Class 'Friable'
                $("#friable_note").hide();
                $("#non_friable_fields").hide();
                $("#non_friable_fields_part2").hide();
                if ($("#friable").val() == '1')
                    $("#friable_note").show();
                if ($("#friable").val() == '0')
                    $("#non_friable_removal").show();

                // Removalist
                $("#removalist_name_div").hide();
                if ($("#removalist").val() == '3') {
                    $("#removalist_name").val('Cape Cod Australia Pty Ltd AD205686');
                    $("#non_friable_fields").show();
                    $("#non_friable_fields_part2").show();
                }
                if ($("#removalist").val() == '385') {
                    $("#removalist_name").val('Handy 1st Pty Ltd AD2122895');
                    $("#non_friable_fields_part2").show();
                }
                if ($("#removalist").val() == 'other') {
                    $("#non_friable_fields_part2").show();
                    $("#removalist_name_div").show();
                }

                // Hygiene Report
                $("#hygiene_report_div").hide();
                if ($("#hygiene").val() == '1')
                    $("#hygiene_report_div").show();

                // Checkbox Other Equip + Method
                $('[name="equip[]"]').eq(5).is(':checked') ? $("#equip_other_div").show() : $("#equip_other_div").hide(); // Equip other
                $('[name="method[]"]').eq(7).is(':checked') ? $("#method_other_div").show() : $("#method_other_div").hide(); // Method other

                $("#type").val() == 'other' ? $("#type_other_div").show() : $("#type_other_div").hide(); // Type
                $("#register").val() == '0' ? $("#register_note").show() : $("#register_note").hide(); // Register
                $("#swms").val() == '0' ? $("#swms_note").show() : $("#swms_note").hide(); // SWMS
                $("#inspection").val() == '0' ? $("#inspection_note").show() : $("#inspection_note").hide();  // Inspection
            }

            // On Change Site ID
            $("#site_id").change(function () {
                displayFields();
            });

            // On Change Supervisor
            $("#supervisor_id").change(function () {
                $.ajax({
                    url: '/user/data/details/' + $("#supervisor_id").val(),
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $("#super_phone").val(data.phone);
                    },
                })
            });

            // On Change Amount
            $("#amount").keyup(function () {
                displayFields();
            });

            // On Change Class 'Friable'
            $("#friable").change(function () {
                displayFields();
            });

            // On Change Class 'Friable'
            $("#removalist").change(function () {
                $("#removalist_name").val('');
                displayFields();
            });


            // On Change Type
            $("#type").change(function () {
                displayFields();
            });

            // On Change Hygiene Report
            $("#hygiene").change(function () {
                displayFields();
            });

            // On Change Equip
            $("#equip").click(function () {
                displayFields();
            });

            // On Change Register
            $("#register").change(function () {
                displayFields();
            });

            // On Change SWMS
            $("#swms").change(function () {
                displayFields();
            });

            // On Change Inspection
            $("#inspection").change(function () {
                displayFields();
            });

            // On Change Assessor
            $("#assessor_name").change(function () {
                if ($("#assessor_name").val() == 'Leon Carnevale') {
                    $("#assessor_phone").val('0451 308 020');
                    $("#assessor_lic").val('1234-567-890');
                    $("#assessor_dept").val('dept 1');
                    $("#assessor_state").val('NSW');
                } else if ($("#assessor_name").val() == 'Mark Spindler') {
                    $("#assessor_phone").val('0417 064 161');
                    $("#assessor_lic").val('1234-567-890');
                    $("#assessor_dept").val('dept 2');
                    $("#assessor_state").val('NSW');
                } else {
                    $("#assessor_phone").val('');
                    $("#assessor_lic").val('');
                    $("#assessor_dept").val('');
                    $("#assessor_state").val('');
                }
            });
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }

        function checkbox_equipOther(el) {
            if (el.checked)
                document.getElementById('equip_other_div').style.display = 'block'
            else {
                document.getElementById('equip_other_div').style.display = 'none';
                $("#equip_other").val('');
            }
        }

        function checkbox_methodOther(el) {
            if (el.checked)
                document.getElementById('method_other_div').style.display = 'block'
            else {
                document.getElementById('method_other_div').style.display = 'none';
                $("#method_other").val('');
            }
        }

    </script>
@stop

