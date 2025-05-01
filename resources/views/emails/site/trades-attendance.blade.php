{{-- @formatter:off --}}
@component('mail::message')
# Monthly Trades Attendance

Attendance reports are attached for all companies that attended a site.

The following companies did not attend (or didn't sign-in) to any site this month.

@foreach ($non_attendance as $company)
- {{ $company }}
@endforeach

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent