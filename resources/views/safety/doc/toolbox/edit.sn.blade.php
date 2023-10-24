@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/toolbox2">Toolbox Talks</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Talk</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        <div class="mt-element-step hidden-sm hidden-xs">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first done">
                    <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                    <div class="mt-step-content font-grey-cascade">Create Talk</div>
                </div>
                <div class="col-md-3 mt-step-col active">
                    <div class="mt-step-number bg-white font-grey">2</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                    <div class="mt-step-content font-grey-cascade">Add content</div>
                </div>
                <div class="col-md-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">3</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Users</div>
                    <div class="mt-step-content font-grey-cascade">Assign Users</div>
                </div>
                <div class="col-md-3 mt-step-col last">
                    <div class="mt-step-number bg-white font-grey">4</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Archive</div>
                    <div class="mt-step-content font-grey-cascade">Talk completed</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Edit Talk</span>
                            <span class="caption-helper">ID: {{ $talk->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($talk, ['method' => 'PATCH', 'action' => ['Safety\ToolboxTalkController@update', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}

                        @include('form-error')

                        <input type="hidden" name="talk_id" id='talk_id' value="{{ $talk->id }}">
                        <input type="hidden" name="version" value="{{ $talk->version }}">
                        <input type="hidden" name="toolbox_type" value="none">
                        <input type="hidden" name="for_company_id" value="{{ Auth::user()->company_id }}">
                        <input type="hidden" name="status" id="status" value="2">
                        <input type="hidden" name="overview" id='overview' value="{{ $talk->overview }}">
                        <input type="hidden" name="hazards" id='hazards' value="{{ $talk->hazards }}">
                        <input type="hidden" name="controls" id='controls' value="{{ $talk->controls }}">
                        <input type="hidden" name="further" id='further' value="{{ $talk->further }}">

                        <div class="form-body">
                            <div class="row">
                                @if($talk->master)
                                    <div class="col-md-12">
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Template</h3>
                                    </div>
                                @endif
                            </div>
                            <div class="row hoverDiv" style="padding: 0px; min-height: 0px">
                                <div class="col-md-9" id="name-show">
                                    <h1 style="margin: 0 0 2px 0">{{ $talk->name }}
                                        <small class="font-grey-silver" style="vertical-align: text-top"> &nbsp; <i class="fa fa-pencil"></i></small>
                                    </h1>
                                </div>
                                <div class="col-md-9" id="name-edit" style="display: none">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name of Toolbox Talk', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $talk->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3 text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa"><span class="font-grey-salsa">version {{ $talk->version }} </span>
                                </div>
                            </div>
                            <hr style="margin: 2px 0 15px 0">

                            <div class="col-md-6">

                            </div>


                            <div class="row">
                                <div class="col-xs-12">
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">OVERVIEW</h5></div>
                                    <div name="sn_overview" id="sn_overview">{!! $talk->overview !!}</div>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE HAZARDS?</h5></div>
                                    <div name="sn_hazards" id="sn_hazards">{!! $talk->hazards !!}</div>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE CONTROLS / WHAT ACTIONS ARE REQUIRED?</h5></div>
                                    <div name="sn_controls" id="sn_controls">{!! $talk->controls !!}</div>
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
                                    <div name="sn_further" id="sn_further">{!! $talk->further !!}</div>
                                </div>
                            </div>
                            <br>

                            <div class="form-actions right">
                                <a href="/safety/doc/toolbox2" class="btn default"> Back</a>
                                <button type="submit" class="btn dark"> Save Draft</button>
                                @if(!$talk->master)
                                    <a data-original-title="Assign Users" data-toggle="modal" href="#modal_final">
                                        <button type="button" class="btn green" id="final"> Assign Users</button>
                                    </a>
                                @else
                                    <button type="button" class="btn green" data-dismiss="modal" id="active">Make Active</button>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Users Modal -->
    <div id="modal_final" class="modal fade bs-modal-sm" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Assign Users</b></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center">You are about leave DRAFT mode and begin to assign USERS.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> You will no longer be able to modify this talk anymore.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn green" data-dismiss="modal" id="continue">Continue</button>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-summernote/summernote.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-summernote/summernote.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>

@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        header: $('meta[name="_token"]').attr('content')
    })

    var ComponentsEditors = function () {
        var handleEditors = function () {
            $('#sn_overview').summernote({height: 150});
            $('#sn_hazards').summernote({height: 300});
            $('#sn_controls').summernote({height: 300});
            $('#sn_further').summernote({height: 100});
        }
        return {
            //main function to initiate the module
            init: function () {
                handleEditors();
            }
        };

    }();

    jQuery(document).ready(function () {
        ComponentsEditors.init();

    });

    $('#name-show').on('click', function () {
        $('#name-show').hide();
        $('#name-edit').show();
    });

    $('#talk_form').on('submit', function (e) {
        e.preventDefault(e);
        submit_form();

        /*$('#overview').val($('#sn_overview').code());
        $('#further').val($('#sn_further').code());
        $.ajax({
            type: "POST",
            url: '/safety/doc/toolbox2/' + $('#talk_id').val(),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                window.location = "/safety/doc/toolbox2/" + $('#talk_id').val() + '/edit';
            },
            error: function (data) {
                alert('Failed to save Toolbox talk');
            }
        })*/
    });

    $('#active').on('click', function () {
        $('#status').val(1);
        submit_form();
    });

    $('#continue').on('click', function () {
        $('#status').val(1);
        submit_form();
    });

    function submit_form() {
        $('#overview').val($('#sn_overview').code());
        $('#hazards').val($('#sn_hazards').code());
        $('#controls').val($('#sn_controls').code());
        $('#further').val($('#sn_further').code());
        $.ajax({
            type: "POST",
            url: '/safety/doc/toolbox2/' + $('#talk_id').val(),
            data: $("#talk_form").serialize(),
            dataType: 'json',
            success: function (data) {
                window.location = "/safety/doc/toolbox2/" + $('#talk_id').val() + '/edit';
            },
            error: function (data) {
                alert('Failed to save Toolbox talk');
            }
        })
    }
</script>
@stop

