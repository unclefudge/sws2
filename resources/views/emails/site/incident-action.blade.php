@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Incident Updated

A incident has been updated for {{ $incident->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $incident->id  }} |
| **Site Name**  | {{ $incident->site->name  }} |
| **Site Address**  | {{ $incident->site->address }}, {{ $incident->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $incident->site->supervisorsSBC() }} |
| **Actions Taken**  | {{ $action->action }} |
| **Submitted by**  | {{ $action->user->name }} ({{ $action->user->company->name }}) |
| **Submitted at**  | {{ $action->created_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/incident/'.$incident->id])
View Incident
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
