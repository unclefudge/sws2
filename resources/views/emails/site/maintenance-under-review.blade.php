@component('mail::message')
# Maintenance Requests Under Review

Please find attached a report for {{ $mains->count() }} currently Under Review.

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent