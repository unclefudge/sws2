@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Incident Notification

A incident report has been lodged for {{ $incident->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $incident->id  }} |
| **Site Name**  | {{ $incident->site->name  }} |
| **Site Address**  | {{ $incident->site->address }}, {{ $incident->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $incident->supervisor  }} |
| **Date/Time**  | {{ $incident->date->format('d/m/Y g:i a') }} |
| **Description**  | {{ $incident->describe  }} |
| **Submitted by**  | {{ $incident->createdBy->name }} ({{ $accident->createdBy->company->name }}) |
| **Submitted at**  | {{ $incident->created_at->format('d/m/Y') }} |

@component('mail::button', ['url' => config('app.url').'/site/incident/'.$incident->id])
View Incident
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
