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
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteScaffoldHandoverController::class, 'store']) }}" class="horizontal-form">
                            @csrf

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
                                        <x-form.select name="site_id" id="site_id" label="Site" plugin="select2" style="width:100%">
                                            @if ($site)
                                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                                            @else
                                                {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                            @endif
                                        </x-form.select>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Scaffold details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 ">
                                        <x-form.textarea name="location" label="Description and location of area handed over" rows="3" placeholder="Specific details"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 ">
                                        <x-form.textarea name="use" label="Intended use of the scaffold" rows="3" placeholder="Specific details"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-7">
                                        <x-form.select name="duty" label="Duty Classification" :options="$duty_class" title="Select class"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="decks" label="No. of working decks"/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Upload Photos/Documents of Scaffold</h5>
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 ">
                                        <x-form.textarea name="notes" rows="5" placeholder="Details"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/scaffold/handover" class="btn default"> Back</a>
                                    <button type="submit" class="btn green" id="submit"> Save</button>
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


