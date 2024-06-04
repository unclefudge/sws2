{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Practical Completion Completed

A prac completion has been completed for {{ $prac->site->name }}.

An AfterCare Form is {{ ($main->ac_form_required) ? 'required' : 'not required' }}

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $prac->id  }} |
| **Site Name**  | {{ $prac->site->name  }} |
| **Site Address**  | {{ $prac->site->address }}, {{ $prac->site->SuburbStatePostcode }} |



@component('mail::button', ['url' => config('app.url').'/site/prac-completion/'.$prac->id])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
