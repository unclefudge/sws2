{{-- @formatter:off --}}
@component('mail::message')
# Equipment Material Transfer

A transfer of Equipment with the following details:

|        |        |
| ------:|--------|
| **Item**  | {{ $item->item_name  }} |
| **Qty**  | {{ $qty  }} |
| **Site** | {{ $site->name  }} |
| **Date/Time** | {{ $item->updated_at->format('d/m/Y g:i a')  }} |
| **Created By** | {{ ($item->user) ? $item->user->name : 'n/a' }} |

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent