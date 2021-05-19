@extends('layout')
<?php $hotwater_types = ['' => 'Select option', 'In Roof' => 'In Roof', 'External' => 'External', 'Internal' => 'Internal', 'Gas' => 'Gas', 'Electric' => 'Electric', 'Solar' => 'Solar', 'Other' => 'Other']; ?>
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription)
            <li><a href="/site/inspection/electrical">Plumbing Inspection Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Plumbing Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteInspectionPlumbingController@update', $report->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        <input type="hidden" name="report_id" id="report_id" value="{{ $report->id }}">
                        <input type="hidden" name="site_id" id="site_id" value="{{ $report->site_id }}">

                        @include('form-error')

                        @if (!$report->assigned_to)
                            {{-- Progress Steps --}}
                            <div class="mt-element-step hidden-sm hidden-xs">
                                <div class="row step-thin" id="steps">
                                    <div class="col-md-4 mt-step-col first done">
                                        <div class="mt-step-number bg-white font-grey">1</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                        <div class="mt-step-content font-grey-cascade">Create report</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col done">
                                        <div class="mt-step-number bg-white font-grey">2</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                        <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                    </div>
                                    <div class="col-md-4 mt-step-col last active">
                                        <div class="mt-step-number bg-white font-grey">3</div>
                                        <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                        <div class="mt-step-content font-grey-cascade">Assign company</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endif

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_name', $report->site->name, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('site_code', 'Site No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('site_code', $report->site->code, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 style="margin: 0px; padding-right: 20px">
                                        @if($report->status == '0')
                                            <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</small></span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                        @endif
                                        @if($report->status == '1' && $report->assigned_to)
                                            <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                            <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                        @endif
                                    </h2>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Client details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('client_name', $errors) !!}">
                                        {!! Form::label('client_name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_name', null, ['class' => 'form-control', (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('client_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('client_address', $errors) !!}">
                                        {!! Form::label('client_address', 'Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_address', null, ['class' => 'form-control', (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('client_address', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::textarea("info", $report->info, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details",  (Auth::user()->allowed2('add.site.inspection')) ? '' : 'readonly']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Gallery --}}
                            <br>
                            <div class="row"  id="photos-show">
                                <div class="col-md-7">
                                    <h4>Photos
                                        @if(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-photos">Edit</button>
                                        @endif</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_gallery')
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-4" id="docs-show">
                                    <h4>Documents
                                        @if(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-docs">Edit</button>
                                        @endif
                                    </h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @include('site/inspection/_docs')
                                </div>
                            </div>

                            <div id="photos-edit">
                                <h4>Photos / Documents
                                    @if(Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report))
                                        <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="view-photos">View</button>
                                    @endif</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="note note-warning">
                                    Multiple photos/images can be uploaded with this maintenance request.
                                    <ul>
                                        <li>Once you have selected your files upload them by clicking
                                            <button class="btn dark btn-outline btn-xs" href="javascript:;"><i class="fa fa-upload"></i> Upload</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Select Files</label>
                                            <input id="multifile" name="multifile[]" type="file" multiple class="file-loading">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Inspection details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Assigned To Company --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('assigned_to', $errors) !!}" style="{{ fieldHasError('assigned_to', $errors) ? '' : 'display:show' }}" id="company-div">
                                        {!! Form::label('assigned_to', 'Assigned to company', ['class' => 'control-label']) !!}
                                        @if(Auth::user()->allowed2('sig.site.inspection'))
                                            <select id="assigned_to" name="assigned_to" class="form-control bs-select" style="width:100%">
                                                @if (!$report->assigned_to)
                                                    <option value="">Select company</option>
                                                @endif
                                                @foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company)
                                                    @if (in_array('8', $company->tradesSkilledIn->pluck('id')->toArray()))
                                                        <option value="{{ $company->id }}" {{ ($report->assigned_to && $report->assigned_to == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            {!! Form::text('assigned_name', ($report->assignedTo) ? $report->assignedTo->name : '', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('assigned_to', $errors) !!}
                                    </div>
                                </div>
                                {{-- Inspection Date/Time --}}
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('inspected_at', $errors) !!}" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}" id="inspected_at-div">
                                        {!! Form::label('inspected_at', 'Date / Time of Inspection', ['class' => 'control-label']) !!}
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('inspected_at', ($report->inspected_at) ? $report->inspected_at->format('d F Y - H:i') : '', ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('inspected_at', $errors) !!}
                                    </div>
                                </div>

                                {{-- Client contacted --}}
                                <div class="col-md-2" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                    <div class="form-group {!! fieldHasError('client_contacted', $errors) !!}">
                                        {!! Form::label('client_contacted', 'Client contacted', ['class' => 'control-label']) !!}
                                        {!! Form::select('client_contacted', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('client_contacted', $errors) !!}
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-2 pull-right">
                                    <div class="form-group">
                                        {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                        @if ($report->status && Auth::user()->allowed2('edit.site.inspection', $report))
                                            {!! Form::select('status', ['1' => 'Active', '0' => 'Completed'], $report->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                                        @else
                                            {!! Form::text('status_text', ($report->status == 0) ? 'Completed' : 'Active', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Inspectors Name + Lic--}}
                            <div class="row note note-warning" id="inspector-div" style="{{ (fieldHasError('inspected_name', $errors) || fieldHasError('inspected_lic', $errors)) ? 'display:show' : 'display:none' }}">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('inspected_name', $errors) !!}">
                                        {!! Form::label('inspected_name', 'Inspection carried out by', ['class' => 'control-label']) !!}
                                        {!! Form::text('inspected_name', Auth::user()->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('inspected_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('inspected_lic', $errors) !!}">
                                        {!! Form::label('inspected_lic', 'Licence No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('inspected_lic', Auth::user()->company->contractorLicence(), ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('inspected_lic', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div id="report-div" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                <h4 class="font-green-haze">Hot / Cold Water</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                {{--Water Pressure / Hammer--}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('pressure', $errors) !!}">
                                            {!! Form::label('pressure', 'Water Pressure (kpa)', ['class' => 'control-label']) !!}
                                            {!! Form::text('pressure', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('pressure', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('pressure_reduction', $errors) !!}">
                                            {!! Form::label('pressure_reduction', '500kpa Water Pressure Reduction Value Recommend', ['class' => 'control-label']) !!}
                                            {!! Form::select('pressure_reduction', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('pressure_reduction', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('hammer', $errors) !!}">
                                            {!! Form::label('hammer', 'Water Hammer', ['class' => 'control-label']) !!}
                                            {!! Form::select('hammer', ['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No', 'N/A' => 'N/A'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('hammer', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Hotwater / Pipes / Gas --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('hotwater_type', $errors) !!}">
                                            {!! Form::label('hotwater_type', 'Existing Hot Water Type', ['class' => 'control-label']) !!}
                                            {!! Form::select('hotwater_type', $hotwater_types, null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('hotwater_type', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('hotwater_lowered', $errors) !!}">
                                            {!! Form::label('hotwater_lowered', 'Will pipes in roof hot water need to be lowerd?', ['class' => 'control-label']) !!}
                                            {!! Form::select('hotwater_lowered', ['' => 'Select option', '1' => 'Yes', '0' => 'No'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('hotwater_lowered', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('fuel_type', $errors) !!}">
                                            {!! Form::label('fuel_type', 'Fuel Type', ['class' => 'control-label']) !!}
                                            {!! Form::select('fuel_type', ['' => 'Select option', 'Gas' => 'Gas', 'Electric' => 'Electric', 'Other' => 'Other'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('fuel_type', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{--  Gas  Meter / Pipes--}}
                                <h4 class="font-green-haze">Gas</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('gas_position', $errors) !!}">
                                            {!! Form::label('gas_position', 'Gas Meter Position OK?', ['class' => 'control-label']) !!}
                                            {!! Form::select('gas_position', ['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No', 'N/A' => 'N/A'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('gas_position', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('gas_lines', $errors) !!}">
                                            {!! Form::label('gas_lines', 'Are gas pipes able to be tapped into?', ['class' => 'control-label']) !!}
                                            {!! Form::select('gas_lines', ['' => 'Select option', '1' => 'Yes - refer to comments below', '0' => 'No - refer to comments below '], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('gas_lines', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('gas_pipes', $errors) !!}">
                                            {!! Form::label('gas_pipes', 'Gas Pipes', ['class' => 'control-label']) !!}
                                            {!! Form::select('gas_pipes', ['' => 'Select option', 'GAL Steel' => 'GAL Steel', 'Copper' => 'Copper', 'Gas Pex' => 'Gas Pex', 'Other' => 'Other'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('gas_pipes', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Gas Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('gas_notes', $errors) !!}">
                                            {!! Form::label('gas_notes', 'Gas Notes', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('gas_notes', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('gas_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>


                                {{-- Existing Plumbing --}}
                                <h4 class="font-green-haze">Condition of existing plumbing</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('existing', $errors) !!}">
                                            {!! Form::label('existing', 'The existing plumbing was found to be', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('existing', null, ['rows' => '5', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('existing', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                <!-- Comments -->
                                <h4 class="font-green-haze">Comments</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                            {!! Form::label('notes', 'Additional notes', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('notes', null, ['rows' => '10', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Water Pressure --}}
                                <h4 class="font-green-haze">Water Pressure</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('pressure_notes', $errors) !!}">
                                            {!! Form::label('pressure_notes', 'Water pressure higher than 500KPA will void the warranty on all mixer sets; it is our recommendation that you have fitted a pressure limiting valve at the metre to avoid possible problems.      ', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('pressure_notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('pressure_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('pressure_cost', $errors) !!}">
                                            {!! Form::label('pressure_cost', 'Cost (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('pressure_cost', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('pressure_cost', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Water Hammer --}}
                                <h4 class="font-green-haze">Water Hammer</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('hammer_notes', $errors) !!}">
                                            {!! Form::label('hammer_notes', 'Water hammer comments', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('hammer_notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('hammer_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Sewer --}}
                                <h4 class="font-green-haze">Sewer</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('sewer_notes', $errors) !!}">
                                            {!! Form::label('sewer_notes', 'Upon closer inspection of the sewer diagram that we have obtained from the Water Board', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('sewer_notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('sewer_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('sewer_cost', $errors) !!}">
                                            {!! Form::label('sewer_cost', 'Cost estimate (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('sewer_cost', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('sewer_cost', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('sewer_allowance', $errors) !!}">
                                            {!! Form::label('sewer_allowance', 'Allowance in your tender document is (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('sewer_allowance', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('sewer_allowance', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('sewer_extra', $errors) !!}">
                                            {!! Form::label('sewer_extra', 'Meaning you may incur extra costs of (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('sewer_extra', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('sewer_extra', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12"><h6>PRICE TO BE CONFIRMED AT TIME OF CONSTRUCTION AND DOES NOT INCLUDE BUILDERS MARGIN</h6><br></div>
                                </div>


                                {{-- Stormwater --}}
                                <h4 class="font-green-haze">Stormwater</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('stormwater_notes', $errors) !!}">
                                            {!! Form::label('stormwater_notes', 'Upon closer examination of your current stormwater system', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('stormwater_notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('stormwater_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('stormwater_cost', $errors) !!}">
                                            {!! Form::label('stormwater_cost', 'Cost estimate (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('stormwater_cost', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('stormwater_cost', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('stormwater_allowance', $errors) !!}">
                                            {!! Form::label('stormwater_allowance', 'Allowance in your tender document is (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('stormwater_allowance', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('stormwater_allowance', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {!! fieldHasError('stormwater_extra', $errors) !!}">
                                            {!! Form::label('stormwater_extra', 'Meaning you may incur extra costs of (incl GST)', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                {!! Form::text('stormwater_extra', null, ['class' => 'form-control']) !!}
                                            </div>
                                            {!! fieldErrorMessage('stormwater_extra', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Stormwater Detention --}}
                                <h4 class="font-green-haze">Onsite Stormwater Detention</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('stormwater_detention_type', $errors) !!}">
                                            {!! Form::select('stormwater_detention_type', ['' => 'Select option', 'Refer to comments below' => 'Refer to comments below', 'Refer to quote' => 'Refer to quote', 'N/A' => 'N/A'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('stormwater_detention_type', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('stormwater_detention_notes', $errors) !!}">
                                            {!! Form::label('stormwater_detention_notes', 'Onsite Stormwater Detention Comments', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('stormwater_detention_notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('stormwater_detention_notes', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Note --}}
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Please note that these remain best estimate until the final position and depth of services are located. Final estimates will be relayed to you at that time for your approval. <br><br>Thank you for your acknowledgment of the above and we will do our best to keep all costs to a minimum.</h6><br></div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/inspection/plumbing" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <!--<script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>-->

    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#assigned_to").select2({placeholder: "Select Company"});

        $("#status").change(function () {
            $('#inspector-div').hide();

            if ($("#status").val() == '0') {
                $('#inspector-div').show();
            }
        });

        $('#photos-edit').hide();
        $("#edit-photos").click(function (e) {
            e.preventDefault();
            $('#photos-show').hide();
            $('#photos-edit').show();
        });
        $("#edit-docs").click(function (e) {
            e.preventDefault();
            $('#photos-show').hide();
            $('#photos-edit').show();
        });
        $("#view-photos").click(function (e) {
            e.preventDefault();
            $('#photos-show').show();
            $('#photos-edit').hide();
        });

        /* Bootstrap Fileinput */
        $("#multifile").fileinput({
            uploadUrl: "/site/inspection/plumbing/upload/", // server upload action
            uploadAsync: true,
            //allowedFileExtensions: ["image"],
            //allowedFileTypes: ["image"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn red",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn dark",
            uploadIcon: "<i class=\"fa fa-upload\"></i> ",
            uploadExtraData: {
                "site_id": site_id,
                "report_id": report_id,
            },
            layoutTemplates: {
                main1: '<div class="input-group {class}">\n' +
                '   {caption}\n' +
                '   <div class="input-group-btn">\n' +
                '       {remove}\n' +
                '       {upload}\n' +
                '       {browse}\n' +
                '   </div>\n' +
                '</div>\n' +
                '<div class="kv-upload-progress hide" style="margin-top:10px"></div>\n' +
                '{preview}\n'
            },
        });

        $('#multifile').on('filepreupload', function (event, data, previewId, index, jqXHR) {
            data.form.append("site_id", $("#site_id").val());
            data.form.append("report_id", $("#report_id").val());
        });
    });
</script>
@stop


