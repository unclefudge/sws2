{{-- @formatter:off --}}
@component('mail::message')
# Password Reset Request

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => config('app.url').'/password/reset/r?token='.$token])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Regards,
{{ config('app.name') }}
@endcomponent
