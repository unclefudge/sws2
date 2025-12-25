@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/wms">SWMS</a><i class="fa fa-circle"></i></li>
        <li><span>View Statement</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        @if ($doc->status == 3 && !$doc->user_signed_id)
            <div class="note note-warning">
                This SWMS is currently in Pending Status waiting for you to Sign Off on it.
                <ul>
                    <li>View the document by clicking on the <i class="fa fa-file-pdf-o" style="font-size: 18px"></i> icon.</li>
                    <li>Sign off on the document by clicking
                        <button class="btn btn-circle dark btn-outline btn-xs" href="javascript:;"><i class="fa fa-cog"></i> Actions</button>
                        and selecting 'Sign Off'
                    </li>
                </ul>
            </div>
        @endif
        {{-- Progress Steps --}}
        @if ($doc->status == 3)
            <br>
            <div class="mt-element-step">
                <div class="row step-line" id="steps">
                    <div class="col-md-3 mt-step-col first done">
                        <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                        <div class="mt-step-content font-grey-cascade">Create SWMS</div>
                    </div>
                    <div class="col-md-3 mt-step-col done">
                        <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                        <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                        <div class="mt-step-content font-grey-cascade">Add content</div>
                    </div>
                    <div class="col-md-3 mt-step-col active">
                        <div class="mt-step-number bg-white font-grey">3</div>
                        <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                        <div class="mt-step-content font-grey-cascade">Request Sign Off</div>
                    </div>
                    <div class="col-md-3 mt-step-col last">
                        <div class="mt-step-number bg-white font-grey">4</div>
                        <div class="mt-step-title uppercase font-grey-cascade">Approved</div>
                        <div class="mt-step-content font-grey-cascade">SWMS accepted</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Safe Work Method Statement</span>
                        </div>
                        <div class="actions">
                            @if (($doc->status == 3 && Auth::user()->allowed2('del.wms', $doc)) || $doc->status != 3)
                                <div class="btn-group">
                                    <a class="btn btn-circle green btn-outline btn-sm" href="javascript:;" data-toggle="dropdown" data-over="dropdown"><i class="fa fa-cog"></i> Actions</a>
                                    <ul class="dropdown-menu pull-right">
                                        <!-- Doc Pending -->
                                        @if ($doc->status == 3 && (Auth::user()->allowed2('del.wms', $doc) || Auth::user()->allowed2('sig.wms', $doc)))
                                            @if ($doc->company_id == Auth::user()->company_id)
                                                @if (Auth::user()->allowed2('sig.wms', $doc))
                                                    )
                                                    <li><a href="/safety/doc/wms/{{ $doc->id }}/signoff"><i class="fa fa-check"></i> Sign Off</a></li>
                                                    <li><a href="/safety/doc/wms/{{ $doc->id }}/reject"><i class="fa fa-ban"></i> Reject</a></li>
                                                    <li class="divider"></li>
                                                @endif
                                                <li><a href="/safety/doc/wms/{{ $doc->id }}/destroy"><i class="fa fa-trash"></i> Delete</a></li>
                                            @endif
                                            @if($doc->for_company_id == Auth::user()->company_id)
                                                @if($doc->attachment)
                                                    <li><a href="/safety/doc/wms/{{ $doc->id }}/signoff"><i class="fa fa-check"></i> Sign Off</a></li>
                                                @else
                                                    <li><a href="/safety/doc/wms/{{ $doc->id }}/reject"><i class="fa fa-ban"></i> Cancel Sign Off Request</a></li>
                                                    <li class="divider"></li>
                                                    <li><a href="/safety/doc/wms/{{ $doc->id }}/destroy"><i class="fa fa-trash"></i> Delete</a></li>
                                                @endif
                                            @endif
                                        @endif


                                        @if ($doc->status != 3)
                                            @if ($doc->attachment)
                                                <li><a data-original-title="Email" data-toggle="modal" href="#email"><i class="fa fa-envelope"></i> Email</a></li>
                                            @elseif($doc->builder && !$doc->master)
                                                <li><a href="/safety/doc/wms/{{ $doc->id }}/pdf"><i class="fa fa-file-text-o"></i> Generate PDF</a></li>
                                            @endif

                                            @if($doc->status == 1 && Auth::user()->allowed2('del.wms', $doc))
                                                <li class="divider"></li>
                                                <li><a data-original-title="Archive" data-toggle="modal" href="#archive"><i class="fa fa-archive"></i> Archive</a></li>
                                            @elseif($doc->status == 0 && Auth::user()->isCompany($doc->owned_by) && Auth::user()->allowed2('del.wms', $doc))
                                                <li class="divider"></li>
                                                <li><a href="/safety/doc/wms/{{ $doc->id }}/archive"><i class="fa fa-archive"></i> Unarchive</a></li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <!-- Fullscreen devices -->
                            <div class="row hidden-sm hidden-xs" style="border-bottom: 1px solid #ccc">
                                <div class="col-xs-12">
                                    <h1 style="margin: 0 0 25px 0"><b>@if($doc->company && !$doc->master)
                                                {{ $doc->company->name }}
                                            @else
                                                {{ Auth::user()->company->name }}
                                            @endif</b>
                                        @if($doc->attachment)
                                            <a href="{{ $doc->attachmentUrl }}"><i class="fa fa-bold fa-file-pdf-o pull-right" style="font-size: 1.4em; padding: 20px"></i></a>
                                        @endif
                                        @if($doc->status == '0')
                                            <span class="uppercase font-red pull-right" style="padding-left: 30px">Archived</span>
                                        @endif
                                        @if($doc->master)
                                            <span class="uppercase font-red pull-right" style="padding-left: 30px">Template </span>
                                        @endif
                                    </h1>
                                </div>
                                <div>
                                    <div class="col-xs-7"><h3 style="margin: 0 0 2px 0">Safe Work Method Statement</h3></div>
                                    <div class="col-xs-5 text-right" style="margin-top: 5px; padding-right: 20px"><span class="font-grey-salsa">version {{ $doc->version }} </span></div>
                                </div>
                            </div>
                            <div class="row hidden-sm hidden-xs" style="padding-top: 10px">
                                <div class="col-md-7 "><span class="pull-left" style="padding: 1px 20px 0px 10px">Activity / Task:</span>
                                    <h4 style="margin: 0px"><b>{{ $doc->name }}</b></h4>
                                </div>
                                <div class="col-xs-5 text-right" style="margin-top: 5px; padding-right: 20px"> @if ($doc->project)
                                        <b>Project / Location:</b> {{ $doc->project }}
                                    @endif</div>
                            </div>

                            <!-- Mobile devices -->
                            <div class="row visible-sm visible-xs">
                                @if($doc->status == '0')
                                    <div class="col-xs-12"><h3 class="font-red uppercase text-center" style="margin-top: 0">Archived</h3></div>
                                @endif

                                <div class="col-xs-12 text-center">
                                    <h3 style="margin: 0 0 25px 0"><b>@if($doc->company)
                                                {{ $doc->company->name }}
                                            @else
                                                Company
                                            @endif</b></h3>
                                </div>
                            </div>
                            <div class="row visible-sm visible-xs">
                                <div class="col-xs-12 text-center">
                                    <h4 style="margin: 0 0 25px 0">
                                        @if($doc->attachment)
                                            <a href="{{ $doc->attachmentUrl }}" class="text-center"><i class="fa fa-bold fa-file-pdf-o" style="font-size: 1.4em; padding: 20px"></i></a>
                                        @endif
                                        <b>{{ $doc->name }}</b>
                                        <small class="font-grey-salsa">v{{ $doc->version }}</small>
                                    </h4>
                                </div>
                                @if ($doc->project)
                                    <div class="col-xs-6 text-right">Project / Location:</div>
                                    <div class="col-xs-6">{{ $doc->project }}</div>
                                @endif
                            </div>


                            <!-- Sign By -->
                            @if (!$doc->master)
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading" style="padding: 1px 15px; background: #f0f6fa">
                                                <h6><b>Company:</b> <span style="font-size: 15px"> &nbsp; &nbsp; {{ $doc->company->name }}</span></h6>
                                            </div>
                                            <div class="panel-body" style="padding: 10px 15px">
                                                <div class="row">
                                                    <div class="col-xs-8">Signed by: @if ($doc->signedCompany)
                                                            {{ $doc->signedCompany->fullname }}
                                                        @else
                                                            <span class="font-red">Pending</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-xs-4">@if ($doc->signedCompany)
                                                            Date: {{ $doc->user_signed_at->format('d/m/Y') }}
                                                        @endif</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading" style="padding: 1px 15px; background: #f0f6fa ">
                                                <h6><b>Principal Contractor:</b> <span style="font-size: 15px"> &nbsp; &nbsp; {{ $doc->principleName }}</span></h6>
                                            </div>
                                            <div class="panel-body" style="padding: 10px 15px">
                                                <div class="row">
                                                    <div class="col-xs-8">
                                                        Accepted by:
                                                        @if ($doc->signedPrinciple)
                                                            {{ $doc->signedPrinciple->fullname }}
                                                        @elseif ($doc->principle_id)
                                                            <span class="font-red">Pending</span>
                                                        @else
                                                            <span class="font-red">Manual signature required</span>
                                                        @endif</div>
                                                    <div class="col-xs-4">@if ($doc->signedPrinciple)
                                                            Date: {{ $doc->principle_signed_at->format('d/m/Y') }}
                                                        @endif</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                            {{-- Doc Builder - Steps / Hazard / Controls --}}
                            @if ($doc->builder)
                                <div class="row" style="margin-top: 20px">
                                    <div class="col-md-12">
                                        <div class="row hidden-sm hidden-xs"
                                             style="border: 1px solid #e7ecf1; padding: 10px 0px; margin: 0px; background: #f0f6fa; font-weight: bold">
                                            <div class="col-md-2">Step</div>
                                            <div class="col-md-2">Potential Hazard</div>
                                            <div class="col-md-8">Controls / Responsible Person(s)</div>
                                        </div>
                                        <div class="visible-sm visible-xs" style="border: 1px solid #e7ecf1; padding: 10px 0px; background: #f0f6fa; font-weight: bold">
                                            <div class="col-md-12">Steps / Hazards / Controls</div>
                                        </div>
                                        <!-- Steps -->
                                        @foreach ($doc->steps->sortBy('order') as $step)
                                            <div class="row row-striped" style="border-bottom: 1px solid lightgrey; padding: 0px; margin: 0px;">
                                                <div class="col-md-2">
                                                    <div class="row">
                                                        <div class="col-xs-2 hidden-sm hidden-xs">{{ $step->order }}.</div>
                                                        <div class="col-xs-10 hidden-sm hidden-xs">{{ $step->name }}</div>
                                                        <div class="col-xs-12 visible-sm visible-xs font-white text-center" style="background: #659be0; padding:5px"><b>Step {{ $step->order }}.</b>
                                                            &nbsp; {{ $step->name }}</div>
                                                    </div>
                                                </div>
                                                <!-- Hazards -->
                                                <div class="col-md-2">
                                                    <span class="visible-sm visible-xs"><b>Hazards:</b><br></span>
                                                    <ul style="margin-left: -15px">
                                                        @foreach ($step->hazards as $hazard)
                                                            <li style="margin-bottom: 3px">{{ $hazard->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <!-- Controls -->
                                                <div class="col-md-8">
                                                    <span class="visible-sm visible-xs"><b>Controls:</b><br></span>
                                                    <ul style="margin-left: -15px">
                                                        @foreach ($step->controls as $control)
                                                            <li style="margin-bottom: 3px">
                                                                <div>{{ $control->name }} <span class="font-blue"><b>By: {!! $control->responsibleName !!} </b></span></div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <br>
                            @endif


                            <div class="row">
                                <!-- Person Responsible Info -->
                                <div class="col-xs-6 text-right"><b>Person responsible for ensuring compliance with SWMS:</b></div>
                                <div class="col-xs-6">{{ $doc->res_compliance }}</div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6 text-right"><b>Person responsible for reviewing SWMS control measures:</b></div>
                                <div class="col-xs-6">{{ $doc->res_review }}</div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-right" style="min-height: 50px">
                                        <br>
                                        <a href="/safety/doc/wms" class="btn default"> Back</a>
                                        @if($doc->master && $doc->status == 1 && Auth::user()->hasPermission2('add.wms'))
                                            <a href="/safety/doc/wms/{{ $doc->id}}/create" class="btn green">Create SWMS using this Template</a>
                                        @endif
                                        @if (Auth::user()->hasPermission2('add.wms') && $doc->status == 1 && $doc->for_company_id == Auth::user()->company_id)
                                            <a href="/safety/doc/wms/{{ $doc->id }}/renew" class="btn green"> Renew</a>
                                        @endif
                                        @if ($doc->status == 3)
                                            @if(Auth::user()->allowed2('sig.wms', $doc))
                                                <a href="/safety/doc/wms/{{ $doc->id }}/reject" class="btn red"> Reject</a>
                                                <a href="/safety/doc/wms/{{ $doc->id }}/signoff" class="btn green"> Sign Off</a>
                                            @elseif ($doc->for_company_id == Auth::user()->company_id && Auth::user()->allowed2('edit.wms', $doc))
                                                <a href="/safety/doc/wms/{{ $doc->id }}/reject" class="btn red"> Cancel Sign Off Request</a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- Email Modal -->
    <div class="modal fade" id="email" tabindex="-1" role="basic" aria-hidden="true">
        {!! Form::model($doc, ['method' => 'POST', 'action' => ['Safety\WmsController@email',  $doc->id]]) !!}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><b>Email File</b></h4>
                </div>
                <div class="modal-body">
                    <p style="margin-top:0px">Please provide an email or multiple emails separated by semi-colon ';'</p>
                    <div class="form-group">
                        <label>Email(s)</label>
                        <input type="text" name="email_list" id="email_list" class="form-control">
                    </div>
                    <div class="mt-checkbox-list">
                        <label class="mt-checkbox mt-checkbox-outline"></label>
                        <input type="checkbox" name="email_self"> Send a copy to yourself?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn green" id="send_email">Send</button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    <!-- Archive Modal -->
    <div class="modal fade bs-modal-sm" id="archive" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Archive Statement</b></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center">You are about to make this statement no longer <span style="text-decoration: underline">active</span> and archive it.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> Once archived only {{ $doc->owned_by->name }} can reactivite it.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a href="/safety/doc/wms/{{ $doc->id }}/archive" class="btn green">Continue</a>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/ladda/ladda-themeless.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        var sendEmailButton = document.getElementById("send_email");
        sendEmailButton.disabled = true

        $('#email_list').keyup(function () {
            sendEmailButton.disabled = false;
        });

        $('#send_email').click(function () {
            $('#send_email').html('<i class="fa fa-spin fa-spinner"> </i>' + ' Sending');
        });
    </script>
@stop

