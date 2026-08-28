<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Maintenance Executive Report</title>
    <style>
        @page { size: A4 landscape; margin: 22mm 10mm 15mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #27313b; font-family: DejaVu Sans, sans-serif; font-size: 8.5px; line-height: 1.35; }
        .report-header { position: fixed; top: -15mm; left: 0; right: 0; width: 100%; border-collapse: collapse; }
        .report-header td { padding: 0 0 6px; vertical-align: bottom; border-bottom: 2px solid #46515f; }
        .report-title { font-size: 18px; font-weight: bold; }
        .report-date { width: 36%; color: #6f7982; text-align: right; white-space: nowrap; }
        .report-footer { position: fixed; bottom: -10mm; left: 0; right: 0; width: 100%; padding-top: 5px; color: #7b858d; font-size: 7.5px; border-top: 1px solid #aeb6bc; }
        .report-footer .page-number { float: right; }
        .report-footer .page-number:after { content: counter(page); }
        h2 { margin: 0 0 8px; color: #46515f; font-size: 13px; }
        .summary-heading { margin-bottom: 10px; color: #6f7982; font-size: 9px; }
        .stats-table { width: 100%; margin-bottom: 8px; border-collapse: collapse; table-layout: fixed; }
        .stats-table td { padding: 6px 8px; border: 1px solid #d7dde1; vertical-align: middle; }
        .stats-table .label { width: 31%; color: #5e6973; background: #f3f5f7; }
        .stats-table .value { width: 19%; color: #27313b; font-size: 10px; font-weight: bold; }
        .summary-note { margin: 6px 0 12px; padding: 7px 9px; color: #8c343a; background: #fff2f3; border-left: 3px solid #e7505a; }
        .summary-columns { width: 100%; border-collapse: separate; border-spacing: 10px 0; table-layout: fixed; }
        .summary-columns > tbody > tr > td { width: 50%; padding: 0; vertical-align: top; }
        .summary-panel { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .summary-panel caption { padding: 7px 8px; color: #fff; background: #46515f; font-size: 10px; font-weight: bold; text-align: left; }
        .summary-panel th { padding: 5px 6px; color: #46515f; background: #e9edf1; text-align: left; border-bottom: 1px solid #b8c0c6; }
        .summary-panel td { padding: 4px 6px; border-bottom: 1px solid #e0e4e7; vertical-align: top; }
        .summary-panel tbody tr:nth-child(even) td { background: #f8f9fa; }
        .summary-panel .number { width: 12%; text-align: right; white-space: nowrap; }
        .summary-panel .supervisor { width: 40%; }
        .summary-panel .total-row td { font-weight: bold; background: #edf4f9 !important; border-top: 1px solid #aeb8bf; }
        .detail-page { page-break-before: always; }
        .detail-section + .detail-section { margin-top: 14px; }
        .section-title { margin: 0; padding: 7px 9px; color: #fff; background: #46515f; font-size: 11px; font-weight: bold; page-break-after: avoid; }
        .section-title span { color: #dbe2e7; font-size: 8px; font-weight: normal; }
        .detail-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .detail-table thead { display: table-header-group; }
        .detail-table tr { page-break-inside: avoid; }
        .detail-table th { padding: 6px 5px; color: #46515f; background: #e9edf1; font-size: 7.5px; text-align: left; text-transform: uppercase; border-bottom: 1px solid #aeb8bf; }
        .detail-table td { padding: 6px 5px; vertical-align: top; border-bottom: 1px solid #dce1e4; overflow-wrap: break-word; word-wrap: break-word; }
        .detail-table tbody tr:nth-child(even) td { background: #f8f9fa; }
        .request { width: 7%; font-weight: bold; white-space: nowrap; }
        .job { width: 7%; white-space: nowrap; }
        .site { width: 25%; }
        .category { width: 16%; }
        .owner { width: 15%; }
        .reported, .allocated, .result { width: 10%; white-space: nowrap; }
        .empty-state { padding: 14px; color: #68737d; background: #f7f8f9; border: 1px solid #d9dde1; text-align: center; }
    </style>
</head>

<body>
@php
    $allRequests = $mains->concat($mains_old);
    $categoryNames = \App\Models\Site\SiteMaintenanceCategory::query()
        ->whereIn('id', $allRequests->pluck('category_id')->filter()->unique()->values())
        ->pluck('name', 'id');
    $uniqueSiteCount = $allRequests->pluck('site_id')->filter()->unique()->count();
@endphp

<table class="report-header">
    <tr>
        <td class="report-title">Site Maintenance Executive Report</td>
        <td class="report-date">Generated {{ date('d/m/Y') }}</td>
    </tr>
</table>

<div class="report-footer">
    <span>Document created {{ date('d/m/Y') }}</span>
    <span class="page-number">Page </span>
</div>

<h2>Executive summary</h2>
<div class="summary-heading">{{ $from->format('d M Y') }} to {{ $to->format('d M Y') }} ({{ $from->diff($to)->days }} days)</div>

<table class="stats-table">
    <tr>
        <td class="label">Total requests</td><td class="value">{{ $allRequests->count() }}</td>
        <td class="label">New requests</td><td class="value">{{ $mains_created->count() }}</td>
    </tr>
    <tr>
        <td class="label">Average days to allocate</td><td class="value">{{ $avg_allocated }}</td>
        <td class="label">Unique sites</td><td class="value">{{ $uniqueSiteCount }}</td>
    </tr>
    <tr>
        <td class="label">Average days to contact client</td><td class="value">{{ $avg_contacted }}</td>
        <td class="label">Average days from appointment to completion</td><td class="value">{{ $avg_appoint }}</td>
    </tr>
    <tr>
        <td class="label">Average days to complete request</td><td class="value">{{ $avg_completed }}</td>
        <td class="label">Requests excluded from averages</td><td class="value">{{ $excluded }}</td>
    </tr>
</table>

<div class="summary-note">Average timing statistics are calculated from requests created after 1 May and exclude {{ $excluded }} earlier request{{ $excluded == 1 ? '' : 's' }}.</div>

<table class="summary-columns">
    <tr>
        <td>
            <table class="summary-panel">
                <caption>Categories summary</caption>
                <thead><tr><th>Category</th><th class="number">Total</th></tr></thead>
                <tbody>
                @forelse($cats as $name => $count)
                    <tr><td>{{ $name }}</td><td class="number">{{ $count }}</td></tr>
                @empty
                    <tr><td colspan="2">No category data available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </td>
        <td>
            <table class="summary-panel">
                <caption>Supervisor summary</caption>
                <thead><tr><th class="number">Total</th><th class="supervisor">Supervisor</th><th class="number">Active</th><th class="number">Completed</th><th class="number">On hold</th></tr></thead>
                <tbody>
                @forelse($supers as $name => $count)
                    <tr>
                        <td class="number">{{ $count[0] + $count[1] + $count[2] }}</td>
                        <td>{{ $name }}</td>
                        <td class="number">{{ $count[0] }}</td>
                        <td class="number">{{ $count[1] }}</td>
                        <td class="number">{{ $count[2] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No supervisor data available.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td class="number">{{ $allRequests->count() }}</td>
                    <td>Total</td>
                    <td class="number">{{ $allRequests->where('status', 1)->count() }}</td>
                    <td class="number">{{ $allRequests->where('status', 0)->count() }}</td>
                    <td class="number">{{ $allRequests->where('status', 3)->count() }}</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<div class="detail-page">
    @foreach([
        ['title' => 'Open requests older than 90 days', 'requests' => $mains_old],
        ['title' => 'Requests updated in the last 90 days', 'requests' => $mains],
    ] as $section)
        <div class="detail-section">
            <div class="section-title">{{ $section['title'] }} <span>({{ $section['requests']->count() }} requests)</span></div>
            @if($section['requests']->count())
                <table class="detail-table">
                    <thead>
                    <tr>
                        <th class="request">Request</th>
                        <th class="job">Job</th>
                        <th class="site">Site</th>
                        <th class="category">Category</th>
                        <th class="owner">Task owner</th>
                        <th class="reported">Reported</th>
                        <th class="allocated">Allocated</th>
                        <th class="result">Completed/status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($section['requests'] as $main)
                        <tr>
                            <td class="request">M{{ $main->code }}</td>
                            <td class="job">{{ $main->site?->code ?? '-' }}</td>
                            <td class="site">{{ $main->site?->name ?? '-' }}</td>
                            <td class="category">{{ $categoryNames[$main->category_id] ?? '-' }}</td>
                            <td class="owner">{{ $main->taskOwner?->name ?? '-' }}</td>
                            <td class="reported">{{ $main->reported?->format('d/m/Y') ?? '-' }}</td>
                            <td class="allocated">{{ $main->assigned_super_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="result">
                                @if($main->status == 0)
                                    {{ $main->updated_at?->format('d/m/Y') ?? '-' }}
                                @elseif($main->status == 1)
                                    Active
                                @else
                                    On Hold
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No maintenance requests in this section.</div>
            @endif
        </div>
    @endforeach
</div>
</body>
</html>
