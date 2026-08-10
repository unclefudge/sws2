@inject('ozstates', 'App\Http\Utilities\OzStates')

@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings/role">Role Management</a><i class="fa fa-circle"></i></li>
        <li><span>Create new role</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New Role</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Misc\RoleController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="name" label="Name"/>
                                    </div>
                                    <div class="col-md-8">
                                        <x-form.input name="description" label="Description"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/settings/role" class="btn default"> Back</a>
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

