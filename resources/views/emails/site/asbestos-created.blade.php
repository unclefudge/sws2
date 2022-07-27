@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Asbestos Notification

A asbestos notification has been lodged for {{ $asbestos->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $asbestos->id  }} |
| **Site Name**  | {{ $asbestos->site->name  }} |
| **Site Address**  | {{ $asbestos->site->address }}, {{ $asbestos->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $asbestos->site->supervisorsSBC() }} |
| **Removal dates**  | {{ $asbestos->date_from->format('d/m/Y') }} to {{ $asbestos->date_to->format('d/m/Y') }} |
| **Amount**  | {{ $asbestos->amount }} |
| **Class**  | {{ ($asbestos->friable) ? 'Class A (Friable)' : 'Class B (Non-Friable)' }} |
| **Type**  | {{ $asbestos->type }} |
| **Location**  | {{ $asbestos->location }} |
| **Submitted by**  | {{ $asbestos->createdBy->name }} ({{ $asbestos->createdBy->company->name }}) |
| **Submitted at**  | {{ $asbestos->created_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/asbestos/notification'.$asbestos->id])
View Notification
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent