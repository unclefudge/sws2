{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Project Supply Overdue

The following Project Supply details are 2 weeks overdue.

<b>Lockup Stage</b><br>
@foreach ($lock as $proj)
- {{ $proj->site->name }} {!! ($proj->lockupDate && !$proj->pracCompleteDate) ? " - Lockup: $proj->lockupDate" : ""  !!} {!! ($proj->pracCompleteDate) ? " - Prac: $proj->pracCompleteDate" : ""  !!}
@endforeach


<b>Prac Completion</b><br>
@foreach ($prac as $proj)
- {{ $proj->site->name }} - Prac: {{ $proj->pracCompleteDate }}
@endforeach


Regards,<br>
{{ config('app.name') }}
@endcomponent
