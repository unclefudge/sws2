<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Supply Information</title>
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
        {{ $project->site->address }}, {{  $project->site->suburb_state_postcode }} <span style="float: right">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </span>
    </div>
</footer>
<div class="container">
    <div class="page22">
        <?php $page_count = 1; ?>
        <div class="row">
            <div class="col-xs-2">
                <img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40">
            </div>
            <div class="col-xs-9">
               <h3 style="text-align: right">Project Information Sheet</h3>
            </div>
        </div>
        <hr style="margin: 5px 0px">
    </div>
    <br>

    <div class="row">
        <div class="col-xs-12">The following Products are a description of the internal and external materials included in your project with Contact Details for your reference.<br><br></div>
    </div>

    <table class="table table-bordered table-hover order-column" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold;">
            <th width="25%" class="pad5">{{ $project->titles()->name }}</th>
            <th width="25%" class="pad5">{{ $project->titles()->supplier }}</th>
            <th width="25%" class="pad5">{{ $project->titles()->type }}</th>
            <th width="15%" class="pad5">{{ $project->titles()->colour }}</th>
        </tr>
        </thead>
        @foreach ($project->itemsOrdered() as $item)
            <tr>
                <td width="25%" class="pad5">{!! $item->product !!}</td>
                <td width="25%" class="pad5">{!! $item->supplier !!}</td>
                <td width="25%" class="pad5">{!! $item->type !!}</td>
                <td width="15%" class="pad5">{!! $item->colour !!}</td>
            </tr>
        @endforeach
    </table>
</div>
</div>
</body>
</html>