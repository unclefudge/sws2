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
                            <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteScaffoldHandoverController::class, 'update'], $report->id) }}" class="horizontal-form" enctype="multipart/form-data" id="form_signed">
                                @csrf
                                @method('PATCH')
                            <x-form.hidden name="report_id" id="report_id" :value="$report->id"/>
                            <x-form.hidden name="site_id" id="site_id" :value="$report->site_id"/>
                            <x-form.hidden name="done_at" id="done_at" value="0"/>

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
                                    <x-form.textarea name="location" label="Description and location of area handed over" :value="$report->location" rows="3" placeholder="Specific details"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 ">
                                    <x-form.textarea name="use" label="Intended use of the scaffold" :value="$report->use" rows="3" placeholder="Specific details"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7">
                                    <x-form.select name="duty" label="Duty Classification" :options="$duty_class" :value="$report->duty" title="Select class"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.input name="decks" label="No. of working decks" :value="$report->decks"/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Documents of Scaffold</h5>
                                    <div style="background:#eee">
                                        <x-form.filepond/><br><br>
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <x-form.textarea name="notes" :value="$report->notes" rows="5" placeholder="Details"/>
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
                                    {{-- Attachments --}}
                                    @php
                                        $attachments = $report->attachments;
                                        $images = $attachments->where('type', 'image');
                                        $files  = $attachments->where('type', 'file');
                                    @endphp

                                    <h5><b>Attachments</b></h5>
                                    @if ($attachments->isNotEmpty())
                                        <hr style="margin: 10px 0px; padding: 0px;">
                                        {{-- Image attachments --}}
                                        @if ($images->isNotEmpty())
                                            <div class="row" style="margin: 0">
                                                @foreach ($images as $attachment)
                                                    <div style="width: 60px; float: left; padding-right: 5px">
                                                        <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                            <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- File attachments --}}
                                        @if ($files->isNotEmpty())
                                            <div class="row" style="margin: 0">
                                                @foreach ($files as $attachment)
                                                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a><br>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <div>None</div>
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
                                    <x-form.input name="inspector_name" label="Name of licensed scaffolder performing handover inspection" :value="$report->inspector_name"/>
                                </div>
                                <div class="col-md-4">
                                    <x-form.datetimepicker name="handover_date" label="Date & Time of Handover" :value="($report->handover_date) ? $report->handover_date->format('d F Y - H:i') : null"/>
                                </div>
                            </div>

                            {{-- SingleFile Upload --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('singlefile') ? 'has-error' : '' }}">
                                        <label class="control-label">Photo of High Risk Work Licence</label>
                                        <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        <x-form.error name="singlefile"/>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="pull-right" style="min-height: 50px">
                                <a href="/site/scaffold/handover" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('edit.site.scaffold.handover', $report ))
                                    <button type="submit" name="save" class="btn green"> Save</button>
                                    <button type="submit" name="save" class="btn red submitForm"> Sign Off</button>
                                @endif
                            </div>
                            <br><br>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
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
            $(".submitForm").click(function (e) {
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
                    $("#form_signed").submit();
                });

            });
        });
    </script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

