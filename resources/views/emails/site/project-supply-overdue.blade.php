{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Project Supply Overdue

The following {!! count($projsupply) !!} Project Supply details are 2 weeks overdue.

@foreach ($projsupply as $proj)
- {{ $proj->site->name }}
@endforeach

Regards,<br>
{{ config('app.name') }}
@endcomponent
