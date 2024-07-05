{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Practical Completion Notification

A practical completion has been assigned for {{ $prac->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $prac->id  }} |
| **Site Name**  | {{ $prac->site->name  }} |
| **Site Address**  | {{ $prac->site->address }}, {{ $prac->site->SuburbStatePostcode }} |
| **Assigned Supervisor**  | {{ ($prac->super_id) ? $prac->supervisor->name : '-' }} |

@component('mail::button', ['url' => config('app.url').'/site/prac-completion/'.$prac->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
