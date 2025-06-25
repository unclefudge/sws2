{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# FOC Requirements Notification

A FOC Requirements has been assigned for {{ $foc->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $foc->id  }} |
| **Site Name**  | {{ $foc->site->name  }} |
| **Site Address**  | {{ $foc->site->address }}, {{ $foc->site->SuburbStatePostcode }} |
| **Assigned Supervisor**  | {{ ($foc->super_id) ? $foc->supervisor->name : '-' }} |

@component('mail::button', ['url' => config('app.url').'/site/foc/'.$foc->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
