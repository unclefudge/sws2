@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Upcoming Jobs Compliance Data</h1>
            <p>Please find attached a report for Upcoming Jobs.</p>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <td width="60" style="border: 1px solid">Date</td>
                    <td width="200" style="border: 1px solid">Site</td>
                    <td width="120" style="border: 1px solid">Supervisor</td>
                    <td width="200" style="border: 1px solid">Company</td>
                    <th width="200" style="border: 1px solid">CC</th>
                    <th width="200" style="border: 1px solid">FC Plans</th>
                    <th width="200" style="border: 1px solid">FC Structural</th>
                </tr>
                @foreach($startdata as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['date'] }}</td>
                        <td style="border: 1px solid">{{ $row['name'] }}</td>
                        <td style="border: 1px solid">{{ $row['supervisor'] }}</td>
                        <td style="border: 1px solid">{{ $row['company'] }}</td>
                        <td style="border: 1px solid; {{ ($row['cc_stage']) ? 'background:'.$settings_colours[$row['cc_stage']] : '' }}">{!! $row['cc'] !!}</td>
                        <td style="border: 1px solid; {{ ($row['fc_plans_stage']) ? 'background:'.$settings_colours[$row['fc_plans_stage']] : '' }}">{!! $row['fc_plans'] !!}</td>
                        <td style="border: 1px solid; {{ ($row['fc_struct_stage']) ? 'background:'.$settings_colours[$row['fc_struct_stage']] : '' }}">{!! $row['fc_struct'] !!}</td>
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