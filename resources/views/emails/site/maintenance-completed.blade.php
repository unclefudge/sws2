@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Maintenance Request Completed

A maintenance request has been completed for {{ $main->site->name }}.

An AfterCare Form is {{ ($main->ac_form_required) ? 'required' : 'not required' }}

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $main->code  }} |
| **Site Name**  | {{ $main->site->name  }} |
| **Site Address**  | {{ $main->site->address }}, {{ $main->site->SuburbStatePostcode }} |



@component('mail::button', ['url' => config('app.url').'/site/maintenance/'.$main->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
