@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-life-ring"></i> Toolbox Talks</h1>
    </div>
@stop
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/toolbox3">Toolbox Talks</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold ">EDIT TALK (v3)</span>
                            <span class="caption-helper">ID: {{ $talk->id }}</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Safety\ToolboxTalk3Controller::class, 'update'], $talk->id) }}" class="horizontal-form" enctype="multipart/form-data" id="talk_form">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <x-form.hidden name="talk_id" :value="$talk->id"/>
                            <x-form.hidden name="version" :value="$talk->version"/>
                            <x-form.hidden name="toolbox_type" value="none"/>
                            <x-form.hidden name="for_company_id" :value="Auth::user()->company_id"/>
                            <x-form.hidden name="status" value="2"/>
                            <x-form.hidden name="draft" value="save"/>
                            {{--}}<input type="hidden" name="overview" id='overview' value="{{ $talk->overview }}">
                            <input type="hidden" name="hazards" id='hazards' value="{{ $talk->hazards }}">
                            <input type="hidden" name="controls" id='controls' value="{{ $talk->controls }}">
                            <input type="hidden" name="further" id='further' value="{{ $talk->further }}">--}}

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
                                        <x-form.input name="name" label="Name of Toolbox Talk" :value="$talk->name"/>
                                    </div>
                                    <div class="col-md-3 text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa"><span class="font-grey-salsa">version {{ $talk->version }} </span>
                                    </div>
                                </div>
                                <hr style="margin: 2px 0 15px 0">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">OVERVIEW</h5></div>
                                        <div><x-form.rich-text name="overview" :value="$talk->overview" variant="document" :upload-url="'/safety/doc/toolbox3/'.$talk->id.'/upload'" :min-height="180"/></div>
                                        <br>
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE HAZARDS?</h5></div>
                                        <div><x-form.rich-text name="hazards" :value="$talk->hazards" variant="document" :upload-url="'/safety/doc/toolbox3/'.$talk->id.'/upload'" :min-height="180"/></div>
                                        <br>
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">WHAT ARE THE CONTROLS / WHAT ACTIONS ARE REQUIRED?</h5></div>
                                        <div><x-form.rich-text name="controls" :value="$talk->controls" variant="document" :upload-url="'/safety/doc/toolbox3/'.$talk->id.'/upload'" :min-height="180"/></div>
                                        <br>
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
                                        <div><x-form.rich-text name="further" :value="$talk->further" variant="document" :upload-url="'/safety/doc/toolbox3/'.$talk->id.'/upload'" :min-height="180"/></div>
                                    </div>
                                </div>
                                <br>

                                <div class="form-actions right">
                                    <a href="/safety/doc/toolbox3" class="btn default"> Back</a>
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
                        </form>
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

    @stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="{{ asset('js/tiptap-editor.js') }}?v={{ file_exists(public_path('js/tiptap-editor.js')) ? filemtime(public_path('js/tiptap-editor.js')) : '1' }}"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $('#name-show').on('click', function () {
            $('#name-show').hide();
            $('#name-edit').show();
        });

        $('#active').on('click', function () {
            $('#status').val(1);
            document.getElementById('talk_form').submit();
        });

        $('#continue').on('click', function () {
            $('#status').val(1);
            document.getElementById('talk_form').submit();
        });
    </script>
@stop
