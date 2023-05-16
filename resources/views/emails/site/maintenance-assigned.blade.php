@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Maintenance Request Notification

A maintenance request has been assigned for {{ $main->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $main->code  }} |
| **Site Name**  | {{ $main->site->name  }} |
| **Site Address**  | {{ $main->site->address }}, {{ $main->site->SuburbStatePostcode }} |
| **Assigned to**  | {{ ($main->assignedTo) ? $main->assignedTo->name : 'N/A' }} |



@component('mail::button', ['url' => config('app.url').'/site/maintenance/'.$main->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
