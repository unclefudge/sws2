{{-- @formatter:off --}}
@component('mail::message')

# URGENT - Contract Time Extensions

{!! nl2br($message) !!}

@component('mail::button', ['url' => config('app.url').'/site/extension'])
View Contract Extensions
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
