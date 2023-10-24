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
                            <span class="caption-subject font-dark bold uppercase"> View Document</span>
                            <span class="caption-helper"> ID: {{ $doc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($doc, ['method' => 'PATCH', 'action' => ['Company\CompanyDocController@update',$company->id, $doc->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        @if (file_exists(public_path($doc->attachment_url)) && filesize(public_path($doc->attachment_url)) == 0)
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
                                    {{-- Category --}}
                                    {!! Form::hidden('category_id', $doc->category_id, ['class' => 'form-control']) !!}
                                    @if ($doc->category_id > 8)
                                        <div class="form-group">
                                            {!! Form::label('category_id_text', 'Category', ['class' => 'control-label']) !!}
                                            {!! Form::text('category_id_text', \App\Models\Company\CompanyDocCategory::find($doc->category_id)->name, ['class' => 'form-control bs-select', 'disabled']) !!}
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
                                            {!! Form::text('ref_no', null, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                        {{-- Insurer --}}
                                        <div class="form-group {!! fieldHasError('ref_name', $errors) !!}">
                                            {!! Form::label('ref_name', 'Insurer', ['class' => 'control-label']) !!}
                                            {!! Form::text('ref_name', null, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                        @if (in_array($doc->category_id, [2, 3]))
                                            {{-- Category --}}
                                            <div class="form-group {!! fieldHasError('ref_type', $errors) !!}">
                                                {!! Form::label('ref_type', 'Category', ['class' => 'control-label']) !!}
                                                {!! Form::select('ref_type', $doc->company->workersCompCategorySelect('prompt'), null, ['class' => 'form-control bs-select', 'readonly']) !!}
                                            </div>
                                        @endif
                                    @endif
                                    {{-- Lic No + Lic Class--}}
                                    @if ($doc->category_id == 7)
                                        <div class="form-group {!! fieldHasError('lic_no', $errors) !!}">
                                            {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_no', $doc->ref_no, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('lic_type', $errors) !!}">
                                            {!! Form::label('lic_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_type', $doc->company->contractorLicenceSBC(), ['class' => 'form-control', 'readonly']) !!}
                                        </div>

                                        {{-- Supervisor of CL --}}
                                        <div class="form-group {!! fieldHasError('supervisor_no', $errors) !!}" id="fields_supervisor_no">
                                            {!! Form::label('supervisor_no', 'How many Supervisors are required to cover the above class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::text('supervisor_no', $doc->ref_name, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                        @if ($doc->ref_name == 1)
                                            <div class="form-group {!! fieldHasError('supervisor_id', $errors) !!}" id="fields_supervisor_id">
                                                {!! Form::label('supervisor_id', 'Supervisor of all class(s) on licence', ['class' => 'control-label']) !!}
                                                {!! Form::text('supervisor_id', $doc->contractorLicenceSupervisor(1), ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                        @endif
                                        @if ($doc->ref_name > 1)
                                            {{-- Supervisor 1 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id1', $errors) !!}">
                                                {!! Form::label('supervisor_id1', 'Supervisor 1', ['class' => 'control-label']) !!}
                                                {!! Form::text('supervisor_id1', (\App\User::find($doc->contractorLicenceSupervisor(1))) ? \App\User::find($doc->contractorLicenceSupervisor(1))->name : '', ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type1', $errors) !!}">
                                                {!! Form::label('lic_type1', 'Supervisor 1 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                {!! Form::text('lic_type1', $doc->contractorLicenceSupervisorClassesSBC(1), ['class' => 'form-control', 'readonly']) !!}
                                            </div>

                                            {{-- Supervisor 2 --}}
                                            <div class="form-group {!! fieldHasError('supervisor_id2', $errors) !!}">
                                                {!! Form::label('supervisor_id2', 'Supervisor 2', ['class' => 'control-label']) !!}
                                                {!! Form::text('supervisor_id2', (\App\User::find($doc->contractorLicenceSupervisor(2))) ? \App\User::find($doc->contractorLicenceSupervisor(2))->name : '', ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type2', $errors) !!}">
                                                {!! Form::label('lic_type2', 'Supervisor 2 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                {!! Form::text('lic_type2', $doc->contractorLicenceSupervisorClassesSBC(2), ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                        @endif
                                        {{-- Supervisor 3 --}}
                                        <div style="display: none" id="fields_supervisor_id3">
                                            <div class="form-group {!! fieldHasError('supervisor_id3', $errors) !!}">
                                                {!! Form::label('supervisor_id3', 'Supervisor 3', ['class' => 'control-label']) !!}
                                                {!! Form::text('supervisor_id3', (\App\User::find($doc->contractorLicenceSupervisor(3))) ? \App\User::find($doc->contractorLicenceSupervisor(3))->name : '', ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                            <div class="form-group {!! fieldHasError('lic_type3', $errors) !!}">
                                                {!! Form::label('lic_type3', 'Supervisor 3 is ONLY responsible for class(s) ', ['class' => 'control-label']) !!}
                                                {!! Form::text('lic_type3', $doc->contractorLicenceSupervisorClassesSBC(3), ['class' => 'form-control', 'readonly']) !!}
                                            </div>
                                        </div>
                                    @endif
                                    {{-- Asbestos Class --}}
                                    <div class="form-group {!! fieldHasError('asb_type', $errors) !!}" style="display: none" id="fields_asb_class">
                                        {!! Form::label('asb_type', 'Class(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('asb_type', ['' => 'Select class', 'A' => 'Class A', 'B' => 'Class B'], null, ['class' => 'form-control bs-select', 'readonly']) !!}
                                    </div>

                                    @if ($doc->category_id == 6)
                                        {{-- Test Expire Type --}}
                                        @if ($company->id == 3)
                                            <div class="form-group {!! fieldHasError('tag_type', $errors) !!}" id="fields_tag_type">
                                                {!! Form::label('tag_type', 'Expiry', ['class' => 'control-label']) !!}
                                                {!! Form::select('tag_type', ['3' => '3 month (site)', '12' => '12 month (office)'], $doc->ref_type, ['class' => 'form-control bs-select', 'readonly']) !!}
                                            </div>
                                        @else
                                            {!! Form::hidden('tag_type', '3') !!}
                                        @endif

                                        {{-- Test date --}}
                                        <div class="form-group {!! fieldHasError('tag_date', $errors) !!}" id="fields_tag_date">
                                            {!! Form::label('tag_date', 'Date of Testing', ['class' => 'control-label']) !!}
                                            {!! Form::text('tag_date', $doc->expiry->subMonths($doc->ref_type)->format('d/m/Y'), ['class' => 'form-control', 'data-date-format' => "dd-mm-yyyy", 'readonly', 'disabled']) !!}
                                            @if ($company->id != 3)
                                                <span class="help-block">Expires 3 months from date of testing</span>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Expiry --}}
                                        <div class="form-group {!! fieldHasError('expiry', $errors) !!}">
                                            {!! Form::label('expiry', 'Expiry', ['class' => 'control-label']) !!}
                                            {!! Form::text('expiry', ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    @endif
                                    {{-- Notes --}}
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
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
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                {{-- SS Renewal --}}
                                @if ($doc->category_id == 4 && $doc->status == 1 && Auth::user()->isCompany($company->id))
                                    <a href="/company/{{ $company->id }}/doc/subcontractor-statement/create" class="btn green"> Renew</a>
                                @endif
                                {{-- PTC Renewal --}}
                                @if ($doc->category_id == 5 && $doc->status == 1 && Auth::user()->isCompany($company->id))
                                    <a href="/company/{{ $company->id }}/doc/period-trade-contract/create" class="btn green"> Renew</a>
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
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

        $("#change_file").click(function () {
            $('#attachment-div').hide();
            $('#singlefile-div').show();
            $('#but_upload').show();
            $('#but_save').hide();
        });

    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

</script>
@stop