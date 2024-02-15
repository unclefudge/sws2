{{-- @formatter:off --}}
@component('mail::message')
# New Toolbox Talk Template

The Toolbox Talk Template **{{ $talk->name }}** (ID: {{ $talk->id }}) created by {{ $talk->createdBy->fullname }} ({{ $talk->createdBy->company->name }}) has been made ACTIVE.

The Toolbox Talk has been assign to the following user(s):
{!! $talk->assignedToSBC() !!}

@component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox2/'.$talk->id])
View Talk
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent

