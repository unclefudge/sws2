{{-- @formatter:off --}}
@component('mail::message')

# Job Start Notification

{!! ($olddate) ? "A Job Start has been moved on **$site->name** from $olddate to $newdate with ($supers) allocated as Supervisor." : "A Job Start has been created for **$site->name** on $newdate with ($supers) allocated as Supervisor."!!}

@component('mail::button', ['url' => config('app.url').'/planner/site/'.$site->id])
View Planner
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
