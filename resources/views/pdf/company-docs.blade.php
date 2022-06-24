<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Job Start Dates</title>
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
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <div class="row">
            <div class="col-xs-12">
                <h3 class="text-center" style="margin: 0px">{!! $header !!}</h3>
            </div>
        </div>
        <hr style="margin: 5px 0px 15px 0px">
        <div class="row" style="border: 1px solid lightgrey; background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            @if ($multiple_company)
                <div class="col-xs-4 border-right" style="padding-left: 3px">Company</div>
            @endif
            <div class="col-xs-3 border-right" style="padding-left: 3px">Document</div>
            <div class="col-xs-2 border-right" style="padding-left: 3px">Expiry</div>
            <div class="col-xs-3 border-right" style="padding-left: 3px">Approved By</div>
        </div>
        @foreach($data as $row)
            <div class="row row-striped" style="border-style: none solid solid; border-width: 1px; border-color:  lightgrey; overflow: hidden;">
                @if ($multiple_company)
                    <div class="col-xs-4 border-right" style="padding-left: 3px">{!! $row['company_name'] !!}</div>
                @endif
                <div class="col-xs-3 border-right" style="padding-left: 3px">{!! $row['doc_name'] !!}</div>
                <div class="col-xs-2 border-right" style="padding-left: 3px">{!! $row['expiry'] !!}</div>
                <div class="col-xs-3 border-right" style="padding-left: 3px">{!! $row['approved_by'] !!} - {!! $row['approved_at'] !!} </div>
            </div>
        @endforeach
    </div>
</body>
</html>