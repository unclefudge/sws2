{{-- @formatter:off --}}
@component('mail::message')

# URGENT - Contract Time Extensions

Please complete the Contract Time Extensions for week of {{ $report->date->format('d/m/Y') }} ASAP.

The following sites are yet to be completed
{!! nl2br($site_list) !!}

@component('mail::button', ['url' => config('app.url').'/site/extension'])
View Contract Extensions
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
