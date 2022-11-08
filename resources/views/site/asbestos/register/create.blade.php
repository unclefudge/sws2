@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/register">Asbestos Register</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Asbestos Register</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteAsbestosRegister', ['action' => 'Site\SiteAsbestosRegisterController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')
                        {!! Form::hidden('no-asbestos', '0', ['class' => 'form-control', 'id' => 'no-asbestos']) !!}

                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_id', $sitelist, null, ['class' => 'form-control select2']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>

                                {{-- No Asbestos --}}
                                <div class="col-md-3 pull-right">
                                    <button class="btn red" style="margin-top: 20px" id="btn-no-asbestos" name="btn-no-asbestos"> Mark No Asbestos Found</button>
                                </div>
                            </div>
                            <div id="asbestos-div">

                                {{-- Asbestos Details --}}
                                <h4>Asbestos Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                {{-- Amount --}}
                                <div class="row">
                                    {{--  Date --}}
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('date', $errors) !!}">
                                            {!! Form::label('date', 'Date Identified', ['class' => 'control-label']) !!}
                                            <div class="input-group date date-picker">
                                                {!! Form::text('date', null, ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                <span class="input-group-btn">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                            {!! fieldErrorMessage('date', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('amount', $errors) !!}">
                                            {!! Form::label('amount', 'Quantity (m2)', ['class' => 'control-label']) !!}
                                            <input type="text" class="form-control" value="{{ old('amount') }}" id="amount" name="amount"> {{--}} onkeypress="return isNumber(event)">--}}
                                            {!! fieldErrorMessage('amount', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('friable', $errors) !!}">
                                            {!! Form::label('friable', 'Asbestos Class', ['class' => 'control-label']) !!}
                                            {!! Form::select('friable', ['' => 'Select class', '1' => 'Class A (Friable)', '0' => 'Class B (Non-Friable)'],
                                                 null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('friable', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Type --}}
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                            {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                            {!! Form::select('type', ['' => 'Select type', 'Asbestos Cement Sheets/Products' => 'Asbestos Cement Sheets/Products',
                                            'Vinyl floor covering' => 'Vinyl floor covering', 'other' => 'Other'],
                                                 null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('type', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-7" style="display: none" id="type_other_div">
                                        <div class="form-group {!! fieldHasError('type_other', $errors) !!}">
                                            {!! Form::label('type_other', 'Other type', ['class' => 'control-label']) !!}
                                            {!! Form::text('type_other', null, ['class' => 'form-control', 'placeholder' => 'Please specify other']) !!}
                                            {!! fieldErrorMessage('type_other', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Location --}}
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                            {!! Form::label('location', 'Location of Asbestos', ['class' => 'control-label']) !!}
                                            {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Location of asbestos']) !!}
                                            {!! fieldErrorMessage('location', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Condition --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('condition', $errors) !!}">
                                            {!! Form::label('condition', 'Condition', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('condition', null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => 'Condition of asbestos']) !!}
                                            {!! fieldErrorMessage('condition', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Assessment --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('assessment', $errors) !!}">
                                            {!! Form::label('assessment', 'Assessment', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('assessment', null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => 'Assessment of asbestos']) !!}
                                            {!! fieldErrorMessage('assessment', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <br><br>
                            </div>
                            <div class="form-actions right">
                                <a href="/site/asbestos/register" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site",});

        // On Change Type
        $("#type").change(function () {
            $("#type").val() == 'other' ? $("#type_other_div").show() : $("#type_other_div").hide(); // Type
        });

        // On Click No Asbestos
        $("#btn-no-asbestos").click(function (e) {
            e.preventDefault(e);
            $("#no-asbestos").val('1');
            $("#asbestos-div").hide();
        });
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if ((charCode > 31 && charCode < 48) || charCode > 57) {
            return false;
        }
        return true;
    }

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });

</script>
@stop

