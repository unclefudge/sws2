<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Holiday Program Oct 2023</title>
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
            /*font-family: 'Martel Sans', sans-serif;*/
            font-weight: 700;
        }

        body {
            font-size: 8px;
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
            padding: 4px !important;
            line-height: 1em !important;
        }

        td.pad3, th.pad3 {
            padding: 2px !important;
            line-height: 1em !important;
        }

    </style>
</head>

<body>
<div class="container">
    {{-- Youth Contacts --}}
    <div class="row">
        <div class="col-xs-12">
                    <img src="{!! URL::to('/') !!}/img/ccc-logo.jpg" style="float: left"><h3 style="margin: 0px; text-align: center">School Holiday Program OCT 2023 - CONTACTS</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">
    <table class="table table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            <th style="width:10%" class="pad5">Name</th>
            <th style="width:4%" class="pad5">D.O.B</th>
            <th style="width:15%" class="pad5">Address</th>
            <th style="width:9%" class="pad5">Parent / Guardian</th>
            <th style="width:6%" class="pad5">Phone</th>
            <th style="width:14%" class="pad5">Email</th>
            <th style="width:6%" class="pad5">Pickup / <br>Dropoff</th>
            <th style="width:3%" class="pad5">Leave</th>
            <th style="width:3%" class="pad5">Photo<br>Consent</th>
            <th style="width:3%" class="pad5">Movie<br>Consent</th>
            <th style="width:3%" class="pad5">Medical<br>Consent</th>
            <th>Medical</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($attendees as $youth)
            <tr>
                <td class="pad3">{{ $youth->name }}</td>
                <td class="pad3">{{ $youth->dob->format('d/mY') }}</td>
                <td class="pad3">{{ $youth->address }}</td>
                <td class="pad3">{{ $youth->parent }}</td>
                <td class="pad3">{{ $youth->phone }}</td>
                <td class="pad3">{{ $youth->email }}</td>
                <td class="pad3">{{ $youth->pickup }}</td>
                <td class="pad3">{{ $youth->leave_unsupervised }}</td>
                <td class="pad3">{{ $youth->consent_photo }}</td>
                <td class="pad3">{{ $youth->consent_movie }}</td>
                <td class="pad3">{{ $youth->consent_medical }}</td>
                <td class="pad3">{{ $youth->medical }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


    {{-- Program Attendance - Page 1 --}}
    <div class="page"></div>
    <div class="row">
        <div class="col-xs-12">
            <img src="{!! URL::to('/') !!}/img/ccc-logo.jpg" style="float: left"><h3 style="margin: 0px; text-align: center">School Holiday Program OCT 2023 - WEEK 1</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">
    <table class="table table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            <th style="width:5%"></th>
            @foreach ($programs as $program)
                @if ($program->id < 5)
                    <th style="width:22%"><h5>{{ $program->date->format('D j') }}<sup>{{ $program->date->format('S') }}</sup><br><b>{{ $program->name }}</b></h5></th>
                @endif
            @endforeach
        </tr>
        </thead>
        <tbody>
        @for($pos = 1;  $pos < 26; $pos++)
            <tr>
                <td class="pad5">{{ $pos }}.</td>
                @for ($prog = 1; $prog < 5; $prog++)
                        <?php
                        $program = \App\Models\Ccc\Program::findOrFail($prog);
                        $youth = $program->youthPosition($pos);
                        ?>
                    <td class="pad5" style="{{ ($youth && $pos > $program->max) ? 'background:#fffdd0' : '' }}">
                        {!! ($youth && $pos > $program->max) ? "Waitlist: " : '' !!}{!! ($youth) ? $youth->name : '' !!}
                    </td>
                @endfor
            </tr>
        @endfor
        </tbody>
    </table>
    {{-- Program Attendance - Page 2 --}}
    <div class="page"></div>
    <div class="row">
        <div class="col-xs-12">
            <img src="{!! URL::to('/') !!}/img/ccc-logo.jpg" style="float: left"><h3 style="margin: 0px; text-align: center">School Holiday Program OCT 2023 - WEEK 2</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">
    <table class="table table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            <th style="width:5%"></th>
            @foreach ($programs as $program)
                @if ($program->id > 4)
                    <th style="width:22%"><h5>{{ $program->date->format('D j') }}<sup>{{ $program->date->format('S') }}</sup><br><b>{{ $program->name }}</b></h5></th>
                @endif
            @endforeach
        </tr>
        </thead>
        <tbody>
        @for($pos = 1;  $pos < 26; $pos++)
            <tr>
                <td class="pad5">{{ $pos }}.</td>
                @for ($prog = 5; $prog < 9; $prog++)
                        <?php
                        $program = \App\Models\Ccc\Program::findOrFail($prog);
                        $youth = $program->youthPosition($pos);
                        ?>
                    <td class="pad5" style="{{ ($youth && $pos > $program->max) ? 'background:#fffdd0' : '' }}">
                        {!! ($youth && $pos > $program->max) ? "Waitlist: " : '' !!}{!! ($youth) ? $youth->name : '' !!}
                    </td>
                @endfor
            </tr>
        @endfor
        </tbody>
    </table>


    {{-- Program Pickups --}}
    <?php $maxrows = 40; $row = 0; $headbuffer = 4; ?>
    @foreach ($programs as $program)
        {{-- Determine Max youth listed for paging --}}
            <?php
            $max = 0;
            foreach ($program->pickups() as $location => $kids) {
                if (count($kids) > $max)
                    $max = count($kids);
            }

            if ($row + $max + $headbuffer > $maxrows) {
                $row = 0;
            }
            ?>

        {{-- header --}}
        @if ($row == 0)
            <div class="page"></div>
            <div class="row">
                <div class="col-xs-12">
                    <img src="{!! URL::to('/') !!}/img/ccc-logo.jpg" style="float: left"><h3 style="margin: 0px; text-align: center">School Holiday Program OCT 2023 - PICKUPS</h3>
                </div>
            </div>
            <hr style="margin: 5px 0px 15px 0px">
        @endif

        <h5>{{ $program->date->format('D j') }}<sup>{{ $program->date->format('S') }}</sup> - <b>{{ $program->name }}</b></h5>
        <table class="table table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
            <thead>
            <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
                @foreach ($program->pickups() as $location => $kids)
                    <td>{{ $location }} &nbsp; ({{ count($kids) }})</td>
                @endforeach
            </tr>
            </thead>
            <tbody>
            <tr>
                @foreach ($program->pickups() as $location => $kids)
                    <td>
                        @foreach ($kids as $name)
                            {{ $name }}<br>
                        @endforeach
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
            <?php $row = $row + $max + $headbuffer ?>
    @endforeach
</div>
</body>
</html>