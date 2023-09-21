<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Toolbox Talk</title>
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
            font-weight: 700;
        }

        body {
            font-size: 10px;
            line-height: 10px;
        }

        body.pdf {
            font-size: 10px;
            line-height: 10px;
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

        td.pad5, th.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

        td.pad0, th.pad0 {
            padding: 0px !important;
            line-height: 1em !important;
            border: 0px !important;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h3 style="margin: 0px">{{ $talk->name }} &nbsp; <span style="font-size:10px; font-weight: normal">version {{ $talk->version }}</span><span class="pull-right" style="font-size:10px; font-weight: normal; margin-top: 10px">Toolbox ID: {{ $talk->id }}</span></h3>
            <hr style="margin: 5px 0px 10px 0px">
            Created by: {{ $talk->createdBy->full_name }}
            <br><br>
        </div>
    </div>

    {{-- Overview --}}
    <div class="row">
        <div class="col-md-12">
            <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h6 style="margin: 5px; font-weight: bold">OVERVIEW</h6></div>
        </div>
        <div class="col-md-12"><br>{!! $talk->iframeField('overview') !!}</div>
    </div>

    {{-- Hazards --}}
    @if ($talk->hazards )
        <br>
        <div class="row">
            <div class="col-md-12">
                <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">HAZARDS</h5></div>
            </div>
            <div class="col-md-12"><br>{!! $talk->iframeField('hazards') !!}</div>
        </div>
    @endif

    {{-- Controls --}}
    @if ($talk->controls )
        <br>
        <div class="row">
            <div class="col-md-12">
                <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">CONTROLS / ACTIONS</h5></div>
            </div>
        </div>
        <div class="col-md-12"><br>{!! $talk->iframeField('controls') !!}</div>
    @endif

    {{-- Further --}}
    @if ($talk->further )
        <br>
        <div class="row">
            <div class="col-md-12">
                <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
            </div>
            <div class="col-md-12"><br>{!! $talk->iframeField('further') !!}</div>
        </div>
    {{--}}
        <div data-oembed-url="https://youtu.be/XP1yIXHfswc">
            <div>
                <div style="left: 0; width: 100%; height: 0; position: relative; padding-bottom: 56.25%;">
                    <iframe allow="accelerometer *; clipboard-write *; encrypted-media *; gyroscope *; picture-in-picture *; web-share *;" allowfullscreen="" scrolling="no" src="//if-cdn.com/pd25kjy" style="top: 0; left: 0; width: 100%; height: 100%; position: absolute; border: 0;"
                            tabindex="-1"></iframe>
                </div>
            </div>
        </div>
        --}}
    @endif
    <br>

    {{--}}<div class="page"></div>--}}

</div>
</body>
</html>