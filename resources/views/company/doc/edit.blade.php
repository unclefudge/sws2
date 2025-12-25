@inject('CompanyDocCategory', 'App\Models\Company\CompanyDocCategory')
@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/{{ $company->id }}/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Upload</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('company/_header')

        {{-- Compliance Documents --}}
        @if (count($company->missingDocs()))
            <div class="row">
                @include('company/_compliance-docs')
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase"> Edit Document</span>
                            <span class="caption-helper"> ID: {{ $doc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($doc, ['method' => 'PATCH', 'action' => ['Company\CompanyDocController@update',$company->id, $doc->id], 'class' => 'horizontal-form', 'files' => true, 'id' => 'doc_form']) !!}
                        @include('form-error')

                        @php
                            $path = "company/{$doc->company->id}/docs/$doc->attachment";
                            $size = $path ? \App\Services\FileBank::fileSize($path) : null;
                        @endphp

                        @if ($path && $size === 0)
                            <div class="alert alert-danger">
                                <i class="fa fa-warning"></i> <b>Error(s) have occured</b><br>
                                <ul>
                                    <li>Uploaded file failed to upload or is an empty file ie. 0 bytes.</li>
                                </ul>
                                <br>Please verify original file and upload new one.
                            </div>
                        @endif

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-9">
                                    @if ($doc->status == 3)
                                        <h2 style="margin: 0 0"><span class="label label-warning">Pending Approval</span></h2><br><br>
                                    @endif
                                    @if ($doc->status == 2)
                                        <div class="alert alert-danger">
                                            The document was not approved for the following reason:
                                            <ul>
                                                <li>{!! nl2br($doc->reject) !!}</li>
                                            </ul>

                                            @if (!in_array($doc->category_id, [4,5]))
                                                <br><b>Please correct the details and click &nbsp;<span class="btn blue btn-outline btn-primary btn-xs">Change File</span>&nbsp; to upload a different file (if required).</b>
                                            @else
                                                <br><b>Please correct the details and upload a different file (if required).</b>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    @if(!$doc->status)
                                        <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Inactive</h3>
                                    @endif
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    {!! Form::hidden('filetype', '', ['id' => 'filetype']) !!}
                                    {{-- Category --}}
                                    {!! Form::hidden('category_id', $doc->category_id, ['class' => 'form-control', 'id' => 'category_id']) !!}
                                    {!! Form::hidden('archive', null, ['class' => 'form-control', 'id' => 'archive']) !!}
                                    {{-- Contract Licence Super Classes 1 --}}
                                    @foreach ($doc->contractorLicenceSupervisorClasses(1) as $val)
                                        <input type="hidden" name="super_class1[]" id="super_class1" value="{{ $val }}">
                                    @endforeach
                                    {{-- Contract Licence Super Classes 2 --}}
                                    <input type="hidden" name="super_class2[]" id="super_class2" value="">
                                    @foreach ($doc->contractorLicenceSupervisorClasses(2) as $val)
                                        <input type="hidden" name="super_class2[]" id="super_class2" value="{{ $val }}">
                                    @endforeach
                                    {{-- Contract Licence Super Classes 3 --}}
                                    <input type="hidden" name="super_class3[]" id="super_class3" value="">
                                    @foreach ($doc->contractorLicenceSupervisorClasses(3) as $val)
                                        <input type="hidden" name="super_class3[]" id="super_class3" value="{{ $val }}">
                                    @endforeach

                                    @if ($doc->category_id > 8 && $doc->category_id != 22 && $doc->category->parent != 22)
                                        <div class="form-group">
                                            {!! Form::label('category_id_text', 'Category', ['class' => 'control-label']) !!}
                                            {!! Form::text('category_id_text', $CompanyDocCategory::find($doc->category_id)->name, ['class' => 'form-control bs-select', 'disabled']) !!}
                                        </div>
                                    @endif

                                    @if ($doc->category_id == 22 || $doc->category->parent == 22)
                                        <div class="form-group">
                                            {!! Form::label('category_id_text', 'Category', ['class' => 'control-label']) !!}
                                            {!! Form::text('category_id_text', ($doc->category->parent) ? $CompanyDocCategory::find($doc->category->parent)->name : $CompanyDocCategory::find($doc->category_id)->name, ['class' => 'form-control bs-select', 'disabled']) !!}
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label('subcategory_id', 'Sub Category', ['class' => 'control-label']) !!}
                                            {!! Form::select('subcategory_id', ($doc->category->parent) ? $CompanyDocCategory::find($doc->category->parent)->subcategorySelect('prompt') : $CompanyDocCategory::find($doc->category_id)->subcategorySelect('prompt'), $doc->category_id, ['class' => 'form-control bs-select']) !!}
                                        </div>
                                    @endif

                                    {{-- Name --}}
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control', ($doc->category_id < 9) ? 'readonly' : '']) !!}
                                    </div>
                                    @if (in_array($doc->category_id, [1, 2, 3]))
                                        {{-- Policy --}}
                                        <div class="form-group {!! fieldHasError('ref_no', $errors) !!}">
                                            {!! Form::label('ref_no', 'Policy No', ['class' => 'control-label']) !!}
                                            {!! Form::text('ref_no', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('ref_no', $errors) !!}
                                        </div>
                                        {{-- Insurer --}}
                                        <div class="form-group {!! fieldHasError('ref_name', $errors) !!}">
                                            {!! Form::label('ref_name', 'Insurer', ['class' => 'control-label']) !!}
                                            {!! Form::text('ref_name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('ref_name', $errors) !!}
                                        </div>
                                        @if (in_array($doc->category_id, [2, 3]))
                                            {{-- Category --}}
                                            <div class="form-group {!! fieldHasError('ref_type', $errors) !!}">
                                                {!! Form::label('ref_type', 'Category', ['class' => 'control-label']) !!}
                                                {!! Form::select('ref_type', $doc->company->workersCompCategorySelect('prompt'), null, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('ref_type', $errors) !!}
                                            </div>
                                        @endif
                                    @endif
                                    {{-- Lic No + Lic Class--}}
                                    @if ($doc->category_id == 7)
                                        <div class="form-group {!! fieldHasError('lic_no', $errors) !!}">
                                            {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_no', $doc->ref_no, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('lic_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('lic_type', $errors) !!}">
                                            {!! Form::label('lic_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            <select id="lic_type" name="lic_type[]" class="form-control select2" width="100%" multiple>
                                                {!! $company->contractorLicenceOptions() !!}
                                            </select>
                                            {!! fieldErrorMessage('lic_type', $errors) !!}
                                        </div>
                                        {{-- Supervisor of CL --}}
                                        <div class="form-group {!! fieldHasError('supervisor_no', $errors) !!}" id="fields_supervisor_no">
                                            {!! Form::label('supervisor_no', 'How many Supervisors are required to cover the above class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_no', ['' => 'Please specify', '1' => '1', '2' => '2', '3' => '3'], $doc->ref_name, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('supervisor_id', $errors) !!}" style="display: none" id="fields_supervisor_id">
                                            {!! Form::label('supervisor_id', 'Supervisor of all class(s) on licence', ['class' => 'control-label']) !!}
                                            {!! Form::select('supervisor_id', $company->staffSelect('prompt'), $doc->contractorLicenceSupervisor(1), ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('supervisor_id', $errors) !!}
                                        </div>
                                        <div style="display: none" id="fields_supervisor_id2">
                                            {{-- Supervisor 1 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id1', $errors) !!}">
                                                {!! Form::label('supervisor_id1', 'Supervisor 1', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id1', $company->staffSelect('prompt'), $doc->contractorLicenceSupervisor(1), ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id1', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type1', $errors) !!}">
                                                {!! Form::label('lic_type1', 'Supervisor 1 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type1" name="lic_type1[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes">
                                                    {!! $company->contractorLicenceOptions($doc->contractorLicenceSupervisorClasses(1)) !!}
                                                </select>
                                                {!! fieldErrorMessage('lic_type1', $errors) !!}
                                            </div>

                                            {{-- Supervisor 2 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id2', $errors) !!}">
                                                {!! Form::label('supervisor_id2', 'Supervisor 2', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id2', $company->staffSelect('prompt'), $doc->contractorLicenceSupervisor(2), ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id2', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type2', $errors) !!}">
                                                {!! Form::label('lic_type2', 'Supervisor 2 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type2" name="lic_type2[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes">
                                                    {!! $company->contractorLicenceOptions($doc->contractorLicenceSupervisorClasses(2)) !!}
                                                </select>
                                                {!! fieldErrorMessage('lic_type2', $errors) !!}
                                            </div>
                                        </div>
                                        {{-- Supervisor 3 --}}
                                        <div style="display: none" id="fields_supervisor_id3">
                                            <div class="form-group {!! fieldHasError('supervisor_id3', $errors) !!}">
                                                {!! Form::label('supervisor_id3', 'Supervisor 3', ['class' => 'control-label']) !!}
                                                {!! Form::select('supervisor_id3', $company->staffSelect('prompt'), $doc->contractorLicenceSupervisor(3), ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('supervisor_id3', $errors) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type3', $errors) !!}">
                                                {!! Form::label('lic_type3', 'Supervisor 3 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                <select id="lic_type3" name="lic_type3[]" class="form-control select2" width="100%" multiple placeholder="Select one or more classes">
                                                    {!! $company->contractorLicenceOptions($doc->contractorLicenceSupervisorClasses(3)) !!}
                                                </select>
                                                {!! fieldErrorMessage('lic_type3', $errors) !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($doc->category_id == 8)
                                        {{-- Asbestos Class --}}
                                        <div class="form-group {!! fieldHasError('asb_type', $errors) !!}" style="display: none" id="fields_asb_class">
                                            {!! Form::label('asb_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::select('asb_type', ['' => 'Select class', 'A' => 'Class A', 'B' => 'Class B'], $doc->ref_type, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('asb_type', $errors) !!}
                                        </div>
                                    @endif

                                    @if ($doc->category_id == 6)
                                        {{-- Test Expire Type --}}
                                        @if ($company->id == 3)
                                            <div class="form-group {!! fieldHasError('tag_type', $errors) !!}" id="fields_tag_type">
                                                {!! Form::label('tag_type', 'Expiry', ['class' => 'control-label']) !!}
                                                {!! Form::select('tag_type', ['3' => '3 month (site)', '12' => '12 month (office)'], $doc->ref_type, ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('tag_type', $errors) !!}
                                            </div>
                                        @else
                                            {!! Form::hidden('tag_type', '3') !!}
                                        @endif

                                        {{-- Test date --}}
                                        <div class="form-group {!! fieldHasError('tag_date', $errors) !!}" id="fields_tag_date">
                                            {!! Form::label('tag_date', 'Date of Testing', ['class' => 'control-label']) !!}
                                            <div class="input-group date date-picker">
                                                {!! Form::text('tag_date', $doc->expiry->subMonths($doc->ref_type)->format('d/m/Y'), ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                            </div>
                                            @if ($company->id != 3)
                                                <span class="help-block">Expires 3 months from date of testing</span>
                                            @endif
                                            {!! fieldErrorMessage('tag_date', $errors) !!}
                                        </div>
                                    @else
                                        {{-- Expiry --}}
                                        <div class="form-group {!! fieldHasError('expiry', $errors) !!}">
                                            {!! Form::label('expiry', ($doc->category_id == 22 || $doc->category->parent == 22) ? 'Renewal' : 'Expiry', ['class' => 'control-label', 'id' => 'expiry_label']) !!}
                                            @if ($doc->category_id == 4)
                                                {!! Form::text('expiry', ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                            @else
                                                <div class="input-group date date-picker">
                                                    {!! Form::text('expiry', ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '', ['class' => 'form-control form-control-inline',
                                                    'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                    <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                                </div>
                                            @endif
                                            {!! fieldErrorMessage('expiry', $errors) !!}
                                        </div>
                                    @endif
                                    {{-- Notes --}}
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    {{-- Attachment --}}
                                    <div class="form-group" id="attachment-div">
                                        <div class="col-md-9">
                                            {!! Form::label('filename', 'Filename', ['class' => 'control-label']) !!}
                                            {!! Form::text('filename', $doc->attachment, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                        <div class="col-md-3">
                                            @if ($doc->category_id == 5 && $doc->status == 3)
                                                <a href="/company/{{ $company->id }}/doc/period-trade-contract/{{ $doc->ref_no }}" target="_blank" id="doc_link"><i class="fa fa-bold fa-3x fa-file-text-o" style="margin-top: 25px;"></i><br>VIEW</a>
                                            @else
                                                <a href="{{ $doc->attachment_url }}" target="_blank" id="doc_link"><i class="fa fa-bold fa-3x fa-file-text-o" style="margin-top: 25px;"></i><br>VIEW</a>
                                            @endif
                                        </div>
                                        @if($doc->for_company_id == Auth::user()->company_id && !in_array($doc->category_id, [4,5]))
                                            {{-- Cant edit SS or PTC--}}
                                            <div class="col-md-3 col-md-offset-9">
                                                <button type="button" class="btn blue" style="margin-top: 25px;" id="change_file"> Change File</button>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Single File -->
                                    <div class="form-group {!! fieldHasError('singlefile', $errors) !!}" style="display: none" id="singlefile-div">
                                        <label class="control-label">Select File (PDF)</label>
                                        <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singlefile', $errors) !!}
                                    </div>

                                    <!-- Single Image File -->
                                    <div class="form-group {!! fieldHasError('singleimage', $errors) !!}" style="display: none" id="singleimage-div">
                                        <label class="control-label">Select File / Photo</label>
                                        <input id="singleimage" name="singleimage" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singleimage', $errors) !!}
                                    </div>

                                    @if (Auth::user()->isCompany($doc->company_id))
                                        @if ($doc->approved_by)
                                            <div style="padding-left: 20px">
                                                Document approved by {{ $doc->approvedBy->name }} on {{ $doc->approved_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                        @if ($doc->status == 0)
                                            <div style="padding-left: 20px">
                                                {{--}}Document archived by {{ $doc->updatedBy->name }} on {{ $doc->updated_at->format('d/m/Y') }} --}}
                                            </div>
                                        @endif
                                    @endif
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                {{-- Achive - only 'live' docs status = 1 --}}
                                @if ($doc->status == 1 && Auth::user()->allowed2('del.company.doc', $doc))
                                    <a class="btn dark" data-toggle="modal" href="#modal_archive"> Archive </a>
                                @endif
                                {{-- Reject / Approve - only pending/rejected docs --}}
                                @if ($doc->category_id == 5 && $doc->status == 3)
                                    <a href="/company/{{ $company->id }}/doc/period-trade-contract/{{ $doc->ref_no }}" class="btn dark" id="but_save">View Contract for Approval</a>
                                @elseif (in_array($doc->status, [2,3]) && Auth::user()->allowed2('sig.company.doc', $doc))
                                    @if ($doc->status == 3)
                                        <a class="btn dark" data-toggle="modal" href="#modal_reject"> Reject </a>
                                    @endif
                                    @if ((in_array($doc->category_id, [1,2,3]) && $company->activeCompanyDoc($doc->category_id) && $company->activeCompanyDoc($doc->category_id)->status == 1))
                                        <a href="#modal_approve_archive" class="btn green" data-toggle="modal" id="approve_archive">Approve</a>
                                    @else
                                        <button type="submit" name="save" value="save" class="btn green" id="approve">Approve</button>
                                    @endif
                                @else
                                    {{-- Save / Upload - only 'current' docs status > 0 --}}
                                    @if ($doc->status != 0)
                                        <button type="submit" class="btn green" id="but_save">Save</button>
                                        <button type="submit" class="btn green" id="but_upload" style="display: none">Upload</button>
                                    @elseif (!$doc->status && Auth::user()->allowed2('del.company.doc', $doc))
                                        <a href="/company/{{ $company->id }}/doc/archive/{{ $doc->id }}" class="btn red" id="but_save">Re-activate</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div id="modal_reject" class="modal fade" id="basic" tabindex="-1" role="modal_reject" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Reject Document</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::model($doc, ['method' => 'POST', 'action' => ['Company\CompanyDocController@reject',$company->id, $doc->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        <div class="form-group {!! fieldHasError('reject', $errors) !!}">
                            {!! Form::label('reject', 'Reason for rejecting document', ['class' => 'control-label']) !!}
                            {!! Form::textarea('reject', null, ['rows' => '3', 'class' => 'form-control']) !!}
                            {!! fieldErrorMessage('reject', $errors) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn green" name="reject_doc" value="reject">Reject</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <!-- Archive Modal -->
        <div id="modal_archive" class="modal fade bs-modal-sm" tabindex="-1" role="modal_arcive" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title text-center"><b>Archive Document</b></h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">You are about to make this document no longer <span style="text-decoration: underline">active</span> and archive it.</p>
                        <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> Once archived only {{ $doc->owned_by->name }} can reactivite it.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <a href="/company/{{ $company->id }}/doc/archive/{{ $doc->id }}" class="btn green">Continue</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approve & Archive Modal --}}
        <div id="modal_approve_archive" class="modal fade bs-modal-sm" id="basic" tabindex="-1" role="modal_approve_archive" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Replace Existing Document</h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <b>{{ $company->name }}</b> currently has the following valid document.<br><br>
                            <a href="{!! ($company->activeCompanyDoc($doc->category_id)) ? $company->activeCompanyDoc($doc->category_id)->attachment_url : '' !!} " target="_blank">{{ $doc->name }}<br>
                                expiry {!! ($company->activeCompanyDoc($doc->category_id) && $company->activeCompanyDoc($doc->category_id)->expiry) ? $company->activeCompanyDoc($doc->category_id)->expiry->format('d/m/Y') : '' !!}</a><br><br>
                            <span class="font-red"><b>By approving this document it will archive the old one.</b></span><br><br>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                            <button type="button" class="btn green" name="archive_doc" id="accept_archive">Accept</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $doc->displayUpdatedBy() !!}
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            $("#filetype").val(''); // clear field on onload

            /* Select2 */
            $("#lic_type").select2({placeholder: "Select one or more", width: '100%'});
            $("#lic_type1").select2({placeholder: "Select one or more", width: '100%'});
            $("#lic_type2").select2({placeholder: "Select one or more", width: '100%'});

            function display_fields() {
                var cat = $("#category_id").val();

                $('#fields_supervisor').hide();
                $('#fields_supervisor_id').hide();
                $('#fields_supervisor_id2').hide();
                $('#fields_supervisor_id3').hide();

                if (cat == 7) { // CL
                    $('#fields_lic_no').show();
                    $('#fields_lic_class').show();
                    $('#fields_supervisors').show();

                    if ($("#supervisor_no").val() == 1)
                        $('#fields_supervisor_id').show();
                    if ($("#supervisor_no").val() > 1)
                        $('#fields_supervisor_id2').show();
                    if ($("#supervisor_no").val() > 2)
                        $('#fields_supervisor_id3').show();
                }

                if (cat == 8)  // Asbestos
                    $('#fields_asb_class').show();
            }

            function cl_supervisors() {
                var lic_types = {};
                $("#lic_type option:selected").each(function () {
                    var val = $(this).val();
                    if (val !== '')
                        lic_types[val] = $(this).text();
                });

                $("#lic_type1").empty();
                $("#lic_type2").empty();
                $("#lic_type3").empty();
                //var super_class1 = $("#super_class1").val();
                //var super_class2 = $("#super_class2").val();
                //var super_class3 = $("#super_class3").val();
                $.each(lic_types, function (index, value) {
                    var selected1 = '';
                    var selected2 = '';
                    var selected3 = '';
                    /*if (!jQuery.inArray(index, super_class1)) {selected1 = ' selected';}
                     if (!jQuery.inArray(index, super_class2)) {selected2 = ' selected';}
                     if (!jQuery.inArray(index, super_class3)) {selected3 = ' selected';}*/
                    $("#lic_type1").append('<option value="' + index + '"' + selected1 + '>' + value + '</option>');
                    $("#lic_type2").append('<option value="' + index + '"' + selected2 + '>' + value + '</option>');
                    $("#lic_type3").append('<option value="' + index + '"' + selected3 + '>' + value + '</option>');
                });
            }

            display_fields();

            $("#lic_type").change(function () {
                display_fields();
                cl_supervisors();
            });

            $("#supervisor_no").change(function () {
                display_fields();
                //cl_supervisors();
                $("#lic_type1").empty();
                $("#lic_type2").empty();
                $("#lic_type3").empty();
            });


            /* Bootstrap Fileinput */
            $("#singlefile").fileinput({
                showUpload: false,
                allowedFileExtensions: ["pdf"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn btn-danger",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn btn-info",
            });

            /* Bootstrap Fileinput */
            $("#singleimage").fileinput({
                showUpload: false,
                allowedFileExtensions: ["pdf", "jpg", "png", "gif"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn btn-danger",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn btn-info",
            });

            $("#change_file").click(function () {
                $('#attachment-div').hide();
                // All uploads can now be images 7/4/25
                $('#singleimage-div').show();
                $('#filetype').val('image');
                /*if ($("#category_id").val() == 6 || $("#category_id").val() == 7 || $("#category_id").val() == 9 || $("#category_id").val() == 10) { // 6 Test Tag, 7 Contractors Lic, 9 Other Lic, 10 Builders Lic
                     $('#singleimage-div').show();
                     $('#filetype').val('image');
                 } else {
                     $('#singlefile-div').show();
                     $('#filetype').val('pdf');
                 }*/
                $('#but_upload').show();
                $('#but_save').hide();
            });

            $("#accept_archive").click(function (e) {
                e.preventDefault();
                $('#archive').val({{ ($company->activeCompanyDoc($doc->category_id)) ? $company->activeCompanyDoc($doc->category_id)->id : null }});
                $("#doc_form").submit();
            });

        });

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });

    </script>
@stop