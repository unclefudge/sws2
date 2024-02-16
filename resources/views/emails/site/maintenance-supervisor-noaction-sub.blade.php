{{-- @formatter:off --}}
@component('mail::message')
# Maintenance Requests Without Appointment or Action in 14 days

{!! $body !!}

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent