{{-- @formatter:off --}}
@component('mail::message')
# Company Updated Details

{{ $company->name }} has updated their Company Details.

|        |        |
| ------:|--------|
| **Name**  | {{ $company->name  }} |
| **Phone**  | {{ $company->phone  }} |
| **Email**  | {{ $company->email  }} |
| **Address**  | {{ $company->address  }} {{ $company->SuburbStatePostcode }} |
| **Primary Contact**  | {{ $company->primary_contact()->fullname  }} @if ($company->primary_contact() && $company->primary_contact()->phone) ({{ $company->primary_contact()->phone  }}) @endif |
| **Secondary Contact**  | {{ ($company->secondary_contact()) ? $company->secondary_contact()->fullname : 'none' }} @if ($company->secondary_contact() && $company->secondary_contact()->phone) ({{ $company->secondary_contact()->phone  }})@endif |
| **Updated By** | {{ $company->updatedBy->name  }} |


@component('mail::button', ['url' => config('app.url').'/company/'.$company->id])
View Company
@endcomponent

Regards,
{{ config('app.name') }}
@endcomponent
