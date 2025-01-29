<h3>Safe Work Method Statement Sign Off Request</h3>
<p>The following Safe Work Method Statement is requesting sign off by {{ $user_fullname }} ({{ $user_company_name }})</p>
<table style="border: none">
    <tr>
        <td width="150" style="text-align: right"><b>Activity / Task</b></td>
        <td width="20">&nbsp;</td>
        <td>{{ $doc_name }}</td>
    </tr>
    <tr>
        <td style="text-align: right"><b>Company</b></td>
        <td>&nbsp;</td>
        <td>{{ $doc_company }}</td>
    </tr>
    <tr>
        <td style="text-align: right"><b>Principal Contractor</b></td>
        <td>&nbsp;</td>
        <td>{{ $doc_principle }}</td>
    </tr>
    <tr>
        <td style="text-align: right">&nbsp;</td>
        <td>&nbsp;</td>
        <td><a href="{{ $url }}">View SWMS</a></td>
    </tr>
</table>
<p><br><br>Regards,</p>
<p>{{ $user_fullname }}</p>

<hr>
<p>This email has been generated on behalf of {{ $user_company_name }}</p>
