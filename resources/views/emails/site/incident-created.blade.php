{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Incident Notification

A incident report has been lodged for {{ $incident->site_name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $incident->id  }} |
| **Site Name**  | {{ $incident->site_name  }} |
| **Supervisor**  | {{ $incident->site_supervisor  }} |
| **Date/Time**  | {{ $incident->date->format('d/m/Y g:i a') }} |
| **Description**  | {!! nl2br2($incident->describe)  !!} |
| **Submitted by**  | {{ $incident->createdBy->name }} ({{ $incident->createdBy->company->name }}) |
| **Submitted at**  | {{ $incident->created_at->format('d/m/Y') }} |

@component('mail::button', ['url' => config('app.url').'/site/incident/'.$incident->id])
View Incident
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
