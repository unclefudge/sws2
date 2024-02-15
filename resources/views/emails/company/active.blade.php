{{-- @formatter:off --}}
@component('mail::message')
# Company Re-Activated

{{ $company->name }} has been reactivated {{ (Auth::check()) ? 'by '. Auth::user()->fullname: '' }}

|        |        |
| ------:|--------|
| **Name**  | {{ $company->name  }} |
| **Phone**  | {{ $company->phone  }} |
| **Email**  | {{ $company->email  }} |
| **Address**  | {{ $company->address  }} {{ $company->SuburbStatePostcode }} |
| **Primary Contact**  | {{ $company->primary_contact()->fullname  }} @if ($company->phone) ({{ $company->phone  }}) @endif |

----

The following users were also reactivated:

|  Username  | Name  | Email  | Phone
| -----------|-------|--------|--------|
@foreach ($company->staffStatus(1) as $staff)
| {{ $staff->username }}  | {{ $staff->fullname }} | {{ $staff->email }} | {{ $staff->phone }}
@endforeach

@component('mail::button', ['url' => config('app.url').'/company/'.$company->id])
View Company
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
