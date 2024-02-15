{{-- @formatter:off --}}
@component('mail::message')
# Toolbox Talk Sign Off Request

The Toolbox Talk **{{ $talk->name }}** created by {{ $talk->createdBy->fullname }} ({{ $talk->createdBy->company->name }}) requires sign off.

Please review the Toolbox Talk and sign off.

@component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox2/'.$talk->id])
View Talk
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
