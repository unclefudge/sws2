{{-- @formatter:off --}}
@component('mail::message')
# ToDo Task Completed

The task **{{ $todo->name  }}** has been completed by **{{ $todo->doneBy->fullname }}**

Task Details: {{ $todo->info }}


@component('mail::button', ['url' => config('app.url').$todo->url()])
View Task
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent

