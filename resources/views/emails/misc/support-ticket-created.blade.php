@component('mail::message')
# New Support Ticket

A new support ticket has been created on {{ config('app.name') }} with the following details:

|        |        |
| ------:|--------|
| **Ticket ID**  | {{ $ticket->id  }} |
| **Priority**  | {{ $ticket->priority_text  }} |
| **Name** | {{ $ticket->name  }} |
| **Description** | {!! nl2br2($ticket->summary) !!} |
| **Date/Time** | {{ $ticket->created_at->format('d/m/Y g:i a')  }} |
| **Created By** | {{ $ticket->createdBy->name  }} |

@component('mail::button', ['url' => config('app.url').'/support/ticket/'.$ticket->id])
View Ticket
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent