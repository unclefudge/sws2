@component('mail::message')
# Missing Company Information

<table style="border: 1px solid; border-collapse: collapse">
    <tr style="border: 1px solid; background-color: #f0f6fa; font-weight: bold;">
        <td width="200" style="border: 1px solid">Company</td>
        <td width="200" style="border: 1px solid">Missing Info / Document</td>
        <td width="110" style="border: 1px solid">Expiry / Last Updated</td>
    </tr>
    @foreach($companies as $row)
        <tr>
            <td style="border: 1px solid">{!! $row[0] !!}</td>
            <td style="border: 1px solid">{!! $row[1] !!}</td>
            <td style="border: 1px solid">{{ $row[2] }}</td>
        </tr>
    @endforeach
</table>

@component('mail::button', ['url' => config('app.url').'/manage/report/missing_company_info'])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
