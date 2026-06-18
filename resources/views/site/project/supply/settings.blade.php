@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/supply">Project Supply Info</a><i class="fa fa-circle"></i></li>
        <li><span>Settings</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Project Supply Infomation Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteProjectSupplyController::class, 'updateSettings']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                {{-- Column Titles --}}
                                <div class="row bold hidden-sm hidden-xs">
                                    <div class="col-md-2">
                                        <x-form.input name="title-product" :value="$title->name"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="title-supplier" :value="$title->supplier"/>
                                        {{--}}<a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="One supplier per line" data-original-title="Supplier"> <i class="fa fa-question-circle font-grey-silver"></i></a>--}}
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="title-type" :value="$title->type"/>
                                        {{--<a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="One type per line" data-original-title="Type"> <i class="fa fa-question-circle font-grey-silver"></i></a>--}}
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="title-colour" :value="$title->colour"/>
                                    </div>
                                    {{--}}<div class="col-md-2">Notes</div>--}}
                                </div>
                                <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">

                                {{-- Products --}}
                                @foreach ($products as $product)
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="hidden-sm hidden-xs">
                                                {{ $product->name }}
                                            </div>
                                            <div class="visible-sm visible-xs">
                                                <br><b>{{ $product->name }}</b>
                                                <hr class="visible-sm visible-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            {{-- Supplier --}}
                                            <div class="visible-sm visible-xs">Supplier</div>
                                            <x-form.textarea name="supplier-{{ $product->id }}" :value="$product->supplier" rows="3"/>
                                        </div>
                                        <div class="col-md-3">
                                            {{-- Type --}}
                                            <div class="visible-sm visible-xs">Type</div>
                                            <x-form.textarea name="type-{{ $product->id }}" :value="$product->type" rows="3"/>
                                        </div>
                                        {{-- Colour --}}
                                        <div class="col-md-4">
                                            <div class="visible-sm visible-xs">Colour</div>
                                            <x-form.input name="colour-{{ $product->id }}" :value="$product->colour"/>
                                        </div>
                                    </div>
                                    <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 0px 0px 10px 0px;">
                                    <div class="visible-sm visible-xs"><br></div>
                                @endforeach

                                <div class="form-actions right">
                                    <a href="/site/supply" class="btn default"> Back</a>
                                    <button type="submit" class="btn green"> Save</button>
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
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
    </script>
@stop

