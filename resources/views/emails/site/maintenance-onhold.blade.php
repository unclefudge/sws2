@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Maintenance Requests currently ON HOLD</h1>
            <p></p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="60" style="border: 1px solid">#</td>
                    <td width="80" style="border: 1px solid">Reported</td>
                    <td width="60" style="border: 1px solid">Job #</td>
                    <td width="200" style="border: 1px solid">Site Name</td>
                    <td width="150" style="border: 1px solid">Task Owner</td>
                    <td width="80" style="border: 1px solid">Hold Date</td>
                    <td style="border: 1px solid">Last Note</td>
                </tr>
                @foreach($data as $main)
                    <tr>
                        <td style="border: 1px solid">M{{ $main->code }}</td>
                        <td style="border: 1px solid">{{ $main->reported->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $main->site->code }}</td>
                        <td style="border: 1px solid">{{ $main->site->name }}</td>
                        <td style="border: 1px solid">{{ $main->taskOwner->name }}</td>
                        <td style="border: 1px solid">{{ ($main->lastAction()) ? $main->lastAction()->updated_at->format('d/m/Y') : $main->created_at->format('d/m/Y') }}</td>
                        <td style="border: 1px solid">{{ $main->lastActionNote() }}</td>
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