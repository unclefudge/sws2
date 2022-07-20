@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
            <li><a href="/site/scaffold/handover">Scaffold Handover Certificate</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>View</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Scaffold Handover Certificate</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                        <div class="actions">
                            @if($report->status == '0')
                                <a data-original-title="Email" data-toggle="modal" href="#modal_email" class="btn btn-circle green btn-outline btn-sm "><i class="fa fa-envelope-o"></i> Email</a>
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/scaffold/handover/{{ $report->id }}/report" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6"><h3 style="margin: 0px">{{ $report->site->name }}</h3></div>
                                <div class="col-md-6">
                                    <h2 style="margin: 0px; padding-right: 20px">
                                        @if($report->status == '0')
                                            <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">HANDOVER DATE {{ $report->handover_date->format('d/m/Y g:i a') }}</small></span>
                                            <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                        @else
                                            <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                            <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                        @endif
                                    </h2>
                                </div>
                            </div>
                            {{-- Site Details--}}
                            <h4 class="font-green-haze">Scaffold Details</h4>
                            <hr class="field-hr">
                            <div class="row">
                                <div class="col-md-2"><b>Site:</b></div>
                                <div class="col-md-10">{{ $report->site->name }}</div>
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
                            <br>

                            {{-- Photos --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="font-green-haze">Photos</h4>
                                    <hr class="field-hr">
                                    @if ($report->docs->count())
                                        <?php $doc_count = 0; ?>
                                        <div style="width: 100%; overflow: hidden;">
                                            @foreach ($report->docs as $doc)
                                                @if ($doc->type == 'photo')
                                                    <div style="width: 60px; float: left; padding-right: 5px">
                                                        <a href="{{ $doc->AttachmentUrl }}" target="_blank" class="html5lightbox " title="{{ $doc->name }}" data-lityXXX>
                                                            <img src="{{ $doc->AttachmentUrl }}" class="thumbnail img-responsive img-thumbnail"></a>
                                                    </div>
                                                    <?php $doc_count ++; ?>
                                                    @if ($doc_count == 10)
                                                        <br>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div>No photos found<br><br></div>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Signed by (Inspector):</b></div>
                                        <div class="col-md-8">{{ $report->inspector_name }}</div>
                                    </div>
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Date:</b></div>
                                        <div class="col-md-8">{{ ($report->signed_at) ? $report->signed_at->format('d/m/Y') : '' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row" style="padding: 5px;">
                                        <div class="col-md-4"><b>Inspector licence:</b></div>
                                        <div class="col-md-8">
                                            @if ($report->inspector_licence)
                                                <a href="{{ $report->inspector_licence_url }}" target="_blank" class="html5lightbox " title="{{ $report->name }}" data-lityXXX>
                                                    <img src="{{ $report->inspector_licence_url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>

                        </div>

                        {!! Form::close() !!}

                        <div class="form-actions right">
                            <a href="/site/scaffold/handover" class="btn default"> Back</a>
                            {{--
                           @if(Auth::user()->allowed2('del.site.scaffold.handover', $report))
                               @if ($report->status)
                                   @if(Auth::user()->allowed2('edit.site.scaffold.handover', $asb))
                                       <a href="/site/asbestos/notification/{{ $report->id }}/edit" class="btn green"> Edit Notification</a>
                                   @endif
                                   <a href="/site/asbestos/notification/{{ $report->id }}/status/0" class="btn red"> Close Notification</a>
                               @else
                                   <a href="/site/asbestos/notification/{{ $report->id }}/status/1" class="btn green"> Re-open Notification</a>
                               @endif
                            @endif
                             --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div id="modal_email" class="modal fade bs-modal" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::model($report, ['method' => 'POST', 'action' => ['Site\SiteScaffoldHandoverController@emailPDF', $report->id], 'class' => 'horizontal-form', 'files' => true, 'id' => 'form_signed']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Email Scaffold Handover Certificate</b></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {!! fieldHasError('decks', $errors) !!}">
                                {!! Form::label('email', 'Email certificate to the below email address(s)', ['class' => 'control-label']) !!}
                                {!! Form::text('email', null, ['class' => 'form-control', 'required']) !!}
                                {!! fieldErrorMessage('email', $errors) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn green">Send</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

