{{-- @formatter:off --}}
@component('mail::message')

# URGENT - Site Shutdowns

Please complete the following Site Shutdowns ASAP.

{!! nl2br($site_list) !!}

@component('mail::button', ['url' => config('app.url').'/site/shutdown'])
View Site Shutdowns
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
