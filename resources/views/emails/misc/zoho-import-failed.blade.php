@component('mail::message')
# Zoho Import Failed

The Zoho import failed

{{ $mesg }}

Regards,<br>
{{ config('app.name') }}
@endcomponent