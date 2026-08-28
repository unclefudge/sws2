<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Under Review</title>
    <style>
        @page {
            margin: 28mm 12mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #222;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.35;
        }

        .report-header {
            position: fixed;
            top: -18mm;
            left: 0;
            right: 0;
            width: 100%;
            border-collapse: collapse;
        }

        .report-header td {
            padding: 0 0 7px;
            vertical-align: bottom;
            border-bottom: 2px solid #333;
        }

        .report-title {
            font-size: 19px;
            font-weight: bold;
        }

        .report-date {
            width: 35%;
            color: #666;
            font-size: 9px;
            text-align: right;
            white-space: nowrap;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table th {
            padding: 7px 6px;
            color: #333;
            background: #e9edf1;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            border-top: 1px solid #b9c0c7;
            border-bottom: 1px solid #9ca4ac;
        }

        .report-table td {
            padding: 7px 6px;
            vertical-align: top;
            border-bottom: 1px solid #d9dde1;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .report-table tbody tr:nth-child(even) td {
            background: #f7f8f9;
        }

        .code {
            width: 10%;
            font-weight: bold;
            white-space: nowrap;
        }

        .reported {
            width: 14%;
            white-space: nowrap;
        }

        .site {
            width: 36%;
        }

        .supervisor {
            width: 24%;
        }

        .updated {
            width: 16%;
            white-space: nowrap;
        }

        .empty-state {
            padding: 18px;
            color: #555;
            background: #f7f8f9;
            border: 1px solid #d9dde1;
            text-align: center;
        }
    </style>
</head>

<body>
<table class="report-header">
    <tr>
        <td class="report-title">Maintenance Under Review</td>
        <td class="report-date">Report generated {{ $today->format('d/m/Y') }}</td>
    </tr>
</table>

@if ($mains->count())
    <table class="report-table">
        <thead>
        <tr>
            <th class="code">#</th>
            <th class="reported">Reported</th>
            <th class="site">Site</th>
            <th class="supervisor">Supervisor</th>
            <th class="updated">Updated</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($mains as $main)
            @php $lastAction = $main->lastAction(); @endphp
            <tr>
                <td class="code">M{{ $main->code }}</td>
                <td class="reported">{{ $main->created_at->format('d/m/Y') }}</td>
                <td class="site">{{ $main->site?->name ?? '-' }}</td>
                <td class="supervisor">{{ $main->taskOwner?->name ?? '-' }}</td>
                <td class="updated">{{ ($lastAction?->updated_at ?? $main->created_at)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div class="empty-state">There are currently no maintenance requests under review.</div>
@endif
</body>
</html>
