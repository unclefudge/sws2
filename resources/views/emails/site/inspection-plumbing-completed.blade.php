@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width: 20%;
    }
</style>

# Inspection Report Completed

A inspection report has been completed for {{ $report->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $report->id  }} |
| **TYPE** | Plumbing |
| **Site Name**  | {{ $report->site->name  }} |
| **Site Address**  | {{ $report->site->address }}, {{ $report->site->SuburbStatePostcode }} |
| **Inspection Date**  | {{ ($report->inspected_at) ? $report->inspected_at->format('d/mY') : '-' }} |
| **Client Contacted**  | {{ ($report->client_contacted) ? $report->client_contacted->format('d/mY') : '-' }} |
| &nbsp;  | &nbsp; |

**Notes**

---

@foreach($report->actions->sortByDesc('created_at') as $action)
    {{ $action->created_at->format('d/m/Y') }} - {{ $action->user->full_name }}
    {{ $action->action }}

@endforeach


@component('mail::button', ['url' => config('app.url').'/site/inspection/plumbing/'.$report->id])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
