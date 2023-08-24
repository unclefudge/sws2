@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Accident Updated

A accident has been updated for {{ $accident->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $accident->id  }} |
| **Site Name**  | {{ $accident->site->name  }} |
| **Site Address**  | {{ $accident->site->address }}, {{ $accident->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $accident->site->supervisorName }} |
| **Actions Taken**  | {!! nl2br2($action->action); !!} |
| **Submitted by**  | {{ $action->user->name }} ({{ $action->user->company->name }}) |
| **Submitted at**  | {{ $action->created_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/accident/'.$accident->id])
View Accident
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
