{{-- @formatter:off --}}
@component('mail::message')
# Safe Work Method Statement Sign Off Request

The Safe Work Method Statement **{{ $swms->name }}** created by {{ $swms->createdBy->fullname }} ({{ $swms->createdBy->company->name }}) requires sign off.

Please review the Safe Work Method Statement and sign off.

@component('mail::button', ['url' => config('app.url').'/safety/doc/swms/'.$talk->id])
View SWMS
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
