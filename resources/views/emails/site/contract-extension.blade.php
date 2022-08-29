@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Contract Time Extension</h1>
            <p>Please find attached a report for Contract Time Extension.</p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="200" style="border: 1px solid">Site</td>
                    <td width="60" style="border: 1px solid">Prac Completion</td>
                    <td width="200" style="border: 1px solid">Extend Reasons</td>
                    <th width="400" style="border: 1px solid">Extend Notes</th>
                </tr>
                @foreach($data as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['name'] }}</td>
                        <td style="border: 1px solid">{{ $row['prac_completion'] }}</td>
                        <td style="border: 1px solid">{{ $row['extend_reasons'] }}</td>
                        <td style="border: 1px solid">{!! nl2br($row['notes']) !!}</td>
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