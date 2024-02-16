{{-- @formatter:off --}}
@component('mail::message')
# Welcome to SafeWorksite

{{ $user->firstname }},

{{ $user->company->name }} has created an account for you with SafeWorksite.

SafeWorksite is an online WHS platform that supports you and your workers in staying safe. To be able to perform any work on a site undertaken by {{ $user->company->name }} you are required to sign into SafeWorksite each time you attend the site.

Your Account Details are:

Login: {{ $user->username }}<br>
Password: {{ $password }}

@component('mail::button', ['url' => config('app.url').'/login'])
Sign In
@endcomponent

If you have any questions in regards to SafeWorksite you may contact {{ $user->company->name }} on {{ $user->company->phone }}

Regards,
{{ config('app.name') }}
@endcomponent
