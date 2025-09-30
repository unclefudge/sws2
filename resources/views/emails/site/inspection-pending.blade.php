@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            The following inspection reports are currently pending signoff or not with client<br><br>
            <h1>Electrical Inspection Reports</h1>
            <b>Admin Review</b><br>
            @if (count($elPendingAdmin))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($elPendingAdmin as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif
            <br><b>Technical Review</b><br>
            @if (count($elPendingTech))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($elPendingTech as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif
            <br><b>Not With Client</b><br>
            @if (count($elClientNot))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($elClientNot as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif
            <br><br>

            <h1>Plumbing Inspection Reports</h1>
            <b>Admin Review</b><br>
            @if (count($plPendingAdmin))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($plPendingAdmin as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif
            <br><b>Technical Review</b><br>
            @if (count($plPendingTech))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($plPendingTech as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif
            <br><b>Not With Client</b><br>
            @if (count($plClientNot))
                <table style="width:100%; border: 1px solid; border-collapse: collapse">
                    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                        <td style="width:60" style="border: 1px solid">Created</td>
                        <td style="width:200" style="border: 1px solid">Site</td>
                        <td style="width:120" style="border: 1px solid">Assigned</td>
                        <td style="border: 1px solid">Assigned to</td>
                    </tr>
                    @foreach($plClientNot as $report)
                        <tr>
                            <td style="border: 1px solid">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td style="border: 1px solid">{{ $report->site->name }}</td>
                            <td style="border: 1px solid">{{ ($report->assigned_at) ? $report->assigned_at->format('d/m/Y') : '-' }}</td>
                            <td style="border: 1px solid">{{ ($report->assignedTo) ? $report->assignedTo->name : '-' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                None<br>
            @endif

            <br>
            <hr>
            <p>This email has been generated on behalf of Cape Cod</p>

            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')