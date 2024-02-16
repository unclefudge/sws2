{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Quality Assurance Handover

A QA Handover has been been completed for {{ $qa->site->name }}.

|                       |        |
| ---------------------:|--------|
| **Site Name**  | {{ $qa->site->name  }} |
| **Site Address**  | {{ $qa->site->address }}, {{ $qa->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $qa->site->supervisorName }} |


@component('mail::button', ['url' => config('app.url').'/site/export/qa'])
Export QA
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent