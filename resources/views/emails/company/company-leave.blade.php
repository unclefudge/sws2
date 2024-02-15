{{-- @formatter:off --}}
@component('mail::message')
# Company Leave

{{ $company->name }} has {{ $action }} leave.

|        |        |
| ------:|--------|
| **Name**  | {{ $company->name  }} |
| **Phone**  | {{ $company->phone  }} |
| **Primary Contact**  | {{ $company->primary_contact()->fullname  }} @if ($company->phone) ({{ $company->phone  }}) @endif |


----

Current leave:

|  Dates  | Reason  |
| --------|---------|
@foreach ($company->leave()->whereDate('to', '>', date('Y-m-d'))->get() as $leave)
| {{ $leave->from->format('M j') }} - {!! ($leave->from->format('M') == $leave->to->format('M')) ? $leave->to->format('j') : $leave->to->format('M j') !!}  | {{ $leave->notes }} |
@endforeach


@component('mail::button', ['url' => config('app.url').'/company/leave'])
View Company Leave
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
