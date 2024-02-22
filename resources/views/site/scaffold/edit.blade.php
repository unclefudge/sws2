@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
            <li><a href="/site/scaffold/handover">Scaffold Handover Certificate</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Certificate</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }
</style>
<?php
$duty_class = [
    '' => 'Select type',
    'Light' => 'Light Duty - up to 225 kg per platform per bay including a concentrated load of 120 kg',
    'Medium' => 'Medium Duty – up to 450 kg per platform per bay including a concentrated load of 150 kg',
    'Heavy' => 'Heavy Duty – up to 675 kg per platform per bay including a concentrated load of 200 kg',
    'Special' => 'Special Duty – designated allowable load as designed'];
?>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Scaffold Handover Certificate</span>
                            <span class="caption-helper">ID: {{ $report->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($report, ['method' => 'PATCH', 'action' => ['Site\SiteScaffoldHandoverController@update', $report->id], 'class' => 'horizontal-form', 'files' => true, 'id' => 'form_signed']) !!}
                            <input type="hidden" name="report_id" id="report_id" value="{{ $report->id }}">
                            <input type="hidden" name="site_id" id="site_id" value="{{ $report->site_id }}">

                            @include('form-error')

                            <h4 class="font-green-haze">Site details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    {{ $report->site->name }}
                                </div>
                            </div>

                            <h4 class="font-green-haze">Scaffold details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Description and location of area handed over', ['class' => 'control-label']) !!}
                                        {!! Form::textarea("location", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details"]) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('use', $errors) !!}">
                                        {!! Form::label('use', 'Intended use of the scaffold', ['class' => 'control-label']) !!}
                                        {!! Form::textarea("use", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details"]) !!}
                                        {!! fieldErrorMessage('use', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('duty', $errors) !!}">
                                        {!! Form::label('duty', 'Duty Classification', ['class' => 'control-label']) !!}
                                        {!! Form::select('duty', $duty_class, null, ['class' => 'form-control bs-select', 'name' => 'duty', 'title' => 'Select class']) !!}
                                        {!! fieldErrorMessage('duty', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('decks', $errors) !!}">
                                        {!! Form::label('decks', 'No. of working decks', ['class' => 'control-label']) !!}
                                        {!! Form::text('decks', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('decks', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Documents of Scaffold</h5>
                                    <div style="background:#eee">
                                        <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::textarea("info", null, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details"]) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Scaffold Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-2"><b>Site:</b></div>
                                <div class="col-md-10">{{ $report->site->code }}-{{ $report->site->name }}</div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2"><b>Description/Location:</b></div>
                                <div class="col-md-10">{!! nl2br($report->location) !!}</div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2"><b>Intended use:</b></div>
                                <div class="col-md-10">{!! nl2br($report->use) !!}</div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2"><b>Duty Classification:</b></div>
                                <div class="col-md-10">{{ $report->duty }}</div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2"><b>No. of working decks:</b></div>
                                <div class="col-md-10">{{ $report->decks }}</div>
                            </div>
                            <hr class="field-hr">
                            <br>

                            {{-- Attachments --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="font-green-haze">Photos / Documents</h4>
                                    <hr class="field-hr">
                                    @if ($report->docs->count())
                                        {{-- Image attachments --}}
                                        <div class="row" style="margin: 0">
                                            @foreach ($report->docs as $file)
                                                @if ($file->type == 'image' && file_exists(substr($file->AttachmentUrl, 1)))
                                                    <div style="width: 60px; float: left; padding-right: 5px">
                                                        <a href="{{ $file->AttachmentUrl }}" target="_blank" class="html5lightbox " title="{{ $file->attachment }}" data-lity>
                                                            <img src="{{ $file->AttachmentUrl }}" class="thumbnail img-responsive img-thumbnail"></a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        {{-- File attachments  --}}
                                        <div class="row" style="margin: 0">
                                            @foreach ($report->docs as $file)
                                                @if ($file->type == 'file' && file_exists(substr($file->AttachmentUrl, 1)))
                                                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $file->AttachmentUrl }}" target="_blank"> {{ $file->name }}</a><br>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div>No photos/documents found<br><br></div>
                                    @endif
                                </div>
                            </div>

                            {{-- Sign Off --}}
                            <h4 class="font-green-haze">Handover Inspection of Scaffold</h4>
                            <hr class="field-hr">
                            <div class="row">
                                <div class="col-md-12">
                                    This scaffold detailed above has been erected in accordance with the attached drawings, the WHS Regulations and the General Guide for scaffolds and scaffolding work; is informed by relevant technical standards and is suitable for its intended purpose.<br><br>
                                </div>
                            </div>

                            {{-- Name + Date --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('inspector_name', $errors) !!}">
                                        {!! Form::label('inspector_name', 'Name of licensed scaffolder performing handover inspection', ['class' => 'control-label']) !!}
                                        {!! Form::text('inspector_name', null, ['class' => 'form-control', 'required']) !!}
                                        {!! fieldErrorMessage('inspector_name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('handover_date', $errors) !!}">
                                        {!! Form::label('handover_date', 'Date & Time of Handover', ['class' => 'control-label']) !!}
                                        <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d"> <!-- bs-datetime -->
                                            {!! Form::text('handover_date', null, ['class' => 'form-control', 'readonly', 'style' => 'background:#FFF']) !!}
                                            <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        {!! fieldErrorMessage('handover_date', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- SingleFile Upload --}}
                            {{--}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                        <label class="control-label">Photo of High Risk Work Licence</label>
                                        <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        {!! fieldErrorMessage('singlefile', $errors) !!}
                                    </div>
                                </div>
                            </div>--}}

                            <hr>
                            <div class="pull-right" style="min-height: 50px">
                                <a href="/site/scaffold/handover" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('add.site.scaffold.handover'))
                                    <button id="signoff_button" type="submit" name="save" class="btn green"> Submit</button>
                                @endif
                            </div>
                            <br><br>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    {{--}}<script src="/js/libs/fileinput.min.js"></script>--}}
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        // Get a reference to the file input element
        const inputElement = document.querySelector('input[type="file"]');

        // Create a FilePond instance
        const pond = FilePond.create(inputElement);
        FilePond.setOptions({
            server: {
                url: '/file/upload',
                fetch: null,
                revert: null,
                headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
            },
            allowMultiple: true,
        });

        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});


        $(document).ready(function () {
            /* Bootstrap Fileinput */
            $("#singlefile").fileinput({
                showUpload: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                browseClass: "btn blue",
                browseLabel: "Browse",
                browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
                //removeClass: "btn btn-danger",
                removeLabel: "",
                removeIcon: "<i class=\"fa fa-trash\"></i> ",
                uploadClass: "btn btn-info",
            });

            // On Click Review Sign Off
            $("#signoff_button").click(function (e) {
                e.preventDefault();
                swal({
                    title: "Confirm Sign Off",
                    text: "I have reviewed and sign off on this Scaffold Handover Certificate.<br>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Sign Off",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    $("#done_at").val(1);
                    $('#form_signed').submit();
                });

            });

        });
    </script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

