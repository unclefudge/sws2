<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract Time Extensions</title>
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
            <h3 style="margin: 0px">Contract Time Extensions</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">
    <h3>Week of {{ $extension->date->format('d/m/Y') }}</h3>
    <table class="table table-striped table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            <th width="15%" class="pad5">Site</th>
            <th width="5%" class="pad5">Supervisor</th>
            <th width="5%" class="pad5">Forecast Completion</th>
            <th width="25%" class="pad5">Extend Reasons</th>
            <th class="pad5">Extend Notes</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $row)
            <tr>
                <td class="pad5">{!! $row['name'] !!}</td>
                <td class="pad5">{!! $row['super_initials'] !!}</td>
                <td class="pad5">{!! $row['completion_date'] !!}</td>
                <td class="pad5">{!! $row['extend_reasons_text'] !!}</td>
                <td class="pad5">{!! nl2br($row['notes']) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>