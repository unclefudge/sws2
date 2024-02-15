{{-- @formatter:off --}}
@component('mail::message')
# Zoho Import Failed

The Zoho import failed

{{ $mesg }}

Regards,
{{ config('app.name') }}
@endcomponent