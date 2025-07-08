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
        @endif
        <li><span>Report Order</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">QA Report Order</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-xs-2 text-center"></div>
                                <div class="col-xs-4"></div>
                                <div class="col-xs-2"></div>
                            </div>
                            @foreach ($templates as $template)
                                <div class="row">
                                    <div class="col-xs-1">
                                        <a href="/site/qa/templates/order/up/{{ $template->id }}" style="margin-left: 10px"><i class="fa fa-chevron-up"></i></a><br>
                                        <a href="/site/qa/templates/order/down/{{ $template->id }}" style="margin-left: 10px"><i class="fa fa-chevron-down"></i></a>
                                    </div>
                                    <div class="col-xs-1">
                                        <span style="margin-top: 5px"> {{ $template->order }}. &nbsp; </span>
                                    </div>
                                    <div class="col-xs-10">{!! $template->name !!}</div>
                                </div>
                                @if (!$loop->last)
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                @endif
                            @endforeach

                        </div>
                        <div class="form-actions right">
                            <a href="/site/qa/templates" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


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

