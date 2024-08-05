{{-- @formatter:off --}}
@component('mail::message')

# Scaffold Handover Certificate Outstanding

The following Scaffold Handover Certificates are outstanding:

@foreach ($outstanding as $id => $array)
    Due: {{$array['due_at']}} - {{$array['name']}}
@endforeach

Regards,<br>
{{ config('app.name') }}
@endcomponent
