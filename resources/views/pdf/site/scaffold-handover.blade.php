<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scaffold Handover Cerificate</title>
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

        tr {
            border: none !important;
        }

        .table2 {
            padding: 2px;
        }

        td.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
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
<div class="container">
    <?php $pagecount = 1; ?>
    <div class="page22">
        <div class="row" style="padding: 5px">
            <div class="col-xs-3"><img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40"></div>
            <div class="col-xs-9"><h3 style="margin: 0px">SCAFFOLD HANDOVER CERTIFICATE</h3></div>
        </div>
        {{-- Job Details --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">JOB DETAILS &nbsp; : &nbsp; {{ $report->site->name }}</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-1">Site</div>
            <div class="col-xs-6">{{ $report->site->name }}</div>
            <div class="col-xs-2">Handover Date</div>
            <div class="col-xs-2">{{ $report->handover_date->format('d/m/Y') }}</div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-1">&nbsp;</div>
            <div class="col-xs-6">{!! $site->full_address !!}</div>
            <div class="col-xs-2">Duty Classification</div>
            <div class="col-xs-2">{{ $report->duty }}</div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-1"></div>
            <div class="col-xs-6"></div>
            <div class="col-xs-2">No. of working decks</div>
            <div class="col-xs-2">{{ $report->decks }}</div>
        </div>

        {{-- Location --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">DESCRIPTION / LOCATION</h5></div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-12">{!! nl2br($report->location) !!}</div>
        </div>
        {{-- Use --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">INTENDED USE</h5></div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-12">{!! nl2br($report->use) !!}</div>
        </div>
        {{-- Photos --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">PHOTOS</h5></div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-12">
                @if ($report->docs->count())
                    <div style="width: 100%; overflow: hidden;">
                        @foreach ($report->docs as $doc)
                            @if ($doc->type == 'photo')
                                <img src="{!! URL::to($doc->AttachmentUrl) !!}" width="160"> &nbsp; &nbsp;
                            @endif
                        @endforeach
                    </div>
                @else
                    <div>No photos found<br><br></div>
                @endif
            </div>
        </div>
        {{-- Signed Off --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">Handover Inspection of Scaffold</h5></div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-12">This scaffold detailed above has been erected in accordance with the attached drawings, the WHS Regulations and the General Guide for scaffolds and scaffolding work; is informed by relevant technical standards and is suitable for its intended purpose.<br><br><br>
            </div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-6">
                Signed by (Inspector) : &nbsp; &nbsp; {{ $report->inspector_name }}<br>
                Date: &nbsp; &nbsp; {{ $report->signed }}<br>
            </div>
            <div class="col-xs-5"><img src="{!! URL::to($report->inspector_licence_url) !!}" width="150"></div>
        </div>
    </div>
    {{-- <div class="page"></div> --}}
</div>
</div>
</body>
</html>