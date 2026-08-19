@extends('layout')
@inject('failureTypes', 'App\Http\Utilities\FailureTypes')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/hazard">Hazard Register</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Hazard</span>
                            <span class="caption-helper"> ID: {{ $hazard->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteHazardController::class, 'update'], $hazard->id) }}">
                            @csrf
                            @method('PATCH')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div id="sitename-show">
                                            <h2 style="margin-top: 0px">{{ $hazard->site->name }}
                                                @if ($hazard->status && Auth::user()->hasAnyRole2('mgt-general-manager|web-admin'))
                                                    <i id="edit-site" class="fa fa-pencil" style="margin-left: 20px; cursor: pointer"></i>
                                                @endif
                                            </h2>
                                            {{ $hazard->site->fulladdress }}
                                        </div>
                                        <div id="sitename-edit" style="display:none">
                                            <b>Re-assign to site</b><br>
                                            <x-form.select name="site_id" plugin="select2" style="width:100%">
                                                {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id', $hazard->site_id)) !!}
                                            </x-form.select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        @if ($hazard->status == 0)
                                            <h2 class="font-red pull-right" style="margin-top: 0px">CLOSED</h2>
                                        @elseif ($hazard->status == '9')
                                            <h2 class="font-red pull-right" style="margin-top: 0px">RESOLVED</h2>
                                        @endif
                                        <b>Job #:</b> {{ $hazard->site->code }}<br>
                                        <b>Supervisor:</b> {{ $hazard->site->supervisorName }}<br>
                                    </div>
                                </div>

                                <hr>
                                <div class="row" style="line-height: 1.5em">
                                    <div class="col-md-8">
                                        <h4 class="font-green-haze">Hazard Details</h4>
                                        <b>Date Raised: </b>{!! $hazard->created_at->format('d/m/Y') !!}<br><br>
                                        @if ($hazard->status && Auth::user()->allowed2('del.site.hazard', $hazard))
                                            <div class="row" style="padding-left: 15px">
                                                <div class="col-md-3" style="padding-left: 0px">
                                                    <b>Risk Rating</b><br>
                                                    <x-form.select name="rating" :options="['' => 'Select rating', '1' => 'Low', '2' => 'Medium', '3' => 'High', '4' => 'Extreme']" :value="$hazard->rating ?? ''"/>
                                                </div>
                                                <br>
                                            </div>
                                        @else
                                            <b>Risk Rating: </b>{!! $hazard->ratingTextColoured !!}<br><br>
                                        @endif
                                        <b>Location of Hazard:</b><br>{{ $hazard->location }}<br><br>
                                        <b>What is the hazard / safety issue:</b><br>{{ $hazard->reason }}<br><br>
                                        @if (!$hazard->status || !Auth::user()->allowed2('del.site.hazard', $hazard))
                                            <b>Failure Type:</b> {{ $hazard->failure_type }}<br><br>
                                            <b>Source:</b><br>{{ $hazard->source }}<br><br>
                                        @else
                                            {{-- Edit - Status Open + allowed to del.site.hazard --}}
                                            <div class="col-md-6" style="padding-left: 0px">
                                                <b>Failure Type</b><br>
                                                <x-form.select name="failure" :options="$failureTypes::all()" :value="$hazard->failure ?? ''"/>
                                            </div>
                                            <div class="col-md-3" style="padding-left: 0px">
                                                <b>Status</b><br>
                                                <x-form.select name="status" :options="['1' => 'Open', '9' => 'Resolved', '0' => 'Closed']" :value="$hazard->status ?? ''"/>
                                            </div>
                                            <div class="col-md-9" style="padding-left: 0px">
                                                <b>Identification Source"</b><br>
                                                <x-form.select name="source"
                                                               :options="['' => 'Select source', 'WHS Inspection' => 'WHS Inspection', 'Worker Identification' => 'Worker Identification', 'Supervisor' => 'Supervisor', 'Client Report' => 'Client Report', 'Regulator' => 'Regulator', 'Council' => 'Council', 'Public' => 'Public']"
                                                               :value="$hazard->source ?? ''"/>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        {{-- Attachments --}}
                                        @php
                                            $attachments = $hazard->attachments;
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

                                        @if ($hazard->status)
                                            <div>
                                                <br>
                                                <x-form.filepond/>
                                                <br><br>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <livewire:misc.actions table="site_hazards" :table-id="$hazard->id" :allow-add="(int) $hazard->status === 1"/>
                                </div>
                            </div>

                            {{-- Assigned Tasks --}}
                            <livewire:misc.assigned-tasks context="hazard" :context-id="$hazard->id"/>

                            <div class="form-actions right">
                                <a href="/site/hazard" class="btn default"> Back</a>
                                {{-- Status Open - allow save --}}
                                @if ($hazard->status)
                                    <button type="submit" class="btn green" id="submit">Save</button>
                                @endif
                                @if(!$hazard->status && Auth::user()->allowed2('del.site.hazard', $hazard))
                                    <a href="/site/hazard/{{ $hazard->id }}/status/1" class="btn green"> Re-open Hazard</a>
                                @endif

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    {{--}}<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>--}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            $("#edit-site").click(function () {
                $("#sitename-show").hide();
                $("#sitename-edit").show();
            });

            // On Change Site ID
            $("#site_id").change(function () {
                var site_id = $("#site_id").select2("val");
                if (site_id != '') {
                    $.ajax({
                        url: '/site/data/details/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $("#address").val(data.address + ', ' + data.suburb);
                            $("#code").val(data.code);
                        },
                    })
                }
            });
        });
    </script>
@stop

