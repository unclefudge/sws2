@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.hazard'))
            <li><a href="/site/hazard">Hazard Register</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Lodge</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Lodge Hazard</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('site_hazard', ['action' => ['Site\SiteHazardController@store'], 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('address', 'Site Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('address', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('code', 'Job #', ['class' => 'control-label']) !!}
                                        {!! Form::text('code', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- Location -->
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                        {!! Form::label('location', 'Location of hazard (eg. bathroom, first floor addition, kitchen, backyard)', ['class' => 'control-label']) !!}
                                        {!! Form::text('location', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('location', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group {!! fieldHasError('rating', $errors) !!}">
                                        {!! Form::label('rating', 'Risk Rating', ['class' => 'control-label']) !!}
                                        {!! Form::select('rating', ['' => 'Select rating', '1' => "Low", '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('rating', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('reason', $errors) !!}">
                                        {!! Form::label('reason', 'What is the hazard / safety issue?', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('reason', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('reason', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                        {!! Form::label('action', 'What action/s (if any) have you taken to resolve the issue?', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('action', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('action', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Video of issue</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('action_required', '1', null,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Does site owner need to take any action?
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/site/hazard" class="btn default"> Back</a>
                                <button type="submit" class="btn green">Submit</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                                <!-- END FORM-->
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
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script>
    //$.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

    // Get a reference to the file input element
    const inputElement = document.querySelector('input[type="file"]');

    // Create a FilePond instance
    const pond = FilePond.create(inputElement);
    FilePond.setOptions({
        server: {
            url: '/file/upload',
            fetch: null,
            revert: null,
            headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
        },
        allowMultiple: true,
    });

    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({
            placeholder: "Select Site",
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

