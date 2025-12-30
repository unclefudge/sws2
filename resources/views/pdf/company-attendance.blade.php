<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Attendance</title>
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

        td.pad5, th.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <table class="table table-striped table-bordered table-hover order-column" id="table1" style="width:100%; padding: 0px; margin: 0px">
            <tr>
                <td class="pad5">
                    <h2 style="margin: 0px">{{ $company->name }}</h2>
                    {{ $company->address }}, {{  $company->suburb_state_postcode }}
                </td>
                <td style="width:20%; text-align: right"><h4><b>Dates:</b> {{ ($from) ? $from->format('d/m/Y') : '' }} - {{ ($to) ? $to->format('d/m/Y') : '' }}</h4></td>
            </tr>
        </table>
        <hr style="margin: 5px 0px">
        <br>
        <table class="table table-bordered table-hover order-column" id="table1" style="width:100%; padding: 0px; margin: 0px">
            <thead>
            <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
                <th style="width:10%" class="pad5">Date</th>
                <th style="width:25%" class="pad5">Site</th>
                <th class="pad5">Attendance</th>
            </tr>
            </thead>
            @foreach($data as $day => $site)
                @foreach($site as $site_name => $data)
                    <tr>
                        {{-- Date --}}
                        <td>
                            @if ($loop->first)
                                {{ $day }}
                            @endif
                        </td>
                        {{-- Site --}}
                        <td>{{ $site_name }}</td>
                        {{-- Attendance --}}
                        <td>
                                <?php $c = count($data); $x = 1; ?>
                            @foreach ($data as $user_id => $name)
                                {{ $name }}{!! ($x < $c) ? ', ' : '' !!}
                                    <?php $x++ ?>
                            @endforeach
                            <br>
                        </td>
                    </tr>
                    @if ($loop->last)
                        <tr>
                            <td colspan="3" style=" border-bottom: 1px solid lightgrey;"></td>
                        </tr>
        @endif
        @endforeach
        @endforeach
    </div>
</div>
</body>
</html>