{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Maintenance Request Notification

A maintenance item has been assigned for {{ $item->maintenance->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $item->maintenance->code  }} |
| **Site Name**  | {{ $item->maintenance->site->name  }} |
| **Site Address**  | {{ $item->maintenance->site->address }}, {{ $item->maintenance->site->SuburbStatePostcode }} |
| **Assigned to**  | {{ ($item->assigned_to) ? $item->assigned->name : 'N/A' }} |
@if ($item->planner_id)
| **Task Date**  | {{ ($item->planner) ? $item->planner->from->format('d/m/Y') : '' }} |
@endif



@component('mail::button', ['url' => config('app.url').'/site/maintenance/'.$item->maintenance->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
