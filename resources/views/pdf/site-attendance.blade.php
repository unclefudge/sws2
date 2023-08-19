<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Attendance</title>
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
    </style>
</head>

<body>
<div class="container">
    @foreach($data as $siteplan)
        <div class="page22">
            <?php $site = App\Models\Site\Site::find($siteplan->site_id) ?>
            <div class="row">
                <div class="col-xs-8">
                    <h3 style="margin: 0px">{{ $site->name }}</h3>{{ $site->address }}, {{  $site->suburb_state_postcode }}</div>
                <div class="col-xs-4">
                    <h6>
                        <b>Supervisor:</b> {{ $site->supervisorName }}
                        {!! ($company) ? "<br><b>Company:</b> $company->name" : '' !!}
                        {!! ($from) ? "<br><b>Dates:</b> ".$from->format('d/m/Y')." - ".$to->format('d/m/Y') : '' !!}
                    </h6>
                </div>
            </div>
            <hr style="margin: 5px 0px">
            <br>
            <div class="row" style="border: 1px solid lightgrey; background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
                <div class="col-xs-1">Date</div>
                <div class="col-xs-3">Company</div>
                <div class="col-xs-8">Attendance</div>
            </div>
            @foreach($siteplan->attendance as $day => $company)
                @foreach($company as $company_name => $data)
                    <div class="row" @if ($loop->last)style="border-bottom: 1px solid lightgrey;" @endif>
                        <div class="col-xs-1">@if ($loop->first) {{ $day }} @endif</div>
                        <div class="col-xs-3">{{ $company_name }}</div>
                        <div class="col-xs-8">
                            <?php $c = count($data); $x = 1;  ?>
                            @foreach ($data as $user_id => $name)
                                {{ $name }}@if ($x < $c), @endif
                                <?php $x ++ ?>
                            @endforeach
                            <br>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endforeach
</div>
</body>
</html>