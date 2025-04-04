<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Outstanding QA Checklists</title>
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
        @foreach ($supers as $sid => $super_name)
            @foreach ($qas as $qa)
                @if ($row_count == 0)
                    {{-- New Page - Show header --}}
                    <table style="width:100%;">
                        <tr>
                            <td><h2 style="margin: 0px">{{ $report_type }} QA Checklists - {{ $supervisor }}</h2></td>
                            <td style="width:150"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></td>
                        </tr>
                    </table>
                    <hr style="margin: 5px 0px">
                    <table style="width:100%;">
                        <tr>
                            <td style="width:200"><b>Site</b></td>
                            <td><b>Name</b></td>
                            {{--}}<td style="width:100"><b>Supervisor</b></td>--}}
                            <td style="width:70"><b>Updated</b></td>
                            <td style="width:120"><b>Completed</b></td>
                        </tr>
                    </table>
                    <hr style="margin: 5px 0px">
                        <?php $row_count++ ?>
                @endif

                @if ($qa->site->supervisorName == $supervisor && $supervisor == $super_name)
                        <?php
                        $row_count++;
                        $total = $qa->items()->count();
                        $completed = $qa->itemsCompleted()->count();
                        $pending = '';
                        if ($total == $completed && $total != 0) {
                            if (!$qa->supervisor_sign_by)
                                $pending = ' - Pending Supervisor';
                            elseif (!$qa->manager_sign_by)
                                $pending = ' - Pending Manager';
                        }
                        ?>
                    <table style="width:100%;">
                        <tr>
                            <td style="width:200">{{ $qa->site->name }}</td>
                            <td>{{ $qa->name }}</td>
                            {{--}}<td style="width:100">{{ $qa->site->supervisorName }}</td>--}}
                            <td style="width:70">{{ $qa->updated_at->format('d/m/Y') }}</td>
                            <td style="width:120"><{{ $completed }}/
                            {{ $total }} {!! $pending !!}</td>
                        </tr>
                    </table>
                @endif

                @if ($row_count > 28)
                    {{-- New Page if no of lines exceed max --}}
                    <div class="page"></div>
                        <?php $row_count = 0; $page_count++ ?>
                @endif
            @endforeach
        @endforeach
        <br><br>
        <?php $row_count = $row_count + 2 ?>
    </div>
</div>
</body>
</html>