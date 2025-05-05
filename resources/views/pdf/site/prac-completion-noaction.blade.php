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
                <div class="col-xs-9"><h3 style="margin: 0px">Practical Completion With No Action in last 14 days</h3></div>
                <div class="col-xs-3"><h6>Report generated {{ $today->format('d/m/Y') }}</h6></div>
            </div>
            <hr style="margin: 5px 0px">
            <h4>{{$super_name}}</h4>

            <table class="table table-striped table-bordered table-hover order-column" id="table1" style="width:100%; padding: 0px; margin: 0px">
                <thead>
                <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
                    <th width="5%" class="pad5">Created</th>
                    <th width="20%" class="pad5">Site</th>
                    <th class="pad5">Assigned Company</th>
                    <th width="5%" class="pad5">Updated</th>
                </tr>
                </thead>
                <tbody>

                    <?php $super_count = 0 ?>
                @foreach ($pracs as $prac)
                    @if ($prac->super_id == $super_id || ($prac->super_id == null && $super_id == '0'))
                        @if ($prac->lastUpdated()->lt(\Carbon\Carbon::now()->subDays(14)))
                                <?php $row_count++; $super_count++; ?>
                            <tr>
                                <td class="pad5">{{ $prac->created_at->format('d/m/Y') }}</td>
                                <td class="pad5">{{ $prac->site->name }}</td>
                                <td class="pad5">{{ $prac->assignedToNames()  }}</td>
                                <td class="pad5">{{ $prac->lastUpdated()->format('d/m/Y') }}</td>

                            </tr>
                        @endif
                    @endif
                @endforeach

                @if ($super_count == 0)
                    <tr>
                        <td colspan="4">No Practical Completion found matching required criteria</td>
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