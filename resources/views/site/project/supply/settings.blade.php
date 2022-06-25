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
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Project Supply Infomation Settings</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteProjectSupply', ['method' => 'POST', 'action' => ['Site\SiteProjectSupplyController@updateSettings',], 'class' => 'horizontal-form', 'files' => true]) !!}

                        @include('form-error')

                        <div class="form-body">
                            {{-- Column Titles --}}
                            <div class="row bold hidden-sm hidden-xs">
                                <div class="col-md-2">
                                    <div class="form-group">{!! Form::text("title-product", $title->name, ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <div class="col-md-3">{!! Form::text("title-supplier", $title->supplier, ['class' => 'form-control']) !!}
                                    {{--}}<a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="One supplier per line"
                                       data-original-title="Supplier"> <i class="fa fa-question-circle font-grey-silver"></i></a>--}}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text("title-type", $title->type, ['class' => 'form-control']) !!}
                                    {{--<a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="One type per line"
                                                              data-original-title="Type"> <i class="fa fa-question-circle font-grey-silver"></i></a>--}}
                                </div>
                                <div class="col-md-4">{!! Form::text("title-colour", $title->colour, ['class' => 'form-control']) !!}</div>
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

                                        <div class="form-group">
                                            {!! Form::textarea("supplier-$product->id", $product->supplier, ['rows' => '3', 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Type --}}
                                        <div class="visible-sm visible-xs">Type</div>
                                        <div class="form-group">
                                            {!! Form::textarea("type-$product->id", $product->type, ['rows' => '3', 'class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    {{-- Colour --}}
                                    <div class="col-md-4">
                                        <div class="visible-sm visible-xs">Colour</div>{!! Form::text("colour-$product->id", $product->colour, ['class' => 'form-control']) !!}</div>
                                </div>
                                <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 0px 0px 10px 0px;">
                                <div class="visible-sm visible-xs"><br></div>
                            @endforeach

                            <div class="form-actions right">
                                <a href="/site/supply" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
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

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
</script>
@stop

