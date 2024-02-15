{{-- @formatter:off --}}
@component('mail::message')
# Company Updated Trades

{{ $company->name }} had their Trades updated.

|        |        |
| ------:|--------|
| **Name**  | {{ $company->name  }} |
| **Trades**  | {{ $company->tradesSkilledInSBC() }} |
| **Licence Overridden**  | {{ ($company->lic_override) ? 'Yes' : 'No' }} |
| **Updated By** | {{ $company->updatedBy->name  }} |


@component('mail::button', ['url' => config('app.url').'/company/'.$company->id])
    View Company
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
