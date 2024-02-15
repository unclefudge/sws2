{{-- @formatter:off --}}
@component('mail::message')
# Supervisor Checklist

You haven't completed your Supervisor Checkist for the day. Please complete it by end of day.

@component('mail::button', ['url' => config('app.url').'/supervisor/checklist/'])
View Checklist
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent