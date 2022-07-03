@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Inspection Report Completed</h1>
            <p>A inspection report has been completed for {{ $report->site->name }}.</p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="60" style="border: 1px solid">Date</td>
                    <td width="200" style="border: 1px solid">Details</td>
                    <td width="120" style="border: 1px solid">Name</td>
                </tr>
                @foreach($report->actions->sortByDesc('created_at') as $action)
                    <tr>
                        <td style="border: 1px solid">{{ $action->created_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $action->action }}</td>
                        <td style="border: 1px solid">{{ $action->user->full_name }}</td>
                    </tr>
                @endforeach
            </table>

            <a class="btn blue" href="{{ config('app.url') }}/site/inspection/plumbing/{{ $report->id }}">View Report</a>

            <br>
            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')
