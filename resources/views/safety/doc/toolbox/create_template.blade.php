@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/toolbox2">Toolbox Talks</a><i class="fa fa-circle"></i></li>
        <li><span>Create Talk</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        <div class="mt-element-step hidden-sm hidden-xs">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first active">
                    <div class="mt-step-number bg-white font-grey">1</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                    <div class="mt-step-content font-grey-cascade">Create Talk</div>
                </div>
                <div class="col-md-3 mt-step-col">
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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Talk From Template</span>
                            <span class="caption-helper">{{ $talk->name }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('toolboxtalk', ['action' => 'Safety\ToolboxTalkController@store', 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <input type="hidden" name="version" value="1.0">
                        <input type="hidden" name="for_company_id" value="{{ Auth::user()->company_id }}">
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">
                        <input type="hidden" name="master_id" value="{{ $talk->id }}">
                        <input type="hidden" name="toolbox_type" value="library">
                        <div class="form-body">
                            <!-- Template or File -->
                            <div class="row">
                                <div class="col-md-6" id="required_fields">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name of Toolbox Talk', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $talk->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            {{-- Only allowed Fudge/Kirstie/Ross access to add to library --}}
                            <div class="row" @if(!in_array(Auth::user()->id, [3, 108, 1155])) style="display: none;" @endif>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <p class="myswitch-label">&nbsp;</p>
                                            {!! Form::label('master', "&nbsp;", ['class' => 'control-label']) !!}
                                            {!! Form::checkbox('master', '1', false, ['class' => 'make-switch',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                        <div class="col-xs-9">
                                            <div style="padding-top:30px">Save as a master template for others to access?</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="form-actions right">
                                <a href="/safety/doc/toolbox2" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Begin</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
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
            display_fields();

            function display_fields() {
                if ($("#toolbox_type").val() == '') {
                    $('#required_fields').hide();
                    $('#library_div').hide();
                    $('#previous_div').hide();
                }
                if ($("#toolbox_type").val() == 'library') {
                    $('#required_fields').show();
                    $('#library_div').show();
                    $('#previous_div').hide();
                    $('#scratch_div').hide();
                }
                if ($("#toolbox_type").val() == 'previous') {
                    $('#required_fields').show();
                    $('#library_div').hide();
                    $('#previous_div').show();
                }
                if ($("#toolbox_type").val() == 'scratch') {
                    $('#required_fields').show();
                    $('#library_div').hide();
                    $('#previous_div').hide();
                }
            }

            /* Select2 */
            $("#master_id").select2({
                placeholder: "Select template",
            });
            /* Select2 */
            $("#previous_id").select2({
                placeholder: "Select previous talk",
            });
            $("#for_company_id").select2({
                placeholder: "Select Company",
            });
            $('#master_id').change(function () {
                $('#name').val('');
                // strip the version out of text
                var name = $("#master_id option:selected").text().replace(/\(v([0-9]*[.])?[0-9]+\)/, "");
                if ($(this).val())
                    $('#name').val(name);
            });
            $('#previous_id').change(function () {
                $('#name').val('');
                // strip the version out of text
                var name = $("#previous_id option:selected").text().replace(/\(v([0-9]*[.])?[0-9]+\)/, "");
                if ($(this).val())
                    $('#name').val(name);
            });
            //$('#transient').bootstrapSwitch('state', false);
            if ($('#master').bootstrapSwitch('state'))
                $('#steps').hide();
            else
                $('#steps').show();
            $('#master').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#steps').toggle();
            });
        });
    </script>
@stop

