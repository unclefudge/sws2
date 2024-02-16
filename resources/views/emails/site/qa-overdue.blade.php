{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Quality Assurance Overdue Notification

A Quality Assurance is currently overdue for {{ $qa->name }} on site {{ $qa->site->name }}.

|                       |        |
| ---------------------:|--------|
| **Site Name**  | {{ $qa->site->name  }} |
| **Site Address**  | {{ $qa->site->address }}, {{ $qa->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $qa->site->supervisorName }} |
| **QA Name**  | {{ $qa->name  }} |


@component('mail::button', ['url' => config('app.url').'/site/qa/'.$qa->id])
View QA
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent