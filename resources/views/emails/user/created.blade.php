{{-- @formatter:off --}}
@component('mail::message')
# New User

A new user has been created on {{ config('app.name') }} with the following details:

|        |        |
| ------:|--------|
| **Username**  | {{ $user->username  }} |
| **Name**  | {{ $user->name  }} |
| **Company** | {{ $user->company->name  }} |
| **Created By** | {{ $created_by->name  }} |

@component('mail::button', ['url' => config('app.url').'/user/'.$user->id])
View User
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
