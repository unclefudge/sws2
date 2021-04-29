@component('mail::message')
# On Hold QA Checklists

Please find attached a report for {{ $qas->count() }} currently On Hold QA's.

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent