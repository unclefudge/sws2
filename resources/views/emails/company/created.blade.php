@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
{{-- @formatter:off --}}
@component('mail::message')
# New Company Created

{{ $company->name }} has been sent a request to join SafeWorksite.

|        |        |
| ------:|--------|
| **Company Name**  | {{ $company->name  }} |
| **Persons Name**  | {{ $company->nickname  }} |
| **Email**  | {{ $company->email  }} |
| **Category**  | {{ $companyTypes::name($company->category) }} |
| **Trades**  | {{ $company->tradesSkilledInSBC()  }} |
| **Created By** | {{ $company->createdBy->name  }} |


Regards,
{{ config('app.name') }}
@endcomponent
