{{-- @formatter:off --}}
@component('mail::message')
# No Works Planned for next 14 days

@foreach ($report as $row)
**{{ $row['supervisor']->name}}**
@foreach ($row['sites'] as $site)
- {{$site->name}}
@endforeach


@endforeach

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent