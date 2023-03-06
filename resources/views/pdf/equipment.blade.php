<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment List</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);
        /*@import url(https://fonts.googleapis.com/css?family=Martel+Sans);*/

        @page {
            margin: .7cm .7cm
        }

        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'PT Sans', serif;
        }

        h1 {
            /*font-family: 'Martel Sans', sans-serif;*/
            font-weight: 700;
        }

        body {
            font-size: 10px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #fbfbfb;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        header {
            position: fixed;
            top: 10px;
            left: 0px;
            right: 0px;
            height: 50px;
        }

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 20px;
        }

        footer .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<body>
<header></header>
<footer>
    <div class="pagenum-container">
        {{--}}Document created {!! date('\ d/m/Y\ ') !!} <span style="float: right">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </span>--}}
        <div class="row">
            <div class="col-xs-10">&nbsp;</div>
            <div class="col-xs-2">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </div>
        </div>
    </div>
</footer>
<div class="container">
    <div class="page22">
        <div class="row">
            <div class="col-xs-8"><h3 style="margin: 0px">Equipment List</h3></div>
            <div class="col-xs-4"><h6><b>Date: {{ \Carbon\Carbon::today()->format('d/m/Y') }}</b></h6></div>
        </div>
        <hr style="margin: 5px 0px">
        <br>
        <h4>General</h4>
        <hr>
        @foreach ($equipment as $equip)
            @if ($equip->category_id == 1)
                <div class="row">
                    <div class="col-md-12"><b>{{ $equip->name }} ({{ $equip->total }})</b></div>
                </div>
                @foreach ($equip->locations() as $location)
                    @if ($location->equipment($equip->id)->qty)
                        <div class="row">
                            <div class="col-xs-1 text-right">{{ $location->equipment($equip->id)->qty }}</div>
                            <div class="col-xs-11">{!! $location->name2 !!}</div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach

        <h4>Materials</h4>
        <hr>
        @foreach ($equipment as $equip)
            @if ($equip->category->parent == 3)
                <div class="row">
                    <div class="col-md-12"><b>{{ $equip->name }} ({{ $equip->total }})</b></div>
                </div>
                @foreach ($equip->locations() as $location)
                    @if ($location->equipment($equip->id)->qty)
                        <div class="row">
                            <div class="col-xs-1 text-right">{{ $location->equipment($equip->id)->qty }}</div>
                            <div class="col-xs-11">{!! $location->name2 !!}</div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach

        <h4>Scaffold</h4>
        <hr>
        @foreach ($equipment as $equip)
            @if ($equip->category_id == 2)
                <div class="row">
                    <div class="col-md-12"><b>{{ $equip->name }} ({{ $equip->total }})</b></div>
                </div>
                @foreach ($equip->locations() as $location)
                    @if ($location->equipment($equip->id)->qty)
                        <div class="row">
                            <div class="col-xs-1 text-right">{{ $location->equipment($equip->id)->qty }}</div>
                            <div class="col-xs-11">{!! $location->name2 !!}</div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>
</div>
</body>
</html>