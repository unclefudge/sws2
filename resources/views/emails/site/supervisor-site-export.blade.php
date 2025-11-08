{{-- @formatter:off --}}
@component('mail::message')
    # Supervisor Site Exports

    Please find attached planners for each supervisor for next 4 weeks.

    Regards,
    {{ config('app.name') }}
@endcomponent
