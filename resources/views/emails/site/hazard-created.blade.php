{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Hazard Notification

A hazard report has been lodged for {{ $hazard->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $hazard->id  }} |
| **Site Name**  | {{ $hazard->site->name  }} |
| **Site Address**  | {{ $hazard->site->address }}, {{ $hazard->site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $hazard->site->supervisorName }} |
| **Rating**  | {{ $hazard->ratingText }} |
| **Location**  | {!! nl2br2($hazard->location) !!} |
| **Reason**  | {!! nl2br($hazard->reason) !!} |
| **Actions Taken**  | {!! nl2br2($action->action); !!} |
| **Submitted by**  | {{ $hazard->createdBy->name }} ({{ $hazard->createdBy->company->name }}) |
| **Submitted at**  | {{ $hazard->created_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/hazard/'.$hazard->id])
View Hazard
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
