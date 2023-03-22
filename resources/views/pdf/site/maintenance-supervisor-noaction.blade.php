<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>On Hold QA Checklists</title>
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
        {{-- New Page - Show header --}}
        <div class="row">
            <div class="col-xs-9"><h3 style="margin: 0px">Maintenance Supervisor No Action</h3></div>
            <div class="col-xs-3"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></div>
        </div>
        <hr style="margin: 5px 0px">
        @foreach ($supers as $super_id => $super_name)
            <h4>{{$super_name}}</h4>
            <table class="table table-striped table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
                <thead>
                <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
                    <th width="5%" class="pad5">#</th>
                    <th width="5%" class="pad5">Reported</th>
                    <th width="15%" class="pad5">Site</th>
                    <th width="10%" class="pad5">Client Contacted</th>
                    <th width="5%" class="pad5">Appointment</th>
                    <th width="5%" class="pad5">Last Action</th>
                    <th width="50%" class="pad5">Note</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($mains as $main)
                    @if ($main->super_id == $super_id)
                        @if (!$main->client_appointment || $main->lastUpdated()->lt(\Carbon\Carbon::now()->subDays(14)))
                            <?php $row_count ++; ?>
                            <tr>
                                <td class="pad5">M{{ $main->code }}</td>
                                <td class="pad5">{{ $main->created_at->format('d/m/Y') }}</td>
                                <td class="pad5">{{ $main->site->name }}</td>
                                <td class="pad5">{{ ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '-'  }}</td>
                                <td class="pad5">{{ ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '-'  }}</td>
                                <td class="pad5">{{ ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y') }}</td>
                                <td class="pad5">{{ $main->lastActionNote() }}</td>

                            </tr>
                        @endif
                    @endif
                @endforeach
                </tbody>
            </table>
        @endforeach
    </div>
</div>
</body>
</html>