{{-- @formatter:off --}}
@component('mail::message')
# Practical Completion With No Action in 14 days

{!! $body !!}

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent