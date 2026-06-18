@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.qa'))
            <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/qa/templates">Templates</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/qa/category">Categories</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Category</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Category</span>
                            <span class="caption-helper">ID: {{ $cat->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteQaCategoryController::class, 'update'], $cat->id) }}" class="horizontal-form" id="qa_form">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                        <div class="form-body">

                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name" :value="$cat->name"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="/site/qa/category" class="btn default"> Back</a>
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

