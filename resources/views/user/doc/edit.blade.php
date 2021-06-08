@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
            <li><a href="/user/{{ $user->id}}/doc">Documents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('user/_header')

        {{-- Compliance Documents --}}


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
                        {!! Form::model($doc, ['method' => 'PATCH', 'action' => ['User\UserDocController@update',$user->id, $doc->id], 'class' => 'horizontal-form', 'files' => true]) !!}
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
                                    @if ($doc->status == 2)
                                        <h2 style="margin: 0 0"><span class="label label-warning">Pending Approval</span></h2><br><br>
                                    @endif
                                    @if ($doc->status == 3)
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
                                        {!! Form::label('name', (in_array($doc->category_id, [6,7,8])) ? 'Document Type' : 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control', ($doc->category_id < 9) ? 'readonly' : '']) !!}
                                        {{-- Ref - Name --}}
                                        {!! Form::hidden('ref_name', $doc->name, ['class' => 'form-control']) !!}
                                    </div>

                                    {{-- Ref Name --}}
                                    @if (in_array($doc->category_id, [6,7,8]))
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('ref_name', 'Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('ref_name', null, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    @endif
                                    {{-- Drivers Lic No + Class--}}
                                    @if ($doc->category_id == 2)
                                        <div class="form-group {!! fieldHasError('lic_no', $errors) !!}">
                                            {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_no', $doc->ref_no, ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('lic_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('drivers_type', $errors) !!}">
                                            {!! Form::label('drivers_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::text('drivers_type', $doc->ref_type, ['class' => 'form-control', 'readonly']) !!}
                                            {{--
                                            <select id="drivers_type" name="drivers_type[]" class="form-control select2" width="100%" multiple readonly>
                                                {!! $user->driversLicenceOptions() !!}
                                            </select> --}}
                                            {!! fieldErrorMessage('drivers_type', $errors) !!}
                                        </div>
                                    @endif
                                    {{-- Contractor Lic No + Class--}}
                                    @if ($doc->category_id == 3)
                                        <div class="form-group {!! fieldHasError('lic_no', $errors) !!}">
                                            {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_no', $doc->ref_no, ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('lic_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('cl_type', $errors) !!}">
                                            {!! Form::label('cl_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::text('cl_type', $user->contractorLicenceSBC(), ['class' => 'form-control', 'readonly']) !!}
                                            {{--
                                            <select id="lic_type" name="lic_type[]" class="form-control select2" width="100%" multiple readonly>
                                                {!! $user->contractorLicenceOptions() !!}
                                            </select>--}}
                                            {!! fieldErrorMessage('lic_type', $errors) !!}
                                        </div>
                                    @endif

                                    {{-- Supervisor Lic No + Class--}}
                                    @if ($doc->category_id == 4)
                                        <div class="form-group {!! fieldHasError('lic_no', $errors) !!}">
                                            {!! Form::label('lic_no', 'Licence No.', ['class' => 'control-label']) !!}
                                            {!! Form::text('lic_no', $doc->ref_no, ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('lic_no', $errors) !!}
                                        </div>
                                        <div class="form-group {!! fieldHasError('super_type', $errors) !!}">
                                            {!! Form::label('super_type', 'Class(s)', ['class' => 'control-label']) !!}
                                            {!! Form::text('super_type', $user->supervisorLicenceSBC(), ['class' => 'form-control', 'readonly']) !!}
                                            {{--
                                            <select id="lic_type" name="lic_type[]" class="form-control select2" width="100%" multiple readonly>
                                                {!! $user->contractorLicenceOptions() !!}
                                            </select>--}}
                                            {!! fieldErrorMessage('lic_type', $errors) !!}
                                        </div>
                                    @endif

                                    @if (in_array($doc->category_id, [2, 3]))
                                        {{-- Expiry --}}
                                        <div class="form-group {!! fieldHasError('expiry', $errors) !!}">
                                            {!! Form::label('expiry', 'Expiry', ['class' => 'control-label']) !!}
                                            {!! Form::text('expiry', ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('expiry', $errors) !!}
                                        </div>
                                    @else
                                        {{-- Issued --}}
                                        <div class="form-group {!! fieldHasError('issued', $errors) !!}">
                                            {!! Form::label('issued', 'Issued Date', ['class' => 'control-label']) !!}
                                            {!! Form::text('issued', ($doc->issued) ? $doc->issued->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                            {!! fieldErrorMessage('issued', $errors) !!}
                                        </div>
                                    @endif

                                    {{-- Notes --}}
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
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
                                            <a href="{{ $doc->attachment_url }}" target="_blank" id="doc_link"><i class="fa fa-bold fa-3x fa-file-text-o" style="margin-top: 25px;"></i><br>VIEW</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/user/{{ $user->id }}/doc" class="btn default"> Back</a>
                                {{-- Achive - only 'live' docs status = 1 --}}
                                @if ($doc->status == 1 && Auth::user()->allowed2('del.user.doc', $doc))
                                    <a class="btn dark" data-toggle="modal" href="#modal_archive"> Archive </a>
                                @endif
                                {{-- Reject / Approve - only pending/rejected docs --}}
                                @if (in_array($doc->status, [2,3]) && Auth::user()->allowed2('sig.user.doc', $doc))
                                    @if ($doc->status == 2)
                                        <a class="btn dark" data-toggle="modal" href="#modal_reject"> Reject </a>
                                    @endif
                                    <button type="submit" name="save" value="save" class="btn green" id="approve">Approve</button>
                                @else
                                    {{-- Save / Upload - only 'current' docs status > 0 --}}
                                    @if ($doc->status != 0)
                                        <button type="submit" class="btn green" id="but_save">Save</button>
                                        <button type="submit" class="btn green" id="but_upload" style="display: none">Upload</button>
                                    @elseif (!$doc->status && Auth::user()->allowed2('del.user.doc', $doc))
                                        <a href="/user/{{ $doc->user_id }}/doc/archive/{{ $doc->id }}" class="btn red" id="but_save">Re-activate</a>
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
                        {!! Form::model($doc, ['method' => 'POST', 'action' => ['User\UserDocController@reject',$user->id, $doc->id], 'class' => 'horizontal-form', 'files' => true]) !!}
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
                        <a href="/user/{{ $user->id }}/doc/archive/{{ $doc->id }}" class="btn green">Continue</a>
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