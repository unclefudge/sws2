{{-- @formatter:off --}}
@component('mail::message')
# ToDo Task Reminder

The task **{{ $todo->name  }}** is still outstanding

Task Details: {{ $todo->info }}


@component('mail::button', ['url' => config('app.url').$todo->url()])
View Task
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent

