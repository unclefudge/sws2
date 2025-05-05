<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Requests</title>
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
        @foreach ($supers as $super_id => $super_name)
            {{-- New Page - Show header --}}
            <div class="row">
                <div class="col-xs-9"><h3 style="margin: 0px">Maintenance Requests With No Appointment or Action in last 14 days</h3></div>
                <div class="col-xs-3"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></div>
            </div>
            <hr style="margin: 5px 0px">
            <h4>{{$super_name}}</h4>
            <h5>No Appointment</h5>
            {{-- Add table header for 1st record found --}}
            <table class="table table-striped table-bordered table-hover order-column" id="table1" style="width:100%; padding: 0px; margin: 0px">
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
                    <?php $super_count = 0 ?>
                @foreach ($mains as $main)
                    @if ($main->super_id == $super_id || ($main->super_id == null && $super_id == '0'))
                        @if (!$main->client_appointment)
                                <?php $row_count++; $super_count++; ?>
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

                @if ($super_count == 0)
                    <tr>
                        <td colspan="7">No Maintenance Requests found matching required criteria</td>
                    </tr>
                @endif
                </tbody>
            </table>

            <h5>No Actions in last 14 days</h5>
            <table class="table table-striped table-bordered table-hover order-column" id="table1" style="width:100%; padding: 0px; margin: 0px">
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

                    <?php $super_count = 0 ?>
                @foreach ($mains as $main)
                    @if ($main->super_id == $super_id || ($main->super_id == null && $super_id == '0'))
                            <?php
                            // Exclude requests that have a task planned 1 week prior or after today
                            $recentTask = ($main->site->jobRecentTask && $main->site->jobRecentTask->gt(\Carbon\Carbon::now()->subDays(7))) ? $main->site->jobRecentTask->format('d/m/Y') : null;
                            $nextTask = ($main->site->jobNextTask && $main->site->jobNextTask->lt(\Carbon\Carbon::now()->addDays(7))) ? $main->site->jobNextTask->format('d/m/Y') : null;
                            $futureTasks = ($main->site->futureTasks()->count()) ? true : false;
                            ?>
                        @if ($main->lastUpdated()->lt(\Carbon\Carbon::now()->subDays(14)) && !($recentTask || $nextTask || $futureTasks))
                                <?php $row_count++; $super_count++; ?>
                            <tr>
                                <td class="pad5">M{{ $main->code }}</td>
                                <td class="pad5">{{ $main->created_at->format('d/m/Y') }}</td>
                                <td class="pad5">{{ $main->site->name }}</td>
                                <td class="pad5">{{ ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '-'  }}</td>
                                <td class="pad5">{{ ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '-'  }}</td>
                                <td class="pad5">{{ ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y') }}</td>
                                <td class="pad5">
                                    @if ($recentTask)
                                        <span style="color: #FF0000">{{ $recentTask }} - Recent Task</span><br>
                                    @elseif ($nextTask)
                                        <span style="color: #FF0000">{{ $nextTask }} - Upcoming Task</span><br>
                                    @endif
                                    {{ $main->lastActionNote() }}
                                </td>

                            </tr>
                        @endif
                    @endif
                @endforeach

                @if ($super_count == 0)
                    <tr>
                        <td colspan="7">No Maintenance Requests found matching required criteria</td>
                    </tr>
                @endif
                </tbody>
            </table>
            @if(!$loop->last)
                <div class="page"></div>
            @endif
        @endforeach
    </div>
</div>
</body>
</html>