{{-- @formatter:off --}}
@component('mail::message')

# Scaffold Handover Certificate

Please review and ensure the correct Plans are provided to Ian ASAP to complete the Scaffold Handover Certificate for this project {{ $report->site->name }}.

Regards,<br>
{{ config('app.name') }}
@endcomponent
