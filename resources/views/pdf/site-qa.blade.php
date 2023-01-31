<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quality Assurance</title>
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

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped > tbody > tr:nth-of-type(even) {
            background-color: #fbfbfb;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        td.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

        header {
            position: fixed;
            top: -60px;
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
<header>header on each page</header>
<footer>
    {{--}}<div class="pagenum-container">
        Document created {!! date('\ d/m/Y\ ') !!} <span style="float: right">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </span>
    </div>--}}
    <div class="pagenum-container">
        <div class="row">
            <div class="col-xs-10">Document created {!! date('\ d/m/Y\ ') !!}</div>
            <div class="col-xs-2">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </div>
        </div>
    </div>
</footer>
<div class="container">
    {{-- Cover Page --}}
    <div style="padding: 20px; margin: auto; width: 90%; height: 950px;">
        <p style="padding-top: 50px">&nbsp;</p>
        <p style="text-align: center"><img src="{!! URL::to('/') !!}/img/logo-capecod2-large.png"></p>
        <p style="padding-top: 100px">&nbsp;</p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">QUALITY</p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">ASSURANCE</p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">CHECKLIST</p>
        <p style="padding-top: 100px">&nbsp;</p>
        <p style="text-align: center; font-size: 20px; font-weight: 400">{{ $site->address }}, {{ $site->suburb }} </p>
    </div>
    <div class="page"></div>

    {{-- Page per QA --}}
    <?php $pagecount = 0; ?>
    @foreach($data as $qa)
        <?php $pagecount ++ ?>
        <div class="row">
            <div class="col-xs-6">
                <img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40">
            </div>
            <div class="col-xs-6">
                <p>JOB NAME: {{ $site->name }}<br>
                    ADDRESS: {{ $site->full_address }}</p>
            </div>
        </div>
        <div class="row" style="padding-top: 5px">
            <div class="col-xs-12 ">
                <br>
                <h5 style="margin: 0px"><b>{{ $qa->name }}</b></h5>
            </div>
        </div>
        <br>

        <!-- Items -->
        <table class="table table-bordered" style="padding: 0px; margin: 0px">
            <thead>
            <tr style="background-color: #F6F6F6; font-weight: bold;">
                <th width="5%"></th>
                <th>Inspection Item</th>
                <th width="15%">Checked Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach($qa->items as $item_num => $item_data )
                <tr>
                    <td class="pad5" style="text-align: center">
                        @if($item_data['status'] == '1')
                            <i class="fa fa-check-square-o" style="color:#32c5d2; font-size: 16px; padding-top: 5px"></i>
                        @elseif($item_data['status'] == '-1')
                            N/A
                        @else
                            <i class="fa fa-square-o" style="color:#e7505a; font-size: 16px; padding-top: 5px"></i>
                        @endif
                    </td>
                    <td class="pad5">{{ $item_data['name'] }}<br>{{ $item_data['done_by'] }}</td>
                    <td class="pad5">{{ $item_data['sign_by'] }}<br>{{ $item_data['sign_at'] }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
        <br><br>

        <!-- Notes -->
        @if ($qa->actions)
            <h6 style="margin: 0px"><b>Notes</b></h6>
            <hr style="margin: 3px 0px">
            @foreach($qa->actions as $action_id => $action_data )
                <div class="row" style="overflow: hidden; padding:5px;">
                    <div class="col-xs-3">{{ $action_data['created_at'] }} - {{ $action_data['created_by'] }}</div>
                    <div class="col-xs-9">{{ $action_data['action'] }}</div>
                </div>
            @endforeach
            <br><br>
        @endif

        {{-- Report Signatures --}}
        <h6 style="margin: 0px"><b>QUALITY ASSURANCE SIGN-OFF</b></h6>
        <hr style="margin: 3px 0px 5px 0px">
        <p>The above inspection items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
        <div class="row" style="overflow: hidden; padding:1px">
            <div class="col-xs-3" style="text-align: right">Site Supervisor</div>
            <div class="col-xs-9">@if ($qa->super_sign_by){{ $qa->super_sign_by }}, &nbsp; {{ $qa->super_sign_at }}@endif</div>
        </div>
        <div class="row" style="overflow: hidden; padding:1px">
            <div class="col-xs-3" style="text-align: right">Construction Manager</div>
            <div class="col-xs-9">@if ($qa->manager_sign_by){{ $qa->manager_sign_by }}, &nbsp; {{ $qa->manager_sign_at }}@endif</div>
        </div>
        @if ($pagecount < count($data))
            <div class="page"></div>
        @endif
    @endforeach
</div>
</body>
</html>