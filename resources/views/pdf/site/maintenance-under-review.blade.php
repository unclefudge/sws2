<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Under Review</title>
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
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <?php $row_count = 0; ?>
        <?php $page_count = 1; ?>
        @if ($mains->count())
            @foreach ($mains as $main)
                @if ($row_count == 0)
                    {{-- New Page - Show header --}}
                    <div class="row">
                        <div class="col-xs-9"><h3 style="margin: 0px">Maintenance Under Review</h3></div>
                        <div class="col-xs-3"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></div>
                    </div>
                    <hr style="margin: 5px 0px">
                    <div class="row">
                        <div class="col-xs-1">#</div>
                        <div class="col-xs-1">Reported</div>
                        <div class="col-xs-5">Site</div>
                        <div class="col-xs-3">Supervisor</div>
                        <div class="col-xs-1">Updated</div>
                    </div>
                    <hr style="margin: 5px 0px">
                    <?php $row_count ++; ?>
                @endif

                <?php $row_count ++; ?>
                <div class="row">
                    <div class="col-xs-1">M{{ $main->code }}</div>
                    <div class="col-xs-1">{{ $main->created_at->format('d/m/Y') }}</div>
                    <div class="col-xs-5">{{ $main->site->name }}</div>
                    <div class="col-xs-3">{{ $main->supervisor }}</div>
                    <div class="col-xs-1">{{ ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y') }}</div>
                </div>

                @if ($row_count > 28) {{-- New Page if no of lines exceed max --}}
                <div class="page"></div>
                <?php $row_count = 0; $page_count ++ ?>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-xs-9"><h3 style="margin: 0px">Maintenance Under Review</h3></div>
                <div class="col-xs-3"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></div>
            </div>
            <div class="row">
                <div class="col-xs-9"><br><br> There are currently no Maintenance Requests under review</div>
            </div>

        @endif


    </div>
</div>
</body>
</html>