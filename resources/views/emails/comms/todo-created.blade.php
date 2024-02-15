{{-- @formatter:off --}}
@component('mail::message')
# ToDo Notification {!! ($todo->due_at && $todo->due_at->lt(Carbon\Carbon::today()) ? ' - OVERDUE' : '') !!}

A task has been sent to you with the following details:

**{{ $todo->name  }}**

Assigned to: {!! $todo->assignedToBySBC() !!}

{!! ($todo->due_at) ? 'Task due: '.$todo->due_at->format('d/m/Y') : '' !!}

{!! $todo->info !!}


@component('mail::button', ['url' => config('app.url').$todo->url()])
View Task
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent

