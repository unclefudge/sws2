@inject('ozstates', 'App\Http\Utilities\OzStates')

@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-users"></i> Client Management</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/client">Clients</a><i class="fa fa-circle"></i></li>
        <li><span>Create new client</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New Client</span>
                            <span class="caption-helper"></span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\ClientController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="name" label="Name"/>
                                    </div>
                                    <div class="col-md-8">
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="address" label="Address"/>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-form.input name="suburb" label="Suburb"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="postcode" label="Postcode"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Phone + Email --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="phone" label="Phone"/>
                                    </div>
                                    <div class="col-md-5">
                                        <x-form.input name="email" label="Email"/>
                                    </div>
                                </div>

                                <h3 class="form-section"></h3>

                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes" rows="2"/>
                                        <span class="help-block"> For internal use only </span>
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="{{URL::previous()}}">
                                        <button type="button" class="btn default"> Back</button>
                                    </a>
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
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
@stop
