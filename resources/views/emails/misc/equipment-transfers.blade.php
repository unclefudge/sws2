{{-- @formatter:off --}}
@component('mail::message')
# Equipment Transfers for Last 7 Days

Please find attached a report for {{ $log->count() }} equipment transfers within the last 7 days.

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent