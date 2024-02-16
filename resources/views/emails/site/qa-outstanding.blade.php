{{-- @formatter:off --}}
@component('mail::message')
# Outstanding QA Checklists

Please find attached a report for {{ $qas->count() }} Outstanding QA's.

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent