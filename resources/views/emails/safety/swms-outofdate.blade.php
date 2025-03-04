{{-- @formatter:off --}}
@component('mail::message')
# Safe Work Method Statements {{ ($outofdate == 'verify') ? 'Verification' : 'Out of Date' }}

<b>{{ $company->name }}</b>

Safe Work Method Statements are required to work on Cape Cod building sites.

@if ($outofdate == 'verify')
Please verify your SWMS are up to date and current.
@elseif ($outofdate == 'none')
You currently haven't completed any and are required to do so.
@else
The following SWMS are Out of Date and need to be updated:

{!! nl2br($outofdate) !!}
@endif

@component('mail::button', ['url' => config('app.url').'/safety/doc/wms/'])
View SWMS
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
