@component('mail::message')
# Support Ticket Updated

A support ticket has been updated on {{ config('app.name') }} with the following details:

|        |        |
| ------:|--------|
| **Ticket ID**  | {{ $ticket->id  }} |
| **Priority**  | {{ $ticket->priority_text  }} |
| **Name** | {{ $ticket->name  }} |
| **Action** | {!! nl2br2($action->action) !!} |
| **Date/Time** | {{ $action->created_at->format('d/m/Y g:i a')  }} |
| **Created By** | {!! (\App\User::find($action->created_by)) ? \App\User::find($action->created_by)->name : '' !!} |

@component('mail::button', ['url' => config('app.url').'/support/ticket/'.$ticket->id])
View Ticket
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent