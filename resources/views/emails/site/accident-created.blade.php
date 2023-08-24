@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Accident Notification

A accident report has been lodged for {{ $accident->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $accident->id  }} |
| **Site Name**  | {{ $accident->site->name  }} |
| **Site Address**  | {{ $accident->site->address }}, {{ $accident->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $accident->supervisor  }} |
| **Date/Time**  | {{ $accident->date->format('d/m/Y g:i a') }} |
| **Worker**  | {{ $accident->name }} (age: {{ $accident->age }}) |
| **Occupation**  | {{ $accident->occupation  }} |
| **Location**  | {!! nl2br2($accident->location)  !!} |
| **Nature**  | {!! nl2br2($accident->nature)  !!} |
| **Referred to**  | {{ $accident->referred  }} |
| **Damage**  | {{ $accident->damage  }} |
| **Description**  | {!! nl2br2($accident->info)  !!} |
| **Submitted by**  | {{ $accident->createdBy->name }} ({{ $accident->createdBy->company->name }}) |
| **Submitted at**  | {{ $accident->created_at->format('d/m/Y') }} |

@component('mail::button', ['url' => config('app.url').'/site/accident/'.$accident->id])
View Accident
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
