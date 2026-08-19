@inject('CompanyDocCategory', 'App\Models\Company\CompanyDocCategory')
@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/company/doc/standard/review">Standard Details Review</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase"> Edit Standard Details</span>
                            <span class="caption-helper"> ID: {{ $doc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanyDocReviewController::class, 'update'], $doc->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" id="stage" value="{{ $doc->stage }}">
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h4 style="margin-bottom: 0px">{{ $doc->name }}</h4>
                                    </div>
                                    <div class="col-md-2">
                                        @if(!$doc->status)
                                            <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Completed</h3>
                                        @endif
                                    </div>
                                </div>
                                <hr class="field-hr">

                                <div class="row">
                                    <div class="col-md-7">
                                        {{-- Stage --}}
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-3"><b>Stage:</b></div>
                                            <div class="col-md-9">{{ $doc->stage_text }}</div>
                                            <div class="col-md-3"><b>Assigned To:</b></div>
                                            <div class="col-md-9">{{ $doc->assignedToSBC() }}</div>
                                            @if ($doc->approved_con)
                                                <div class="col-md-12">Approved by Construction Manager ({{ $doc->approved_con->format('d/m/Y') }})</div>
                                            @endif
                                            @if ($doc->approved_adm)
                                                <div class="col-md-12">Approved by Drafting Manager ({{ $doc->approved_adm->format('d/m/Y') }})</div>
                                            @endif
                                        </div>
                                        <br>

                                        {{-- Review Process --}}
                                        <h4 class="font-green-haze">Review Process</h4>
                                        <hr class="field-hr">
                                        <div class="row">
                                            <div class="col-md-3"><b>Current version:</b></div>
                                            <div class="col-md-9">
                                                @if (!$doc->current_doc)
                                                    <a href="{{  $doc->original_doc_url }}" target="_blank"> Original Standard Details </a>
                                                @else
                                                    <a href="{{  $doc->current_doc_url }}" target="_blank"> {{ $doc->current_doc }} </a>
                                                @endif
                                            </div>
                                        </div>
                                        <br>
                                        @if ($doc->status)
                                            @if ($doc->stage == '1')
                                                {{-- Approval --}}
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <x-form.select name="approve_version" label="Do you approve the current version1" :options="['' => 'Select option', '0' => 'No', '1' => 'Yes']"/>
                                                        </div>
                                                    </div>
                                                    <div id="reviewdate_field" class="col-md-6" style="display: none">
                                                        <x-form.datepicker name="next_review_date" label="Next Review Date" format="dd/mm/yyyy" readonly/>
                                                    </div>
                                                </div>

                                                {{-- Assign Draftperson --}}
                                                <div id="assign_draft_field" style="display: none">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <x-form.select name="assign_user" label="Assign to user:" :options="Auth::user()->company->staffSelect('prompt')" plugin="select2"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <x-form.datepicker name="due_at" label="Task due date" format="dd/mm/yyyy" readonly/>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($doc->stage == '2')
                                                {{-- Draftsperson to review --}}
                                                <div class="row note note-warning">
                                                    <div class="col-md-12">Please review the <a href="{{ $doc->current_doc_url }}" target="_blank">current version</a> and make the requested changes.</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-5">
                                        <h4 class="font-green-haze">Files</h4>
                                        <hr class="field-hr">
                                        1. &nbsp; <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $doc->original_doc_url }}" target="_blank"> Original Standard Details</a><br>
                                        @if (count($doc->files))
                                                <?php $counter = 2; ?>
                                            @foreach($doc->files as $file)
                                                {{ $counter++ }}. &nbsp; <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $file->attachment_url }}" target="_blank"> {{ $file->attachment }}</a> <i>({{ $file->updatedBy->initials }})</i><br>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div id="file-upload">
                                    {{-- SingleFile Upload --}}
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="control-label">Uploaded a document with the required changes</label>
                                                <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <livewire:misc.actions table="company_docs_review" :table-id="$doc->id" :allow-add="(bool) $doc->status"/>

                                <div class="form-actions right">
                                    <a href="/company/doc/standard/review" class="btn default"> Back</a>
                                    @if ($doc->status == '1')
                                        <button id="renew_button" type="submit" name="renew" class="btn green" value="1"> Renew</button>
                                        <button id="save_button" type="submit" name="save" class="btn green" value="1"> Save</button>
                                    @endif
                                </div>
                            </div>
                        </form>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#assign_user").select2({placeholder: "Select user", width: '100%'});

            function display_fields() {
                $('#file-upload').hide();
                $('#approve_button').hide();
                $('#save_button').hide();
                $('#renew_button').hide();
                $('#reviewdate_field').hide();
                $('#assign_draft_field').hide();

                if ($("#stage").val() == '1') {
                    // Approved
                    if ($("#approve_version").val() == '1') {
                        $('#file-upload').hide();
                        $('#reviewdate_field').show();
                        $('#renew_button').show();
                    }

                    // Not Approved
                    if ($("#approve_version").val() == '0') {
                        $('#assign_draft_field').show();
                        $('#reviewdate_field').hide();
                        $('#save_button').show();
                    }
                }

                // Changes + new file requested
                if ($("#stage").val() == '2') {
                    $('#file-upload').show();

                    if ($("#singlefile").val() != '')
                        $('#save_button').show();
                }
            }


            display_fields();

            $("#approve_version").change(function () {
                display_fields();
            });

            $("#assign_user").change(function () {
                display_fields();
            });

            $("#singlefile").change(function () {
                display_fields();
            });


            /* Bootstrap Fileinput */
            $("#singlefile").fileinput({
                showUpload: false,
                allowedFileExtensions: ["pdf", "jpg", "jpeg", "png", "gif"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn btn-danger",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn btn-info",
            });
        });

    </script>
@stop