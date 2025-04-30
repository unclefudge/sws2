{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Zoho Site Sync

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $site->id  }} |
| **Site Name**  | {{ $site->name  }} |

**Zoho Data**
{!! var_dump($zoho) !!}


**Difference
{!! var_dump($diff) !!}
@component('mail::button', ['url' => config('app.url').'/site/'.$site->id])
View Site
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
