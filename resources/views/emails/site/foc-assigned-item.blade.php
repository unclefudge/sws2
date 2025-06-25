{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# FOC Requirements Notification

A FOC Requirements has been assigned for {{ $item->foc->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $item->foc->id  }} |
| **Site Name**  | {{ $item->foc->site->name  }} |
| **Site Address**  | {{ $item->foc->site->address }}, {{ $item->foc->site->SuburbStatePostcode }} |
| **Assigned Supervisor**  | {{ ($item->foc->super_id) ? $item->foc->supervisor->name : '-' }} |
| **Assigned Task(s)**  | {{ ($item->assigned_to) ? $item->assigned->name : 'N/A' }} |
@if ($item->planner_id)
| **Task Date**  | {{ ($item->planner) ? $item->planner->from->format('d/m/Y') : '' }} |
@endif



@component('mail::button', ['url' => config('app.url').'/site/foc/'.$item->foc->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
