<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Maintenance Executive Report</title>
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

        .row-striped:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .row-striped:nth-of-type(even) {
            background-color: #f4f4f4;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
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
        Document created {!! date('\ d/m/Y\ ') !!} <span style="float: right">Page <span class="pagenum"></span> &nbsp; &nbsp; &nbsp; </span>
    </div>
</footer>
<div class="container">
    <div class="page22">
        <?php $row_count = 5; ?>
        <?php $page_count = 1; ?>
        <h3 style="margin: 0px">Site Maintenance Executive Report</h3>
        <hr style="margin: 5px 0px">

        {{-- Summary Stats --}}
        <div class="row">
            <div class="col-xs-4">Date Range (90 days)</div>
            <div class="col-xs-4">{{ $from->format('d M') }} - {{ $to->format('d M Y') }}</div>
            <div class="col-xs-2">Total Requests</div>
            <div class="col-xs-2">{{ ($mains->count() + $mains_old->count()) }}</div>
        </div>
        <div class="row">
            <div class="col-xs-4">Average days for allocating Requests</div>
            <div class="col-xs-4">{{ $avg_allocated }}</div>
            <div class="col-xs-2">New Requests</div>
            <div class="col-xs-2">{{ $mains_created->count() }}</div>
        </div>
        <div class="row">
            <div class="col-xs-4">Average days for client contacted</div>
            <div class="col-xs-4">{{ $avg_contacted }}</div>
            <div class="col-xs-2">Unique Sites</div>
            <div class="col-xs-2">{{ ($mains->groupBy('site_id')->count() + $mains_old->groupBy('site_id')->count()) }}</div>
        </div>
            <div class="row">
                <div class="col-xs-4">Average days from appointment to completion</div>
                <div class="col-xs-4">{{ $avg_appoint }}</div>
                <div class="col-xs-2"></div>
                <div class="col-xs-2"></div>
            </div>
            <div class="row">
                <div class="col-xs-4">Average days for completing Requests</div>
                <div class="col-xs-4">{{ $avg_completed }}</div>
                <div class="col-xs-2"></div>
                <div class="col-xs-2"></div>
            </div>
        <hr style="margin: 5px 0px">

        <div class="row">
            <div class="col-xs-6">
                <b>Categories Summary</b>
                <table>
                    @foreach ($cats as $name => $count)
                        <tr>
                            <td width="30px">{{ $count }}</td>
                            <td>{!! $name !!}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="col-xs-6">
                <table>
                    <tr>
                        <td width="30px"><b>#</b></td>
                        <td width="150px"><b>Supervisor</b></td>
                        <td width="80px"><b>Active</b></td>
                        <td width="80px"><b>Completed</b></td>
                        <td width="80px"><b>On Hold</b></td>
                    </tr>
                    @foreach ($supers as $name => $count)
                        <tr>
                            <td>{!! ($count[0] + $count[1] + $count[2]) !!}</td>
                            <td>{{ $name }}</td>
                            <td>{!! $count[0] !!}</td>
                            <td>{!! $count[1] !!}</td>
                            <td>{!! $count[2] !!}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5"><hr style="padding: 2px; margin: 2px 0px"></td>
                    </tr>
                    <tr><td></td>
                        <td></td>
                        <td>{{ ($mains->where('status', 1)->count() + $mains_old->where('status', 1)->count()) }}</td>
                        <td>{{ ($mains->where('status', 0)->count() + $mains_old->where('status', 0)->count()) }}</td>
                        <td>{{ ($mains->where('status', 3)->count() + $mains_old->where('status', 3)->count()) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="page"></div>
        <?php $row_count = 6; $page_count ++ ?>
            {{-- New Page - Show header --}}
            <h3 style="margin: 0px">Site Maintenance Executive Report</h3>
            <hr style="margin: 5px 0px">

        {{--Old  Maintenance Requests --}}
        <h4>Open Requests Older than 90 Days &nbsp;
            <small style="color: #999"> (#{{ $mains_old->count() }})</small>
        </h4>
        <hr style="margin: 5px 0px">
        <div class="row">
            <div class="col-xs-1">#</div>
            <div class="col-xs-1">Site</div>
            <div class="col-xs-2">Name</div>
            <div class="col-xs-2">Category</div>
            <div class="col-xs-1">Task Owner</div>
            <div class="col-xs-1">Reported Date</div>
            <div class="col-xs-1">Allocated Date</div>
            <div class="col-xs-1">Completed</div>
        </div>
        <hr style="margin: 5px 0px">

        @foreach ($mains_old as $main)
            <?php $row_count ++;?>
            <div class="row">
                <div class="col-xs-1">M{{ $main->code }}</div>
                <div class="col-xs-1">{{ $main->site->code }}</div>
                <div class="col-xs-2">{{ $main->site->name }}</div>
                <div class="col-xs-2">{{ ($main->category_id) ? \App\Models\Site\SiteMaintenanceCategory::find($main->category_id)->name : '-' }}</div>
                <div class="col-xs-1">{{ ($main->super_id) ? $main->taskOwner->name : 'Unassigned' }}</div>
                <div class="col-xs-1">{{ $main->reported->format('d/m/Y') }}</div>
                <div class="col-xs-1">{{ ($main->assigned_at) ? $main->assigned_at->format('d/m/Y') : '-' }}</div>
                <div class="col-xs-1">
                    @if ($main->status == 0)
                        {{  $main->updated_at->format('d/m/Y') }}
                    @else
                        {{ ($main->status && $main->status == 1) ? 'Active' : 'On Hold'  }}
                    @endif
                </div>
            </div>

            @if ($row_count > 28) {{-- New Page if no of lines exceed max --}}
            <div class="page"></div>
            <?php $row_count = 0; $page_count ++ ?>
            @endif
        @endforeach

        {{-- Maintenance Requests --}}
        <hr style="margin: 5px 0px">
        <h4>Requests Updated in Last 90 Days &nbsp;
            <small style="color: #999"> (#{{ $mains->count() }})</small>
        </h4>
        <hr style="margin: 5px 0px">
        <div class="row">
            <div class="col-xs-1">#</div>
            <div class="col-xs-1">Site</div>
            <div class="col-xs-2">Name</div>
            <div class="col-xs-2">Category</div>
            <div class="col-xs-1">Task Owner</div>
            <div class="col-xs-1">Reported Date</div>
            <div class="col-xs-1">Allocated Date</div>
            <div class="col-xs-1">Completed</div>
        </div>
        <hr style="margin: 5px 0px">

        @foreach ($mains as $main)
            @if ($row_count == 0)
                {{-- New Page - Show header --}}
                <h3 style="margin: 0px">Site Maintenance Executive Report</h3>
                <hr style="margin: 5px 0px">
                <div class="row">
                    <div class="col-xs-1">#</div>
                    <div class="col-xs-1">Site</div>
                    <div class="col-xs-2">Name</div>
                    <div class="col-xs-2">Category</div>
                    <div class="col-xs-1">Task Owner</div>
                    <div class="col-xs-1">Reported Date</div>
                    <div class="col-xs-1">Allocated Date</div>
                    <div class="col-xs-1">Completed</div>
                </div>
                <hr style="margin: 5px 0px">
                <?php $row_count ++ ?>
            @endif

            <?php $row_count ++;?>
            <div class="row">
                <div class="col-xs-1">M{{ $main->code }}</div>
                <div class="col-xs-1">{{ $main->site->code }}</div>
                <div class="col-xs-2">{{ $main->site->name }}</div>
                <div class="col-xs-2">{{ ($main->category_id) ? \App\Models\Site\SiteMaintenanceCategory::find($main->category_id)->name : '-' }}</div>
                <div class="col-xs-1">{{ ($main->super_id) ? $main->taskOwner->name : 'Unassigned' }}</div>
                <div class="col-xs-1">{{ $main->reported->format('d/m/Y') }}</div>
                <div class="col-xs-1">{{ ($main->assigned_at) ? $main->assigned_at->format('d/m/Y') : '-' }}</div>
                <div class="col-xs-1">
                    @if ($main->status == 0)
                        {{  $main->updated_at->format('d/m/Y') }}
                    @else
                        {{ ($main->status && $main->status == 1) ? 'Active' : 'On Hold'  }}
                    @endif
                </div>
            </div>

            @if ($row_count > 28) {{-- New Page if no of lines exceed max --}}
            <div class="page"></div>
            <?php $row_count = 0; $page_count ++ ?>
            @endif
        @endforeach
    </div>
</div>
</body>
</html>