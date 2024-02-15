@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/wms">SWMS</a><i class="fa fa-circle"></i></li>
        <li><span>Create Statement</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        <div class="mt-element-step">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first active">
                    <div class="mt-step-number bg-white font-grey">1</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                    <div class="mt-step-content font-grey-cascade">Create SWMS</div>
                </div>
                <div class="col-md-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">2</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                    <div class="mt-step-content font-grey-cascade">Add content</div>
                </div>
                <div class="col-md-3 mt-step-col">
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
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Renew Existing Statement</span>
                            <span class="caption-helper">ID: {{ $doc->id }} - {{ $doc->name }}</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('wmsdoc', ['action' => 'Safety\WmsController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <input type="hidden" name="version" value="1.0">
                        <div class="form-body">
                            {!! Form::hidden('swms_type', ($doc->builder) ? 'library' : 'upload') !!}
                            {!! Form::hidden('master_id', $doc->id) !!}
                            {!! Form::hidden('replace_switch', 1) !!}
                            {!! Form::hidden('replace_id', $doc->id) !!}
                            {!! Form::hidden('principle_id', $doc->principle_id) !!}
                            {!! Form::hidden('principle', $doc->principle) !!}
                            {!! Form::hidden('builder', $doc->builder) !!}

                            <div class="note note-warning">The old SWMS will be archived if you continue</div>
                            {{-- Name --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name of Work Activity / Task', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $doc->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                @if (!$doc->builder)
                                    <div class="col-md-6" id="upload_div">
                                        <div class="form-group {!! fieldHasError('attachment', $errors) !!}">
                                            <label class="control-label">Select File</label>
                                            <input id="attachment" name="attachment" type="file" class="file-loading">
                                            {!! fieldErrorMessage('attachment', $errors) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Save as Template -->
                            {{-- Only allowed Fudge access to add to library --}}
                            @if(in_array(Auth::user()->id, [3, 108, 1155]))
                                <div class="row" id="master_div">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <p class="myswitch-label">&nbsp;</p>
                                            <span style="padding-right: 30px">Save as a master template for others to access?</span>
                                            {!! Form::label('master', "&nbsp;", ['class' => 'control-label']) !!}
                                            {!! Form::checkbox('master', '1', false, ['class' => 'make-switch',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="master" value="0">
                            @endif
                        </div>
                        <div class="form-actions right">
                            <a href="/safety/doc/wms" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Begin</button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#replace_id").select2({placeholder: "Select previous SWMS",});
            $("#for_company_id").select2({placeholder: "Select Company",});


            $('#principle_id').change(function () {
                principle_name();
            });

            function principle_name() {
                if ($('#principle_id').val() == 'other')
                    $('#principle-div').show();
                else
                    $('#principle-div').hide();
            }

            principle_name();

            /* toggle Principle + set in on page load */
            if ($('#principle_switch').bootstrapSwitch('state') == false) {
                $('#principle-div').show();
            }

            $('#principle_switch').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#principle-div').toggle();
            });

            /* toggle Replace + set in on page load */
            if ($('#replace_switch').bootstrapSwitch('state') == true) {
                $('#replace-div').show();
            }

            $('#replace_switch').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#replace-div').toggle();
            });

            /* Bootstrap Fileinput */
            $("#attachment").fileinput({
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
        });
    </script>
@stop

