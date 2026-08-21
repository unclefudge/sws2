<div @if ($hasPending) wire:poll.4s @endif>
    <h4 style="margin:0 0 12px">Reports created in the last 10 days</h4>

    @if ($reports->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-bordered table-nohover order-column report-table">
                <thead>
                <tr class="mytable-header">
                    <th>Report</th>
                    <th style="width:20%">Status</th>
                    <th style="width:20%">Date</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($reports as $report)
                    <tr wire:key="recent-report-{{ $report->id }}">
                        <td>
                            @if ($report->status === 'completed')
                                <a href="/reports/{{ $report->id }}" target="_blank">{{ $report->name }}</a>
                            @else
                                {{ $report->name }}
                            @endif
                        </td>
                        <td>
                            @if ($report->status === 'pending')
                                <span class="font-grey-salsa"><i class="fa fa-spinner fa-pulse"></i> Pending</span>
                            @elseif ($report->status === 'processing')
                                <span class="font-yellow-gold"><i class="fa fa-spinner fa-pulse"></i> Processing…</span>
                            @elseif ($report->status === 'completed')
                                <span class="font-green">Ready</span>
                            @elseif ($report->status === 'failed')
                                <span class="font-red">Failed</span>
                            @else
                                {{ ucfirst($report->status) }}
                            @endif
                        </td>
                        <td>{{ $report->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="font-grey-silver">No reports.</div>
    @endif
</div>
