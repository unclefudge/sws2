@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.scaffold.handover'))
            <li><a href="/site/scaffold/handover">Scaffold Handover Certificate</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Create Certificate</span></li>
    </ul>
@stop

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
                            <span class="caption-subject font-green-haze bold uppercase">Create Scaffold Handover Certificate</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteScaffoldHandover', ['action' => 'Site\SiteScaffoldHandoverController@store', 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        {{-- Progress Steps --}}
                        <div class="mt-element-step hidden-sm hidden-xs">
                            <div class="row step-thin" id="steps">
                                <div class="col-md-6 mt-step-col first active">
                                    <div class="mt-step-number bg-white font-grey">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                    <div class="mt-step-content font-grey-cascade">Create certificate</div>
                                </div>
                                <div class="col-md-6 mt-step-col last">
                                    <div class="mt-step-number bg-white font-grey">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                                    <div class="mt-step-content font-grey-cascade">Certificate Sign Off</div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-body">
                            <h4 class="font-green-haze">Site details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            @if ($site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @else
                                                {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                            @endif
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Scaffold details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Description and location of area handed over', ['class' => 'control-label']) !!}
                                        {!! Form::textarea("location", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details"]) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('use', $errors) !!}">
                                        {!! Form::label('use', 'Intended use of the scaffold', ['class' => 'control-label']) !!}
                                        {!! Form::textarea("use", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details"]) !!}
                                        {!! fieldErrorMessage('use', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('duty', $errors) !!}">
                                        {!! Form::label('duty', 'Duty Classification', ['class' => 'control-label']) !!}
                                        {!! Form::select('duty', $duty_class, null, ['class' => 'form-control bs-select', 'name' => 'duty', 'title' => 'Select class']) !!}
                                        {!! fieldErrorMessage('duty', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('decks', $errors) !!}">
                                        {!! Form::label('decks', 'No. of working decks', ['class' => 'control-label']) !!}
                                        {!! Form::text('decks', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('decks', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Documents of Scaffold</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Notes</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12 ">
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::textarea("notes", null, ['rows' => '5', 'class' => 'form-control', 'placeholder' => "Details"]) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/scaffold/handover" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                        </div>
                        {!! Form::close() !!} <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});
        });
    </script>
@stop


