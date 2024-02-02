@component('mail::message')
    # Company Updated Business Creditor Code

    {{ $company->name }} has updated their Creditor Code to: {{ $company->creditor_code }}

    Regards,
    {{ config('app.name') }}
@endcomponent
