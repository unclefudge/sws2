{{-- @formatter:off --}}
@component('mail::message')

# Scaffold Handover Certificate

Please find attached a copy of the Scaffold Handover Certificate for {{ $report->site->name }}.

Regards,<br>
{{ config('app.name') }}
@endcomponent
