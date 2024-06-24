@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li>Supervisor Checklist</li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        {{-- Reports --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Weekly Supervisor Checklist</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body">
                        @foreach ($categories as $category)
                            <h4 class="font-green-haze"><b>{{$category->name}}</b>{!! ($category->description) ? ": <small>$category->description</small>" : '' !!}</h4>
                            <hr class="field-hr">
                            <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                @foreach ($category->questions as $question)
                                    <tr>
                                        <td>{{$question->name}}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endforeach

                        <div class="row">
                            <div class="col-md-12">
                                Finally plan tomorrows run, Starting project 7 am along with time allocation and so on till office between 2:30 & 3 daily. Go home turn off, knowing that you have done all you can.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {

        });

    </script>
@stop