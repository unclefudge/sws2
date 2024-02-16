{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width: 20%;
    }
</style>

# Electrical Inspection Report

Please find attached the Electrical Inspection Report for {{ $report->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $report->id  }} |
| **TYPE** | Plumbing |
| **Site Name**  | {{ $report->site->name  }} |
| **Site Address**  | {{ $report->site->address }}, {{ $report->site->SuburbStatePostcode }} |


@component('mail::button', ['url' => config('app.url').'/site/inspection/electrical/'.$report->id])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
