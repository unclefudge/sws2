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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Statement</span>
                            <span class="caption-helper"></span>
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
                            <!-- Type of SWMS -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('swms_type', 'Which method would you like to use?', ['class' => 'control-label']) !!}
                                        {!! Form::select('swms_type',
                                        ['' => 'Select option', 'library' => 'Use a template from the SWMS library or Previous Statement', 'upload' => 'Upload an existing SWMS as a file', 'scratch' => 'Start from Scratch'],
                                         null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('swms_type', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6" id="library_div" style="display: none;">
                                    <div class="form-group {!! fieldHasError('master_id', $errors) !!}" id="master-div">
                                        <label for="master_id" class="control-label">Template</label>
                                        <select id="master_id" name="master_id" class="form-control select2" style="width:100%">
                                            <option></option>
                                            <optgroup label="Templates">
                                                @foreach(Auth::user()->company->wmsTemplateSelect() as $value => $name)
                                                    <option value="{{ $value }}" {{ (old("master_id") == $value ? 'selected':'') }} >{{ $name }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Previous Statements">
                                                @foreach(Auth::user()->company->wmsDocSelect() as $value => $name)
                                                    <option value="{{ $value }}" {{ (old("master_id") == $value ? 'selected':'') }}>{{ $name }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                        {!! fieldErrorMessage('master_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-6" id="upload_div" style="display: none;">
                                    <div class="form-group {!! fieldHasError('attachment', $errors) !!}">
                                        <label class="control-label">Select File</label>
                                        <input id="attachment" name="attachment" type="file" class="file-loading">
                                        {!! fieldErrorMessage('attachment', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div id="required_fields" style="display: none;">
                                <!-- Name -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('name', 'Name of Work Activity / Task', ['class' => 'control-label']) !!}
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('name', $errors) !!}
                                        </div>
                                    </div>
                                    {{--}}
                                    @if(Auth::user()->company->subscription)
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('for_company_id', $errors) !!}">
                                                <label for="for_company_id" class="control-label">Company</label>
                                                <select id="for_company_id" name="for_company_id" class="form-control select2" style="width:100%">
                                                    <option value=""></option>
                                                    @foreach(Auth::user()->company->companiesSelect() as $value => $name)
                                                        <option value="{{ $value }}" {{ (old("for_company_id") == $value ? 'selected':'') }} >{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                {!! fieldErrorMessage('for_company_id', $errors) !!}
                                            </div>
                                        </div>
                                    @else
                                    <input type="hidden" name="for_company_id" value="{{ Auth::user()->company_id }}">
                                    @endif --}}
                                </div>
                                <!-- Principal Contractor -->
                                @if(Auth::user()->company->subscription)
                                        <?php
                                        $principle_array = ['other' => 'Other'];
                                        if (Auth::user()->permissionLevel('add.wms', Auth::user()->company->id))
                                            $principle_array = [Auth::user()->company->id => Auth::user()->company->name] + $principle_array;
                                        if (Auth::user()->permissionLevel('add.wms', Auth::user()->company->parent_company))
                                            $principle_array = [Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name] + $principle_array;
                                        ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            {!! Form::label('principle_id', 'Principal Contractor', ['class' => 'control-label']) !!}
                                            {!! Form::select('principle_id', $principle_array, null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('principle_id', $errors) !!}

                                            {{--}}
                                            <div class="form-group">
                                                <p class="myswitch-label">&nbsp; </p>
                                         <span style="padding-right: 30px">Is {{ Auth::user()->company->reportsTo()->name }} the Principal
                                            Contractor?</span>
                                                {!! Form::label('principle_switch', "&nbsp;", ['class' => 'control-label']) !!}
                                                {!! Form::checkbox('principle_switch', '1', true, ['class' => 'make-switch',
                                                 'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                                 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                            </div>--}}
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('principle', $errors) !!}" style="display: none" id="principle-div">
                                                {!! Form::label('principle', 'Principal Contractor Name', ['class' => 'control-label']) !!}
                                                {!! Form::text('principle', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('principle', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Replacing Expired SWMS --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <p class="myswitch-label">&nbsp; </p>
                                            <span style="padding-right: 30px">Is this replacing an existing or expired SWMS?</span>
                                            {!! Form::label('replace_switch', "&nbsp;", ['class' => 'control-label']) !!}
                                            {!! Form::checkbox('replace_switch', '1', (isset($data['replace_id'])) ? true : false, ['class' => 'make-switch',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <?php $replace_value = ''; if (old("replace_id")) $replace_value = old("replace_id"); elseif (isset($data['replace_id'])) $replace_value = $data['replace_id']; ?>
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('replace_id', $errors) !!}" style="display: none" id="replace-div">
                                            <label for="replace_id" class="control-label">SWMS to Replace</label>
                                            <select id="replace_id" name="replace_id" class="form-control select2" style="width:100%">
                                                <option></option>
                                                <optgroup label="Existing Statements">
                                                    @foreach(Auth::user()->company->wmsDocSelect() as $value => $name)
                                                        <option value="{{ $value }}" {{ ($replace_value == $value ? 'selected':'') }}>{{ $name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            {!! fieldErrorMessage('replace_id', $errors) !!}<br>
                                            <div class="note note-warning">NOTE: The chosen SWMS will be archived</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Save as Template -->
                            {{-- Only allowed Fudge/Kirstie/Ross access to add to library --}}
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
            display_fields();

            $("#swms_type").change(function () {
                display_fields()
            });

            function display_fields() {
                if ($("#swms_type").val() == '') {
                    $('#required_fields').hide();
                    $('#library_div').hide();
                    $('#upload_div').hide();
                }
                if ($("#swms_type").val() == 'library') {
                    $('#required_fields').show();
                    $('#library_div').show();
                    $('#upload_div').hide();
                    $('#scratch_div').hide();
                }
                if ($("#swms_type").val() == 'upload') {
                    $('#required_fields').show();
                    $('#library_div').hide();
                    $('#upload_div').show();
                }
                if ($("#swms_type").val() == 'scratch') {
                    $('#required_fields').show();
                    $('#library_div').hide();
                    $('#upload_div').hide();
                }
            }

            /* Select2 */
            $("#master_id").select2({placeholder: "Select template",});

            $("#replace_id").select2({placeholder: "Select previous SWMS",});

            $("#for_company_id").select2({placeholder: "Select Company",});

            $('#master_id').change(function () {
                $('#name').val('');
                var name = $("#master_id option:selected").text().replace(/\(([^)]+)\)$/, ""); // (principle) out of text
                var name = name.replace(/v([0-9]*[.])?[0-9]+/, ""); // strip the version vX.X
                if ($(this).val())
                    $('#name').val(name);
            });

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

            /* Set Master field to false if SWMS type = 'upload' */
            $('#swms_type').change(function () {
                if ($(this).val() == 'upload') {
                    $('#master_div').hide();
                    $('#master').bootstrapSwitch('state', false);
                } else
                    $('#master_div').show();
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

