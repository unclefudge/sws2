@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.maintenance'))
            <li><a href="/site/maintenance">Maintenance Register</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/maintenance/category">Categories</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Create Category</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New Category</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceCategoryController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')
                            <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="name" label="Name"/>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions right">
                                <a href="/site/maintenance/category" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {

        });
    </script>
@stop
