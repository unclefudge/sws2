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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Company\CompanyDocReviewController::class, 'update'], $doc->id) }}" class="horizontal-form" enctype="multipart/form-data" id="doc_form">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        @if ($doc->status == 3)
                                            <h2 style="margin: 0 0"><span class="label label-warning">Pending Approval</span></h2><br><br>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        @if(!$doc->status)
                                            <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Inactive</h3>
                                        @endif
                                    </div>
                                </div>

                                <h3>{{ $doc->name }}</h3>
                                <hr class="field-hr">

                                <div class="row">
                                    <div class="col-md-8">
                                        {{-- Stage --}}
                                        <h4 class="font-green-haze">Status</h4>
                                        <hr class="field-hr">
                                        <div class="row" style="line-height: 2">
                                            <div class="col-md-3"><b>Stage:</b></div>
                                            <div class="col-md-9">{{ $doc->stage }}</div>
                                            <div class="col-md-3"><b>Assigned To:</b></div>
                                            <div class="col-md-9">{{ $doc->assignedToSBC() }}</div>
                                        </div>
                                        <br>

                                        {{-- Review Process --}}
                                        <h4 class="font-green-haze">Review Process</h4>
                                        <hr class="field-hr">
                                        <div class="row">
                                            <div class="col-md-3"><b>Current version:</b></div>
                                            <div class="col-md-9">
                                                <a href="{{  $doc->current_doc_url }}" target="_blank"> {{ ($doc->current_doc) ? $doc->current_doc : $doc->original_doc }} </a>
                                                @if (!$doc->current_doc)
                                                    <span class="font-red"> &nbsp; (Original Standard)</span>
                                                @endif
                                            </div>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <x-form.select name="approve_version" label="Do you approval the current version" :options="['' => 'Select option', '0' => 'No', '1' => 'Yes']"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h4 class="font-green-haze">Files</h4>
                                        <hr class="field-hr">
                                        <i class="fa fa-file-pdf-o"></i> &nbsp; <a href="{{ $doc->original_doc_url }}" target="_blank"> Original Standard </a>
                                    </div>
                                </div>

                                <div id="file-upload">
                                    {{-- SingleFile Upload --}}
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="control-label">Please uploaded a document with the required changes</label>
                                                <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <livewire:misc.actions table="company_docs_review" :table-id="$doc->id"/>

                                <div class="form-actions right">
                                    <a href="/company/doc/standard/review" class="btn default"> Back</a>
                                    <button id="approve_button" type="submit" name="approve" class="btn green"> Approve</button>
                                    <button id="save_button" type="submit" name="save" class="btn green"> Save</button>
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
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            function display_fields() {
                $('#file-upload').hide();
                $('#approve_button').hide();
                $('#save_button').hide();

                // Approved
                if ($('#approve_version').val() == '1') {
                    $('#approve_button').show();
                }

                // Not Approved
                if ($('#approve_version').val() == '0') {
                    $('#file-upload').show();

                    if ($('#singlefile').val() != '')
                        $('#save_button').show();
                }
            }

            display_fields();

            $('#approve_version').change(function () {
                display_fields();
            });

            $('#singlefile').change(function () {
                display_fields();
            });

            $('#singlefile').fileinput({
                showUpload: false,
                allowedFileExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
                browseClass: 'btn blue',
                browseLabel: 'Browse',
                browseIcon: '<i class="fa fa-folder-open"></i> ',
                removeLabel: '',
                removeIcon: '<i class="fa fa-trash"></i> ',
                uploadClass: 'btn btn-info',
            });
        });
    </script>
@stop
