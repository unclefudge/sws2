<h3>Upcoming Job Start Dates</h3>
<table style="border: 1px solid; border-collapse: collapse">
    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
        <td width="80" style="border: 1px solid">Date</td>
        <td width="200" style="border: 1px solid">Site</td>
        <td width="120" style="border: 1px solid">Supervisor</td>
        <td width="200" style="border: 1px solid">Company</td>
    </tr>
    @foreach($startdata as $row)
        <tr>
            <td style="border: 1px solid">{{ $row['date'] }}</td>
            <td style="border: 1px solid">{{ $row['name'] }}</td>
            <td style="border: 1px solid">{{ $row['supervisor'] }}</td>
            <td style="border: 1px solid">{{ $row['company'] }}</td>
        </tr>
    @endforeach
</table>
<br>
<hr>
<p>This email has been generated on behalf of {{ $user_company_name }}</p>
