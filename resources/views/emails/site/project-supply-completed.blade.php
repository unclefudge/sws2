@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Project Supply Completed

A Project Supply Information has been completed for {{ $project->site->name }}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $project->code  }} |
| **Site Name**  | {{ $project->site->name  }} |
| **Site Address**  | {{ $project->site->address }}, {{ $project->site->SuburbStatePostcode }} |



@component('mail::button', ['url' => config('app.url').'/site/supply/'.$project->id])
View Details
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
