{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# FOC Requirements Completed

The FOC List Items for {{ $foc->site->name }} have now been completed for you to process your next steps.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $foc->id  }} |
| **Site Name**  | {{ $foc->site->name  }} |
| **Site Address**  | {{ $foc->site->address }}, {{ $foc->site->SuburbStatePostcode }} |



@component('mail::button', ['url' => config('app.url').'/site/foc/'.$foc->id])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
