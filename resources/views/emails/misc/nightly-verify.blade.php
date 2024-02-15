{{-- @formatter:off --}}
@component('mail::message')
# Nightly Job

The nightly job {{ $status }}

Regards,
{{ config('app.name') }}
@endcomponent