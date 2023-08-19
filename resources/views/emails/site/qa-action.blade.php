@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Quality Assurance Updated

A Quality Assurance has been updated for {{ $qa->name }} on site {{ $qa->site->name }}.

|                       |        |
| ---------------------:|--------|
| **Site Name**  | {{ $qa->site->name  }} |
| **Site Address**  | {{ $qa->site->address }}, {{ $qa->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $qa->site->supervisorName }} |
| **QA Name**  | {{ $qa->name  }} |
| **Actions Taken**  | {{ $action->action }} |
| **Submitted by**  | {{ $action->user->name }} ({{ $action->user->company->name }}) |
| **Submitted at**  | {{ $action->created_at->format('d/m/Y g:i a') }} |


@component('mail::button', ['url' => config('app.url').'/site/qa/'.$qa->id])
View QA
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
