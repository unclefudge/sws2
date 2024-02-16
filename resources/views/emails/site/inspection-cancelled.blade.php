{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Canceled Plumbing/Electrical Inspections

**{{ $site->name }}** has recently been put on hold/cancelled and had the following open Inspections scheduled:

{!! nl2br($cancelled) !!}

Please ensure relevant persons are made aware.

Regards,<br>
{{ config('app.name') }}
@endcomponent
