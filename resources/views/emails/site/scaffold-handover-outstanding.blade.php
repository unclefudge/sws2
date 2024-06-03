{{-- @formatter:off --}}
@component('mail::message')

# Scaffold Handover Certificate Outstanding

The following Scaffold Handover Certificates are outstanding:

@foreach ($outstanding as $id => $name)
    {{$name}}
@endforeach

Regards,<br>
{{ config('app.name') }}
@endcomponent
