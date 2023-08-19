<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Plan</title>
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
    </style>
</head>

<body>
<div class="container">
    <?php $site_count = 0; ?>
    @foreach($data as $siteplan)
        <?php $site_count ++ ?>
        <?php $site = App\Models\Site\Site::find($siteplan->site_id) ?>
        <div class="row">
            <div class="col-xs-8">
                <h3 style="margin: 0px">{{ $site->name }}</h3>{{ $site->address }}, {{  $site->suburb_state_postcode }}</div>
            <div class="col-xs-4">
                <h6><b>Supervisor:</b> {{ $site->supervisorName }}</h6>
            </div>
        </div>

        <hr style="margin: 5px 0px">
        @foreach($siteplan->weeks as $week_num => $week_data )
            <div class="row">
                <div class="col-xs-12"><h6 style="margin: 0px 0px 5px 0px"><b>Week {{ $week_num }}</b></h6></div>
            </div>
            <table class="table table-striped table-bordered table-hover order-column" style="padding: 0px; margin: 0px">
                @foreach($week_data as $row )
                    <?php $cell_array = explode(' ', trim($row[1])) ?>
                    @if($cell_array[0] == 'MONDAY' || $cell_array[0] == 'TUESDAY' || $cell_array[0] == 'WEDNESDAY' || $cell_array[0] == 'THURSDAY' || $cell_array[0] == 'FRIDAY')
                        <thead>
                        <tr style="background-color: #F6F6F6; font-weight: bold;">
                            <th width="16%" class="pad5">{!! $row[1] !!}</th>
                            <th width="16%" class="pad5">{!! $row[2] !!}</th>
                            <th width="16%" class="pad5">{!! $row[3] !!}</th>
                            <th width="16%" class="pad5">{!! $row[4] !!}</th>
                            <th width="16%" class="pad5">{!! $row[5] !!}</th>
                        </tr>
                        </thead>
                    @else
                        <tr>
                            @if($row[1] == 'NOTHING-ON-PLAN')
                                <td colspan="5" class="pad5">No tasks for this week</td>
                            @else
                                <td width="16%" class="pad5">{!! $row[1] !!}</td>
                                <td width="16%" class="pad5">{!! $row[2] !!}</td>
                                <td width="16%" class="pad5">{!! $row[3] !!}</td>
                                <td width="16%" class="pad5">{!! $row[4] !!}</td>
                                <td width="16%" class="pad5">{!! $row[5] !!}</td>
                            @endif
                        </tr>
                    @endif
                @endforeach
            </table>
            <br>
        @endforeach
        @if ($site_count < count($data))
            <div class="page"></div>
        @endif
    @endforeach
</div>
</body>
</html>