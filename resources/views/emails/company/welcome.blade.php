{{-- @formatter:off --}}
@component('mail::message')
# Welcome to SafeWorksite

{{ $name }},

Your company {{ $company->name }} has been invited to join SafeWorksite by <b>{{ $parent_company->name }}</b>.

SafeWorksite is an online WHS platform that supports you and your workers in staying safe. To be able to perform any work on a site managed by {{ $parent_company->name }} you are required to sign up and register any workers within your company.

@component('mail::button', ['url' => config('app.url').'/signup/ref/'.$company->signup_key])
    Sign Up
@endcomponent

If you have any questions in regards to your sign up on SafeWorksite, please contact {{ $parent_company->name }}.

Regards,
{{ config('app.name') }}
@endcomponent
