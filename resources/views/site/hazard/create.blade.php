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
    <style>
        /* Directly in blade they work */

    </style>
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Lodge Hazard</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action('Site\SiteHazardController@store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.select name="site_id" label="Site" class="select2">
                                        {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                    </x-form.select>
                                </div>
                                <div class="col-md-4">
                                    <x-form.input name="address" label="Site Address" readonly/>
                                </div>
                                <div class="col-md-2">
                                    <x-form.input name="code" label="Job #" readonly/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-7">
                                    <x-form.input name="location" label="Location of hazard"/>
                                </div>
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <x-form.select name="rating" label="Risk Rating" :options="['' => 'Select rating', 1 => 'Low', 2 => 'Medium', 3 => 'High', 4 => 'Extreme',]" class="bs-select"/>
                                </div>
                            </div>

                            <x-form.textarea name="reason" label="What is the hazard / safety issue?"/>

                            <x-form.textarea name="action" label="What action/s have you taken?"/>

                            <div class="row" style="margin-bottom: 20px">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Video of issue</h5>
                                    <x-form.filepond/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <x-form.checkbox2 name="action_required"/>
                                </div>
                                <div class="col-md-4" style="height: 4rem; line-height: 4rem;">Does site owner need to take any action?</div>
                            </div>


                            <div class="form-actions right">
                                <a href="/site/hazard" class="btn default">Back</a>
                                <button class="btn green">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
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

