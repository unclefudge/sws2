@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><a href="/designer-postcode">Designer Postcodes</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Designer Postcode</span>
                            <span class="caption-helper"> ID: {{ $postcode->id }}</span>
                        </div>
                    </div>

                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\DesignerPostcodeController::class, 'update'], $postcode->id) }}">
                            @csrf
                            @method('PATCH')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="suburb" label="Suburb" :value="$postcode->suburb"/>
                                    </div>

                                    <div class="col-md-2">
                                        <x-form.input name="postcode" label="Postcode" :value="$postcode->postcode"/>
                                    </div>

                                    <div class="col-md-3 pull-right">
                                        <label class="control-label" style="margin-bottom: 0px">Status</label>
                                        <div style="padding-top: 8px;">
                                            <x-form.checkbox2 name="active" :checked="$postcode->active" :on-text="'Active'" :off-text="'Disabled'"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.select name="council" label="Council" :options="['' => 'Select Council'] + $councils" :value="$postcode->council" plugin="select2" placeholder="Select Council"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/settings/designer-postcode" class="btn default">Back</a>
                                <button type="submit" class="btn green">Save</button>
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
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
@stop
