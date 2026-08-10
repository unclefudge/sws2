@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span></span></li>
        <li><a href="/safety/doc/sds">Safety Data Sheets</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create SDS </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Safety\SdsController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                {{-- Name + Category --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="name" label="Name" required/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-form.select name="categories[]" label="Category" :options="App\Models\Safety\SafetyDocCategory::sdsCats('all')" plugin="select2" multiple required title="Check all applicable categories"/>
                                    </div>
                                </div>

                                {{-- Manufacturer + Date --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="manufacturer" label="Manufacturer"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.datepicker name="date" label="Date"/>
                                    </div>
                                </div>

                                {{-- Application --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="application" label="Application:" rows="3"/>
                                    </div>
                                </div>
                                {{-- Hazardous + Dangerous --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.select name="hazardous" label="Hazardous" :options="['0' => 'No', '1' => 'Yes']"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.select name="dangerous" label="Dangerous" :options="['0' => 'No', '1' => 'Yes']"/>
                                    </div>
                                </div>

                                {{-- SingleFile Upload --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Select File</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                        </div>
                                    </div>
                                </div>


                                <div class="form-actions right">
                                    <a href="/safety/doc/sds" class="btn default"> Back</a>
                                    <button type="submit" class="btn green">Save</button>
                                </div>
                            </div>
                        </form>
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
    <!--<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>-->
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });


        $(document).ready(function () {
            /* Select2 */
            $("#categories").select2({
                placeholder: "Check all applicable categories",
            });

            /* Bootstrap Fileinput */
            $("#singlefile").fileinput({
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