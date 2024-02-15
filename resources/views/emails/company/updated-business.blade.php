@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
{{-- @formatter:off --}}
@component('mail::message')
# Company Updated Business Details

{{ $company->name }} has updated their Company Details.

|        |        |
| ------:|--------|
| **Name**  | {{ $company->name  }} |
| **Business Entity**  | {{ ($company->business_entity) ? $companyEntityTypes::name($company->business_entity) : '-' }} |
| **Category**  | {{ $companyTypes::name($company->category) }} |
| **ABN**  | {{ $company->abn  }} |
| **GST**  | @if($company->gst) Yes @elseif($company->gst == '0') No @else - @endif |
| **Updated By** | {{ $company->updatedBy->name  }} |


@component('mail::button', ['url' => config('app.url').'/company/'.$company->id])
    View Company
@endcomponent

Regards,
{{ config('app.name') }}
@endcomponent
