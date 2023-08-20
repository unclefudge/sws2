@component('mail::message')

# Contract Time Extensions Report

Please find attached the Contract Time Extensions Report for week of {{ $report->date->format('d/m/Y') }} to be processed.

@component('mail::button', ['url' => config('app.url').'/site/extension'])
View Contract Extensions
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
