<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Practical Completion Dates</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);

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
            font-size: 8px;
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
        td.pad5, th.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h3 style="margin: 0px">Practical Completion Dates</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">

    <table class="table table-striped table-bordered table-hover order-column" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold;">
            <th width="10%" class="pad5">Completion Date</th>
            <th width="5%" class="pad5">Job #</th>
            <th width="15%" class="pad5">Site</th>
            <th width="15%" class="pad5">Supervisor</th>
            <th class="pad5">Prac Papers Signed</th>
        </tr>
        </thead>
        <tbody>
        @foreach($startdata as $row)
            <tr>
                <td class="pad5">{!! $row['date'] !!}</td>
                <td class="pad5">{!! $row['code'] !!}</td>
                <td class="pad5">{!! $row['name'] !!}</td>
                <td class="pad5">{!! $row['supervisor'] !!}</td>
                <td class="pad5">{!! $row['completion_signed'] !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{--
    <div class="row" style="border: 1px solid lightgrey; background-color: #f0f6fa; font-weight: bold; overflow: hidden;">
        <div class="col-xs-4" style="padding: 0px">
            <div class="col-xs-3 border-right" style="padding-left: 3px">Completion Date</div>
            <div class="col-xs-2 border-right" style="padding-left: 3px">Job #</div>
            <div class="col-xs-7 border-right" style="padding-left: 3px">Site</div>
        </div>
        <div class="col-xs-3 border-right" style="padding-left: 3px;">Supervisor</div>
        <div class="col-xs-2 border-right" style="padding-left: 3px">Prac Papers Signed</div>

    </div>
    @foreach($startdata as $row)
        <div class="row row-striped" style="border-style: none solid solid; border-width: 1px; border-color:  lightgrey; overflow: hidden;">
            <div class="col-xs-4" style="padding: 0px">
                <div class="col-xs-3 border-right" style="padding-left: 3px">{!! $row['date'] !!}</div>
                <div class="col-xs-2 border-right" style="padding-left: 3px">{!! $row['code'] !!}</div>
                <div class="col-xs-7 border-right" style="padding-left: 3px">{!! $row['name'] !!}</div>
            </div>
            <div class="col-xs-3 border-right" style="padding-left: 3px">{!! $row['supervisor'] !!}</div>
            <div class="col-xs-2 border-right" style="padding-left: 3px">{!! $row['completion_signed'] !!}</div>
        </div>
    @endforeach
    --}}
</div>
</body>
</html>