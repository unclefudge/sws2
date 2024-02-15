{{-- @formatter:off --}}
@component('mail::message')
# Zoho Missing Data

{{ $mesg }}

---

Regards,
{{ config('app.name') }}
@endcomponent