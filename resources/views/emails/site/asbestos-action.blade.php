@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Asbestos Notification Update

A asbestos notification has been updated for {{ $asbestos->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $asbestos->id  }} |
| **Site Name**  | {{ $asbestos->site->name  }} |
| **Site Address**  | {{ $asbestos->site->address }}, {{ $asbestos->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $asbestos->site->supervisorName }} |
| **Actions Taken**  | {{ $action->action }} |
| **Submitted by**  | {{ $action->user->name }} ({{ $action->user->company->name }}) |
| **Submitted at**  | {{ $action->created_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/asbestos/notification/'.$asbestos->id])
View Notification
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
