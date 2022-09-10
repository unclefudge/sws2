@component('mail::message')

# Contract Time Extensions Report

Please find attached the Contract Time Extensions Report for week of {{ $report->date->format('d/m/Y') }}.

@component('mail::button', ['url' => config('app.url').'/site/extension/'.$report->id])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
