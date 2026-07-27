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
                        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyDocController::class, 'update'], ['cid' => $company->id, 'doc' => $doc->id]) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
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
                                        <x-form.hidden name="category_id" :value="$doc->category_id"/>
                                        @if ($doc->category_id > 8)
                                            <x-form.input name="category_id_text" label="Category" :value="\App\Models\Company\CompanyDocCategory::find($doc->category_id)->name" disabled/>
                                        @endif

                                        {{-- Name --}}
                                        <x-form.input name="name" label="Name" :value="$doc->name" :readonly="$doc->category_id < 9"/>
                                        @if (in_array($doc->category_id, [1, 2, 3]))
                                            {{-- Policy --}}
                                            <x-form.input name="ref_no" label="Policy No" :value="$doc->ref_no" readonly/>
                                            {{-- Insurer --}}
                                            <x-form.input name="ref_name" label="Insurer" :value="$doc->ref_name" readonly/>
                                            @if (in_array($doc->category_id, [2, 3]))
                                                {{-- Category --}}
                                                <x-form.select name="ref_type" label="Category" :options="$doc->company->workersCompCategorySelect('prompt')" :value="$doc->ref_type" readonly/>
                                            @endif
                                        @endif
                                        {{-- Lic No + Lic Class--}}
                                        @if ($doc->category_id == 7)
                                            <x-form.input name="lic_no" label="Licence No." :value="$doc->ref_no" readonly/>
                                            <x-form.input name="lic_type" label="Class(s)" :value="$doc->company->contractorLicenceSBC()" readonly/>

                                            {{-- Supervisor of CL --}}
                                            <div id="fields_supervisor_no">
                                                <x-form.input name="supervisor_no" label="How many Supervisors are required to cover the above class(s)" :value="$doc->ref_name" readonly/>
                                            </div>
                                            @if ($doc->ref_name == 1)
                                                <div id="fields_supervisor_id">
                                                    <x-form.input name="supervisor_id" label="Supervisor of all class(s) on licence" :value="$doc->contractorLicenceSupervisor(1)" readonly/>
                                                </div>
                                            @endif
                                            @if ($doc->ref_name > 1)
                                                {{-- Supervisor 1 --}}
                                                <x-form.input name="supervisor_id1" label="Supervisor 1" :value="(\App\User::find($doc->contractorLicenceSupervisor(1))) ? \App\User::find($doc->contractorLicenceSupervisor(1))->name : ''" readonly/>
                                                <x-form.input name="lic_type1" label="Supervisor 1 is ONLY responsible for class(s)" :value="$doc->contractorLicenceSupervisorClassesSBC(1)" readonly/>

                                                {{-- Supervisor 2 --}}
                                                <x-form.input name="supervisor_id2" label="Supervisor 2" :value="(\App\User::find($doc->contractorLicenceSupervisor(2))) ? \App\User::find($doc->contractorLicenceSupervisor(2))->name : ''" readonly/>
                                                <x-form.input name="lic_type2" label="Supervisor 2 is ONLY responsible for class(s)" :value="$doc->contractorLicenceSupervisorClassesSBC(2)" readonly/>
                                            @endif
                                            {{-- Supervisor 3 --}}
                                            <div style="display: none" id="fields_supervisor_id3">
                                                <x-form.input name="supervisor_id3" label="Supervisor 3" :value="(\App\User::find($doc->contractorLicenceSupervisor(3))) ? \App\User::find($doc->contractorLicenceSupervisor(3))->name : ''" readonly/>
                                                <x-form.input name="lic_type3" label="Supervisor 3 is ONLY responsible for class(s)" :value="$doc->contractorLicenceSupervisorClassesSBC(3)" readonly/>
                                            </div>
                                        @endif
                                        {{-- Asbestos Class --}}
                                        <div style="display: none" id="fields_asb_class">
                                            <x-form.select name="asb_type" label="Class(s)" :options="['' => 'Select class', 'A' => 'Class A', 'B' => 'Class B']" :value="$doc->asb_type" readonly/>
                                        </div>

                                        @if ($doc->category_id == 6)
                                            {{-- Test Expire Type --}}
                                            @if ($company->id == 3)
                                                <div id="fields_tag_type">
                                                    <x-form.select name="tag_type" label="Expiry" :options="['3' => '3 month (site)', '12' => '12 month (office)']" :value="$doc->ref_type" readonly/>
                                                </div>
                                            @else
                                                <x-form.hidden name="tag_type" value="3"/>
                                            @endif

                                            {{-- Test date --}}
                                            <div id="fields_tag_date">
                                                <x-form.input name="tag_date" label="Date of Testing" :value="$doc->expiry->subMonths($doc->ref_type)->format('d/m/Y')" readonly disabled/>
                                                @if ($company->id != 3)
                                                    <span class="help-block">Expires 3 months from date of testing</span>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Expiry --}}
                                            <x-form.input name="expiry" label="Expiry" :value="($doc->expiry) ? $doc->expiry->format('d/m/Y') : ''" readonly/>
                                        @endif
                                        {{-- Notes --}}
                                        <x-form.textarea name="notes" label="Notes" :value="$doc->notes" rows="3" readonly/>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Attachment --}}
                                        <div class="form-group" id="attachment-div">
                                            <div class="col-md-9">
                                                <x-form.input name="filename" label="Filename" :value="$doc->attachment" readonly/>
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
                        </form>
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
                        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyDocController::class, 'reject'], ['cid' => $company->id, 'id' => $doc->id]) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            <x-form.textarea name="reject" label="Reason for rejecting document" :value="$doc->reject" rows="3"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn green" name="reject_doc" value="reject">Reject</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Archive Modal --}}
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