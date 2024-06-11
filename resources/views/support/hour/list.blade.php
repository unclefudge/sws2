@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/ticket">Support Tickets</a><i class="fa fa-circle"></i></li>
        <li><span>Support Hours</span></li>
    </ul>
@stop

@section('content')

    <style>
        .keybox {
            /*float: left;*/
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 3px 5px 0px;
            cursor: pointer !important;
        }

        .keybox2 {
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 3px 5px 0px;
            cursor: pointer !important;
        }

        .state-red {
            background-color: #e26a6a;
        }

        .state-orange {
            background-color: #f4d03f;
        }

        .state-green {
            background-color: #36d7b7;
        }

        .state-grey {
            background-color: #e9edef;
        }

    </style>

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <!-- Tickets -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Support Hours</span>
                        </div>
                        <div class="actions">
                            @if (Auth::user()->hasAnyRole2('web-admin'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/support/hours/update" data-original-title="Hours">Update</a>
                            @endif
                            <a class="btn btn-circle green btn-outline btn-sm" href="/support/ticket" data-original-title="Ticket">Tickets</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table style="width:100%">
                            <tr style="font-weight: bold;">
                                <td style="width: 140px">Day</td>
                                <td style="width: 80px; text-align: center">9 -11</td>
                                <td style="width: 80px; text-align: center">11 - 1</td>
                                <td style="width: 80px; text-align: center">1 - 3</td>
                                <td style="width: 80px; text-align: center">3 - 5</td>
                                <td> &nbsp; &nbsp; Comments</td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <hr style="margin: 5px 0 5px 0">
                                </td>
                            </tr>
                            @foreach ($hours as $hour)
                                <tr style="height:40px; border-bottom: 3px solid #FFF;">
                                    <td>{{ $hour->day }}</td>
                                    <td style="background-color: {{$hour->statusColour($hour->h9_11)}}">&nbsp;</td>
                                    <td style="background-color: {{$hour->statusColour($hour->h11_1)}}; border-left: 1px solid #fff">&nbsp;</td>
                                    <td style="background-color: {{$hour->statusColour($hour->h1_3)}}; border-left: 1px solid #fff">&nbsp;</td>
                                    <td style="background-color: {{$hour->statusColour($hour->h3_5)}}; border-left: 1px solid #fff">&nbsp;</td>
                                    <td>
                                        <div style="margin: 0 20px">{!! $hour->notes !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <br><br>
                        <div class="row">
                            <div class="col-md-12">
                                <span class="keybox state-red">&nbsp; &nbsp;&nbsp;</span> Unavailable
                                <span class="keybox state-orange" style="margin-left: 20px">&nbsp; &nbsp;&nbsp;</span> Available to work
                                <span class="keybox state-green" style="margin-left: 20px">&nbsp; &nbsp;&nbsp;</span> Working
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">

    </script>

    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop