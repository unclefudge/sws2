{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Practical Completion Notification

A practical completion has been assigned for {{ $item->prac->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $item->prac->id  }} |
| **Site Name**  | {{ $item->prac->site->name  }} |
| **Site Address**  | {{ $item->prac->site->address }}, {{ $item->prac->site->SuburbStatePostcode }} |
| **Assigned Supervisor**  | {{ ($item->prac->super_id) ? $item->prac->supervisor->name : '-' }} |
| **Assigned Task(s)**  | {{ ($item->assigned_to) ? $item->assigned->name : 'N/A' }} |
@if ($item->planner_id)
| **Task Date**  | {{ ($item->planner) ? $item->planner->from->format('d/m/Y') : '' }} |
@endif



@component('mail::button', ['url' => config('app.url').'/site/prac-completion/'.$item->prac->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
