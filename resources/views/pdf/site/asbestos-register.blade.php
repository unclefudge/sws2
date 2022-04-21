<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asbestos Register</title>
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

        .row-striped:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .row-striped:nth-of-type(even) {
            background-color: #f4f4f4;
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
<footer>
    <div class="pagenum-container">
        <hr style="margin: 0px">
        Document created: {!! date('\ d/m/Y\ ') !!} <span style="float: right">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </span><br>Version: {{ $asb->version }}
    </div>
</footer>
<div class="container">
    <div class="page22">
        <?php $page_count = 1; ?>
        <div class="row">
            <div class="col-xs-10">
                <h3>Asbestos Register</h3>
            </div>
            <div class="col-xs-2">
                <img src="{!! URL::to('/') !!}/img/logo-capecod2.png">
            </div>
        </div>
        <hr style="margin: 5px 0px">
    </div>

    <div class="row">
        <div class="col-xs-10">
            <h4 style="margin: 0px">{{ $asb->site->name }}</h4>{{ $asb->site->address }}, {{  $asb->site->suburb_state_postcode }}
        </div>
        <div class="col-xs-2">Last Updated: {{ $asb->updated_at->format('d/m/Y') }}</div>
    </div><br><br>

    <table class="table table-striped table-bordered table-hover order-column" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #f0f6fa; font-weight: bold;">
            <th width="5%" class="pad5">Date<br>Identified</th>
            <th width="20%" class="pad5"><br>Location of ACM</th>
            <th width="16%" class="pad5"><br>Type</th>
            <th width="8%" class="pad5">Friable /<br>Non-Friable</th>
            <th width="5%" class="pad5"><br>Quantity</th>
            <th width="23%" class="pad5"><br>Condition</th>
            <th width="23%" class="pad5"><br>Assessment / Action</th>
        </tr>
        </thead>
        @foreach ($asb->items as $item)
            <tr>
                <td width="5%" class="pad5">{!! $item->date->format('d/m/Y') !!}</td>
                <td width="20%" class="pad5">{!! $item->location !!}</td>
                <td width="16%" class="pad5">{!! $item->type !!}</td>
                <td width="8%" class="pad5">{!! ($item->friable) ? 'Friable' : 'Non-friable' !!}</td>
                <td width="5%" class="pad5">{!! $item->amount !!}</td>
                <td width="23%" class="pad5">{!! $item->condition !!}</td>
                <td width="23%" class="pad5">{!! $item->assessment !!}</td>
            </tr>
        @endforeach
    </table>
</div>
</div>
</body>
</html>