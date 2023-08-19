@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# {!! ($action == 'new') ? 'New Site Created' : 'Site Updated' !!}

{!! ($action == 'new') ? "A new site $site->name has been created" : "Site $site->name has updated it's status to ".$site->statusText() !!}.

|                       |        |
| ---------------------:|--------|
| **ID**  | {{ $site->id  }} |
| **Job #**  | {{ $site->code  }} |
| **Site Name**  | {{ $site->name  }} |
| **Address**  | {{ $site->address }}, {{ $site->SuburbStatePostcode }} |
| **Supervisor**  | {{ $site->supervisorName }} |
| **Status**  | {!! $site->statusText() !!} |
| **Updated by**  | {!! \App\User::find($site->updated_by)->name !!} |
| **Updated at**  | {{ $site->updated_at->format('d/m/Y g:i a') }} |

@component('mail::button', ['url' => config('app.url').'/site/'.$site->slug])
View Site
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
