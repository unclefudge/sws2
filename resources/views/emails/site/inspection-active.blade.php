@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Open Inspection Reports</h1>
            <h3>Electrical Inspection Reports</h3>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="60" style="border: 1px solid">Created</td>
                    <td width="200" style="border: 1px solid">Site</td>
                    <td width="120" style="border: 1px solid">Assigned</td>
                    <td style="border: 1px solid">Assigned to</td>
                </tr>
                @foreach($electrical as $report)
                    <tr>
                        <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $report->site->name }}</td>
                        <td style="border: 1px solid">{{ $report->assigned_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $report->assignedTo->name }}</td>
                    </tr>
                @endforeach
            </table>
            <br><br>
            <h3>Plumbing Inspection Reports</h3>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="60" style="border: 1px solid">Created</td>
                    <td width="200" style="border: 1px solid">Site</td>
                    <td width="120" style="border: 1px solid">Assigned</td>
                    <td style="border: 1px solid">Assigned to</td>
                </tr>
                @foreach($plumbing as $report)
                    <tr>
                        <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $report->site->name }}</td>
                        <td style="border: 1px solid">{{ $report->assigned_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $report->assignedTo->name }}</td>
                    </tr>
                @endforeach
            </table>
            <br>
            <hr>
            <p>This email has been generated on behalf of Cape Cod</p>

            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')